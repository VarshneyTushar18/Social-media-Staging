<?php

namespace Modules\AppWebsiteBuilder\Support;

use Illuminate\Support\Facades\View;
use Modules\AppWebsiteBuilder\Models\WebsiteSite;

class WebsiteLandingHtmlExporter
{
    public static function forSite(WebsiteSite $site, array $content): string
    {
        $content = self::withAbsoluteImageUrls($content);

        return trim(View::make('appwebsitebuilder::exports.landing-html', [
            'site' => $site,
            'content' => $content,
        ])->render());
    }

    /**
     * @param  array<string, mixed>  $content
     * @return array<string, mixed>
     */
    public static function withAbsoluteImageUrls(array $content): array
    {
        /** @var array<string, mixed> $normalized */
        $normalized = self::absolutizeImageUrlsRecursive($content);

        return $normalized;
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected static function absolutizeImageUrlsRecursive(array $data): array
    {
        foreach ($data as $key => $value) {
            if ($key === 'image_url' && is_string($value)) {
                $data[$key] = self::absoluteImageUrl($value);

                continue;
            }

            if (is_array($value)) {
                $data[$key] = self::absolutizeImageUrlsRecursive($value);
            }
        }

        if (isset($data['hero_image_url']) && is_string($data['hero_image_url'])) {
            $data['hero_image_url'] = self::absoluteImageUrl($data['hero_image_url']);
        }

        return $data;
    }

    public static function absoluteImageUrl(string $url): string
    {
        $url = trim($url);

        if ($url === '') {
            return '';
        }

        if (str_starts_with($url, 'http://') || str_starts_with($url, 'https://')) {
            return $url;
        }

        if (str_starts_with($url, '//')) {
            return 'https:'.$url;
        }

        if (str_starts_with($url, '/')) {
            return url($url);
        }

        return url('/'.ltrim($url, '/'));
    }
}