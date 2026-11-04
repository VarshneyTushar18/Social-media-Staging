<?php

namespace Modules\AppFiles\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Str;
use Modules\AppFiles\Support\Google\PublishDriveOAuthService;
use Throwable;

class PublishDriveOAuthController extends Controller
{
    public function __construct(
        protected PublishDriveOAuthService $oauth,
    ) {}

    public function connect(Request $request): RedirectResponse
    {
        if ($this->oauth->clientId() === '' || $this->oauth->clientSecret() === '') {
            return redirect()
                ->route('settings.files')
                ->with('error', __('Add Client ID and Web client secret, save settings, then connect.'));
        }

        $oauthState = Str::random(40);

        $request->session()->put('appfiles_publish_drive_oauth', [
            'state' => $oauthState,
            'user_id' => auth()->id(),
        ]);

        return redirect()->away($this->oauth->buildAuthorizationUrl($oauthState));
    }

    public function callback(Request $request): RedirectResponse
    {
        $context = (array) $request->session()->pull('appfiles_publish_drive_oauth', []);

        if ($context === [] || ($context['user_id'] ?? null) !== auth()->id()) {
            return redirect()
                ->route('settings.files')
                ->with('error', __('The Google connection session expired. Please try again.'));
        }

        if (($context['state'] ?? null) !== $request->string('state')->toString()) {
            return redirect()
                ->route('settings.files')
                ->with('error', __('Invalid Google connection state. Please try again.'));
        }

        if ($request->filled('error')) {
            return redirect()
                ->route('settings.files')
                ->with('error', $request->string('error_description')->toString() ?: __('Google authorization was cancelled.'));
        }

        $code = $request->string('code')->toString();

        if ($code === '') {
            return redirect()
                ->route('settings.files')
                ->with('error', __('Google did not return an authorization code.'));
        }

        try {
            $token = $this->oauth->exchangeCode($code);
            $email = $this->oauth->fetchUserEmail($token['access_token']);
            $this->oauth->storeTokens($token['refresh_token'], $email !== '' ? $email : null);
        } catch (Throwable $exception) {
            return redirect()
                ->route('settings.files')
                ->with('error', $exception->getMessage());
        }

        return redirect()
            ->route('settings.files')
            ->with('success', __('Google account connected.'));
    }

    public function disconnect(): RedirectResponse
    {
        $this->oauth->disconnect();

        return redirect()
            ->route('settings.files')
            ->with('success', __('Google account disconnected.'));
    }
}
