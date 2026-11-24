<?php

namespace Modules\AppWebsiteBuilder\Support;

class WebsitePublishConfig
{
    public static function configuredPublishDomain(): string
    {
        return strtolower(trim((string) config('modules.appwebsitebuilder.publish_domain', '')));
    }

    /**
     * Path URLs ({APP_URL}/sites/{slug}) for local artisan serve; host subdomains in production.
     */
    public static function usesPathUrls(): bool
    {
        if (self::configuredPublishDomain() === '') {
            return true;
        }

        $host = strtolower((string) (parse_url((string) config('app.url'), PHP_URL_HOST) ?: ''));

        return in_array($host, ['localhost', '127.0.0.1', '[::1]'], true);
    }

    public static function publishDomain(): string
    {
        return self::usesPathUrls() ? '' : self::configuredPublishDomain();
    }

    public static function pathPrefix(): string
    {
        $prefix = trim((string) config('modules.appwebsitebuilder.publish_path_prefix', 'sites'), '/');

        return $prefix !== '' ? $prefix : 'sites';
    }

    /**
     * Pattern for UI preview, containing literal "{slug}".
     */
    public static function urlPattern(): string
    {
        $slugPlaceholder = '{slug}';
        $domain = self::publishDomain();

        if ($domain !== '') {
            $scheme = parse_url((string) config('app.url'), PHP_URL_SCHEME) ?: 'https';

            return $scheme.'://'.$slugPlaceholder.'.'.$domain;
        }

        return rtrim(url(self::pathPrefix()), '/').'/'.$slugPlaceholder;
    }

    public static function publicUrl(?string $slug): ?string
    {
        if ($slug === null || $slug === '') {
            return null;
        }

        $domain = self::publishDomain();

        if ($domain !== '') {
            $scheme = parse_url((string) config('app.url'), PHP_URL_SCHEME) ?: 'https';

            return $scheme.'://'.$slug.'.'.$domain;
        }

        return url(self::pathPrefix().'/'.$slug);
    }

    public static function routeName(): string
    {
        return self::publishDomain() !== ''
            ? 'websites.published.subdomain'
            : 'websites.published.show';
    }
}
