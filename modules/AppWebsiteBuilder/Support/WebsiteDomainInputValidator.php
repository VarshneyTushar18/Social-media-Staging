<?php

namespace Modules\AppWebsiteBuilder\Support;

use Illuminate\Support\Str;

class WebsiteDomainInputValidator
{
  /**
     * Trim and normalize raw search/domain input (not necessarily a FQDN).
     */
    public static function cleanSearchInput(string $input): string
    {
        $value = strtolower(trim($input));
        $value = preg_replace('/^https?:\/\//', '', $value) ?? $value;
        $value = preg_replace('/^www\./', '', $value) ?? $value;
        $value = preg_replace('/\/.*$/', '', $value) ?? $value;
        $value = preg_replace('/\s+/', '', $value) ?? $value;

        return Str::limit(trim($value, '.-'), 253, '');
    }

    /**
     * Validate search box input (keywords or full domain). Returns an error message or null.
     */
    public static function validateSearchInput(string $input): ?string
    {
        $value = self::cleanSearchInput($input);

        if ($value === '') {
            return __('Please enter a domain name first.');
        }

        if (strlen($value) > 253) {
            return __('Domain is too long');
        }

        if (str_contains($value, '.')) {
            if (! self::isValidFqdn($value)) {
                return __('Enter a valid domain, like yourbrand.com');
            }
        } elseif (! preg_match('/^[a-z0-9-]+$/', $value)) {
            return __('Use only letters, numbers and hyphens');
        }

        return null;
    }

    public static function normalizeRegisteredDomain(string $domain): string
    {
        return WebsiteCustomDomain::normalize($domain);
    }

    /**
     * Full domain required (for publish / select). Returns normalized domain or throws ValidationException-style message via null + message.
     *
     * @return array{domain: string, error: null}|array{domain: null, error: string}
     */
    public static function validateRegisteredDomain(string $domain): array
    {
        $normalized = self::normalizeRegisteredDomain($domain);

        if ($normalized === '') {
            return ['domain' => null, 'error' => __('Enter a valid domain, like yourbrand.com')];
        }

        if (! self::isValidFqdn($normalized)) {
            return ['domain' => null, 'error' => __('Enter a valid domain, like yourbrand.com')];
        }

        return ['domain' => $normalized, 'error' => null];
    }

    public static function isValidFqdn(string $domain): bool
    {
        $domain = strtolower(trim($domain));

        if ($domain === '' || strlen($domain) > 253) {
            return false;
        }

        return (bool) preg_match(
            '/^(?!-)[a-z0-9-]{1,63}(?<!-)(\.[a-z0-9-]{1,63})*\.[a-z]{2,}$/',
            $domain
        );
    }

    public static function isForbiddenHostOrIp(string $target): bool
    {
        $target = strtolower(rtrim(trim($target), '.'));

        if ($target === '' || in_array($target, ['localhost', '127.0.0.1', '0.0.0.0', '::1'], true)) {
            return true;
        }

        if (filter_var($target, FILTER_VALIDATE_IP) !== false) {
            return filter_var(
                $target,
                FILTER_VALIDATE_IP,
                FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE
            ) === false;
        }

        if (str_ends_with($target, '.localhost') || str_contains($target, 'localhost')) {
            return true;
        }

        return false;
    }
}