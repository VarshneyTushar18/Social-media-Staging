<?php

namespace Modules\AppWebsiteBuilder\Support;

class WebsiteTemplateLayouts
{
    /**
     * @return array<string, mixed>
     */
    public static function config(string $templateId): array
    {
        $base = [
            'template_key' => $templateId,
            'skin' => 'modern',
            'nav' => 'light',
            'hero' => 'split',
            'about' => 'split',
            'services' => 'image-cards',
            'highlights' => 'icon-cards',
            'contact' => 'gradient',
            'sections' => ['about', 'services', 'highlights', 'contact'],
        ];

        $galleryPreset = WebsiteTemplateLayoutPresets::forTemplate($templateId);

        if (is_array($galleryPreset)) {
            return array_merge($base, $galleryPreset);
        }

        $overrides = match ($templateId) {
            'classic-business' => [
                'skin' => 'corporate',
                'nav' => 'light',
                'hero' => 'split',
                'about' => 'split',
                'services' => 'image-cards',
                'highlights' => 'icon-cards',
                'contact' => 'gradient',
            ],
            'modern-startup' => [
                'skin' => 'saas',
                'nav' => 'dark',
                'hero' => 'center',
                'services' => 'feature-list',
                'highlights' => 'stat-row',
                'about' => 'stack',
                'sections' => ['services', 'highlights', 'about', 'contact'],
            ],
            'local-service' => [
                'skin' => 'marketplace',
                'hero' => 'split',
                'services' => 'icon-row',
                'highlights' => 'list-check',
                'contact' => 'light-split',
            ],
            'trusted-trades' => [
                'skin' => 'corporate',
                'hero' => 'banner',
                'services' => 'feature-list',
                'highlights' => 'stat-row',
                'about' => 'split-reverse',
            ],
            'ecommerce-spotlight' => [
                'skin' => 'saas',
                'nav' => 'dark',
                'hero' => 'center',
                'services' => 'product-grid',
                'highlights' => 'icon-cards',
                'about' => 'stack',
                'sections' => ['services', 'highlights', 'about', 'contact'],
            ],
            'creative-studio' => [
                'skin' => 'studio',
                'nav' => 'minimal',
                'hero' => 'overlap',
                'services' => 'feature-list',
                'highlights' => 'icon-cards',
                'about' => 'stack',
                'contact' => 'minimal-bar',
            ],
            'hospitality-menu' => [
                'skin' => 'marketplace',
                'hero' => 'banner',
                'services' => 'menu-rows',
                'highlights' => 'list-check',
                'contact' => 'light-split',
            ],
            'pet-grooming-salon' => [
                'skin' => 'marketplace',
                'nav' => 'light',
                'hero' => 'banner',
                'about' => 'split',
                'services' => 'image-cards',
                'highlights' => 'list-check',
                'contact' => 'light-split',
            ],
            'pet-spa-daycare' => [
                'skin' => 'luxury',
                'nav' => 'minimal',
                'hero' => 'center',
                'about' => 'stack',
                'services' => 'icon-row',
                'highlights' => 'icon-cards',
                'contact' => 'gradient',
                'sections' => ['highlights', 'services', 'about', 'contact'],
            ],
            'pet-mobile-grooming' => [
                'skin' => 'corporate',
                'nav' => 'light',
                'hero' => 'center',
                'about' => 'stack',
                'services' => 'feature-list',
                'highlights' => 'stat-row',
                'contact' => 'light-split',
                'sections' => ['services', 'about', 'highlights', 'contact'],
            ],
            'pet-boutique' => [
                'skin' => 'studio',
                'nav' => 'dark',
                'hero' => 'overlap',
                'about' => 'stack',
                'services' => 'product-grid',
                'highlights' => 'stat-row',
                'contact' => 'minimal-bar',
                'sections' => ['services', 'highlights', 'about', 'contact'],
            ],
            'pet-care-classic' => [
                'skin' => 'glass',
                'nav' => 'light',
                'hero' => 'split',
                'about' => 'split-reverse',
                'services' => 'image-cards',
                'highlights' => 'stat-row',
                'contact' => 'gradient',
            ],
            default => [],
        };

        return array_merge($base, $overrides);
    }
}
