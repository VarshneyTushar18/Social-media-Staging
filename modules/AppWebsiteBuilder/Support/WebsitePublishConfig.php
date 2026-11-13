<?php

namespace Modules\AppWebsiteBuilder\Support;

class WebsitePublishConfig
{
    public static function publishDomain(): string
    {
        return strtolower(trim((string) config('modules.appwebsitebuilder.publish_domain', '')));
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
