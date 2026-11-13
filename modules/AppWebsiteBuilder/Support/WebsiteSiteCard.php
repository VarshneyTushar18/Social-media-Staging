<?php

namespace Modules\AppWebsiteBuilder\Support;

use Illuminate\Support\Collection;
use Modules\AppWebsiteBuilder\Models\WebsiteSite;

class WebsiteSiteCard
{
    protected const string PREVIEW_FALLBACK = 'https://images.unsplash.com/photo-1497366216548-37526070297c?auto=format&fit=crop&w=800&q=80';

    /**
     * @param  Collection<int, WebsiteSite>  $sites
     * @return list<array<string, mixed>>
     */
    public static function fromCollection(Collection $sites): array
    {
        return $sites
            ->map(fn (WebsiteSite $site): array => self::fromSite($site))
            ->values()
            ->all();
    }

    /**
     * @return array<string, mixed>
     */
    public static function fromSite(WebsiteSite $site): array
    {
        $content = is_array($site->generated_content) ? $site->generated_content : [];

        $previewUrl = trim((string) data_get($content, 'hero_image_url', ''));
        if ($previewUrl === '') {
            $previewUrl = self::PREVIEW_FALLBACK;
        }

        $accent = WebsiteLandingAssets::sanitizeHexPublic(
            (string) data_get($content, 'brand_primary', '#5b4dff'),
            '#5b4dff'
        );

        $sections = is_array($content['sections'] ?? null) ? $content['sections'] : [];
        $hero = collect($sections)->first(
            fn ($section) => is_array($section) && strtolower((string) data_get($section, 'type')) === 'hero'
        ) ?? [];

        $headline = trim((string) data_get($hero, 'headline', ''));
        $subheadline = trim((string) data_get($hero, 'subheadline', ''));
        $tagline = trim((string) data_get($content, 'tagline', ''));
        $siteType = trim((string) ($site->site_type ?? ''));
        $templateName = trim((string) data_get($content, 'template_name', ''));

        $summary = $subheadline !== ''
            ? $subheadline
            : ($tagline !== '' ? $tagline : $siteType);

        if ($summary === '') {
            $summary = $site->build_method === 'ai'
                ? __('Generated from your prompt')
                : __('Created from a template');
        }

        $buildLabel = match ($site->build_method) {
            'ai' => __('AI prompt'),
            'template' => __('Template'),
            default => ucfirst((string) $site->build_method),
        };

        $metaParts = array_filter([
            $buildLabel,
            $templateName !== '' ? $templateName : null,
            $siteType !== '' && $siteType !== $site->name ? $siteType : null,
        ]);

        return [
            'id' => $site->id,
            'name' => $site->name,
            'build_label' => $buildLabel,
            'meta_line' => implode(' · ', $metaParts),
            'site_type' => $siteType,
            'template_name' => $templateName,
            'headline' => $headline,
            'summary' => $summary,
            'status' => $site->status,
            'is_published' => $site->status === WebsiteSite::STATUS_PUBLISHED,
            'subdomain_slug' => $site->subdomain_slug,
            'public_url' => $site->subdomain_slug
                ? WebsitePublishConfig::publicUrl((string) $site->subdomain_slug)
                : null,
            'preview_url' => $previewUrl,
            'preview_fallback' => self::PREVIEW_FALLBACK,
            'accent' => $accent,
            'updated_label' => $site->updated_at?->diffForHumans() ?? '',
        ];
    }
}
