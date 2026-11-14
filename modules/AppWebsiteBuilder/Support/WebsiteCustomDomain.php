<?php

namespace Modules\AppWebsiteBuilder\Support;

class WebsiteCustomDomain
{
    public static function normalize(?string $host): string
    {
        $host = strtolower(trim((string) $host));
        $host = preg_replace('/^(https?:\/\/)?(www\.)?/', '', $host) ?? $host;
        $host = rtrim($host, '.');

        return $host;
    }

    /**
     * @return list<array{type: string, host: string, points_to: string, ttl: string, notes: ?string}>
     */
    public static function dnsRecords(): array
    {
        $publishDomain = WebsitePublishConfig::publishDomain();
        $appUrl = (string) config('app.url');
        $appHost = parse_url($appUrl, PHP_URL_HOST) ?: 'your-app-host.example.com';
        $appHost = strtolower((string) $appHost);

        $aRecord = trim((string) config('modules.appwebsitebuilder.publish_a_record', ''));

        if ($aRecord !== '') {
            return [
                [
                    'type' => 'A',
                    'host' => '@',
                    'points_to' => $aRecord,
                    'ttl' => '600',
                    'notes' => __('Root domain (apex)'),
                ],
                [
                    'type' => 'CNAME',
                    'host' => 'www',
                    'points_to' => $appHost,
                    'ttl' => '600',
                    'notes' => __('Optional www subdomain'),
                ],
            ];
        }

        if ($publishDomain !== '') {
            return [
                [
                    'type' => 'CNAME',
                    'host' => '@',
                    'points_to' => $appHost,
                    'ttl' => '600',
                    'notes' => __('Some registrars use ALIAS/ANAME for root @ to :host', ['host' => $appHost]),
                ],
                [
                    'type' => 'CNAME',
                    'host' => 'www',
                    'points_to' => $publishDomain,
                    'ttl' => '600',
                    'notes' => null,
                ],
            ];
        }

        return [
            [
                'type' => 'CNAME',
                'host' => 'www',
                'points_to' => $appHost,
                'ttl' => '600',
                'notes' => __('Point www to your app host'),
            ],
            [
                'type' => 'A',
                'host' => '@',
                'points_to' => __('Use your server IP (ask your host)'),
                'ttl' => '600',
                'notes' => __('Or redirect apex to www at your registrar'),
            ],
        ];
    }

    public static function publicUrl(?string $customDomain): ?string
    {
        $domain = self::normalize($customDomain);

        if ($domain === '') {
            return null;
        }

        $scheme = parse_url((string) config('app.url'), PHP_URL_SCHEME) ?: 'https';

        return $scheme.'://'.$domain;
    }
}
