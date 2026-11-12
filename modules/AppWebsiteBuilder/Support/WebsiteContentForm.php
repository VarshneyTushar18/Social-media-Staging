<?php

namespace Modules\AppWebsiteBuilder\Support;

class WebsiteContentForm
{
    /**
     * @return array<string, mixed>
     */
    public static function fromStoredContent(array $stored, string $fallbackSiteName): array
    {
        $hero = self::section($stored, 'hero');
        $about = self::section($stored, 'about');
        $services = self::section($stored, 'services');
        $highlights = self::section($stored, 'highlights');
        $contact = self::section($stored, 'contact');

        return [
            'siteName' => (string) ($stored['site_name'] ?? $fallbackSiteName),
            'tagline' => (string) ($stored['tagline'] ?? ''),
            'brandPrimary' => (string) ($stored['brand_primary'] ?? '#5b4dff'),
            'heroHeadline' => (string) data_get($hero, 'headline', ''),
            'heroSubheadline' => (string) data_get($hero, 'subheadline', ''),
            'heroCtaText' => (string) data_get($hero, 'cta_text', ''),
            'heroImageUrl' => (string) ($stored['hero_image_url'] ?? data_get($hero, 'image_url', '')),
            'aboutHeadline' => (string) data_get($about, 'headline', ''),
            'aboutBody' => (string) data_get($about, 'body', ''),
            'aboutImageUrl' => (string) data_get($about, 'image_url', ''),
            'servicesHeadline' => (string) data_get($services, 'headline', ''),
            'serviceItems' => self::mapItems((array) data_get($services, 'items', []), 3),
            'highlightsHeadline' => (string) data_get($highlights, 'headline', ''),
            'highlightsSubheadline' => (string) data_get($highlights, 'subheadline', ''),
            'highlightItems' => self::mapItems((array) data_get($highlights, 'items', []), 4),
            'contactHeadline' => (string) data_get($contact, 'headline', ''),
            'contactBody' => (string) data_get($contact, 'body', ''),
            'contactImageUrl' => (string) data_get($contact, 'image_url', ''),
            'contactEmail' => (string) data_get($contact, 'email_placeholder', ''),
            'contactPhone' => (string) data_get($contact, 'phone_placeholder', ''),
        ];
    }

    /**
     * @param  array<string, mixed>  $form
     * @return array<string, mixed>
     */
    public static function toStoredContent(array $stored, array $form): array
    {
        $stored['site_name'] = trim((string) ($form['siteName'] ?? ''));
        $stored['tagline'] = trim((string) ($form['tagline'] ?? ''));
        $stored['brand_primary'] = WebsiteLandingAssets::sanitizeHexPublic(
            (string) ($form['brandPrimary'] ?? ''),
            '#5b4dff'
        );

        $heroImageUrl = trim((string) ($form['heroImageUrl'] ?? ''));

        $stored['sections'] = self::patchSection($stored, 'hero', array_filter([
            'type' => 'hero',
            'headline' => trim((string) ($form['heroHeadline'] ?? '')),
            'subheadline' => trim((string) ($form['heroSubheadline'] ?? '')),
            'cta_text' => trim((string) ($form['heroCtaText'] ?? '')),
            'image_url' => $heroImageUrl !== '' ? $heroImageUrl : null,
        ], fn ($v) => $v !== null));

        if ($heroImageUrl !== '') {
            $stored['hero_image_url'] = $heroImageUrl;
        }

        $aboutImageUrl = trim((string) ($form['aboutImageUrl'] ?? ''));

        $stored['sections'] = self::patchSection($stored, 'about', array_filter([
            'type' => 'about',
            'headline' => trim((string) ($form['aboutHeadline'] ?? '')),
            'body' => trim((string) ($form['aboutBody'] ?? '')),
            'image_url' => $aboutImageUrl !== '' ? $aboutImageUrl : null,
        ], fn ($v) => $v !== null));

        $stored['sections'] = self::patchSection($stored, 'services', [
            'type' => 'services',
            'headline' => trim((string) ($form['servicesHeadline'] ?? '')),
            'items' => self::mergeItemCopy(
                (array) data_get(self::section($stored, 'services'), 'items', []),
                (array) ($form['serviceItems'] ?? []),
            ),
        ]);

        $stored['sections'] = self::patchSection($stored, 'highlights', [
            'type' => 'highlights',
            'headline' => trim((string) ($form['highlightsHeadline'] ?? '')),
            'subheadline' => trim((string) ($form['highlightsSubheadline'] ?? '')),
            'items' => self::mergeItemCopy(
                (array) data_get(self::section($stored, 'highlights'), 'items', []),
                (array) ($form['highlightItems'] ?? []),
            ),
        ]);

        $contactImageUrl = trim((string) ($form['contactImageUrl'] ?? ''));

        $stored['sections'] = self::patchSection($stored, 'contact', array_filter([
            'type' => 'contact',
            'headline' => trim((string) ($form['contactHeadline'] ?? '')),
            'body' => trim((string) ($form['contactBody'] ?? '')),
            'email_placeholder' => trim((string) ($form['contactEmail'] ?? '')),
            'phone_placeholder' => trim((string) ($form['contactPhone'] ?? '')),
            'image_url' => $contactImageUrl !== '' ? $contactImageUrl : null,
        ], fn ($v) => $v !== null));

        return $stored;
    }

    /**
     * @return array<string, string>
     */
    public static function validationRules(): array
    {
        return [
            'siteName' => 'required|string|max:120',
            'tagline' => 'nullable|string|max:200',
            'brandPrimary' => 'nullable|string|max:20',
            'heroHeadline' => 'nullable|string|max:200',
            'heroSubheadline' => 'nullable|string|max:500',
            'heroCtaText' => 'nullable|string|max:80',
            'heroImageUrl' => 'nullable|string|max:2000',
            'aboutHeadline' => 'nullable|string|max:200',
            'aboutBody' => 'nullable|string|max:5000',
            'aboutImageUrl' => 'nullable|string|max:2000',
            'servicesHeadline' => 'nullable|string|max:200',
            'serviceItems' => 'array',
            'serviceItems.*.title' => 'nullable|string|max:120',
            'serviceItems.*.description' => 'nullable|string|max:500',
            'serviceItems.*.image_url' => 'nullable|string|max:2000',
            'highlightsHeadline' => 'nullable|string|max:200',
            'highlightsSubheadline' => 'nullable|string|max:300',
            'highlightItems' => 'array',
            'highlightItems.*.title' => 'nullable|string|max:120',
            'highlightItems.*.description' => 'nullable|string|max:500',
            'highlightItems.*.image_url' => 'nullable|string|max:2000',
            'contactHeadline' => 'nullable|string|max:200',
            'contactImageUrl' => 'nullable|string|max:2000',
            'contactBody' => 'nullable|string|max:2000',
            'contactEmail' => 'nullable|string|max:120',
            'contactPhone' => 'nullable|string|max:40',
        ];
    }

    /**
     * @param  array<string, mixed>  $stored
     * @return array<string, mixed>
     */
    protected static function section(array $stored, string $type): array
    {
        foreach ((array) ($stored['sections'] ?? []) as $section) {
            if (is_array($section) && strtolower((string) ($section['type'] ?? '')) === strtolower($type)) {
                return $section;
            }
        }

        return ['type' => $type];
    }

    /**
     * @param  array<int, array<string, mixed>>  $existing
     * @param  array<int, array<string, mixed>>  $formItems
     * @return array<int, array<string, mixed>>
     */
    protected static function mergeItemCopy(array $existing, array $formItems): array
    {
        $merged = [];
        $count = max(count($existing), count($formItems));

        for ($i = 0; $i < $count; $i++) {
            $base = is_array($existing[$i] ?? null) ? $existing[$i] : [];
            $form = is_array($formItems[$i] ?? null) ? $formItems[$i] : [];
            $titleForm = trim((string) ($form['title'] ?? ''));
            $descriptionForm = trim((string) ($form['description'] ?? ''));
            $base['title'] = $titleForm !== '' ? $titleForm : trim((string) ($base['title'] ?? $base['name'] ?? ''));
            $base['description'] = $descriptionForm !== ''
                ? $descriptionForm
                : trim((string) ($base['description'] ?? $base['body'] ?? $base['text'] ?? ''));

            $imageUrlForm = trim((string) ($form['image_url'] ?? ''));
            if ($imageUrlForm !== '') {
                $base['image_url'] = $imageUrlForm;
            }

            foreach (['image_query', 'image_alt', 'icon'] as $mediaKey) {
                if (array_key_exists($mediaKey, $base) && filled($base[$mediaKey])) {
                    continue;
                }

                if (array_key_exists($mediaKey, $form) && filled($form[$mediaKey])) {
                    $base[$mediaKey] = $form[$mediaKey];
                }
            }

            $merged[] = $base;
        }

        return $merged;
    }

    /**
     * @param  array<string, mixed>  $patch
     * @return array<int, array<string, mixed>>
     */
    protected static function patchSection(array $stored, string $type, array $patch): array
    {
        $sections = is_array($stored['sections'] ?? null) ? $stored['sections'] : [];
        $found = false;

        foreach ($sections as $index => $section) {
            if (! is_array($section)) {
                continue;
            }

            if (strtolower((string) ($section['type'] ?? '')) === strtolower($type)) {
                $sections[$index] = array_merge($section, $patch);
                $found = true;
                break;
            }
        }

        if (! $found) {
            $sections[] = $patch;
        }

        return array_values($sections);
    }

    /**
     * @param  array<int, mixed>  $items
     * @return array<int, array{title: string, description: string, image_url: string}>
     */
    protected static function mapItems(array $items, int $minimum): array
    {
        $mapped = [];

        foreach ($items as $item) {
            if (! is_array($item)) {
                continue;
            }

            $mapped[] = [
                'title' => trim((string) ($item['title'] ?? $item['name'] ?? '')),
                'description' => trim((string) ($item['description'] ?? $item['body'] ?? $item['text'] ?? '')),
                'image_url' => trim((string) ($item['image_url'] ?? '')),
            ];
        }

        while (count($mapped) < $minimum) {
            $mapped[] = ['title' => '', 'description' => '', 'image_url' => ''];
        }

        return $mapped;
    }
}
