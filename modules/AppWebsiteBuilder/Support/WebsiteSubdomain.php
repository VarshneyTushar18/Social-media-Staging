<?php

namespace Modules\AppWebsiteBuilder\Support;

class WebsiteSubdomain
{
    /**
     * @var list<string>
     */
    public const RESERVED = [
        'admin',
        'api',
        'app',
        'dashboard',
        'domains',
        'ftp',
        'livewire',
        'login',
        'mail',
        'portal',
        'register',
        'sites',
        'staging',
        'static',
        'support',
        'www',
    ];

    public static function normalize(string $value): string
    {
        $slug = strtolower(trim($value));
        $slug = (string) preg_replace('/[^a-z0-9-]+/', '-', $slug);
        $slug = (string) preg_replace('/-+/', '-', $slug);

        return trim($slug, '-');
    }

    public static function suggestFromLabel(string $label): string
    {
        return self::normalize($label);
    }

    public static function validationError(string $slug): ?string
    {
        if ($slug === '') {
            return __('Enter a subdomain name.');
        }

        if (strlen($slug) < 3) {
            return __('Subdomain must be at least 3 characters.');
        }

        if (strlen($slug) > 63) {
            return __('Subdomain cannot exceed 63 characters.');
        }

        if (! preg_match('/^[a-z0-9](?:[a-z0-9-]*[a-z0-9])?$/', $slug)) {
            return __('Use lowercase letters, numbers, and hyphens only. Cannot start or end with a hyphen.');
        }

        if (in_array($slug, self::RESERVED, true)) {
            return __('That subdomain is reserved. Try another name.');
        }

        return null;
    }
}
