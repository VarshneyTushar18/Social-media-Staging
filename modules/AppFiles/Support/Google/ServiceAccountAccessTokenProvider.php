<?php

namespace Modules\AppFiles\Support\Google;

use Illuminate\Support\Facades\Http;
use RuntimeException;

class ServiceAccountAccessTokenProvider
{
    /**
     * @param  list<string>  $scopes
     */
    public function getAccessToken(array $credentials, array $scopes): string
    {
        $clientEmail = trim((string) ($credentials['client_email'] ?? ''));
        $privateKey = (string) ($credentials['private_key'] ?? '');

        if ($clientEmail === '' || $privateKey === '') {
            throw new RuntimeException('Google service account credentials are incomplete.');
        }

        $jwt = $this->buildJwt($clientEmail, $privateKey, $scopes);
        $response = Http::asForm()
            ->timeout(30)
            ->post('https://oauth2.googleapis.com/token', [
                'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
                'assertion' => $jwt,
            ]);

        if (! $response->successful()) {
            throw new RuntimeException('Google service account authentication failed.');
        }

        $token = trim((string) $response->json('access_token', ''));

        if ($token === '') {
            throw new RuntimeException('Google service account returned no access token.');
        }

        return $token;
    }

    /**
     * @param  list<string>  $scopes
     */
    protected function buildJwt(string $clientEmail, string $privateKey, array $scopes): string
    {
        $now = time();
        $header = $this->base64UrlEncode(json_encode(['alg' => 'RS256', 'typ' => 'JWT'], JSON_THROW_ON_ERROR));
        $payload = $this->base64UrlEncode(json_encode([
            'iss' => $clientEmail,
            'scope' => implode(' ', array_values(array_filter(array_map('trim', $scopes)))),
            'aud' => 'https://oauth2.googleapis.com/token',
            'iat' => $now,
            'exp' => $now + 3600,
        ], JSON_THROW_ON_ERROR));

        $unsigned = $header.'.'.$payload;
        $signature = '';

        $key = openssl_pkey_get_private($privateKey);

        if ($key === false) {
            throw new RuntimeException('Google service account private key is invalid.');
        }

        $signed = openssl_sign($unsigned, $signature, $key, OPENSSL_ALGO_SHA256);

        if (! $signed) {
            throw new RuntimeException('Could not sign Google service account JWT.');
        }

        return $unsigned.'.'.$this->base64UrlEncode($signature);
    }

    protected function base64UrlEncode(string $value): string
    {
        return rtrim(strtr(base64_encode($value), '+/', '-_'), '=');
    }
}
