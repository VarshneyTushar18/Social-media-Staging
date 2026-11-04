<?php

namespace Modules\AppFiles\Support\Google;

use RuntimeException;

class GoogleDriveAccess
{
    public function __construct(
        protected PublishDriveOAuthService $oauth,
    ) {}

    public function canAuthenticate(): bool
    {
        return $this->oauth->isReady();
    }

    public function getAccessToken(): string
    {
        if ($this->oauth->isReady()) {
            return $this->oauth->getAccessToken();
        }

        throw new RuntimeException('Google Drive backup is not configured. Connect your Google account in File Manager settings.');
    }

    public function usesOAuth(): bool
    {
        return true;
    }
}
