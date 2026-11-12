<?php

namespace Modules\AppWebsiteBuilder\Support;

class WebsiteTemplateGalleryCatalog
{
    /**
     * @return list<array<string, mixed>>
     */
    public static function definitions(): array
    {
        $cleaning = [
            self::row('cleaning-pro-trust', 'cleaning', __('Pro Trust Cleaning'), __('Licensed crews, insured work, and before/after proof blocks.'), '#0284c7', 'https://images.unsplash.com/photo-1581578731548-c64695cc6952?auto=format&fit=crop&w=800&q=80', true, 'cleaning'),
            self::row('cleaning-fresh-bright', 'cleaning', __('Fresh & Bright'), __('Airy hero and checklist highlights for home cleaning brands.'), '#0d9488', 'https://images.unsplash.com/photo-1628177142898-93e36e4e3a50?auto=format&fit=crop&w=800&q=80', false, 'cleaning'),
            self::row('cleaning-eco-green', 'cleaning', __('Eco Green Clean'), __('Earth-tone palette with eco service pillars and quote CTAs.'), '#16a34a', 'https://images.unsplash.com/photo-1527515637462-cff94eecc1ac?auto=format&fit=crop&w=800&q=80', false, 'cleaning'),
            self::row('cleaning-commercial-grade', 'cleaning', __('Commercial Grade'), __('Bold banner hero for offices, retail, and facility contracts.'), '#1d4ed8', 'https://images.unsplash.com/photo-1486406146921-c626a44e1e76?auto=format&fit=crop&w=800&q=80', false, 'cleaning'),
            self::row('cleaning-express-mobile', 'cleaning', __('Express Mobile'), __('Mobile-first layout for same-day and on-the-go crews.'), '#0369a1', 'https://images.unsplash.com/photo-1504307651254-35680f356dfd?auto=format&fit=crop&w=800&q=80', false, 'cleaning'),
        ];

        $beauty = [
            self::row('beauty-luxe-salon', 'beauty', __('Luxe Salon'), __('Editorial overlap hero with service menu and stylist trust row.'), '#be185d', 'https://images.unsplash.com/photo-1522337360788-8b13dee7a37e?auto=format&fit=crop&w=800&q=80', true, 'beauty'),
            self::row('beauty-modern-studio', 'beauty', __('Modern Studio'), __('Minimal dark nav, centered hero, and feature service list.'), '#db2777', 'https://images.unsplash.com/photo-1633685042214-98d45ae2b0a7?auto=format&fit=crop&w=800&q=80', false, 'beauty'),
            self::row('beauty-wellness-retreat', 'beauty', __('Wellness Retreat'), __('Calm stack layout for spa, skin, and holistic care.'), '#7c3aed', 'https://images.unsplash.com/photo-1540555700478-4be289fbecef?auto=format&fit=crop&w=800&q=80', false, 'beauty'),
            self::row('beauty-bridal-glow', 'beauty', __('Bridal Glow'), __('Soft split hero with packages grid and testimonial stats.'), '#e11d48', 'https://images.unsplash.com/photo-1516975080664-ed2fc6a32937?auto=format&fit=crop&w=800&q=80', false, 'beauty'),
            self::row('beauty-urban-barber', 'beauty', __('Urban Barber'), __('Stat-forward highlights and bold type for barbershops.'), '#0f172a', 'https://images.unsplash.com/photo-1503951914875-452162b0f3f1?auto=format&fit=crop&w=800&q=80', false, 'beauty'),
        ];

        $ecommerce = [
            self::row('ecommerce-spotlight', 'ecommerce', __('E-commerce Spotlight'), __('Product grid hero with offers and conversion blocks.'), '#7c3aed', 'https://images.unsplash.com/photo-1556742049-0cfed4f6a45d?auto=format&fit=crop&w=800&q=80', true, 'ecommerce'),
            self::row('ecommerce-boutique-chic', 'ecommerce', __('Boutique Chic'), __('Fashion-forward imagery and curated collection rows.'), '#c026d3', 'https://images.unsplash.com/photo-1441986300917-64674bd600d8?auto=format&fit=crop&w=800&q=80', false, 'ecommerce'),
            self::row('ecommerce-market-bold', 'ecommerce', __('Market Bold'), __('High-contrast storefront with category tiles.'), '#ea580c', 'https://images.unsplash.com/photo-1556740758-90de374c12ad?auto=format&fit=crop&w=800&q=80', false, 'ecommerce'),
            self::row('ecommerce-luxury-minimal', 'ecommerce', __('Luxury Minimal'), __('Premium minimal layout for high-end catalogs.'), '#171717', 'https://images.unsplash.com/photo-1472851294608-062f824d29d5?auto=format&fit=crop&w=800&q=80', false, 'ecommerce'),
            self::row('ecommerce-flash-deals', 'ecommerce', __('Flash Deals'), __('Promo-led hero with urgency stats and bundle cards.'), '#dc2626', 'https://images.unsplash.com/photo-1607082348824-0a96f2a4b9da?auto=format&fit=crop&w=800&q=80', false, 'ecommerce'),
        ];

        $pet = [
            self::row('pet-grooming-salon', 'pet', __('Pet Grooming Salon'), __('Booking-ready layout with baths, grooms, and add-ons.'), '#059669', 'https://images.unsplash.com/photo-1560066984-138dadb4c035?auto=format&fit=crop&w=800&q=80', true, 'pet'),
            self::row('pet-spa-daycare', 'pet', __('Pet Spa & Daycare'), __('Spa packages, daycare, and calm wellness sections.'), '#8b5cf6', 'https://images.unsplash.com/photo-1587300003388-59208cc962cb?auto=format&fit=crop&w=800&q=80', false, 'pet'),
            self::row('pet-mobile-grooming', 'pet', __('Mobile Pet Grooming'), __('Van routes, service map, and at-home booking CTAs.'), '#0ea5e9', 'https://images.unsplash.com/photo-1530281700549-e82e7bf110d6?auto=format&fit=crop&w=800&q=80', false, 'pet'),
            self::row('pet-boutique', 'pet', __('Pet Boutique'), __('Retail grids with grooming bundles and loyalty perks.'), '#7c3aed', 'https://images.unsplash.com/photo-1450778869180-41d0601e046e?auto=format&fit=crop&w=800&q=80', false, 'pet'),
            self::row('pet-care-classic', 'pet', __('Classic Pet Care'), __('Reviews-first layout for established groomers.'), '#5b4dff', 'https://images.unsplash.com/photo-1601758228041-f3b2795255f1?auto=format&fit=crop&w=800&q=80', false, 'pet'),
        ];

        $restaurant = [
            self::row('hospitality-menu', 'restaurant', __('Hospitality & Menu'), __('Menu rows, reservations, and dining ambiance.'), '#ea580c', 'https://images.unsplash.com/photo-1414235077428-338989a2e8c0?auto=format&fit=crop&w=800&q=80', true, 'restaurant'),
            self::row('restaurant-bistro-warm', 'restaurant', __('Warm Bistro'), __('Cozy banner hero with chef specials and hours.'), '#b45309', 'https://images.unsplash.com/photo-1517248135467-4c7edcad34c4?auto=format&fit=crop&w=800&q=80', false, 'restaurant'),
            self::row('restaurant-fine-dining', 'restaurant', __('Fine Dining'), __('Elegant split layout for tasting menus and events.'), '#1c1917', 'https://images.unsplash.com/photo-1550966841-cedc1018c39d?auto=format&fit=crop&w=800&q=80', false, 'restaurant'),
            self::row('restaurant-cafe-casual', 'restaurant', __('Casual Café'), __('Bright café energy with pastry highlights.'), '#ca8a04', 'https://images.unsplash.com/photo-1554118811-1e4d4dfbd76c?auto=format&fit=crop&w=800&q=80', false, 'restaurant'),
            self::row('restaurant-rooftop-bar', 'restaurant', __('Rooftop Bar & Grill'), __('Nightlife hero with cocktails and skyline vibes.'), '#7e22ce', 'https://images.unsplash.com/photo-1572116469696-31de0f17cc34?auto=format&fit=crop&w=800&q=80', false, 'restaurant'),
        ];

        $creative = [
            self::row('creative-studio', 'creative', __('Creative Studio'), __('Overlap hero and portfolio-forward hierarchy.'), '#e11d48', 'https://images.unsplash.com/photo-1498050108023-c5249f4df085?auto=format&fit=crop&w=800&q=80', true, 'creative'),
            self::row('creative-portfolio-bold', 'creative', __('Bold Portfolio'), __('Large type case studies and project grid.'), '#f97316', 'https://images.unsplash.com/photo-1561070791-2526d30994b5?auto=format&fit=crop&w=800&q=80', false, 'creative'),
            self::row('creative-agency-minimal', 'creative', __('Agency Minimal'), __('Whitespace-led agency pitch with service pillars.'), '#0f766e', 'https://images.unsplash.com/photo-1552664730-d307ca884978?auto=format&fit=crop&w=800&q=80', false, 'creative'),
            self::row('creative-photography-grid', 'creative', __('Photography Grid'), __('Masonry-style gallery sections for visual artists.'), '#4f46e5', 'https://images.unsplash.com/photo-1452587925148-ce544e77e70d?auto=format&fit=crop&w=800&q=80', false, 'creative'),
            self::row('creative-motion-reel', 'creative', __('Motion & Reel'), __('Dynamic stat row for video, motion, and social studios.'), '#9333ea', 'https://images.unsplash.com/photo-1618005182384-a83a8bd57fbe?auto=format&fit=crop&w=800&q=80', false, 'creative'),
        ];

        return array_merge($cleaning, $beauty, $ecommerce, $pet, $restaurant, $creative);
    }

    /**
     * @return array<string, mixed>
     */
    protected static function row(
        string $id,
        string $gallerySlug,
        string $name,
        string $description,
        string $accent,
        string $previewUrl,
        bool $recommended,
        string $niche
    ): array {
        $categoryMap = [
            'cleaning' => 'local',
            'beauty' => 'local',
            'pet' => 'local',
            'ecommerce' => 'ecommerce',
            'restaurant' => 'restaurant',
            'creative' => 'creative',
        ];

        $category = $categoryMap[$gallerySlug] ?? 'general';

        return [
            'id' => $id,
            'gallery_slug' => $gallerySlug,
            'name' => $name,
            'description' => $description,
            'accent' => $accent,
            'preview_url' => $previewUrl,
            'for_categories' => [$category],
            'for_niches' => [$niche],
            'recommended_for' => $recommended ? [$category] : [],
        ];
    }

    /**
     * @return array<string, mixed>|null
     */
    public static function copyOverrides(string $templateId, string $siteName, string $typeLabel): ?array
    {
        $queryMap = [
            'cleaning-pro-trust' => 'professional cleaning team',
            'cleaning-fresh-bright' => 'bright clean home interior',
            'cleaning-eco-green' => 'eco friendly cleaning',
            'cleaning-commercial-grade' => 'office cleaning service',
            'cleaning-express-mobile' => 'mobile car cleaning van',
            'beauty-luxe-salon' => 'luxury hair salon',
            'beauty-modern-studio' => 'modern beauty studio',
            'beauty-wellness-retreat' => 'spa wellness retreat',
            'beauty-bridal-glow' => 'bridal makeup studio',
            'beauty-urban-barber' => 'barbershop interior',
            'ecommerce-boutique-chic' => 'boutique clothing store',
            'ecommerce-market-bold' => 'online marketplace shopping',
            'ecommerce-luxury-minimal' => 'luxury product display',
            'ecommerce-flash-deals' => 'shopping sale promotion',
            'restaurant-bistro-warm' => 'cozy restaurant bistro',
            'restaurant-fine-dining' => 'fine dining restaurant',
            'restaurant-cafe-casual' => 'casual cafe coffee',
            'restaurant-rooftop-bar' => 'rooftop bar night',
            'creative-portfolio-bold' => 'design portfolio workspace',
            'creative-agency-minimal' => 'creative agency office',
            'creative-photography-grid' => 'photography studio',
            'creative-motion-reel' => 'video production studio',
        ];

        if (! isset($queryMap[$templateId])) {
            return null;
        }

        return [
            'hero_image_query' => $queryMap[$templateId],
            'hero_headline' => __('Welcome to :name', ['name' => $siteName]),
            'hero_subheadline' => __('A professional :type layout crafted for your brand.', ['type' => $typeLabel]),
            'hero_cta' => __('Book now'),
            'about_image_query' => $queryMap[$templateId],
        ];
    }

    public static function slugForTemplateId(string $templateId): ?string
    {
        foreach (self::definitions() as $definition) {
            if (($definition['id'] ?? '') === $templateId) {
                return (string) ($definition['gallery_slug'] ?? '');
            }
        }

        return null;
    }

    /**
     * Force category-appropriate Unsplash URLs on every template section (hero, about, services, highlights, contact).
     *
     * @param  array<string, mixed>  $content
     * @return array<string, mixed>
     */
    public static function applyContentImages(string $templateId, array $content): array
    {
        return self::applyContentImagesInternal($templateId, $content, false);
    }

    public static function applyContentImagesFillMissing(string $templateId, array $content): array
    {
        return self::applyContentImagesInternal($templateId, $content, true);
    }

    /**
     * @return list<string>
     */
    public static function stockImagePoolForTemplate(string $templateId): array
    {
        $definition = self::findDefinition($templateId);

        if ($definition === null) {
            return [];
        }

        return self::uniqueSlugImagePool((string) ($definition['gallery_slug'] ?? ''));
    }

    /**
     * @return list<string>
     */
    public static function stockImagePoolForGallerySlug(string $slug): array
    {
        return self::uniqueSlugImagePool($slug);
    }

    /**
     * @param  array<string, mixed>  $content
     * @return array<string, mixed>
     */
    protected static function applyContentImagesInternal(string $templateId, array $content, bool $onlyMissing): array
    {
        $definition = self::findDefinition($templateId);

        if ($definition === null) {
            return $content;
        }

        $slug = (string) ($definition['gallery_slug'] ?? '');
        $pool = self::uniqueSlugImagePool($slug);

        if ($pool === []) {
            return $content;
        }

        /** @var array<string, true> $usedPhotoKeys */
        $usedPhotoKeys = [];
        $siblingIndex = self::siblingIndex($templateId, $slug);
        $cursor = $siblingIndex % count($pool);

        $reservePhoto = static function (string $url) use (&$usedPhotoKeys): string {
            $usedPhotoKeys[self::photoKey($url)] = true;

            return $url;
        };

        $nextUnique = static function (int $width) use (&$usedPhotoKeys, $pool, &$cursor): string {
            $attempts = 0;
            $count = count($pool);

            while ($attempts < $count) {
                $raw = $pool[$cursor % $count];
                $cursor++;
                $attempts++;
                $key = self::photoKey($raw);

                if (isset($usedPhotoKeys[$key])) {
                    continue;
                }

                $usedPhotoKeys[$key] = true;

                return self::sizeUrl($raw, $width);
            }

            $raw = $pool[$cursor % $count];
            $cursor++;

            return self::sizeUrl($raw, $width);
        };

        $previewUrl = (string) ($definition['preview_url'] ?? $pool[0]);

        $existingHero = trim((string) ($content['hero_image_url'] ?? ''));

        if ($onlyMissing && self::isPersistedImageUrl($existingHero)) {
            $hero = $existingHero;
            $reservePhoto($existingHero);
        } else {
            $hero = self::sizeUrl($reservePhoto($previewUrl), 1600);
            $content['hero_image_url'] = $hero;
            $content['hero_image_alt'] = (string) ($definition['name'] ?? __('Hero'));
        }

        $sections = is_array($content['sections'] ?? null) ? $content['sections'] : [];

        foreach ($sections as $index => $section) {
            if (! is_array($section)) {
                continue;
            }

            $type = strtolower((string) ($section['type'] ?? ''));

            if ($type === 'hero') {
                if (! $onlyMissing || ! self::isPersistedImageUrl((string) ($section['image_url'] ?? ''))) {
                    $section['image_url'] = $nextUnique(1200);
                }

                if (self::photoKey((string) ($section['image_url'] ?? '')) === self::photoKey($hero)) {
                    $section['image_url'] = $nextUnique(1200);
                }

                $sections[$index] = $section;

                continue;
            }

            if ($type === 'about') {
                if (! $onlyMissing || ! self::isPersistedImageUrl((string) ($section['image_url'] ?? ''))) {
                    $section['image_url'] = $nextUnique(900);
                }

                if (self::photoKey((string) ($section['image_url'] ?? '')) === self::photoKey($hero)) {
                    $section['image_url'] = $nextUnique(900);
                }

                $sections[$index] = $section;

                continue;
            }

            if ($type === 'contact') {
                if (! $onlyMissing || ! self::isPersistedImageUrl((string) ($section['image_url'] ?? ''))) {
                    $section['image_url'] = $nextUnique(1200);
                }

                $sections[$index] = $section;

                continue;
            }

            if ($type === 'services' && is_array($section['items'] ?? null) && self::servicesLayoutUsesItemImages($templateId, $content)) {
                foreach ($section['items'] as $itemIndex => $item) {
                    if (! is_array($item)) {
                        continue;
                    }

                    if ($onlyMissing && self::isPersistedImageUrl((string) ($item['image_url'] ?? ''))) {
                        $reservePhoto((string) $item['image_url']);

                        continue;
                    }

                    $item['image_url'] = $nextUnique(800);
                    $section['items'][$itemIndex] = $item;
                }

                $sections[$index] = $section;
            }
        }

        $content['sections'] = $sections;

        $content = WebsiteLandingAssets::ensureUniqueLandingImages($content);

        return self::ensureDistinctHeroDualImages($templateId, $content);
    }

    /**
     * When the hero layout shows background + inset images, keep them different but on-category.
     *
     * @param  array<string, mixed>  $content
     * @return array<string, mixed>
     */
    public static function ensureDistinctHeroDualImages(string $templateId, array $content): array
    {
        if (! self::heroLayoutUsesDualImages($content, $templateId)) {
            return $content;
        }

        $background = trim((string) ($content['hero_image_url'] ?? ''));
        if ($background === '') {
            return $content;
        }

        $definition = self::findDefinition($templateId);
        $slug = (string) ($definition['gallery_slug'] ?? '');
        $pool = self::uniqueSlugImagePool($slug);

        if ($pool === []) {
            return $content;
        }

        $backgroundKey = self::photoKey($background);
        $sections = is_array($content['sections'] ?? null) ? $content['sections'] : [];

        foreach ($sections as $index => $section) {
            if (! is_array($section) || strtolower((string) ($section['type'] ?? '')) !== 'hero') {
                continue;
            }

            $feature = trim((string) ($section['image_url'] ?? ''));
            if ($feature !== '' && self::photoKey($feature) !== $backgroundKey) {
                break;
            }

            foreach ($pool as $rawUrl) {
                if (self::photoKey($rawUrl) === $backgroundKey) {
                    continue;
                }

                $section['image_url'] = self::sizeUrl($rawUrl, 1200);
                $sections[$index] = $section;
                break 2;
            }

            break;
        }

        $content['sections'] = $sections;

        return $content;
    }

  /**
     * @param  array<string, mixed>  $content
     */
    /**
     * @param  array<string, mixed>  $content
     */
    protected static function servicesLayoutUsesItemImages(string $templateId, array $content): bool
    {
        $layout = is_array($content['layout_config'] ?? null)
            ? $content['layout_config']
            : (\Modules\AppWebsiteBuilder\Support\WebsiteTemplateLayouts::config($templateId) ?: []);

        $style = (string) ($layout['services'] ?? 'image-cards');

        return in_array($style, ['product-grid', 'image-cards'], true);
    }

    /**
     * @param  array<string, mixed>  $content
     */
    protected static function heroLayoutUsesDualImages(array $content, string $templateId): bool
    {
        $layout = is_array($content['layout_config'] ?? null)
            ? $content['layout_config']
            : (\Modules\AppWebsiteBuilder\Support\WebsiteTemplateLayouts::config($templateId) ?: []);

        $heroStyle = (string) ($layout['hero'] ?? 'split');

        return in_array($heroStyle, ['overlap', 'split'], true);
    }

    /**
     * @return list<string>
     */
    protected static function uniqueSlugImagePool(string $slug): array
    {
        $unique = [];
        $seen = [];

        foreach (self::slugImagePool($slug) as $url) {
            $key = self::photoKey($url);

            if (isset($seen[$key])) {
                continue;
            }

            $seen[$key] = true;
            $unique[] = $url;
        }

        return $unique;
    }

    protected static function isPersistedImageUrl(string $url): bool
    {
        $url = trim($url);

        return $url !== '' && (
            str_starts_with($url, 'http://')
            || str_starts_with($url, 'https://')
            || str_starts_with($url, '/')
        );
    }

    protected static function photoKey(string $url): string
    {
        if (preg_match('/photo-([a-zA-Z0-9-]+)/', $url, $matches) === 1) {
            return (string) $matches[1];
        }

        return md5($url);
    }

    /**
     * @return array<string, mixed>|null
     */
    protected static function findDefinition(string $templateId): ?array
    {
        foreach (self::definitions() as $definition) {
            if (($definition['id'] ?? '') === $templateId) {
                return $definition;
            }
        }

        return null;
    }

    protected static function siblingIndex(string $templateId, string $slug): int
    {
        $index = 0;

        foreach (self::definitions() as $definition) {
            if (($definition['gallery_slug'] ?? '') !== $slug) {
                continue;
            }

            if (($definition['id'] ?? '') === $templateId) {
                return $index;
            }

            $index++;
        }

        return 0;
    }

    protected static function sizeUrl(string $url, int $width): string
    {
        if (preg_match('/w=\d+/', $url) === 1) {
            return (string) preg_replace('/w=\d+/', 'w='.$width, $url);
        }

        $join = str_contains($url, '?') ? '&' : '?';

        return $url.$join.'w='.$width.'&q=80&auto=format&fit=crop';
    }

    /**
     * @return list<string>
     */
    protected static function slugImagePool(string $slug): array
    {
        return match ($slug) {
            'cleaning' => [
                'https://images.unsplash.com/photo-1581578731548-c64695cc6952?auto=format&fit=crop&w=800&q=80',
                'https://images.unsplash.com/photo-1628177142898-93e36e4e3a50?auto=format&fit=crop&w=800&q=80',
                'https://images.unsplash.com/photo-1527515637462-cff94eecc1ac?auto=format&fit=crop&w=800&q=80',
                'https://images.unsplash.com/photo-1486406146921-c626a44e1e76?auto=format&fit=crop&w=800&q=80',
                'https://images.unsplash.com/photo-1504307651254-35680f356dfd?auto=format&fit=crop&w=800&q=80',
                'https://images.unsplash.com/photo-1558618666-fcd25c85cd64?auto=format&fit=crop&w=800&q=80',
                'https://images.unsplash.com/photo-1585421514284-efb74c2b69ba?auto=format&fit=crop&w=800&q=80',
                'https://images.unsplash.com/photo-1563453393-21f99541a4de?auto=format&fit=crop&w=800&q=80',
                'https://images.unsplash.com/photo-1527515862127-4fc8b5f9d1c6?auto=format&fit=crop&w=800&q=80',
                'https://images.unsplash.com/photo-1582735689369-4fe89db7114c?auto=format&fit=crop&w=800&q=80',
            ],
            'beauty' => [
                'https://images.unsplash.com/photo-1503951914875-452162b0f3f1?auto=format&fit=crop&w=800&q=80',
                'https://images.unsplash.com/photo-1585747860715-4564b700468a?auto=format&fit=crop&w=800&q=80',
                'https://images.unsplash.com/photo-1522337360788-8b13dee7a37e?auto=format&fit=crop&w=800&q=80',
                'https://images.unsplash.com/photo-1633685042214-98d45ae2b0a7?auto=format&fit=crop&w=800&q=80',
                'https://images.unsplash.com/photo-1540555700478-4be289fbecef?auto=format&fit=crop&w=800&q=80',
                'https://images.unsplash.com/photo-1516975080664-ed2fc6a32937?auto=format&fit=crop&w=800&q=80',
                'https://images.unsplash.com/photo-1521590832167-b7a8d3d65f36?auto=format&fit=crop&w=800&q=80',
                'https://images.unsplash.com/photo-1562322140-8baeececf3df?auto=format&fit=crop&w=800&q=80',
                'https://images.unsplash.com/photo-1599351431613-8ccc30a446d3?auto=format&fit=crop&w=800&q=80',
                'https://images.unsplash.com/photo-1487412940908-9e0b1e5e3e3a?auto=format&fit=crop&w=800&q=80',
            ],
            'ecommerce' => [
                'https://images.unsplash.com/photo-1556742049-0cfed4f6a45d?auto=format&fit=crop&w=800&q=80',
                'https://images.unsplash.com/photo-1441986300917-64674bd600d8?auto=format&fit=crop&w=800&q=80',
                'https://images.unsplash.com/photo-1556740758-90de374c12ad?auto=format&fit=crop&w=800&q=80',
                'https://images.unsplash.com/photo-1523275335684-37898b6baf30?auto=format&fit=crop&w=800&q=80',
                'https://images.unsplash.com/photo-1472851294608-062f824d29d5?auto=format&fit=crop&w=800&q=80',
                'https://images.unsplash.com/photo-1607082348824-0a96f2a4b9da?auto=format&fit=crop&w=800&q=80',
                'https://images.unsplash.com/photo-1556740732-4044f8e55f93?auto=format&fit=crop&w=800&q=80',
                'https://images.unsplash.com/photo-1542744173-8e7e53463bb0?auto=format&fit=crop&w=800&q=80',
            ],
            'pet' => [
                'https://images.unsplash.com/photo-1560066984-138dadb4c035?auto=format&fit=crop&w=800&q=80',
                'https://images.unsplash.com/photo-1587300003388-59208cc962cb?auto=format&fit=crop&w=800&q=80',
                'https://images.unsplash.com/photo-1530281700549-e82e7bf110d6?auto=format&fit=crop&w=800&q=80',
                'https://images.unsplash.com/photo-1450778869180-41d0601e046e?auto=format&fit=crop&w=800&q=80',
                'https://images.unsplash.com/photo-1601758228041-f3b2795255f1?auto=format&fit=crop&w=800&q=80',
                'https://images.unsplash.com/photo-1514888286974-6c03e2ca1dba?auto=format&fit=crop&w=800&q=80',
                'https://images.unsplash.com/photo-1543852786-1cf6624b9987?auto=format&fit=crop&w=800&q=80',
            ],
            'restaurant' => [
                'https://images.unsplash.com/photo-1414235077428-338989a2e8c0?auto=format&fit=crop&w=800&q=80',
                'https://images.unsplash.com/photo-1517248135467-4c7edcad34c4?auto=format&fit=crop&w=800&q=80',
                'https://images.unsplash.com/photo-1550966841-cedc1018c39d?auto=format&fit=crop&w=800&q=80',
                'https://images.unsplash.com/photo-1554118811-1e4d4dfbd76c?auto=format&fit=crop&w=800&q=80',
                'https://images.unsplash.com/photo-1572116469696-31de0f17cc34?auto=format&fit=crop&w=800&q=80',
                'https://images.unsplash.com/photo-1559339352-11d035aa65de?auto=format&fit=crop&w=800&q=80',
                'https://images.unsplash.com/photo-1467003909585-2f8a72700288?auto=format&fit=crop&w=800&q=80',
                'https://images.unsplash.com/photo-1504674900247-0877df9cc836?auto=format&fit=crop&w=800&q=80',
                'https://images.unsplash.com/photo-1424847651674-bfc20b74e9ad?auto=format&fit=crop&w=800&q=80',
                'https://images.unsplash.com/photo-1551218808-94e220e084d2?auto=format&fit=crop&w=800&q=80',
            ],
            'creative' => [
                'https://images.unsplash.com/photo-1498050108023-c5249f4df085?auto=format&fit=crop&w=800&q=80',
                'https://images.unsplash.com/photo-1561070791-2526d30994b5?auto=format&fit=crop&w=800&q=80',
                'https://images.unsplash.com/photo-1552664730-d307ca884978?auto=format&fit=crop&w=800&q=80',
                'https://images.unsplash.com/photo-1452587925148-ce544e77e70d?auto=format&fit=crop&w=800&q=80',
                'https://images.unsplash.com/photo-1618005182384-a83a8bd57fbe?auto=format&fit=crop&w=800&q=80',
                'https://images.unsplash.com/photo-1561998337-6081172c2578?auto=format&fit=crop&w=800&q=80',
                'https://images.unsplash.com/photo-1542744094-24638e79068b?auto=format&fit=crop&w=800&q=80',
                'https://images.unsplash.com/photo-1507238691740-187a34b26150?auto=format&fit=crop&w=800&q=80',
                'https://images.unsplash.com/photo-1572044162444-5ec8d2ed2d6b?auto=format&fit=crop&w=800&q=80',
                'https://images.unsplash.com/photo-1558655146-9a402cabf029?auto=format&fit=crop&w=800&q=80',
            ],
            default => [],
        };
    }
}
