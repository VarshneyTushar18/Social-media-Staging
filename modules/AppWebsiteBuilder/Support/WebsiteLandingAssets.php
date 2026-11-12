<?php

namespace Modules\AppWebsiteBuilder\Support;

class WebsiteLandingAssets
{
    /**
     * @param  array<string, mixed>  $content
     * @return array<string, mixed>
     */
    public static function enrich(array $content, string $siteType, string $userPrompt = ''): array
    {
        $content['brand_primary'] = self::sanitizeHex(
            (string) ($content['brand_primary'] ?? ''),
            '#5b4dff'
        );

        if (trim($userPrompt) === '' && filled($content['user_prompt'] ?? null)) {
            $userPrompt = trim((string) $content['user_prompt']);
        }

        $visualContext = WebsitePromptStockImages::visualContext($content, $siteType, $userPrompt);
        $promptDrivenImages = trim($userPrompt) !== '' || ($content['generation_mode'] ?? '') === 'prompt';

        $gallerySlug = null;

        if (($content['source'] ?? '') === 'template' && filled($content['template_id'] ?? null)) {
            $gallerySlug = WebsiteTemplateGalleryCatalog::slugForTemplateId((string) $content['template_id']);
        }

        $stockContext = $gallerySlug !== null ? $gallerySlug.' '.$siteType : $siteType;
        $useGalleryStockLock = $gallerySlug !== null;

        $heroQuery = (string) ($content['hero_image_query'] ?? $siteType);

        if (! self::hasPersistedImageUrl((string) ($content['hero_image_url'] ?? ''))) {
            $content['hero_image_url'] = $promptDrivenImages
                ? WebsitePromptStockImages::url($visualContext.' '.$heroQuery, 1600, 0)
                : self::heroImageUrl($stockContext, $heroQuery);
        }
        $content['hero_image_alt'] = (string) ($content['hero_image_alt'] ?? __('Hero image for :type', ['type' => $siteType]));

        $sections = is_array($content['sections'] ?? null) ? $content['sections'] : [];
        $sections = self::normalizeSectionTypes($sections);

        foreach ($sections as $index => $section) {
            if (! is_array($section)) {
                continue;
            }

            $type = strtolower((string) ($section['type'] ?? ''));

            if ($type === 'hero') {
                if (! self::hasPersistedImageUrl((string) ($content['hero_image_url'] ?? ''))
                    && self::hasPersistedImageUrl((string) ($section['image_url'] ?? ''))) {
                    $content['hero_image_url'] = (string) $section['image_url'];
                }

                if (! $useGalleryStockLock) {
                    if (! self::hasPersistedImageUrl((string) ($section['image_url'] ?? ''))) {
                        $templateId = (string) ($content['template_id'] ?? '');
                        $layout = $templateId !== '' ? WebsiteTemplateLayouts::config($templateId) : [];
                        $heroStyle = (string) ($layout['hero'] ?? 'split');

                        if (in_array($heroStyle, ['split', 'overlap'], true)) {
                            $section['image_url'] = $promptDrivenImages
                                ? WebsitePromptStockImages::url($visualContext.' '.$heroQuery.' portrait', 1200, 4)
                                : self::aboutImageUrl($stockContext, $heroQuery.' feature');
                        } elseif (self::hasPersistedImageUrl((string) ($content['hero_image_url'] ?? ''))) {
                            $section['image_url'] = (string) $content['hero_image_url'];
                        }
                    }
                }

                $section['image_alt'] = $section['image_alt'] ?? $content['hero_image_alt'];
            }

            if ($type === 'about' && ! $useGalleryStockLock) {
                $aboutQuery = (string) ($section['image_query'] ?? $siteType.' team');
                $section['image_url'] = $section['image_url'] ?? (
                    $promptDrivenImages
                        ? WebsitePromptStockImages::url($visualContext.' about '.$aboutQuery, 900, 2)
                        : self::aboutImageUrl($stockContext, $aboutQuery)
                );
                $section['image_alt'] = $section['image_alt'] ?? __('About :name', ['name' => $content['site_name'] ?? $siteType]);
            }

            if ($type === 'contact' && empty($section['image_url'])) {
                $section['image_url'] = $promptDrivenImages
                    ? WebsitePromptStockImages::url($visualContext.' contact location', 1200, 3)
                    : self::contactBackgroundImageUrl($stockContext);
            }

            if (in_array($type, ['services', 'highlights'], true) && is_array($section['items'] ?? null)) {
                foreach ($section['items'] as $itemIndex => $item) {
                    if (! is_array($item)) {
                        continue;
                    }

                    $item = self::normalizeListItem($item);
                    $item['icon'] = self::sanitizeIcon((string) ($item['icon'] ?? ''), $itemIndex);

                    if ($type === 'services' && ! $useGalleryStockLock) {
                        $itemQuery = (string) ($item['image_query'] ?? ($item['title'] ?? 'service'));
                        $itemTitle = (string) ($item['title'] ?? '');
                        $item['image_url'] = $item['image_url'] ?? (
                            $promptDrivenImages
                                ? WebsitePromptStockImages::url($visualContext.' '.$itemQuery.' '.$itemTitle, 800, 10 + $itemIndex)
                                : self::serviceImageUrl($stockContext, $itemQuery, $itemIndex)
                        );
                    }

                    if ($type === 'highlights' && ! $useGalleryStockLock && empty($item['image_url'] ?? null)) {
                        $itemQuery = (string) ($item['image_query'] ?? ($item['title'] ?? 'highlight'));
                        $item['image_url'] = $promptDrivenImages
                            ? WebsitePromptStockImages::url($visualContext.' '.$itemQuery, 800, 20 + $itemIndex)
                            : self::serviceImageUrl($stockContext, $itemQuery, $itemIndex + 3);
                    }

                    $section['items'][$itemIndex] = $item;
                }
            }

            $sections[$index] = $section;
        }

        $siteName = (string) ($content['site_name'] ?? $siteType);
        $promptMode = ($content['generation_mode'] ?? '') === 'prompt' || trim($userPrompt) !== '';
        $content['sections'] = self::ensureHighlightsSection($sections, $siteType, $siteName, $promptMode);
        $content['sections'] = self::orderSections($content['sections']);
        $content['sections'] = self::completeHighlightsCopy($content['sections'], $siteName, $promptMode);
        $content['sections'] = self::completeServicesCopy($content['sections']);
        $content['sections'] = self::completeContactCopy($content['sections'], $promptMode);

        if (($content['source'] ?? '') === 'template') {
            $templateId = (string) ($content['template_id'] ?? '');

            if ($templateId !== '' && WebsiteTemplateGalleryCatalog::slugForTemplateId($templateId) !== null) {
                $content = WebsiteTemplateGalleryCatalog::applyContentImagesFillMissing($templateId, $content);
            }
        }

        $content = self::ensureUniqueLandingImages($content);

        return self::ensureDistinctHeroDualImages(
            $content,
            $siteType,
            $heroQuery,
            $visualContext,
            $promptDrivenImages,
            $stockContext,
        );
    }

    /**
     * @param  array<string, mixed>  $content
     * @return array<string, mixed>
     */
    protected static function ensureDistinctHeroDualImages(
        array $content,
        string $siteType,
        string $heroQuery,
        string $visualContext,
        bool $promptDrivenImages,
        string $stockContext,
    ): array {
        $templateId = (string) ($content['template_id'] ?? '');

        if ($templateId !== '' && WebsiteTemplateGalleryCatalog::slugForTemplateId($templateId) !== null) {
            return WebsiteTemplateGalleryCatalog::ensureDistinctHeroDualImages($templateId, $content);
        }

        $layout = is_array($content['layout_config'] ?? null)
            ? $content['layout_config']
            : ($templateId !== '' ? WebsiteTemplateLayouts::config($templateId) : []);

        $heroStyle = (string) ($layout['hero'] ?? 'split');
        if (! in_array($heroStyle, ['overlap', 'split'], true)) {
            return $content;
        }

        $background = trim((string) ($content['hero_image_url'] ?? ''));
        if ($background === '') {
            return $content;
        }

        $backgroundKey = self::normalizePhotoKey($background);
        $sections = is_array($content['sections'] ?? null) ? $content['sections'] : [];

        foreach ($sections as $index => $section) {
            if (! is_array($section) || strtolower((string) ($section['type'] ?? '')) !== 'hero') {
                continue;
            }

            $feature = trim((string) ($section['image_url'] ?? ''));
            if ($feature !== '' && self::normalizePhotoKey($feature) !== $backgroundKey) {
                break;
            }

            $aboutQuery = $siteType.' team';
            foreach ($sections as $aboutSection) {
                if (is_array($aboutSection) && strtolower((string) ($aboutSection['type'] ?? '')) === 'about') {
                    $aboutQuery = (string) ($aboutSection['image_query'] ?? $aboutQuery);
                    $aboutUrl = trim((string) ($aboutSection['image_url'] ?? ''));
                    if ($aboutUrl !== '' && self::normalizePhotoKey($aboutUrl) !== $backgroundKey) {
                        $section['image_url'] = $aboutUrl;
                        $sections[$index] = $section;
                        $content['sections'] = $sections;

                        return $content;
                    }
                }
            }

            $section['image_url'] = $promptDrivenImages
                ? WebsitePromptStockImages::url($visualContext.' '.$heroQuery.' feature', 1200, 7)
                : self::aboutImageUrl($stockContext, $aboutQuery);

            if (self::normalizePhotoKey((string) $section['image_url']) === $backgroundKey) {
                $section['image_url'] = $promptDrivenImages
                    ? WebsitePromptStockImages::url($visualContext.' '.$heroQuery.' showcase', 1200, 11)
                    : self::serviceImageUrl($stockContext, $heroQuery, 2);
            }

            $sections[$index] = $section;
            break;
        }

        $content['sections'] = $sections;

        return $content;
    }

    protected static function normalizePhotoKey(string $url): string
    {
        if (preg_match('#images\.unsplash\.com/photo-([a-zA-Z0-9_-]+)#', $url, $matches)) {
            return 'unsplash:'.$matches[1];
        }

        return trim($url);
    }

    public static function isSamePhoto(string $a, string $b): bool
    {
        $a = trim($a);
        $b = trim($b);

        if ($a === '' || $b === '') {
            return false;
        }

        if ($a === $b) {
            return true;
        }

        return self::normalizePhotoKey($a) === self::normalizePhotoKey($b);
    }

    /**
     * @param  array<int, array<string, mixed>>  $sections
     * @return array<int, array<string, mixed>>
     */
    /**
     * @param  array<int, array<string, mixed>>  $sections
     * @return array<int, array<string, mixed>>
     */
    protected static function completeHighlightsCopy(array $sections, string $siteName, bool $promptMode = false): array
    {
        $defaults = self::defaultHighlightItems($siteName);

        foreach ($sections as $index => $section) {
            if (! is_array($section) || strtolower((string) ($section['type'] ?? '')) !== 'highlights') {
                continue;
            }

            if (! filled($section['headline'] ?? null)) {
                $section['headline'] = __('Why choose :name', ['name' => $siteName]);
            }

            if (! $promptMode && ! filled($section['subheadline'] ?? null)) {
                $section['subheadline'] = __('Everything you need to grow online.');
            }

            $items = is_array($section['items'] ?? null) ? $section['items'] : [];

            if ($promptMode) {
                foreach ($items as $itemIndex => $item) {
                    if (is_array($item)) {
                        $items[$itemIndex] = self::normalizeListItem($item);
                    }
                }
                $section['items'] = array_values(array_slice($items, 0, 4));
                $sections[$index] = $section;
                break;
            }

            foreach ($defaults as $defaultIndex => $defaultItem) {
                if (! isset($items[$defaultIndex]) || ! is_array($items[$defaultIndex])) {
                    $items[$defaultIndex] = $defaultItem;
                    continue;
                }

                $items[$defaultIndex] = self::normalizeListItem($items[$defaultIndex]);

                if (! filled($items[$defaultIndex]['title'] ?? null)) {
                    $items[$defaultIndex]['title'] = $defaultItem['title'];
                }

                if (! filled($items[$defaultIndex]['description'] ?? null)) {
                    $items[$defaultIndex]['description'] = $defaultItem['description'];
                }

                if (! filled($items[$defaultIndex]['icon'] ?? null)) {
                    $items[$defaultIndex]['icon'] = $defaultItem['icon'];
                }
            }

            $section['items'] = array_values(array_slice($items, 0, 4));
            $sections[$index] = $section;
            break;
        }

        return $sections;
    }

    /**
     * @param  array<int, array<string, mixed>>  $sections
     * @return array<int, array<string, mixed>>
     */
    protected static function completeContactCopy(array $sections, bool $promptMode = false): array
    {
        foreach ($sections as $index => $section) {
            if (! is_array($section) || strtolower((string) ($section['type'] ?? '')) !== 'contact') {
                continue;
            }

            if (! filled($section['headline'] ?? null)) {
                $section['headline'] = __('Get in touch');
            }

            if (! $promptMode && ! filled($section['body'] ?? null)) {
                $section['body'] = __('We would love to hear from you. Reach out anytime.');
            }

            $sections[$index] = $section;
            break;
        }

        return $sections;
    }

    protected static function completeServicesCopy(array $sections): array
    {
        foreach ($sections as $index => $section) {
            if (! is_array($section) || strtolower((string) ($section['type'] ?? '')) !== 'services') {
                continue;
            }

            if (! filled($section['headline'] ?? null)) {
                $section['headline'] = __('Our services');
            }

            $sections[$index] = $section;
            break;
        }

        return $sections;
    }

    /**
     * @return array<int, array{title: string, description: string, icon: string, image_query: string}>
     */
    protected static function defaultHighlightItems(string $siteName): array
    {
        return [
            ['title' => __('Fast setup'), 'description' => __('Launch a polished site without waiting on design cycles.'), 'icon' => 'bolt', 'image_query' => 'startup workspace'],
            ['title' => __('Mobile ready'), 'description' => __('Layouts adapt to phones, tablets, and desktops automatically.'), 'icon' => 'mobile-screen', 'image_query' => 'mobile business'],
            ['title' => __('Secure & reliable'), 'description' => __('Best practices for performance, privacy, and uptime.'), 'icon' => 'shield-halved', 'image_query' => 'security technology'],
            ['title' => __('Support that cares'), 'description' => __('Friendly help when you need to update content or publish.'), 'icon' => 'headset', 'image_query' => 'customer support'],
        ];
    }

    protected static function ensureHighlightsSection(array $sections, string $siteType, string $siteName, bool $promptMode = false): array
    {
        foreach ($sections as $section) {
            if (is_array($section) && strtolower((string) ($section['type'] ?? '')) === 'highlights') {
                return $sections;
            }
        }

        $sections[] = [
            'type' => 'highlights',
            'headline' => __('Why choose :name', ['name' => $siteName]),
            'subheadline' => $promptMode ? '' : __('Everything you need to grow online.'),
            'items' => $promptMode ? [] : self::defaultHighlightItems($siteName),
        ];

        return $sections;
    }

    /**
     * @param  array<int, array<string, mixed>>  $sections
     * @return array<int, array<string, mixed>>
     */
    /**
     * @param  array<int, mixed>  $sections
     * @return array<int, array<string, mixed>>
     */
    protected static function normalizeSectionTypes(array $sections): array
    {
        $fallbackTypes = ['hero', 'about', 'services', 'highlights', 'contact'];

        foreach ($sections as $index => $section) {
            if (! is_array($section)) {
                continue;
            }

            $type = strtolower(trim((string) ($section['type'] ?? '')));
            if ($type === '' && isset($fallbackTypes[$index])) {
                $section['type'] = $fallbackTypes[$index];
                $sections[$index] = $section;
            }
        }

        return $sections;
    }

    protected static function orderSections(array $sections): array
    {
        $order = ['hero' => 1, 'about' => 2, 'services' => 3, 'highlights' => 4, 'contact' => 5];

        return collect($sections)
            ->filter(fn ($section) => is_array($section))
            ->sortBy(fn (array $section) => $order[strtolower((string) ($section['type'] ?? ''))] ?? 99)
            ->values()
            ->all();
    }

    /**
     * @param  array<string, mixed>  $item
     * @return array<string, mixed>
     */
    protected static function normalizeListItem(array $item): array
    {
        if (! filled($item['title'] ?? null) && filled($item['name'] ?? null)) {
            $item['title'] = (string) $item['name'];
        }

        if (! filled($item['description'] ?? null)) {
            foreach (['body', 'text', 'summary'] as $key) {
                if (filled($item[$key] ?? null)) {
                    $item['description'] = (string) $item[$key];
                    break;
                }
            }
        }

        return $item;
    }

    public static function sanitizeHexPublic(string $value, string $fallback = '#5b4dff'): string
    {
        return self::sanitizeHex($value, $fallback);
    }

    protected static function hasPersistedImageUrl(string $url): bool
    {
        $url = trim($url);

        return $url !== '' && (str_starts_with($url, 'http://') || str_starts_with($url, 'https://'));
    }

    protected static function sanitizeHex(string $value, string $fallback): string
    {
        $value = trim($value);

        if (preg_match('/^#[0-9A-Fa-f]{6}$/', $value) === 1) {
            return $value;
        }

        return $fallback;
    }

    protected static function sanitizeIcon(string $icon, int $index): string
    {
        $icon = preg_replace('/[^a-z0-9-]/', '', strtolower($icon)) ?? '';

        if ($icon === '') {
            return ['cart-shopping', 'star', 'shield-halved'][$index % 3];
        }

        return $icon;
    }

    protected static function heroImageUrl(string $siteType, string $query): string
    {
        $key = self::categoryKey($siteType.' '.$query);

        return match ($key) {
            'ecommerce', 'store', 'shop' => 'https://images.unsplash.com/photo-1556742049-0cfed4f6a45d?auto=format&fit=crop&w=1600&q=80',
            'beauty', 'salon', 'barber' => 'https://images.unsplash.com/photo-1503951914875-452162b0f3f1?auto=format&fit=crop&w=1600&q=80',
            'restaurant', 'food' => 'https://images.unsplash.com/photo-1517248135467-4c7edcad34c4?auto=format&fit=crop&w=1600&q=80',
            'cleaning' => 'https://images.unsplash.com/photo-1581578731548-c64695cc6952?auto=format&fit=crop&w=1600&q=80',
            'pet', 'grooming' => 'https://images.unsplash.com/photo-1560066984-138dadb4c035?auto=format&fit=crop&w=1600&q=80',
            'contractor', 'construction' => 'https://images.unsplash.com/photo-1504307651254-35680f356dfd?auto=format&fit=crop&w=1600&q=80',
            default => 'https://images.unsplash.com/photo-1497366216548-37526070297c?auto=format&fit=crop&w=1600&q=80',
        };
    }

    protected static function aboutImageUrl(string $siteType, string $query): string
    {
        $key = self::categoryKey($siteType.' '.$query);

        return match ($key) {
            'ecommerce', 'store' => 'https://images.unsplash.com/photo-1523275335684-37898b6baf30?auto=format&fit=crop&w=900&q=80',
            'restaurant', 'food' => 'https://images.unsplash.com/photo-1414235077428-338989a2e8c0?auto=format&fit=crop&w=900&q=80',
            'beauty', 'salon', 'barber' => 'https://images.unsplash.com/photo-1585747860715-4564b700468a?auto=format&fit=crop&w=900&q=80',
            'cleaning' => 'https://images.unsplash.com/photo-1581578731548-c64695cc6952?auto=format&fit=crop&w=900&q=80',
            'pet', 'grooming' => 'https://images.unsplash.com/photo-1601758228041-f3b2795255f1?auto=format&fit=crop&w=900&q=80',
            'creative' => 'https://images.unsplash.com/photo-1498050108023-c5249f4df085?auto=format&fit=crop&w=900&q=80',
            default => 'https://images.unsplash.com/photo-1497366216548-37526070297c?auto=format&fit=crop&w=900&q=80',
        };
    }

    protected static function contactBackgroundImageUrl(string $siteType): string
    {
        $key = self::categoryKey($siteType);

        return match ($key) {
            'ecommerce', 'store', 'shop' => 'https://images.unsplash.com/photo-1556740758-90de374c12ad?auto=format&fit=crop&w=1200&q=80',
            'restaurant', 'food' => 'https://images.unsplash.com/photo-1559339352-11d035aa65de?auto=format&fit=crop&w=1200&q=80',
            default => 'https://images.unsplash.com/photo-1497366811353-6870744d04b2?auto=format&fit=crop&w=1200&q=80',
        };
    }

    protected static function serviceImageUrl(string $siteType, string $query, int $index): string
    {
        $category = self::categoryKey($siteType.' '.$query);

        $pool = match ($category) {
            'beauty', 'salon', 'barber' => [
                'https://images.unsplash.com/photo-1522337360788-8b13dee7a37e?auto=format&fit=crop&w=800&q=80',
                'https://images.unsplash.com/photo-1585747860715-4564b700468a?auto=format&fit=crop&w=800&q=80',
                'https://images.unsplash.com/photo-1503951914875-452162b0f3f1?auto=format&fit=crop&w=800&q=80',
                'https://images.unsplash.com/photo-1633685042214-98d45ae2b0a7?auto=format&fit=crop&w=800&q=80',
            ],
            'cleaning' => [
                'https://images.unsplash.com/photo-1581578731548-c64695cc6952?auto=format&fit=crop&w=800&q=80',
                'https://images.unsplash.com/photo-1628177142898-93e36e4e3a50?auto=format&fit=crop&w=800&q=80',
                'https://images.unsplash.com/photo-1527515637462-cff94eecc1ac?auto=format&fit=crop&w=800&q=80',
                'https://images.unsplash.com/photo-1504307651254-35680f356dfd?auto=format&fit=crop&w=800&q=80',
            ],
            'pet', 'grooming' => [
                'https://images.unsplash.com/photo-1560066984-138dadb4c035?auto=format&fit=crop&w=800&q=80',
                'https://images.unsplash.com/photo-1587300003388-59208cc962cb?auto=format&fit=crop&w=800&q=80',
                'https://images.unsplash.com/photo-1601758228041-f3b2795255f1?auto=format&fit=crop&w=800&q=80',
                'https://images.unsplash.com/photo-1530281700549-e82e7bf110d6?auto=format&fit=crop&w=800&q=80',
                'https://images.unsplash.com/photo-1450778869180-41d0601e046e?auto=format&fit=crop&w=800&q=80',
                'https://images.unsplash.com/photo-1514888286974-6c03e2ca1dba?auto=format&fit=crop&w=800&q=80',
                'https://images.unsplash.com/photo-1543852786-1cf6624b9987?auto=format&fit=crop&w=800&q=80',
            ],
            'restaurant', 'food' => [
                'https://images.unsplash.com/photo-1504674900247-0877df9cc836?auto=format&fit=crop&w=800&q=80',
                'https://images.unsplash.com/photo-1414235077428-338989a2e8c0?auto=format&fit=crop&w=800&q=80',
                'https://images.unsplash.com/photo-1517248135467-4c7edcad34c4?auto=format&fit=crop&w=800&q=80',
                'https://images.unsplash.com/photo-1554118811-1e4d4dfbd76c?auto=format&fit=crop&w=800&q=80',
            ],
            'ecommerce', 'store', 'shop' => [
                'https://images.unsplash.com/photo-1556742049-0cfed4f6a45d?auto=format&fit=crop&w=800&q=80',
                'https://images.unsplash.com/photo-1441986300917-64674bd600d8?auto=format&fit=crop&w=800&q=80',
                'https://images.unsplash.com/photo-1523275335684-37898b6baf30?auto=format&fit=crop&w=800&q=80',
                'https://images.unsplash.com/photo-1556740758-90de374c12ad?auto=format&fit=crop&w=800&q=80',
            ],
            'creative' => [
                'https://images.unsplash.com/photo-1498050108023-c5249f4df085?auto=format&fit=crop&w=800&q=80',
                'https://images.unsplash.com/photo-1561070791-2526d30994b5?auto=format&fit=crop&w=800&q=80',
                'https://images.unsplash.com/photo-1452587925148-ce544e77e70d?auto=format&fit=crop&w=800&q=80',
                'https://images.unsplash.com/photo-1558655146-9a402cabf029?auto=format&fit=crop&w=800&q=80',
            ],
            default => [
                'https://images.unsplash.com/photo-1497366216548-37526070297c?auto=format&fit=crop&w=800&q=80',
                'https://images.unsplash.com/photo-1553877522-43269d4ea984?auto=format&fit=crop&w=800&q=80',
                'https://images.unsplash.com/photo-1504307651254-35680f356dfd?auto=format&fit=crop&w=800&q=80',
                'https://images.unsplash.com/photo-1556740758-90de374c12ad?auto=format&fit=crop&w=800&q=80',
            ],
        };

        $offset = crc32($category) % count($pool);

        return $pool[($offset + $index) % count($pool)];
    }

    protected static function categoryKey(string $text): string
    {
        $text = strtolower($text);

        foreach (['ecommerce', 'store', 'shop', 'beauty', 'salon', 'barber', 'restaurant', 'food', 'cleaning', 'pet', 'grooming', 'creative', 'contractor', 'construction'] as $token) {
            if (str_contains($text, $token)) {
                return $token;
            }
        }

        return 'default';
    }

    /**
     * Guarantee every image slot on the landing page uses a different photo (same page, no repeats).
     *
     * @param  array<string, mixed>  $content
     * @return array<string, mixed>
     */
    public static function ensureUniqueLandingImages(array $content): array
    {
        $siteType = (string) ($content['site_type'] ?? $content['site_name'] ?? 'business');
        $pool = self::imagePoolForContent($content, $siteType);

        if ($pool === []) {
            return $content;
        }

        /** @var array<string, true> $used */
        $used = [];
        $cursor = 0;
        $poolCount = count($pool);

        $claimOrReplace = static function (string $url, int $width) use (&$used, &$cursor, $pool, $poolCount): string {
            $url = trim($url);
            $key = $url === '' ? '' : self::normalizePhotoKey($url);

            if ($url !== '' && $key !== '' && ! isset($used[$key])) {
                $used[$key] = true;

                return $url;
            }

            for ($attempt = 0; $attempt < $poolCount * 2; $attempt++) {
                $raw = $pool[$cursor % $poolCount];
                $cursor++;
                $candidateKey = self::normalizePhotoKey($raw);

                if (isset($used[$candidateKey])) {
                    continue;
                }

                $used[$candidateKey] = true;

                return self::unsplashSized($raw, $width);
            }

            $raw = $pool[$cursor % $poolCount];
            $cursor++;
            $used[self::normalizePhotoKey($raw)] = true;

            return self::unsplashSized($raw, $width);
        };

        $content['hero_image_url'] = $claimOrReplace((string) ($content['hero_image_url'] ?? ''), 1600);

        $sections = is_array($content['sections'] ?? null) ? $content['sections'] : [];

        foreach ($sections as $index => $section) {
            if (! is_array($section)) {
                continue;
            }

            $type = strtolower((string) ($section['type'] ?? ''));

            if ($type === 'hero' && array_key_exists('image_url', $section)) {
                $section['image_url'] = $claimOrReplace((string) ($section['image_url'] ?? ''), 1200);
            }

            if ($type === 'about') {
                $section['image_url'] = $claimOrReplace((string) ($section['image_url'] ?? ''), 900);
            }

            if ($type === 'contact' && filled($section['image_url'] ?? null)) {
                $section['image_url'] = $claimOrReplace((string) $section['image_url'], 1200);
            }

            if (in_array($type, ['services', 'highlights'], true) && is_array($section['items'] ?? null)) {
                foreach ($section['items'] as $itemIndex => $item) {
                    if (! is_array($item) || ! filled($item['image_url'] ?? null)) {
                        continue;
                    }

                    $item['image_url'] = $claimOrReplace((string) $item['image_url'], 800);
                    $section['items'][$itemIndex] = $item;
                }
            }

            $sections[$index] = $section;
        }

        $content['sections'] = $sections;

        return $content;
    }

    /**
     * @param  array<string, mixed>  $content
     * @return array{hero: string, feature: string, about: string, service: string}
     */
    public static function landingImageFallbacks(array $content, string $siteType = 'business'): array
    {
        $generic = 'https://images.unsplash.com/photo-1497366216548-37526070297c?auto=format&fit=crop&w=800&q=80';
        $pool = self::imagePoolForContent($content, $siteType);

        if ($pool === []) {
            return [
                'hero' => $generic,
                'feature' => $generic,
                'about' => $generic,
                'service' => $generic,
            ];
        }

        $last = count($pool) - 1;

        return [
            'hero' => self::unsplashSized($pool[0], 1600),
            'feature' => self::unsplashSized($pool[min(1, $last)], 1200),
            'about' => self::unsplashSized($pool[min(2, $last)], 900),
            'service' => self::unsplashSized($pool[min(3, $last)], 800),
        ];
    }

    /**
     * @param  array<string, mixed>  $content
     * @return list<string>
     */
    protected static function imagePoolForContent(array $content, string $siteType): array
    {
        $templateId = (string) ($content['template_id'] ?? '');

        if ($templateId !== '') {
            $galleryPool = WebsiteTemplateGalleryCatalog::stockImagePoolForTemplate($templateId);

            if ($galleryPool !== []) {
                return $galleryPool;
            }
        }

        $pool = [];

        for ($index = 0; $index < 12; $index++) {
            $pool[] = self::serviceImageUrl($siteType, $siteType, $index);
        }

        return self::uniqueUrlsByPhotoKey($pool);
    }

    /**
     * @param  list<string>  $urls
     * @return list<string>
     */
    protected static function uniqueUrlsByPhotoKey(array $urls): array
    {
        $unique = [];
        $seen = [];

        foreach ($urls as $url) {
            $key = self::normalizePhotoKey($url);

            if (isset($seen[$key])) {
                continue;
            }

            $seen[$key] = true;
            $unique[] = $url;
        }

        return $unique;
    }

    protected static function unsplashSized(string $url, int $width): string
    {
        if (preg_match('/w=\d+/', $url) === 1) {
            return (string) preg_replace('/w=\d+/', 'w='.$width, $url);
        }

        $join = str_contains($url, '?') ? '&' : '?';

        return $url.$join.'w='.$width.'&q=80&auto=format&fit=crop';
    }
}
