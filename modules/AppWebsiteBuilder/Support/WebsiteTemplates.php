<?php

namespace Modules\AppWebsiteBuilder\Support;

use InvalidArgumentException;

class WebsiteTemplates
{
    /**
     * @return list<array<string, mixed>>
     */
    public static function galleryCatalogDefinitions(): array
    {
        return WebsiteTemplateGalleryCatalog::definitions();
    }

    /**
     * Legacy templates kept for existing saved sites (not shown in the 30-template gallery).
     *
     * @return list<array<string, mixed>>
     */
    protected static function legacyCatalogDefinitions(): array
    {
        return [
            [
                'id' => 'classic-business',
                'name' => __('Classic Business'),
                'description' => __('Balanced hero, services grid, and trust highlights for any local brand.'),
                'accent' => '#5b4dff',
                'for_categories' => ['general', 'local', 'restaurant', 'ecommerce', 'creative'],
                'for_niches' => ['cleaning', 'beauty', 'trades', 'general'],
                'recommended_for' => ['general'],
                'preview_url' => 'https://images.unsplash.com/photo-1497366216548-37526070297c?auto=format&fit=crop&w=800&q=80',
                'preview_by_category' => [
                    'local' => 'https://images.unsplash.com/photo-1504307651254-35680f356dfd?auto=format&fit=crop&w=800&q=80',
                    'restaurant' => 'https://images.unsplash.com/photo-1517248135467-4c7edcad34c4?auto=format&fit=crop&w=800&q=80',
                    'ecommerce' => 'https://images.unsplash.com/photo-1556742049-0cfed4f6a45d?auto=format&fit=crop&w=800&q=80',
                    'creative' => 'https://images.unsplash.com/photo-1498050108023-c5249f4df085?auto=format&fit=crop&w=800&q=80',
                ],
            ],
            [
                'id' => 'modern-startup',
                'name' => __('Modern Startup'),
                'description' => __('Clean lines, bold headline, and product-style feature cards.'),
                'accent' => '#0ea5e9',
                'for_categories' => ['general', 'ecommerce', 'creative', 'local'],
                'for_niches' => ['cleaning', 'beauty', 'trades', 'general'],
                'recommended_for' => ['general', 'ecommerce'],
                'preview_url' => 'https://images.unsplash.com/photo-1460925895917-afdab827c52f?auto=format&fit=crop&w=800&q=80',
                'preview_by_category' => [
                    'local' => 'https://images.unsplash.com/photo-1553877522-43269d4ea984?auto=format&fit=crop&w=800&q=80',
                ],
            ],
            [
                'id' => 'local-service',
                'name' => __('Local Service'),
                'description' => __('Warm welcome copy built for salons, cleaning, and trades.'),
                'accent' => '#059669',
                'for_categories' => ['local'],
                'for_niches' => ['cleaning', 'beauty', 'trades', 'general'],
                'recommended_for' => ['local'],
                'preview_url' => 'https://images.unsplash.com/photo-1581578731548-c64695cc6952?auto=format&fit=crop&w=800&q=80',
            ],
            [
                'id' => 'trusted-trades',
                'name' => __('Trusted Trades'),
                'description' => __('Before/after proof, service areas, and quote requests.'),
                'accent' => '#0284c7',
                'for_categories' => ['local'],
                'for_niches' => ['cleaning', 'trades', 'general'],
                'recommended_for' => [],
                'preview_url' => 'https://images.unsplash.com/photo-1504307651254-35680f356dfd?auto=format&fit=crop&w=800&q=80',
            ],
        ];
    }

    /**
     * @return list<array<string, mixed>>
     */
    public static function catalogDefinitions(): array
    {
        return array_merge(
            self::galleryCatalogDefinitions(),
            self::legacyCatalogDefinitions(),
        );
    }

    public static function resolveLocalNiche(string $siteType): string
    {
        $text = strtolower(trim($siteType));

        if (str_contains($text, 'pet') || str_contains($text, 'groom') || str_contains($text, 'dog') || str_contains($text, 'cat')) {
            return 'pet';
        }

        if (str_contains($text, 'clean')) {
            return 'cleaning';
        }

        if (str_contains($text, 'salon') || str_contains($text, 'beauty') || str_contains($text, 'spa')) {
            return 'beauty';
        }

        if (str_contains($text, 'contractor') || str_contains($text, 'construction') || str_contains($text, 'plumb') || str_contains($text, 'electric')) {
            return 'trades';
        }

        return 'general';
    }

    public static function resolveCategory(string $siteType): string
    {
        $text = strtolower(trim($siteType));

        foreach ([
            'ecommerce' => ['ecommerce', 'e-commerce', 'store', 'shop', 'retail'],
            'restaurant' => ['restaurant', 'cafe', 'food', 'dining', 'bistro', 'bakery'],
            'local' => ['pet', 'grooming', 'cleaning', 'salon', 'beauty', 'spa', 'contractor', 'construction', 'plumber', 'electrician', 'landscap'],
            'creative' => ['studio', 'agency', 'design', 'photography', 'portfolio', 'artist'],
        ] as $category => $tokens) {
            foreach ($tokens as $token) {
                if (str_contains($text, $token)) {
                    return $category;
                }
            }
        }

        return 'general';
    }

    /**
     * Gallery category chips — each template id appears in exactly one bucket.
     *
     * @return array<string, string>
     */
    public static function galleryExampleSlugLabels(): array
    {
        return [
            'cleaning' => __('Cleaning Service'),
            'beauty' => __('Beauty Salon'),
            'ecommerce' => __('E-commerce Store'),
            'pet' => __('Pet Grooming'),
            'restaurant' => __('Local Restaurant'),
            'creative' => __('Design Studio'),
        ];
    }

    /**
     * @return list<string>
     */
    public static function galleryExampleOptions(): array
    {
        return array_values(self::galleryExampleSlugLabels());
    }

    /**
     * Five unique templates per gallery category slug (30 total, zero overlap).
     *
     * @return array<string, list<string>>
     */
    public static function exclusiveGalleryTemplateIdsBySlug(): array
    {
        $map = [];

        foreach (self::galleryCatalogDefinitions() as $definition) {
            $slug = (string) ($definition['gallery_slug'] ?? '');

            if ($slug === '') {
                continue;
            }

            $map[$slug][] = (string) ($definition['id'] ?? '');
        }

        return $map;
    }

    public static function resolveGalleryExampleSlug(string $siteType): ?string
    {
        $siteType = trim($siteType);

        if ($siteType === '') {
            return null;
        }

        foreach (self::galleryExampleSlugLabels() as $slug => $label) {
            if ($siteType === $label) {
                return $slug;
            }
        }

        return self::inferGalleryExampleSlug($siteType);
    }

    protected static function inferGalleryExampleSlug(string $siteType): ?string
    {
        $category = self::resolveCategory($siteType);
        $niche = $category === 'local' ? self::resolveLocalNiche($siteType) : 'general';

        return match (true) {
            $category === 'restaurant' => 'restaurant',
            $category === 'ecommerce' => 'ecommerce',
            $category === 'creative' => 'creative',
            $niche === 'pet' => 'pet',
            $niche === 'cleaning', $niche === 'trades' => 'cleaning',
            $niche === 'beauty' => 'beauty',
            $category === 'local' => 'cleaning',
            default => 'beauty',
        };
    }

    /**
     * Templates shown on step 3 for the given website type.
     *
     * @return list<array{id: string, name: string, description: string, preview_url: string, accent: string, recommended: bool}>
     */
    public static function catalogFor(string $siteType): array
    {
        $slug = self::resolveGalleryExampleSlug($siteType) ?? self::inferGalleryExampleSlug($siteType);

        if ($slug !== null) {
            $matched = self::matchedTemplatesForSiteType($siteType);
            $result = self::galleryRowsForExclusiveSlug($slug, $siteType, $matched);

            if ($result !== []) {
                $result = self::ensureDistinctPreviewUrls(array_slice($result, 0, 5), $matched['niche']);

                return self::stripTemplateSortKeys($result);
            }
        }

        $matched = self::matchedTemplatesForSiteType($siteType);
        $result = $matched['items'];
        $niche = $matched['niche'];

        if ($result === []) {
            return self::catalogFor(__('Business'));
        }

        $result = self::ensureDistinctPreviewUrls(array_slice($result, 0, 5), $niche);

        return self::stripTemplateSortKeys($result);
    }

    /**
     * Full template gallery for the templates page (no five-item cap).
     *
     * @return list<array<string, mixed>>
     */
    public static function galleryCatalogSize(): int
    {
        return count(self::galleryCatalogDefinitions());
    }

    public static function galleryForSiteType(string $siteType): array
    {
        $siteType = trim($siteType);

        if ($siteType === '') {
            return self::galleryAllTemplates();
        }

        foreach (self::galleryExampleSlugLabels() as $slug => $label) {
            if (strcasecmp($siteType, $label) === 0) {
                return self::galleryForCategorySlug($slug, $siteType);
            }
        }

        $slug = self::resolveGalleryExampleSlug($siteType);

        if ($slug !== null) {
            $byCategory = self::galleryForCategorySlug($slug, $siteType);

            if ($byCategory !== []) {
                return $byCategory;
            }
        }

        return self::galleryTextSearch($siteType);
    }

    /**
     * @return list<array<string, mixed>>
     */
    public static function galleryForCategorySlug(string $slug, string $siteType): array
    {
        $matched = self::matchedTemplatesForSiteType($siteType);
        $result = self::galleryRowsForExclusiveSlug($slug, $siteType, $matched);

        if ($result === []) {
            return [];
        }

        $result = self::ensureDistinctPreviewUrls($result, $matched['niche']);

        return self::dedupeTemplateRowsById(self::stripTemplateSortKeys($result));
    }

    /**
     * @return list<array<string, mixed>>
     */
    public static function galleryTextSearch(string $query): array
    {
        $needle = strtolower(trim($query));

        if ($needle === '') {
            return self::galleryAllTemplates();
        }

        $tokens = preg_split('/\s+/', $needle) ?: [];

        $filtered = array_values(array_filter(self::galleryAllTemplates(), static function (array $template) use ($needle, $tokens): bool {
            $haystack = strtolower(
                (string) ($template['id'] ?? '').' '
                .(string) ($template['name'] ?? '').' '
                .(string) ($template['description'] ?? '')
            );

            if (str_contains($haystack, $needle)) {
                return true;
            }

            foreach ($tokens as $token) {
                if ($token !== '' && ! str_contains($haystack, $token)) {
                    return false;
                }
            }

            return $tokens !== [];
        }));

        return $filtered;
    }

    /**
     * @param  array{items: list<array<string, mixed>>, niche: string}  $matched
     * @return list<array<string, mixed>>
     */
    protected static function galleryRowsForExclusiveSlug(string $slug, string $siteType, array $matched): array
    {
        $ids = self::exclusiveGalleryTemplateIdsBySlug()[$slug] ?? [];

        if ($ids === []) {
            return [];
        }

        $matchedById = [];

        foreach ($matched['items'] as $item) {
            $matchedById[(string) ($item['id'] ?? '')] = $item;
        }

        $category = self::resolveCategory($siteType);
        $niche = (string) ($matched['niche'] ?? 'general');
        $typeLabel = trim($siteType) !== '' ? trim($siteType) : __('your business');

        $result = [];

        foreach ($ids as $id) {
            if (isset($matchedById[$id])) {
                $result[] = $matchedById[$id];

                continue;
            }

            $definition = self::findDefinitionById($id);

            if ($definition === null) {
                continue;
            }

            $recommended = self::isRecommended($definition, $category, $niche);
            $isFirstInSlug = ($ids[0] ?? '') === $id;

            $result[] = [
                'id' => $id,
                'name' => (string) $definition['name'],
                'description' => self::descriptionForType($definition, $category, $typeLabel, $niche),
                'preview_url' => self::previewForDefinition($definition, $category, $niche),
                'accent' => (string) ($definition['accent'] ?? '#5b4dff'),
                'recommended' => $recommended || $isFirstInSlug,
                'sort' => ($recommended || $isFirstInSlug) ? 0 : 1,
            ];
        }

        usort($result, fn (array $a, array $b): int => ($a['sort'] ?? 1) <=> ($b['sort'] ?? 1) ?: strcmp((string) $a['name'], (string) $b['name']));

        return $result;
    }

    /**
     * @return array<string, mixed>|null
     */
    protected static function findDefinitionById(string $templateId): ?array
    {
        foreach (self::catalogDefinitions() as $definition) {
            if (($definition['id'] ?? '') === $templateId) {
                return $definition;
            }
        }

        return null;
    }

    /**
     * Every catalog template for the gallery (same card shape as filtered results).
     *
     * @return list<array<string, mixed>>
     */
    public static function galleryAllTemplates(): array
    {
        $result = [];

        foreach (self::galleryCatalogDefinitions() as $definition) {
            $preview = (string) ($definition['preview_url'] ?? '');

            $result[] = [
                'id' => (string) $definition['id'],
                'name' => (string) $definition['name'],
                'description' => (string) ($definition['description'] ?? ''),
                'preview_url' => $preview,
                'accent' => (string) ($definition['accent']),
                'recommended' => false,
                'sort' => 0,
            ];
        }

        usort($result, fn (array $a, array $b): int => strcmp((string) $a['name'], (string) $b['name']));

        $result = self::ensureDistinctPreviewUrls($result, 'general');

        return self::dedupeTemplateRowsById(self::stripTemplateSortKeys($result));
    }

    /**
     * @param  list<array<string, mixed>>  $rows
     * @return list<array<string, mixed>>
     */
    protected static function dedupeTemplateRowsById(array $rows): array
    {
        $seen = [];
        $unique = [];

        foreach ($rows as $row) {
            $id = (string) ($row['id'] ?? '');

            if ($id === '' || isset($seen[$id])) {
                continue;
            }

            $seen[$id] = true;
            $unique[] = $row;
        }

        return $unique;
    }

    /**
     * @return array{items: list<array<string, mixed>>, niche: string}
     */
    protected static function matchedTemplatesForSiteType(string $siteType): array
    {
        $category = self::resolveCategory($siteType);
        $niche = $category === 'local' ? self::resolveLocalNiche($siteType) : 'general';
        $typeLabel = trim($siteType) !== '' ? trim($siteType) : __('your business');

        $result = [];

        foreach (self::catalogDefinitions() as $definition) {
            $fits = in_array($category, (array) ($definition['for_categories'] ?? []), true);

            if (! $fits) {
                continue;
            }

            if ($category === 'local' && ! self::definitionMatchesLocalNiche($definition, $niche)) {
                continue;
            }

            $recommended = self::isRecommended($definition, $category, $niche);

            $preview = self::previewForDefinition($definition, $category, $niche);
            $description = self::descriptionForType($definition, $category, $typeLabel, $niche);

            $result[] = [
                'id' => (string) $definition['id'],
                'name' => (string) $definition['name'],
                'description' => $description,
                'preview_url' => $preview,
                'accent' => (string) $definition['accent'],
                'recommended' => $recommended,
                'sort' => $recommended ? 0 : 1,
            ];
        }

        usort($result, fn (array $a, array $b): int => $a['sort'] <=> $b['sort'] ?: strcmp($a['name'], $b['name']));

        return ['items' => $result, 'niche' => $niche];
    }

    /**
     * @param  list<array<string, mixed>>  $rows
     * @return list<array<string, mixed>>
     */
    protected static function stripTemplateSortKeys(array $rows): array
    {
        return array_map(function (array $row): array {
            unset($row['sort']);

            return $row;
        }, $rows);
    }

    /**
     * @param  array<string, mixed>  $definition
     */
    protected static function definitionMatchesLocalNiche(array $definition, string $niche): bool
    {
        $allowed = (array) ($definition['for_niches'] ?? ['general', 'cleaning', 'beauty', 'trades']);

        return in_array($niche, $allowed, true);
    }

    /**
     * @param  array<string, mixed>  $definition
     */
    protected static function isRecommended(array $definition, string $category, string $niche): bool
    {
        if ($category === 'local' && $niche === 'pet') {
            return ($definition['id'] ?? '') === 'pet-grooming-salon';
        }

        return in_array($category, (array) ($definition['recommended_for'] ?? []), true);
    }

    /**
     * @param  array<string, mixed>  $definition
     */
    protected static function previewForDefinition(array $definition, string $category, string $niche): string
    {
        if ($category === 'local' && $niche === 'pet') {
            return (string) ($definition['preview_url'] ?? '');
        }

        if (isset($definition['preview_by_niche'][$niche]) && is_string($definition['preview_by_niche'][$niche])) {
            return $definition['preview_by_niche'][$niche];
        }

        return (string) ($definition['preview_by_category'][$category] ?? $definition['preview_url'] ?? '');
    }

    /**
     * @param  list<array<string, mixed>>  $templates
     * @return list<array<string, mixed>>
     */
    protected static function ensureDistinctPreviewUrls(array $templates, string $niche): array
    {
        /** @var array<string, true> $usedPhotoKeys */
        $usedPhotoKeys = [];
        $pool = self::previewUrlPoolForNiche($niche);

        foreach ($templates as $index => $template) {
            $id = (string) ($template['id'] ?? '');
            $url = (string) ($template['preview_url'] ?? '');
            $photoKey = self::previewPhotoKey($url);

            if ($url === '' || $photoKey === '' || isset($usedPhotoKeys[$photoKey])) {
                $url = self::pickDistinctPreviewFromPool($pool, $usedPhotoKeys, $id, $index);
                $template['preview_url'] = $url;
                $photoKey = self::previewPhotoKey($url);
            }

            if ($photoKey !== '') {
                $usedPhotoKeys[$photoKey] = true;
            }

            $templates[$index] = $template;
        }

        foreach ($templates as $index => $template) {
            $id = (string) ($template['id'] ?? '');
            $url = (string) ($template['preview_url'] ?? '');
            $template['preview_fallback'] = self::previewFallbackForTemplate($id, $url, $pool, $index, $usedPhotoKeys);
            $templates[$index] = $template;
        }

        return $templates;
    }

  protected static function previewPhotoKey(string $url): string
    {
        if (preg_match('/photo-([a-zA-Z0-9_-]+)/', $url, $matches) === 1) {
            return (string) $matches[1];
        }

        return trim($url) === '' ? '' : md5($url);
    }

    /**
     * @param  array<string, true>  $usedPhotoKeys
     * @param  list<string>  $pool
     */
    protected static function pickDistinctPreviewFromPool(array $pool, array $usedPhotoKeys, string $templateId, int $index): string
    {
        if ($pool === []) {
            return '';
        }

        $start = abs(crc32($templateId)) % count($pool);

        for ($offset = 0; $offset < count($pool); $offset++) {
            $candidate = $pool[($start + $offset) % count($pool)];
            $key = self::previewPhotoKey($candidate);

            if ($key !== '' && ! isset($usedPhotoKeys[$key])) {
                return $candidate;
            }
        }

        return $pool[$index % count($pool)] ?? $pool[0];
    }

    /**
     * @param  array<string, true>  $usedPhotoKeys  Primary preview photo keys on sibling cards (fallbacks may reuse pool).
     * @param  list<string>  $pool
     */
    protected static function previewFallbackForTemplate(string $templateId, string $primary, array $pool, int $index, array $usedPhotoKeys): string
    {
        if ($pool === []) {
            return $primary;
        }

        $primaryKey = self::previewPhotoKey($primary);
        $start = (abs(crc32($templateId.'-fallback')) + 3) % count($pool);

        for ($offset = 0; $offset < count($pool); $offset++) {
            $candidate = $pool[($start + $offset) % count($pool)];
            $key = self::previewPhotoKey($candidate);

            if ($key === '' || $key === $primaryKey || isset($usedPhotoKeys[$key])) {
                continue;
            }

            return $candidate;
        }

        for ($offset = 0; $offset < count($pool); $offset++) {
            $candidate = $pool[($index + $offset + 1) % count($pool)];
            $key = self::previewPhotoKey($candidate);

            if ($key !== $primaryKey) {
                return $candidate;
            }
        }

        return $primary;
    }

    /**
     * @return list<string>
     */
    protected static function previewUrlPoolForNiche(string $niche): array
    {
        return match ($niche) {
            'pet' => WebsiteTemplateGalleryCatalog::stockImagePoolForGallerySlug('pet'),
            'cleaning' => [
                'https://images.unsplash.com/photo-1581578731548-c64695cc6952?auto=format&fit=crop&w=800&q=80',
                'https://images.unsplash.com/photo-1504307651254-35680f356dfd?auto=format&fit=crop&w=800&q=80',
                'https://images.unsplash.com/photo-1553877522-43269d4ea984?auto=format&fit=crop&w=800&q=80',
                'https://images.unsplash.com/photo-1497366216548-37526070297c?auto=format&fit=crop&w=800&q=80',
            ],
            'beauty' => [
                'https://images.unsplash.com/photo-1560066984-138dadb4c035?auto=format&fit=crop&w=800&q=80',
                'https://images.unsplash.com/photo-1522337360788-8b13dee7a37e?auto=format&fit=crop&w=800&q=80',
                'https://images.unsplash.com/photo-1497366811353-6870744d04b2?auto=format&fit=crop&w=800&q=80',
            ],
            default => [
                'https://images.unsplash.com/photo-1497366216548-37526070297c?auto=format&fit=crop&w=800&q=80',
                'https://images.unsplash.com/photo-1460925895917-afdab827c52f?auto=format&fit=crop&w=800&q=80',
                'https://images.unsplash.com/photo-1553877522-43269d4ea984?auto=format&fit=crop&w=800&q=80',
                'https://images.unsplash.com/photo-1551434678-e076c223a692?auto=format&fit=crop&w=800&q=80',
                'https://images.unsplash.com/photo-1497366811353-6870744d04b2?auto=format&fit=crop&w=800&q=80',
            ],
        };
    }

    /**
     * @param  array<string, mixed>  $definition
     */
    protected static function descriptionForType(array $definition, string $category, string $typeLabel, string $niche): string
    {
        if ($category === 'local' && $niche === 'pet') {
            return (string) ($definition['description'] ?? '');
        }

        return match ($category) {
            'local' => __('Tailored for :type with booking-friendly sections and trust highlights.', ['type' => $typeLabel]),
            'restaurant' => __('Menus, ambiance, and contact sections suited for :type.', ['type' => $typeLabel]),
            'ecommerce' => __('Product-forward layout for :type with offers and conversion blocks.', ['type' => $typeLabel]),
            'creative' => __('Showcase work and story for :type with bold visual hierarchy.', ['type' => $typeLabel]),
            default => (string) ($definition['description'] ?? ''),
        };
    }

    /**
     * @return list<array{id: string, name: string, description: string, preview_url: string, accent: string}>
     */
    public static function catalog(): array
    {
        return array_map(function (array $definition): array {
            return [
                'id' => (string) $definition['id'],
                'name' => (string) $definition['name'],
                'description' => (string) $definition['description'],
                'preview_url' => (string) $definition['preview_url'],
                'accent' => (string) $definition['accent'],
            ];
        }, self::catalogDefinitions());
    }

    /**
     * Full catalog list with optional legacy category/text filters (used when no business type is selected).
     *
     * @return list<array<string, mixed>>
     */
    public static function galleryItems(string $category = 'all', string $search = ''): array
    {
        $category = strtolower(trim($category));
        $search = strtolower(trim($search));
        $items = [];

        foreach (self::catalogDefinitions() as $definition) {
            $categories = array_map('strtolower', (array) ($definition['for_categories'] ?? []));

            if ($category !== '' && $category !== 'all' && ! in_array($category, $categories, true)) {
                continue;
            }

            $name = (string) ($definition['name'] ?? '');
            $description = (string) ($definition['description'] ?? '');
            $id = (string) ($definition['id'] ?? '');
            $haystack = strtolower($name.' '.$description.' '.$id.' '.implode(' ', $categories));

            if ($search !== '' && ! str_contains($haystack, $search)) {
                continue;
            }

            $previewUrl = (string) ($definition['preview_url'] ?? '');

            $items[] = [
                'id' => $id,
                'name' => $name,
                'description' => $description,
                'preview_url' => $previewUrl,
                'preview_fallback' => $previewUrl,
                'accent' => (string) ($definition['accent'] ?? '#5b4dff'),
                'recommended' => false,
                'categories' => $categories,
            ];
        }

        usort($items, fn (array $a, array $b): int => strcmp((string) $a['name'], (string) $b['name']));

        $niche = 'general';

        foreach ($items as $item) {
            $slug = WebsiteTemplateGalleryCatalog::slugForTemplateId((string) ($item['id'] ?? ''));

            if ($slug === 'pet') {
                $niche = 'pet';
                break;
            }
        }

        return self::ensureDistinctPreviewUrls($items, $niche);
    }

    public static function find(string $templateId): ?array
    {
        foreach (self::catalogDefinitions() as $template) {
            if ($template['id'] === $templateId) {
                return [
                    'id' => (string) $template['id'],
                    'name' => (string) $template['name'],
                    'description' => (string) $template['description'],
                    'preview_url' => (string) $template['preview_url'],
                    'accent' => (string) $template['accent'],
                ];
            }
        }

        return null;
    }

    public static function isAllowedForSiteType(string $templateId, string $siteType): bool
    {
        foreach (self::catalogFor($siteType) as $template) {
            if ($template['id'] === $templateId) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param  array<string, mixed>  $context
     * @return array<string, mixed>
     */
    public static function buildContent(string $templateId, string $siteType, array $context = []): array
    {
        $template = self::find($templateId);

        if ($template === null) {
            throw new InvalidArgumentException(__('Unknown website template.'));
        }

        $siteName = trim($siteType) !== '' ? trim($siteType) : __('My website');

        $category = self::resolveCategory($siteType);
        $niche = $category === 'local' ? self::resolveLocalNiche($siteType) : 'general';
        $copy = self::copyPack($templateId, $siteName, $siteType, $category, $niche);

        $payload = [
            'site_name' => $siteName,
            'tagline' => $copy['tagline'],
            'brand_primary' => $template['accent'],
            'hero_image_query' => $copy['hero_image_query'],
            'hero_image_alt' => __('Hero image for :name', ['name' => $siteName]),
            'template_id' => $templateId,
            'template_name' => $template['name'],
            'sections' => [
                [
                    'type' => 'hero',
                    'headline' => $copy['hero_headline'],
                    'subheadline' => $copy['hero_subheadline'],
                    'cta_text' => $copy['hero_cta'],
                ],
                [
                    'type' => 'about',
                    'headline' => $copy['about_headline'],
                    'body' => $copy['about_body'],
                    'image_query' => $copy['about_image_query'],
                ],
                [
                    'type' => 'services',
                    'headline' => $copy['services_headline'],
                    'items' => $copy['services_items'],
                ],
                [
                    'type' => 'highlights',
                    'headline' => $copy['highlights_headline'],
                    'subheadline' => $copy['highlights_subheadline'],
                    'items' => $copy['highlights_items'],
                ],
                [
                    'type' => 'contact',
                    'headline' => $copy['contact_headline'],
                    'body' => $copy['contact_body'],
                    'email_placeholder' => __('you@example.com'),
                    'phone_placeholder' => __('+1 (555) 000-0000'),
                ],
            ],
            'source' => 'template',
        ];

        $enriched = WebsiteLandingAssets::enrich($payload, $siteType);

        $layout = WebsiteTemplateLayouts::config($templateId);
        $enriched['layout_variant'] = (string) ($layout['hero'] ?? 'split');
        $enriched['layout_config'] = $layout;

        if (WebsiteTemplateGalleryCatalog::slugForTemplateId($templateId) !== null) {
            $enriched = WebsiteTemplateGalleryCatalog::applyContentImages($templateId, $enriched);
        } elseif (is_array($legacyDefinition = self::findDefinition($templateId)) && filled($legacyDefinition['preview_url'] ?? null)) {
            $enriched['hero_image_url'] = self::largePreviewUrl((string) $legacyDefinition['preview_url']);
        }

        return WebsiteLandingAssets::ensureUniqueLandingImages($enriched);
    }

    public static function findDefinition(string $templateId): ?array
    {
        foreach (self::catalogDefinitions() as $template) {
            if (($template['id'] ?? '') === $templateId) {
                return $template;
            }
        }

        return null;
    }

    protected static function largePreviewUrl(string $url): string
    {
        if (preg_match('/w=\d+/', $url) === 1) {
            return (string) preg_replace('/w=\d+/', 'w=1600', $url);
        }

        return str_contains($url, '?') ? $url.'&w=1600&q=80' : $url.'?w=1600&q=80';
    }

    /**
     * @return array<string, mixed>
     */
    protected static function copyPack(string $templateId, string $siteName, string $siteType, string $category, string $niche = 'general'): array
    {
        $typeLabel = $siteType !== '' ? $siteType : $siteName;

        $base = [
            'tagline' => __('Professional :type services you can trust.', ['type' => $typeLabel]),
            'hero_image_query' => $typeLabel.' business storefront',
            'hero_headline' => __('Welcome to :name', ['name' => $siteName]),
            'hero_subheadline' => __('We help customers discover, book, and love what you offer.'),
            'hero_cta' => __('Get started'),
            'about_headline' => __('About :name', ['name' => $siteName]),
            'about_body' => __(':name is a :type business focused on quality, clarity, and results. This template gives you a polished starting point you can edit anytime.', [
                'name' => $siteName,
                'type' => strtolower($typeLabel),
            ]),
            'about_image_query' => $typeLabel.' team',
            'services_headline' => __('What we offer'),
            'services_items' => self::servicesForCategory($category, $typeLabel, $niche),
            'highlights_headline' => __('Why choose :name', ['name' => $siteName]),
            'highlights_subheadline' => __('Built from a designer template and ready to customize.'),
            'highlights_items' => [
                ['title' => __('Template layout'), 'description' => __('Proven section order: hero, story, services, trust, contact.'), 'icon' => 'layer-group', 'image_query' => 'web design'],
                ['title' => __('Editable copy'), 'description' => __('Update headlines and descriptions in the preview editor.'), 'icon' => 'pen', 'image_query' => 'writing desk'],
                ['title' => __('Mobile ready'), 'description' => __('Looks great on phones and desktops out of the box.'), 'icon' => 'mobile-screen', 'image_query' => 'mobile phone'],
                ['title' => __('Quick launch'), 'description' => __('Skip the blank page and publish faster.'), 'icon' => 'rocket', 'image_query' => 'startup'],
            ],
            'contact_headline' => __('Get in touch'),
            'contact_body' => __('Tell us about your project—we typically respond within one business day.'),
        ];

        $pack = match ($templateId) {
            'modern-startup' => array_merge($base, [
                'tagline' => __('The smarter way to run a :type business online.', ['type' => strtolower($typeLabel)]),
                'hero_headline' => __('Grow :name with confidence', ['name' => $siteName]),
                'hero_subheadline' => __('A crisp, modern layout that puts your offer front and center.'),
                'hero_cta' => __('Start now'),
                'about_body' => __(':name uses simple messaging and strong visuals so visitors understand your value in seconds.', ['name' => $siteName]),
            ]),
            'local-service' => array_merge($base, [
                'tagline' => __('Your neighborhood :type experts.', ['type' => strtolower($typeLabel)]),
                'hero_headline' => __('Trusted :type in your area', ['type' => $typeLabel]),
                'hero_subheadline' => __('Friendly service, fair pricing, and results you can see.'),
                'hero_cta' => __('Book a visit'),
                'services_headline' => __('Popular services'),
                'contact_body' => __('Call, email, or send a message—we are happy to answer questions.'),
            ]),
            'ecommerce-spotlight' => array_merge($base, [
                'tagline' => __('Shop :name — curated for your lifestyle.', ['name' => $siteName]),
                'hero_headline' => __('Discover what is new at :name', ['name' => $siteName]),
                'hero_subheadline' => __('Highlight products, promotions, and reasons to buy today.'),
                'hero_cta' => __('Shop collection'),
                'hero_image_query' => 'online store products',
                'services_headline' => __('Featured offers'),
                'services_items' => [
                    ['title' => __('Best sellers'), 'description' => __('Showcase what customers love most.'), 'icon' => 'fire', 'image_query' => 'retail products'],
                    ['title' => __('Bundles'), 'description' => __('Pair items for better value.'), 'icon' => 'boxes-stacked', 'image_query' => 'gift boxes'],
                    ['title' => __('Fast delivery'), 'description' => __('Set expectations for shipping and pickup.'), 'icon' => 'truck-fast', 'image_query' => 'delivery'],
                ],
            ]),
            'creative-studio' => array_merge($base, [
                'tagline' => __('Design, story, and craft for :name.', ['name' => $siteName]),
                'hero_headline' => __('Made with care at :name', ['name' => $siteName]),
                'hero_subheadline' => __('An expressive layout for studios, agencies, and makers.'),
                'hero_cta' => __('View work'),
                'hero_image_query' => 'creative studio workspace',
                'about_headline' => __('Our story'),
            ]),
            'trusted-trades' => array_merge($base, [
                'tagline' => __('Reliable :type—done right the first time.', ['type' => $typeLabel]),
                'hero_headline' => __('Quality :type you can count on', ['type' => $typeLabel]),
                'hero_subheadline' => __('Licensed, insured, and focused on clear estimates and great results.'),
                'hero_cta' => __('Request a quote'),
                'services_headline' => __('Our work'),
            ]),
            'pet-grooming-salon', 'pet-spa-daycare', 'pet-mobile-grooming', 'pet-boutique', 'pet-care-classic' => array_merge($base, self::petGroomingCopy($templateId, $siteName, $typeLabel)),
            'hospitality-menu' => array_merge($base, [
                'tagline' => __('Fresh flavors and warm hospitality at :name.', ['name' => $siteName]),
                'hero_headline' => __('Dine with us at :name', ['name' => $siteName]),
                'hero_subheadline' => __('Seasonal dishes, cozy atmosphere, and service you will remember.'),
                'hero_cta' => __('View menu'),
                'hero_image_query' => 'restaurant dining',
                'services_headline' => __('Menu highlights'),
                'services_items' => [
                    ['title' => __('Chef specials'), 'description' => __('Rotating plates made with local ingredients.'), 'icon' => 'utensils', 'image_query' => 'gourmet food'],
                    ['title' => __('Private events'), 'description' => __('Host celebrations, meetings, and group dinners.'), 'icon' => 'champagne-glasses', 'image_query' => 'restaurant event'],
                    ['title' => __('Reservations'), 'description' => __('Book a table online or by phone in minutes.'), 'icon' => 'calendar-check', 'image_query' => 'restaurant reservation'],
                ],
                'contact_body' => __('Questions about hours, catering, or reservations? Reach out anytime.'),
            ]),
            default => $base,
        };

        $galleryOverrides = WebsiteTemplateGalleryCatalog::copyOverrides($templateId, $siteName, $typeLabel);

        if (is_array($galleryOverrides)) {
            $pack = array_merge($pack, $galleryOverrides);
        }

        $gallerySlug = WebsiteTemplateGalleryCatalog::slugForTemplateId($templateId);

        if ($gallerySlug !== null) {
            $pack['highlights_items'] = self::galleryHighlightsForSlug($gallerySlug, $siteName);
            $pack['highlights_subheadline'] = __('What sets :name apart.', ['name' => $siteName]);
        }

        if (str_starts_with($templateId, 'cleaning-')) {
            $pack = array_merge($pack, [
                'tagline' => __('Spotless spaces by :name.', ['name' => $siteName]),
                'hero_cta' => __('Get a quote'),
                'services_headline' => __('Cleaning packages'),
            ]);
        }

        if (str_starts_with($templateId, 'beauty-')) {
            $pack = array_merge($pack, [
                'tagline' => __('Look and feel your best at :name.', ['name' => $siteName]),
                'hero_cta' => __('Book appointment'),
                'services_headline' => __('Services & pricing'),
            ]);
        }

        if (str_starts_with($templateId, 'restaurant-')) {
            $pack = array_merge($pack, [
                'tagline' => __('Great food and warm service at :name.', ['name' => $siteName]),
                'hero_cta' => __('Reserve a table'),
                'services_headline' => __('Menu highlights'),
                'hero_image_query' => 'restaurant dining',
            ]);
        }

        if (str_starts_with($templateId, 'creative-') && $templateId !== 'creative-studio') {
            $pack = array_merge($pack, [
                'tagline' => __('Ideas made visible at :name.', ['name' => $siteName]),
                'hero_cta' => __('See our work'),
            ]);
        }

        if (str_starts_with($templateId, 'ecommerce-') && $templateId !== 'ecommerce-spotlight') {
            $pack = array_merge($pack, [
                'hero_cta' => __('Shop now'),
            ]);
        }

        if ($niche === 'pet' && str_starts_with($templateId, 'pet-')) {
            return $pack;
        }

        return self::applyPetNicheCopy($pack, $category, $niche, $siteName, $typeLabel);
    }

    /**
     * @return array<string, mixed>
     */
    protected static function petGroomingCopy(string $templateId, string $siteName, string $typeLabel): array
    {
        $shared = [
            'tagline' => __('Gentle hands, happy pets at :name.', ['name' => $siteName]),
            'hero_image_query' => 'dog grooming salon',
            'hero_headline' => __('Welcome to :name', ['name' => $siteName]),
            'hero_subheadline' => __('Professional grooming for dogs and cats—clean coats, happy tails.'),
            'hero_cta' => __('Book grooming'),
            'about_headline' => __('About our salon'),
            'about_body' => __(':name is a :type team that treats every pet like family. From puppies to seniors, we focus on calm handling and great results.', [
                'name' => $siteName,
                'type' => strtolower($typeLabel),
            ]),
            'about_image_query' => 'pet groomer with dog',
            'services_headline' => __('Grooming services'),
            'services_items' => [
                ['title' => __('Bath & brush'), 'description' => __('Wash, dry, and brush-out for a fresh, healthy coat.'), 'icon' => 'shower', 'image_query' => 'dog bath'],
                ['title' => __('Full groom'), 'description' => __('Breed-specific cuts, sanitary trim, and finishing touches.'), 'icon' => 'scissors', 'image_query' => 'dog grooming'],
                ['title' => __('Nails & add-ons'), 'description' => __('Nail trim, teeth brushing, de-shedding, and paw treatments.'), 'icon' => 'paw', 'image_query' => 'pet grooming'],
            ],
            'highlights_subheadline' => __('Why pet parents choose us.'),
            'highlights_items' => [
                ['title' => __('Calm handling'), 'description' => __('Patient staff trained for anxious or senior pets.'), 'icon' => 'heart', 'image_query' => 'happy dog'],
                ['title' => __('Clean & safe'), 'description' => __('Sanitized tools and a stress-free salon environment.'), 'icon' => 'shield-halved', 'image_query' => 'pet salon'],
                ['title' => __('Easy booking'), 'description' => __('Reserve online or call for your preferred groomer.'), 'icon' => 'calendar-check', 'image_query' => 'appointment'],
                ['title' => __('Photo updates'), 'description' => __('Optional before/after snapshots for proud pet parents.'), 'icon' => 'camera', 'image_query' => 'groomed dog'],
            ],
            'contact_body' => __('Questions about breeds, pricing, or first visits? We are happy to help.'),
        ];

        return match ($templateId) {
            'pet-spa-daycare' => array_merge($shared, [
                'hero_subheadline' => __('Spa packages, daycare play, and grooming under one roof.'),
                'hero_cta' => __('View packages'),
                'services_headline' => __('Spa & daycare'),
                'services_items' => [
                    ['title' => __('Spa packages'), 'description' => __('Blueberry facials, paw balm, and aromatherapy baths.'), 'icon' => 'spa', 'image_query' => 'dog spa'],
                    ['title' => __('Daycare play'), 'description' => __('Supervised groups with rest breaks and water breaks.'), 'icon' => 'dog', 'image_query' => 'dogs playing'],
                    ['title' => __('Groom & go'), 'description' => __('Drop off for daycare and pick up freshly groomed.'), 'icon' => 'clock', 'image_query' => 'pet daycare'],
                ],
            ]),
            'pet-mobile-grooming' => array_merge($shared, [
                'hero_headline' => __('Mobile grooming from :name', ['name' => $siteName]),
                'hero_subheadline' => __('We come to your driveway—less stress, no salon wait times.'),
                'hero_cta' => __('Check service area'),
                'services_items' => [
                    ['title' => __('Van full groom'), 'description' => __('Climate-controlled mobile salon at your curb.'), 'icon' => 'van-shuttle', 'image_query' => 'mobile grooming'],
                    ['title' => __('Multi-pet visits'), 'description' => __('Household discounts when we groom more than one pet.'), 'icon' => 'paw', 'image_query' => 'multiple dogs'],
                    ['title' => __('Senior pet care'), 'description' => __('Gentle sessions without a car ride to a shop.'), 'icon' => 'heart', 'image_query' => 'senior dog'],
                ],
            ]),
            'pet-boutique' => array_merge($shared, [
                'tagline' => __('Grooming, treats, and essentials at :name.', ['name' => $siteName]),
                'hero_subheadline' => __('Retail shelves meet full-service grooming in one visit.'),
                'hero_cta' => __('Shop & book'),
                'services_headline' => __('Shop & services'),
                'services_items' => [
                    ['title' => __('Boutique retail'), 'description' => __('Collars, treats, and coat care products pets love.'), 'icon' => 'store', 'image_query' => 'pet store'],
                    ['title' => __('Grooming bundles'), 'description' => __('Pair a groom with take-home shampoo or brushes.'), 'icon' => 'gift', 'image_query' => 'pet products'],
                    ['title' => __('Loyalty perks'), 'description' => __('Punch cards and member pricing for regulars.'), 'icon' => 'star', 'image_query' => 'loyalty card'],
                ],
            ]),
            'pet-care-classic' => array_merge($shared, [
                'hero_subheadline' => __('A timeless layout for established grooming salons.'),
                'hero_cta' => __('Schedule appointment'),
            ]),
            default => $shared,
        };
    }

    /**
     * @param  array<string, mixed>  $pack
     * @return array<string, mixed>
     */
    protected static function applyPetNicheCopy(array $pack, string $category, string $niche, string $siteName, string $typeLabel): array
    {
        if ($category !== 'local' || $niche !== 'pet') {
            return $pack;
        }

        if (str_starts_with((string) ($pack['hero_image_query'] ?? ''), 'dog grooming')) {
            return $pack;
        }

        return array_merge($pack, [
            'hero_image_query' => 'dog grooming salon',
            'about_image_query' => 'pet groomer with dog',
            'services_items' => self::servicesForCategory('local', $typeLabel, 'pet'),
        ]);
    }

    /**
     * @return list<array{title: string, description: string, icon: string, image_query: string}>
     */
    protected static function galleryHighlightsForSlug(string $slug, string $siteName): array
    {
        return match ($slug) {
            'cleaning' => [
                ['title' => __('Insured crews'), 'description' => __('Background-checked teams and clear estimates.'), 'icon' => 'shield-halved', 'image_query' => 'cleaning service'],
                ['title' => __('Deep clean systems'), 'description' => __('Room-by-room checklists every visit.'), 'icon' => 'broom', 'image_query' => 'home cleaning'],
                ['title' => __('Flexible scheduling'), 'description' => __('Weekly, move-out, and emergency slots.'), 'icon' => 'calendar-check', 'image_query' => 'calendar'],
                ['title' => __('Satisfaction guarantee'), 'description' => __('We re-clean areas you are not happy with.'), 'icon' => 'star', 'image_query' => 'happy home'],
            ],
            'beauty' => [
                ['title' => __('Skilled stylists'), 'description' => __('Booth rentals or chair teams with specialty training.'), 'icon' => 'scissors', 'image_query' => 'hair salon'],
                ['title' => __('Premium products'), 'description' => __('Color-safe, skin-kind formulas on every service.'), 'icon' => 'spray-can-sparkles', 'image_query' => 'beauty products'],
                ['title' => __('Easy booking'), 'description' => __('Reserve cuts, color, and spa add-ons online.'), 'icon' => 'calendar-check', 'image_query' => 'salon appointment'],
                ['title' => __('Walk-ins welcome'), 'description' => __('Same-day fades, trims, and blowouts when chairs open.'), 'icon' => 'store', 'image_query' => 'barbershop'],
            ],
            'ecommerce' => [
                ['title' => __('Curated catalog'), 'description' => __('Collections built for how your customers shop.'), 'icon' => 'tags', 'image_query' => 'online store'],
                ['title' => __('Fast checkout'), 'description' => __('Clear shipping, returns, and payment badges.'), 'icon' => 'cart-shopping', 'image_query' => 'checkout'],
                ['title' => __('Member perks'), 'description' => __('Loyalty tiers and bundle pricing.'), 'icon' => 'gift', 'image_query' => 'retail loyalty'],
                ['title' => __('Real support'), 'description' => __('Humans on chat—not bots only.'), 'icon' => 'headset', 'image_query' => 'customer service retail'],
            ],
            'pet' => [
                ['title' => __('Gentle handling'), 'description' => __('Patient staff for puppies and seniors.'), 'icon' => 'heart', 'image_query' => 'dog grooming'],
                ['title' => __('Clean salon'), 'description' => __('Sanitized tools between every pet.'), 'icon' => 'shield-halved', 'image_query' => 'pet salon'],
                ['title' => __('Breed expertise'), 'description' => __('Cuts tailored to coat type and lifestyle.'), 'icon' => 'scissors', 'image_query' => 'dog haircut'],
                ['title' => __('Photo updates'), 'description' => __('Before/after snaps for proud parents.'), 'icon' => 'camera', 'image_query' => 'groomed dog'],
            ],
            'restaurant' => [
                ['title' => __('Seasonal menu'), 'description' => __('Dishes that rotate with local ingredients.'), 'icon' => 'utensils', 'image_query' => 'restaurant food'],
                ['title' => __('Cozy atmosphere'), 'description' => __('Lighting and seating for every occasion.'), 'icon' => 'wine-glass', 'image_query' => 'restaurant interior'],
                ['title' => __('Events & catering'), 'description' => __('Private dining and party trays.'), 'icon' => 'champagne-glasses', 'image_query' => 'restaurant event'],
                ['title' => __('Reserve online'), 'description' => __('Hold a table in a few taps.'), 'icon' => 'calendar-check', 'image_query' => 'restaurant reservation'],
            ],
            'creative' => [
                ['title' => __('Portfolio depth'), 'description' => __('Case studies that show process and results.'), 'icon' => 'images', 'image_query' => 'design portfolio'],
                ['title' => __('On-brand systems'), 'description' => __('Identity, web, and campaign work aligned.'), 'icon' => 'palette', 'image_query' => 'branding studio'],
                ['title' => __('Fast iterations'), 'description' => __('Collaborative reviews and clear milestones.'), 'icon' => 'bolt', 'image_query' => 'creative workshop'],
                ['title' => __('Launch ready'), 'description' => __('Files and specs your team can ship with.'), 'icon' => 'rocket', 'image_query' => 'design deliverables'],
            ],
            default => [
                ['title' => __('Professional layout'), 'description' => __('Structured for trust and conversions.'), 'icon' => 'layer-group', 'image_query' => 'business'],
                ['title' => __('Editable content'), 'description' => __('Update copy any time in the editor.'), 'icon' => 'pen', 'image_query' => 'content editing'],
                ['title' => __('Mobile ready'), 'description' => __('Responsive on every screen size.'), 'icon' => 'mobile-screen', 'image_query' => 'mobile website'],
                ['title' => __('Quick launch'), 'description' => __('Go live without starting from scratch.'), 'icon' => 'rocket', 'image_query' => 'launch'],
            ],
        };
    }

    /**
     * @return list<array{title: string, description: string, icon: string, image_query: string}>
     */
    protected static function servicesForCategory(string $category, string $typeLabel, string $niche = 'general'): array
    {
        return match ($category) {
            'local' => $niche === 'pet' ? [
                ['title' => __('Bath & brush'), 'description' => __('Wash, dry, and brush-out for a fresh coat.'), 'icon' => 'shower', 'image_query' => 'dog bath'],
                ['title' => __('Full groom'), 'description' => __('Cuts and styling tailored to your pet’s breed.'), 'icon' => 'scissors', 'image_query' => 'dog grooming'],
                ['title' => __('Nail trim'), 'description' => __('Quick, careful trims as part of every visit.'), 'icon' => 'paw', 'image_query' => 'pet nails'],
            ] : [
                ['title' => __('On-site visit'), 'description' => __('Convenient appointments at your home or our location.'), 'icon' => 'house', 'image_query' => $typeLabel],
                ['title' => __('Core :type', ['type' => $typeLabel]), 'description' => __('The service your customers book most often.'), 'icon' => 'star', 'image_query' => $typeLabel],
                ['title' => __('Care plans'), 'description' => __('Recurring visits and maintenance packages.'), 'icon' => 'calendar-days', 'image_query' => 'service business'],
            ],
            'restaurant' => [
                ['title' => __('Lunch & dinner'), 'description' => __('Daily menus built around fresh, seasonal ingredients.'), 'icon' => 'bowl-food', 'image_query' => 'restaurant food'],
                ['title' => __('Takeout'), 'description' => __('Order ahead for quick pickup.'), 'icon' => 'bag-shopping', 'image_query' => 'takeout food'],
                ['title' => __('Catering'), 'description' => __('Platters and trays for events of any size.'), 'icon' => 'users', 'image_query' => 'catering'],
            ],
            'ecommerce' => [
                ['title' => __('New arrivals'), 'description' => __('Highlight the latest products in your catalog.'), 'icon' => 'sparkles', 'image_query' => 'new products'],
                ['title' => __('Bundles'), 'description' => __('Save when customers buy complementary items together.'), 'icon' => 'boxes-stacked', 'image_query' => 'product bundle'],
                ['title' => __('Shipping'), 'description' => __('Clear delivery times and return policies.'), 'icon' => 'truck', 'image_query' => 'package delivery'],
            ],
            'creative' => [
                ['title' => __('Brand design'), 'description' => __('Identity systems that feel unmistakably you.'), 'icon' => 'palette', 'image_query' => 'branding'],
                ['title' => __('Campaigns'), 'description' => __('Launch creative that converts across channels.'), 'icon' => 'bullhorn', 'image_query' => 'marketing'],
                ['title' => __('Retainers'), 'description' => __('Ongoing partnership for studios and growing teams.'), 'icon' => 'handshake', 'image_query' => 'creative team'],
            ],
            default => [
                ['title' => __('Consultation'), 'description' => __('Understand your goals and map the right solution.'), 'icon' => 'comments', 'image_query' => 'meeting office'],
                ['title' => __('Core service'), 'description' => __('Deliver the work your customers come to you for.'), 'icon' => 'star', 'image_query' => $typeLabel],
                ['title' => __('Ongoing support'), 'description' => __('Stay in touch with updates, care, and follow-through.'), 'icon' => 'headset', 'image_query' => 'customer success'],
            ],
        };
    }
}
