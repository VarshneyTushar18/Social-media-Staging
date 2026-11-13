<?php

namespace Modules\AppWebsiteBuilder\Support;

use Modules\AppWebsiteBuilder\Models\WebsiteSite;

class WebsiteAuditContext
{
    /**
     * @return array{source: string, label: string, payload: string}
     */
    public static function fromBuilderSite(WebsiteSite $site): array
    {
        $content = is_array($site->generated_content) ? $site->generated_content : [];
        $slug = (string) ($site->subdomain_slug ?? '');
        $publicUrl = $slug !== '' ? WebsitePublishConfig::publicUrl($slug) : null;

        $summary = [
            'site_name' => $site->name,
            'site_type' => $site->site_type,
            'build_method' => $site->build_method,
            'status' => $site->status,
            'public_url' => $publicUrl,
            'tagline' => data_get($content, 'tagline'),
            'sections' => data_get($content, 'sections'),
            'user_prompt' => data_get($content, 'user_prompt'),
        ];

        $encoded = json_encode($summary, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

        return [
            'source' => 'builder',
            'label' => (string) $site->name,
            'payload' => is_string($encoded) ? $encoded : '{}',
        ];
    }

    /**
     * @param  array{url: string, status: int, title: string, meta_description: string, h1: string, excerpt: string, https: bool}  $snapshot
     * @return array{source: string, label: string, payload: string}
     */
    public static function fromExternalSnapshot(array $snapshot): array
    {
        $encoded = json_encode($snapshot, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

        return [
            'source' => 'external',
            'label' => (string) ($snapshot['url'] ?? ''),
            'payload' => is_string($encoded) ? $encoded : '{}',
        ];
    }
}
