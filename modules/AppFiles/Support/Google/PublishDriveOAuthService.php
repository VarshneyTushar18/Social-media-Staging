<?php

namespace Modules\AppFiles\Support\Google;

use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Http;
use Modules\AdminSettings\Support\OptionStore;
use RuntimeException;
use Throwable;

class PublishDriveOAuthService
{
    /** Full Drive scope so a user-chosen parent folder ID (created in drive.google.com) is accessible for uploads. */
    public const SCOPE = 'https://www.googleapis.com/auth/drive https://www.googleapis.com/auth/userinfo.email';

    public function __construct(
        protected OptionStore $options,
    ) {}

    public function redirectUri(): string
    {
        return url('integrations/google/publish-drive/callback');
    }

    public function clientId(): string
    {
        return trim((string) $this->options->get('appfiles_google_drive_client_id', ''));
    }

    public function clientSecret(): string
    {
        return trim((string) $this->options->get('appfiles_publish_drive_oauth_client_secret', ''));
    }

    public function connectedEmail(): string
    {
        return trim((string) $this->options->get('appfiles_publish_drive_oauth_email', ''));
    }

    public function hasRefreshToken(): bool
    {
        return $this->decryptedRefreshToken() !== '';
    }

    public function isReady(): bool
    {
        return $this->clientId() !== ''
            && $this->clientSecret() !== ''
            && $this->hasRefreshToken();
    }

    public function buildAuthorizationUrl(string $state): string
    {
        if ($this->clientId() === '') {
            throw new RuntimeException('Google OAuth Client ID is missing in File Manager settings.');
        }

        $query = http_build_query([
            'client_id' => $this->clientId(),
            'redirect_uri' => $this->redirectUri(),
            'response_type' => 'code',
            'scope' => self::SCOPE,
            'access_type' => 'offline',
            'prompt' => 'consent',
            'state' => $state,
        ]);

        return 'https://accounts.google.com/o/oauth2/v2/auth?'.$query;
    }

    public function exchangeCode(string $code): array
    {
        $response = Http::asForm()
            ->timeout(30)
            ->post('https://oauth2.googleapis.com/token', [
                'code' => $code,
                'client_id' => $this->clientId(),
                'client_secret' => $this->clientSecret(),
                'redirect_uri' => $this->redirectUri(),
                'grant_type' => 'authorization_code',
            ]);

        if (! $response->successful()) {
            $detail = trim((string) $response->json('error_description', $response->json('error', '')));

            throw new RuntimeException(
                $detail !== ''
                    ? 'Google OAuth token exchange failed: '.$detail
                    : 'Google OAuth token exchange failed.',
            );
        }

        $accessToken = trim((string) $response->json('access_token', ''));
        $refreshToken = trim((string) $response->json('refresh_token', ''));
        $expiresIn = (int) $response->json('expires_in', 0);

        if ($accessToken === '') {
            throw new RuntimeException('Google OAuth did not return an access token.');
        }

        if ($refreshToken === '') {
            throw new RuntimeException('Google OAuth did not return a refresh token. Revoke app access in your Google account and connect again.');
        }

        return [
            'access_token' => $accessToken,
            'refresh_token' => $refreshToken,
            'expires_in' => $expiresIn,
        ];
    }

    public function storeTokens(string $refreshToken, ?string $email = null): void
    {
        $this->options->set('appfiles_publish_drive_oauth_refresh_token', Crypt::encryptString($refreshToken));

        if ($email !== null && trim($email) !== '') {
            $this->options->set('appfiles_publish_drive_oauth_email', trim($email));
        }
    }

    public function disconnect(): void
    {
        $this->options->set('appfiles_publish_drive_oauth_refresh_token', '');
        $this->options->set('appfiles_publish_drive_oauth_email', '');
    }

    public function getAccessToken(): string
    {
        $refreshToken = $this->decryptedRefreshToken();

        if ($refreshToken === '') {
            throw new RuntimeException('Google Drive backup is not connected. Connect your Google account in File Manager settings.');
        }

        if ($this->clientId() === '' || $this->clientSecret() === '') {
            throw new RuntimeException('Google OAuth Client ID and Client Secret are required for Drive backup.');
        }

        $response = Http::asForm()
            ->timeout(30)
            ->post('https://oauth2.googleapis.com/token', [
                'client_id' => $this->clientId(),
                'client_secret' => $this->clientSecret(),
                'refresh_token' => $refreshToken,
                'grant_type' => 'refresh_token',
            ]);

        if (! $response->successful()) {
            $detail = trim((string) $response->json('error_description', $response->json('error', '')));

            throw new RuntimeException(
                $detail !== ''
                    ? 'Google OAuth refresh failed: '.$detail
                    : 'Google OAuth refresh failed. Reconnect your Google account.',
            );
        }

        $accessToken = trim((string) $response->json('access_token', ''));
        $nextRefresh = trim((string) $response->json('refresh_token', ''));

        if ($nextRefresh !== '') {
            $this->storeTokens($nextRefresh);
        }

        if ($accessToken === '') {
            throw new RuntimeException('Google OAuth refresh returned no access token.');
        }

        return $accessToken;
    }

    public function fetchUserEmail(string $accessToken): string
    {
        $response = Http::withToken($accessToken)
            ->acceptJson()
            ->timeout(15)
            ->get('https://www.googleapis.com/oauth2/v2/userinfo');

        if (! $response->successful()) {
            return '';
        }

        return trim((string) $response->json('email', ''));
    }

    protected function decryptedRefreshToken(): string
    {
        $stored = trim((string) $this->options->get('appfiles_publish_drive_oauth_refresh_token', ''));

        if ($stored === '') {
            return '';
        }

        try {
            return trim(Crypt::decryptString($stored));
        } catch (Throwable) {
            return '';
        }
    }
}

