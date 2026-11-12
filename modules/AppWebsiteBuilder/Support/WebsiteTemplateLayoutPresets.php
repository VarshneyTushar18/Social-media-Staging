<?php

namespace Modules\AppWebsiteBuilder\Support;

class WebsiteTemplateLayoutPresets
{
    /**
     * @return array<string, mixed>|null
     */
    public static function forTemplate(string $templateId): ?array
    {
        $presets = self::presets();

        return $presets[$templateId] ?? null;
    }

    /**
     * Visual skins: corporate, marketplace, saas, glass, luxury, studio, pulse, modern (default CSS only).
     *
     * @return array<string, array<string, mixed>>
     */
    protected static function presets(): array
    {
        return [
            // Cleaning — 5 distinct structures
            'cleaning-pro-trust' => ['nav' => 'light', 'hero' => 'banner', 'about' => 'split-reverse', 'services' => 'feature-list', 'highlights' => 'stat-row', 'contact' => 'light-split', 'skin' => 'corporate'],
            'cleaning-fresh-bright' => ['nav' => 'light', 'hero' => 'center', 'about' => 'stack', 'services' => 'icon-row', 'highlights' => 'list-check', 'contact' => 'gradient', 'sections' => ['highlights', 'services', 'about', 'contact'], 'skin' => 'marketplace'],
            'cleaning-eco-green' => ['nav' => 'minimal', 'hero' => 'split', 'about' => 'split', 'services' => 'image-cards', 'highlights' => 'icon-cards', 'contact' => 'minimal-bar', 'skin' => 'glass'],
            'cleaning-commercial-grade' => ['nav' => 'dark', 'hero' => 'banner', 'about' => 'stack', 'services' => 'feature-list', 'highlights' => 'stat-row', 'contact' => 'gradient', 'sections' => ['services', 'highlights', 'about', 'contact'], 'skin' => 'corporate'],
            'cleaning-express-mobile' => ['nav' => 'light', 'hero' => 'overlap', 'about' => 'stack', 'services' => 'icon-row', 'highlights' => 'icon-cards', 'contact' => 'light-split', 'sections' => ['services', 'about', 'highlights', 'contact'], 'skin' => 'pulse'],

            // Beauty — 5 distinct structures
            'beauty-luxe-salon' => ['nav' => 'minimal', 'hero' => 'overlap', 'about' => 'split', 'services' => 'image-cards', 'highlights' => 'stat-row', 'contact' => 'minimal-bar', 'skin' => 'luxury'],
            'beauty-modern-studio' => ['nav' => 'dark', 'hero' => 'center', 'about' => 'stack', 'services' => 'feature-list', 'highlights' => 'icon-cards', 'contact' => 'gradient', 'skin' => 'saas'],
            'beauty-wellness-retreat' => ['nav' => 'light', 'hero' => 'split', 'about' => 'stack', 'services' => 'icon-row', 'highlights' => 'list-check', 'contact' => 'light-split', 'sections' => ['about', 'services', 'highlights', 'contact'], 'skin' => 'glass'],
            'beauty-bridal-glow' => ['nav' => 'light', 'hero' => 'split', 'about' => 'split-reverse', 'services' => 'image-cards', 'highlights' => 'stat-row', 'contact' => 'gradient', 'skin' => 'luxury'],
            'beauty-urban-barber' => ['nav' => 'dark', 'hero' => 'banner', 'about' => 'stack', 'services' => 'feature-list', 'highlights' => 'stat-row', 'contact' => 'minimal-bar', 'sections' => ['services', 'highlights', 'contact', 'about'], 'skin' => 'pulse'],

            // E-commerce
            'ecommerce-spotlight' => ['nav' => 'dark', 'hero' => 'center', 'about' => 'stack', 'services' => 'product-grid', 'highlights' => 'icon-cards', 'contact' => 'gradient', 'sections' => ['services', 'highlights', 'about', 'contact'], 'skin' => 'saas'],
            'ecommerce-boutique-chic' => ['nav' => 'minimal', 'hero' => 'overlap', 'about' => 'split', 'services' => 'product-grid', 'highlights' => 'stat-row', 'contact' => 'minimal-bar', 'skin' => 'luxury'],
            'ecommerce-market-bold' => ['nav' => 'dark', 'hero' => 'banner', 'about' => 'stack', 'services' => 'image-cards', 'highlights' => 'icon-row', 'contact' => 'light-split', 'sections' => ['services', 'about', 'highlights', 'contact'], 'skin' => 'marketplace'],
            'ecommerce-luxury-minimal' => ['nav' => 'minimal', 'hero' => 'center', 'about' => 'split-reverse', 'services' => 'product-grid', 'highlights' => 'list-check', 'contact' => 'minimal-bar', 'sections' => ['services', 'highlights', 'contact', 'about'], 'skin' => 'glass'],
            'ecommerce-flash-deals' => ['nav' => 'dark', 'hero' => 'banner', 'about' => 'stack', 'services' => 'feature-list', 'highlights' => 'stat-row', 'contact' => 'gradient', 'skin' => 'pulse'],

            // Pet (existing ids)
            'pet-grooming-salon' => ['nav' => 'light', 'hero' => 'banner', 'about' => 'split', 'services' => 'image-cards', 'highlights' => 'list-check', 'contact' => 'light-split', 'skin' => 'marketplace'],
            'pet-spa-daycare' => ['nav' => 'minimal', 'hero' => 'center', 'about' => 'stack', 'services' => 'icon-row', 'highlights' => 'icon-cards', 'contact' => 'gradient', 'sections' => ['highlights', 'services', 'about', 'contact'], 'skin' => 'luxury'],
            'pet-mobile-grooming' => ['nav' => 'light', 'hero' => 'center', 'about' => 'stack', 'services' => 'feature-list', 'highlights' => 'stat-row', 'contact' => 'light-split', 'sections' => ['services', 'about', 'highlights', 'contact'], 'skin' => 'corporate'],
            'pet-boutique' => ['nav' => 'dark', 'hero' => 'overlap', 'about' => 'stack', 'services' => 'product-grid', 'highlights' => 'stat-row', 'contact' => 'minimal-bar', 'sections' => ['services', 'highlights', 'about', 'contact'], 'skin' => 'studio'],
            'pet-care-classic' => ['nav' => 'light', 'hero' => 'split', 'about' => 'split-reverse', 'services' => 'image-cards', 'highlights' => 'stat-row', 'contact' => 'gradient', 'skin' => 'glass'],

            // Restaurant
            'hospitality-menu' => ['hero' => 'banner', 'services' => 'menu-rows', 'highlights' => 'list-check', 'contact' => 'light-split', 'skin' => 'marketplace'],
            'restaurant-bistro-warm' => ['nav' => 'light', 'hero' => 'banner', 'about' => 'split', 'services' => 'menu-rows', 'highlights' => 'icon-cards', 'contact' => 'gradient', 'skin' => 'marketplace'],
            'restaurant-fine-dining' => ['nav' => 'dark', 'hero' => 'split', 'about' => 'split-reverse', 'services' => 'menu-rows', 'highlights' => 'stat-row', 'contact' => 'minimal-bar', 'skin' => 'luxury'],
            'restaurant-cafe-casual' => ['nav' => 'light', 'hero' => 'center', 'about' => 'stack', 'services' => 'image-cards', 'highlights' => 'list-check', 'contact' => 'light-split', 'sections' => ['services', 'highlights', 'about', 'contact'], 'skin' => 'glass'],
            'restaurant-rooftop-bar' => ['nav' => 'dark', 'hero' => 'overlap', 'about' => 'stack', 'services' => 'feature-list', 'highlights' => 'stat-row', 'contact' => 'gradient', 'skin' => 'pulse'],

            // Creative
            'creative-studio' => ['nav' => 'minimal', 'hero' => 'overlap', 'about' => 'stack', 'services' => 'feature-list', 'highlights' => 'icon-cards', 'contact' => 'minimal-bar', 'skin' => 'studio'],
            'creative-portfolio-bold' => ['nav' => 'dark', 'hero' => 'banner', 'about' => 'split', 'services' => 'image-cards', 'highlights' => 'stat-row', 'contact' => 'gradient', 'skin' => 'corporate'],
            'creative-agency-minimal' => ['nav' => 'minimal', 'hero' => 'center', 'about' => 'stack', 'services' => 'icon-row', 'highlights' => 'list-check', 'contact' => 'minimal-bar', 'sections' => ['about', 'services', 'highlights', 'contact'], 'skin' => 'glass'],
            'creative-photography-grid' => ['nav' => 'light', 'hero' => 'split', 'about' => 'split-reverse', 'services' => 'image-cards', 'highlights' => 'icon-cards', 'contact' => 'light-split', 'skin' => 'luxury'],
            'creative-motion-reel' => ['nav' => 'dark', 'hero' => 'center', 'about' => 'stack', 'services' => 'feature-list', 'highlights' => 'stat-row', 'contact' => 'gradient', 'sections' => ['highlights', 'services', 'about', 'contact'], 'skin' => 'saas'],
        ];
    }
}
