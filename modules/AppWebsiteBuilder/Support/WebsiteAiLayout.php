<?php

namespace Modules\AppWebsiteBuilder\Support;

class WebsiteAiLayout
{
    /**
     * @return array<string, list<string>>
     */
    public static function allowedVariants(): array
    {
        return [
            'nav' => ['light', 'dark', 'minimal'],
            'hero' => ['split', 'center', 'banner', 'overlap'],
            'about' => ['split', 'split-reverse', 'stack'],
            'services' => ['image-cards', 'feature-list', 'icon-row', 'product-grid', 'menu-rows'],
            'highlights' => ['icon-cards', 'stat-row', 'list-check'],
            'contact' => ['gradient', 'light-split', 'minimal-bar'],
        ];
    }

    /**
     * @param  array<string, mixed>  $layoutFromAi
     * @return array<string, mixed>
     */
    /**
     * @param  array<string, mixed>  $layoutFromAi
     */
    public static function isComplete(array $layoutFromAi): bool
    {
        foreach (array_keys(self::allowedVariants()) as $key) {
            $value = strtolower(trim((string) ($layoutFromAi[$key] ?? '')));
            if ($value === '' || ! in_array($value, self::allowedVariants()[$key], true)) {
                return false;
            }
        }

        if (! is_array($layoutFromAi['sections'] ?? null)) {
            return false;
        }

        return self::sanitizeSectionOrder($layoutFromAi['sections']) !== null;
    }

    /**
     * @param  array<string, mixed>  $layoutFromAi
     * @return array<string, mixed>
     */
    public static function resolve(array $layoutFromAi, string $siteType, string $userPrompt): array
    {
        $userPrompt = trim($userPrompt);
        $promptMode = $userPrompt !== '';
        $seed = $promptMode ? $userPrompt : trim($siteType);
        $hints = self::inferVariantsFromPrompt($seed, $siteType);

        $config = [
            'template_key' => 'ai-prompt',
            'source' => 'ai',
            'from_template' => false,
            'sections' => ['about', 'services', 'highlights', 'contact'],
            'layout_source' => self::isComplete($layoutFromAi) ? 'ai' : 'inferred',
        ];

        foreach (self::allowedVariants() as $key => $allowed) {
            $fromAi = strtolower(trim((string) ($layoutFromAi[$key] ?? '')));
            if ($fromAi !== '' && in_array($fromAi, $allowed, true)) {
                $config[$key] = $fromAi;

                continue;
            }

            if (isset($hints[$key]) && in_array($hints[$key], $allowed, true)) {
                $config[$key] = $hints[$key];

                continue;
            }

            if ($promptMode) {
                $config[$key] = $allowed[self::hashIndex($seed.'|'.$key, count($allowed))];
            } else {
                $config[$key] = $allowed[self::hashIndex($seed.'|'.$siteType.'|'.$key, count($allowed))];
            }
        }

        $sections = $layoutFromAi['sections'] ?? null;
        if (is_array($sections)) {
            $order = self::sanitizeSectionOrder($sections);
            if ($order !== null) {
                $config['sections'] = $order;
            }
        } else {
            $config['sections'] = self::sectionOrderFromPrompt($seed.($promptMode ? '' : '|'.$siteType));
        }

        if (self::isComplete($layoutFromAi)) {
            $config['layout_source'] = 'ai';
        } elseif ($promptMode && $hints !== []) {
            $config['layout_source'] = 'prompt_inferred';
        }

        $skinFromAi = strtolower(trim((string) ($layoutFromAi['skin'] ?? '')));
        $allowedSkins = self::allowedSkins();
        if ($skinFromAi !== '' && in_array($skinFromAi, $allowedSkins, true)) {
            $config['skin'] = $skinFromAi;
        } else {
            $config['skin'] = self::inferSkin($seed.($promptMode ? '' : '|'.$siteType), $siteType);
        }

        return $config;
    }

    /**
     * @return list<string>
     */
    public static function allowedSkins(): array
    {
        return ['corporate', 'marketplace', 'saas', 'glass', 'luxury', 'studio', 'pulse', 'modern'];
    }

    public static function inferSkin(string $userPrompt, string $siteType): string
    {
        $blob = strtolower(trim($userPrompt.' '.$siteType));

        if (preg_match('/\b(restaurant|cafe|food|delivery|dining|bistro|bakery|catering|pizza)\b/', $blob)) {
            return 'marketplace';
        }

        if (preg_match('/\b(saas|software|app|platform|tech|startup|ai)\b/', $blob)) {
            return 'saas';
        }

        if (preg_match('/\b(agency|creative|studio|portfolio|photography|design)\b/', $blob)) {
            return 'studio';
        }

        if (preg_match('/\b(luxury|boutique|fashion|elegant|wedding|bridal|spa)\b/', $blob)) {
            return 'luxury';
        }

        if (preg_match('/\b(deals|flash|bold|ads|gym|fitness|nightlife|bar)\b/', $blob)) {
            return 'pulse';
        }

        if (preg_match('/\b(cleaning|plumber|electrician|contractor|business|corporate|b2b|consulting)\b/', $blob)) {
            return 'corporate';
        }

        if (preg_match('/\b(minimal|modern|wellness|glass)\b/', $blob)) {
            return 'glass';
        }

        $skins = ['corporate', 'marketplace', 'saas', 'glass', 'luxury', 'studio', 'pulse'];

        return $skins[self::hashIndex($blob.'|skin', count($skins))];
    }

    public static function promptInstructions(): string
    {
        $variants = collect(self::allowedVariants())
            ->map(fn (array $options, string $key) => $key.': '.implode('|', $options))
            ->implode('; ');

        return implode("\n", [
            'This is prompt-only generation — do NOT copy any pre-made website template, template gallery id, or fixed layout preset.',
            'Required "layout" object with ALL keys set from the owner instructions: '.$variants.'.',
            'layout.sections: array of four keys in display order — about, services, highlights, contact (each once).',
            'Read the owner prompt carefully: luxury vs playful vs B2B vs restaurant vs shop must change nav, hero, services style, and section order.',
            'Two different owner prompts MUST produce different layout and theme values. Do not default every shop to center hero + product-grid.',
            WebsiteAiTheme::promptInstructions(),
        ]);
    }

    /**
     * @param  array<int, mixed>  $sections
     * @return list<string>|null
     */
    protected static function sanitizeSectionOrder(array $sections): ?array
    {
        $order = [];
        foreach ($sections as $section) {
            $section = strtolower(trim((string) $section));
            if (in_array($section, ['about', 'services', 'highlights', 'contact'], true) && ! in_array($section, $order, true)) {
                $order[] = $section;
            }
        }

        return count($order) === 4 ? $order : null;
    }

    /**
     * @return list<string>
     */
    protected static function sectionOrderFromPrompt(string $seed): array
    {
        $permutations = [
            ['about', 'services', 'highlights', 'contact'],
            ['services', 'highlights', 'about', 'contact'],
            ['services', 'about', 'highlights', 'contact'],
            ['highlights', 'services', 'about', 'contact'],
            ['about', 'highlights', 'services', 'contact'],
            ['services', 'highlights', 'contact', 'about'],
            ['highlights', 'about', 'services', 'contact'],
            ['about', 'services', 'contact', 'highlights'],
        ];

        return $permutations[self::hashIndex($seed.'|sections', count($permutations))];
    }

    /**
     * Soft fallbacks when AI omits a layout key — inferred from prompt + site type text only.
     *
     * @return array<string, string>
     */
    protected static function inferVariantsFromPrompt(string $text, string $siteType): array
    {
        $blob = strtolower($text.' '.$siteType);
        $hints = [];

        if (preg_match('/\b(ecommerce|e-commerce|online store|shop|retail|marketplace|boutique)\b/', $blob)) {
            if (preg_match('/\b(luxury|premium|elegant|boutique|fashion)\b/', $blob)) {
                $hints['nav'] = 'minimal';
                $hints['hero'] = 'overlap';
                $hints['services'] = 'image-cards';
            } elseif (preg_match('/\b(b2b|wholesale|industrial)\b/', $blob)) {
                $hints['hero'] = 'banner';
                $hints['services'] = 'feature-list';
                $hints['highlights'] = 'stat-row';
            } else {
                $hints['services'] = 'product-grid';
            }
        }

        if (preg_match('/\b(restaurant|cafe|food|dining|bistro|bakery|menu|catering|pizza|bar)\b/', $blob)) {
            $hints['hero'] = 'banner';
            $hints['services'] = 'menu-rows';
            $hints['highlights'] = 'list-check';
            $hints['contact'] = 'light-split';
        }

        if (preg_match('/\b(agency|studio|design|creative|portfolio|photography|artist)\b/', $blob)) {
            $hints['nav'] = 'minimal';
            $hints['hero'] = 'overlap';
            $hints['about'] = 'stack';
            $hints['contact'] = 'minimal-bar';
        }

        if (preg_match('/\b(cleaning|plumber|electrician|contractor|construction|landscap|trades|hvac|roofing)\b/', $blob)) {
            $hints['hero'] = 'banner';
            $hints['services'] = 'feature-list';
            $hints['highlights'] = 'stat-row';
            $hints['about'] = 'split-reverse';
        }

        if (preg_match('/\b(luxury|premium|upscale|elegant|vip|exclusive)\b/', $blob)) {
            $hints['nav'] = 'dark';
            $hints['hero'] = 'center';
            $hints['contact'] = 'gradient';
        }

        if (preg_match('/\b(saas|startup|software|app|tech|platform)\b/', $blob)) {
            $hints['nav'] = 'dark';
            $hints['hero'] = 'split';
            $hints['services'] = 'feature-list';
            $hints['highlights'] = 'icon-cards';
        }

        if (preg_match('/\b(salon|spa|beauty|wellness|grooming|pet)\b/', $blob)) {
            $hints['hero'] = 'split';
            $hints['services'] = 'image-cards';
            $hints['highlights'] = 'list-check';
        }

        return $hints;
    }

    protected static function hashIndex(string $seed, int $count): int
    {
        if ($count <= 0) {
            return 0;
        }

        return abs(crc32($seed)) % $count;
    }
}
