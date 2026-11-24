<?php

namespace Modules\AppWebsiteBuilder\Support;

class WebsiteContentForm
{
    /**
     * Section types in saved JSON order (no enrich() filler).
     *
     * @return list<string>
     */
    public static function sectionTypesInStoredOrder(array $stored): array
    {
        $types = [];

        foreach ((array) ($stored['sections'] ?? []) as $section) {
            if (! is_array($section)) {
                continue;
            }

            $type = strtolower(trim((string) ($section['type'] ?? '')));

            if ($type === '' || in_array($type, $types, true)) {
                continue;
            }

            $types[] = $type;
        }

        return $types;
    }

    /**
     * Edit panel blocks top-to-bottom for this site (nav first, footer last when saved).
     *
     * @return list<string>
     */
    public static function editorSectionOrder(array $stored): array
    {
        $types = self::sectionTypesInStoredOrder($stored);

        if ($types === []) {
            return [];
        }

        $order = ['nav'];

        foreach ($types as $type) {
            if (in_array($type, ['nav', 'footer'], true)) {
                continue;
            }

            $order[] = $type;
        }

        if (self::hasStoredSection($stored, 'footer')) {
            $order[] = 'footer';
        }

        return $order;
    }

    /**
     * Body section types for preview order (saved order, no enrich() filler).
     *
     * @return list<string>
     */
    public static function editorBodySectionTypes(array $stored): array
    {
        $canonical = WebsiteAiLayout::canonicalBodySectionOrder();

        if (! is_array($stored['sections'] ?? null)) {
            return $canonical;
        }

        return array_values(array_filter(
            self::sectionTypesInStoredOrder($stored),
            fn (string $type): bool => in_array($type, $canonical, true),
        ));
    }

    public static function editorShowsHero(array $stored): bool
    {
        return self::hasStoredSection($stored, 'hero');
    }

    public static function editorShowsFooter(array $stored): bool
    {
        return self::hasStoredSection($stored, 'footer');
    }

    public static function hasStoredSection(array $stored, string $type): bool
    {
        foreach ((array) ($stored['sections'] ?? []) as $section) {
            if (! is_array($section)) {
                continue;
            }

            if (strtolower((string) ($section['type'] ?? '')) === strtolower($type)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param  array<int, mixed>  $items
     */
    protected static function editorListMinimum(array $items): int
    {
        $count = 0;

        foreach ($items as $item) {
            if (is_array($item)) {
                $count++;
            }
        }

        return $count;
    }

    /**
     * @param  list<string>  $bodySections
     */
    protected static function editsBodySection(array $bodySections, string $type): bool
    {
        return in_array($type, $bodySections, true);
    }
    /**
     * @return list<array{href: string, label: string}>
     */
    public static function defaultNavLinks(): array
    {
        return [
            ['href' => '#home', 'label' => __('Home')],
            ['href' => '#about', 'label' => __('About')],
            ['href' => '#services', 'label' => __('Services')],
            ['href' => '#faq', 'label' => __('FAQ')],
            ['href' => '#contact', 'label' => __('Contact')],
        ];
    }

    /**
     * @return list<array{href: string, label: string}>
     */
    public static function footerCopyrightTextForForm(array $footer, string $siteName): string
    {
        $text = trim((string) data_get($footer, 'copyright_text', ''));

        if ($text !== '') {
            return $text;
        }

        $siteName = trim($siteName);
        $base = '© '.date('Y').' '.$siteName;
        $legacyNote = trim((string) data_get($footer, 'copyright_note', ''));

        return $legacyNote !== '' ? $base.' · '.$legacyNote : $base;
    }

    /**
     * @return list<array{href: string, label: string}>
     */
    public static function navLinksFromStored(array $stored): array
    {
        $raw = $stored['nav_links'] ?? null;

        if (! is_array($raw) || $raw === []) {
            return self::defaultNavLinks();
        }

        $links = [];

        foreach ($raw as $row) {
            if (! is_array($row)) {
                continue;
            }

            $label = trim((string) ($row['label'] ?? ''));

            if ($label === '') {
                continue;
            }

            $href = trim((string) ($row['href'] ?? '#'));

            if ($href === '') {
                $href = '#';
            }

            $links[] = ['href' => $href, 'label' => $label];
        }

        return $links !== [] ? $links : self::defaultNavLinks();
    }

    /**
     * @param  list<string>|null  $bodySections  Saved body types (omit to derive from $stored)
     * @return array<string, mixed>
     */
    public static function fromStoredContent(array $stored, string $fallbackSiteName, ?array $bodySections = null): array
    {
        $bodySections ??= self::editorBodySectionTypes($stored);

        $hero = self::section($stored, 'hero');
        $about = self::section($stored, 'about');
        $services = self::section($stored, 'services');
        $highlights = self::section($stored, 'highlights');
        $faq = self::section($stored, 'faq');
        $contact = self::section($stored, 'contact');
        $footer = self::section($stored, 'footer');

        $serviceItemsRaw = (array) data_get($services, 'items', []);
        $highlightItemsRaw = (array) data_get($highlights, 'items', []);
        $faqItemsRaw = (array) data_get($faq, 'items', []);

        return [
            'siteName' => (string) ($stored['site_name'] ?? $fallbackSiteName),
            'tagline' => (string) ($stored['tagline'] ?? ''),
            'brandPrimary' => (string) ($stored['brand_primary'] ?? '#5b4dff'),
            'navLinks' => self::navLinksFromStored($stored),
            'navCtaText' => trim((string) ($stored['nav_cta_text'] ?? '')) !== ''
                ? trim((string) $stored['nav_cta_text'])
                : trim((string) data_get($hero, 'cta_text', '')),
            'heroHeadline' => self::editorShowsHero($stored) ? (string) data_get($hero, 'headline', '') : '',
            'heroSubheadline' => self::editorShowsHero($stored) ? (string) data_get($hero, 'subheadline', '') : '',
            'heroCtaText' => self::editorShowsHero($stored) ? (string) data_get($hero, 'cta_text', '') : '',
            'heroImageUrl' => self::editorShowsHero($stored)
                ? (string) ($stored['hero_image_url'] ?? data_get($hero, 'image_url', ''))
                : '',
            'aboutHeadline' => self::editsBodySection($bodySections, 'about') ? (string) data_get($about, 'headline', '') : '',
            'aboutBody' => self::editsBodySection($bodySections, 'about') ? (string) data_get($about, 'body', '') : '',
            'aboutImageUrl' => self::editsBodySection($bodySections, 'about') ? (string) data_get($about, 'image_url', '') : '',
            'servicesHeadline' => self::editsBodySection($bodySections, 'services') ? (string) data_get($services, 'headline', '') : '',
            'serviceItems' => self::editsBodySection($bodySections, 'services')
                ? self::mapItems($serviceItemsRaw, self::editorListMinimum($serviceItemsRaw))
                : [],
            'highlightsHeadline' => self::editsBodySection($bodySections, 'highlights') ? (string) data_get($highlights, 'headline', '') : '',
            'highlightsSubheadline' => self::editsBodySection($bodySections, 'highlights') ? (string) data_get($highlights, 'subheadline', '') : '',
            'highlightItems' => self::editsBodySection($bodySections, 'highlights')
                ? self::mapItems($highlightItemsRaw, self::editorListMinimum($highlightItemsRaw))
                : [],
            'faqHeadline' => self::editsBodySection($bodySections, 'faq') ? (string) data_get($faq, 'headline', '') : '',
            'faqSubheadline' => self::editsBodySection($bodySections, 'faq') ? (string) data_get($faq, 'subheadline', '') : '',
            'faqItems' => self::editsBodySection($bodySections, 'faq')
                ? self::mapFaqItems($faqItemsRaw, self::editorListMinimum($faqItemsRaw))
                : [],
            'contactHeadline' => self::editsBodySection($bodySections, 'contact') ? (string) data_get($contact, 'headline', '') : '',
            'contactBody' => self::editsBodySection($bodySections, 'contact') ? (string) data_get($contact, 'body', '') : '',
            'contactImageUrl' => self::editsBodySection($bodySections, 'contact') ? (string) data_get($contact, 'image_url', '') : '',
            'contactEmail' => self::editsBodySection($bodySections, 'contact') ? (string) data_get($contact, 'email_placeholder', '') : '',
            'contactPhone' => self::editsBodySection($bodySections, 'contact') ? (string) data_get($contact, 'phone_placeholder', '') : '',
            'footerTagline' => self::hasStoredSection($stored, 'footer')
                ? (trim((string) data_get($footer, 'tagline', '')) !== ''
                    ? trim((string) data_get($footer, 'tagline', ''))
                    : trim((string) ($stored['tagline'] ?? '')))
                : '',
            'footerExploreHeading' => self::hasStoredSection($stored, 'footer')
                ? (string) data_get($footer, 'explore_heading', __('Explore'))
                : '',
            'footerExploreSubheading' => self::hasStoredSection($stored, 'footer')
                ? (string) data_get($footer, 'explore_subheading', '')
                : '',
            'footerConnectHeading' => self::hasStoredSection($stored, 'footer')
                ? (string) data_get($footer, 'connect_heading', __('Connect'))
                : '',
            'footerFacebookUrl' => self::hasStoredSection($stored, 'footer') ? (string) data_get($footer, 'facebook_url', '') : '',
            'footerInstagramUrl' => self::hasStoredSection($stored, 'footer') ? (string) data_get($footer, 'instagram_url', '') : '',
            'footerLinkedinUrl' => self::hasStoredSection($stored, 'footer') ? (string) data_get($footer, 'linkedin_url', '') : '',
            'footerCopyrightText' => self::hasStoredSection($stored, 'footer')
                ? self::footerCopyrightTextForForm($footer, (string) ($stored['site_name'] ?? $fallbackSiteName))
                : '',
        ];
    }

    /**
     * @param  array<string, mixed>  $form
     * @param  list<string>|null  $editableBodySections
     * @return array<string, mixed>
     */
    public static function toStoredContent(
        array $stored,
        array $form,
        ?array $editableBodySections = null,
        bool $patchHero = true,
    ): array {
        $editableBodySections ??= WebsiteAiLayout::canonicalBodySectionOrder();
        $stored['site_name'] = trim((string) ($form['siteName'] ?? ''));
        $stored['tagline'] = trim((string) ($form['tagline'] ?? ''));
        $stored['brand_primary'] = WebsiteLandingAssets::sanitizeHexPublic(
            (string) ($form['brandPrimary'] ?? ''),
            '#5b4dff'
        );

        $navLinks = [];

        foreach ((array) ($form['navLinks'] ?? []) as $link) {
            if (! is_array($link)) {
                continue;
            }

            $label = trim((string) ($link['label'] ?? ''));
            $href = trim((string) ($link['href'] ?? ''));

            if ($label === '') {
                continue;
            }

            $navLinks[] = [
                'href' => $href !== '' ? $href : '#',
                'label' => $label,
            ];
        }

        $stored['nav_links'] = $navLinks !== [] ? $navLinks : self::defaultNavLinks();

        $navCta = trim((string) ($form['navCtaText'] ?? ''));
        $stored['nav_cta_text'] = $navCta !== '' ? $navCta : null;

        $heroImageUrl = trim((string) ($form['heroImageUrl'] ?? ''));

        if ($patchHero && self::hasStoredSection($stored, 'hero')) {
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
        }

        $aboutImageUrl = trim((string) ($form['aboutImageUrl'] ?? ''));

        if (self::editsBodySection($editableBodySections, 'about')) {
            $stored['sections'] = self::patchSection($stored, 'about', array_filter([
                'type' => 'about',
                'headline' => trim((string) ($form['aboutHeadline'] ?? '')),
                'body' => trim((string) ($form['aboutBody'] ?? '')),
                'image_url' => $aboutImageUrl !== '' ? $aboutImageUrl : null,
            ], fn ($v) => $v !== null));
        }

        if (self::editsBodySection($editableBodySections, 'services')) {
            $stored['sections'] = self::patchSection($stored, 'services', [
                'type' => 'services',
                'headline' => trim((string) ($form['servicesHeadline'] ?? '')),
                'items' => self::mergeItemCopy(
                    (array) data_get(self::section($stored, 'services'), 'items', []),
                    (array) ($form['serviceItems'] ?? []),
                ),
            ]);
        }

        if (self::editsBodySection($editableBodySections, 'highlights')) {
            $stored['sections'] = self::patchSection($stored, 'highlights', [
                'type' => 'highlights',
                'headline' => trim((string) ($form['highlightsHeadline'] ?? '')),
                'subheadline' => trim((string) ($form['highlightsSubheadline'] ?? '')),
                'items' => self::mergeItemCopy(
                    (array) data_get(self::section($stored, 'highlights'), 'items', []),
                    (array) ($form['highlightItems'] ?? []),
                ),
            ]);
        }

        if (self::editsBodySection($editableBodySections, 'faq')) {
            $stored['sections'] = self::patchSection($stored, 'faq', [
                'type' => 'faq',
                'headline' => trim((string) ($form['faqHeadline'] ?? '')),
                'subheadline' => trim((string) ($form['faqSubheadline'] ?? '')),
                'items' => self::mergeFaqItems(
                    (array) data_get(self::section($stored, 'faq'), 'items', []),
                    (array) ($form['faqItems'] ?? []),
                ),
            ]);
        }

        $contactImageUrl = trim((string) ($form['contactImageUrl'] ?? ''));

        if (self::editsBodySection($editableBodySections, 'contact')) {
            $stored['sections'] = self::patchSection($stored, 'contact', array_filter([
                'type' => 'contact',
                'headline' => trim((string) ($form['contactHeadline'] ?? '')),
                'body' => trim((string) ($form['contactBody'] ?? '')),
                'email_placeholder' => trim((string) ($form['contactEmail'] ?? '')),
                'phone_placeholder' => trim((string) ($form['contactPhone'] ?? '')),
                'image_url' => $contactImageUrl !== '' ? $contactImageUrl : null,
            ], fn ($v) => $v !== null));
        }

        if (self::hasStoredSection($stored, 'footer')) {
            $stored['sections'] = self::patchSection($stored, 'footer', [
                'type' => 'footer',
                'tagline' => trim((string) ($form['footerTagline'] ?? '')),
                'explore_heading' => trim((string) ($form['footerExploreHeading'] ?? '')) ?: __('Explore'),
                'explore_subheading' => trim((string) ($form['footerExploreSubheading'] ?? '')),
                'connect_heading' => trim((string) ($form['footerConnectHeading'] ?? '')) ?: __('Connect'),
                'facebook_url' => trim((string) ($form['footerFacebookUrl'] ?? '')),
                'instagram_url' => trim((string) ($form['footerInstagramUrl'] ?? '')),
                'linkedin_url' => trim((string) ($form['footerLinkedinUrl'] ?? '')),
                'copyright_text' => trim((string) ($form['footerCopyrightText'] ?? '')),
            ]);
        }

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
            'navLinks' => 'array',
            'navLinks.*.href' => 'nullable|string|max:120',
            'navLinks.*.label' => 'nullable|string|max:80',
            'navCtaText' => 'nullable|string|max:80',
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
            'faqHeadline' => 'nullable|string|max:200',
            'faqSubheadline' => 'nullable|string|max:300',
            'faqItems' => 'array',
            'faqItems.*.question' => 'nullable|string|max:300',
            'faqItems.*.answer' => 'nullable|string|max:2000',
            'contactHeadline' => 'nullable|string|max:200',
            'contactImageUrl' => 'nullable|string|max:2000',
            'contactBody' => 'nullable|string|max:2000',
            'contactEmail' => 'nullable|string|max:120',
            'contactPhone' => 'nullable|string|max:40',
            'footerTagline' => 'nullable|string|max:200',
            'footerExploreHeading' => 'nullable|string|max:80',
            'footerExploreSubheading' => 'nullable|string|max:160',
            'footerConnectHeading' => 'nullable|string|max:80',
            'footerFacebookUrl' => 'nullable|string|max:500',
            'footerInstagramUrl' => 'nullable|string|max:500',
            'footerLinkedinUrl' => 'nullable|string|max:500',
            'footerCopyrightText' => 'nullable|string|max:300',
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

    /**
     * @param  array<int, mixed>  $items
     * @return array<int, array{question: string, answer: string}>
     */
    protected static function mapFaqItems(array $items, int $minimum): array
    {
        $mapped = [];

        foreach ($items as $item) {
            if (! is_array($item)) {
                continue;
            }

            $mapped[] = [
                'question' => trim((string) ($item['question'] ?? '')),
                'answer' => trim((string) ($item['answer'] ?? '')),
            ];
        }

        while (count($mapped) < $minimum) {
            $mapped[] = ['question' => '', 'answer' => ''];
        }

        return $mapped;
    }

    /**
     * @param  array<int, array<string, mixed>>  $existing
     * @param  array<int, array<string, mixed>>  $formItems
     * @return array<int, array<string, mixed>>
     */
    protected static function mergeFaqItems(array $existing, array $formItems): array
    {
        $merged = [];
        $count = max(count($existing), count($formItems));

        for ($i = 0; $i < $count; $i++) {
            $base = is_array($existing[$i] ?? null) ? $existing[$i] : [];
            $form = is_array($formItems[$i] ?? null) ? $formItems[$i] : [];
            $question = trim((string) ($form['question'] ?? ''));
            $answer = trim((string) ($form['answer'] ?? ''));

            $base['question'] = $question !== '' ? $question : trim((string) ($base['question'] ?? ''));
            $base['answer'] = $answer !== '' ? $answer : trim((string) ($base['answer'] ?? ''));

            $merged[] = $base;
        }

        return $merged;
    }
}
