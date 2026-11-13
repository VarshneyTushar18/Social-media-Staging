<?php

namespace Modules\AppWebsiteBuilder\Support;

class WebsiteAiTheme
{
    /**
     * @return array<string, list<string>>
     */
    public static function allowedOptions(): array
    {
        return [
            'surface' => ['light', 'dark', 'warm', 'cool'],
            'heading_weight' => ['normal', 'bold', 'black'],
            'card_style' => ['bordered', 'flat', 'elevated', 'glass'],
            'cta_style' => ['pill', 'rounded', 'square'],
            'section_rhythm' => ['airy', 'default', 'compact'],
        ];
    }

    /**
     * @param  array<string, mixed>  $fromAi
     * @return array<string, mixed>
     */
    public static function resolve(array $fromAi, string $userPrompt, string $brandPrimary): array
    {
        $userPrompt = trim($userPrompt);
        $hints = self::inferFromPrompt($userPrompt);
        $theme = ['source' => 'ai'];

        foreach (self::allowedOptions() as $key => $allowed) {
            $value = strtolower(trim((string) ($fromAi[$key] ?? '')));
            if ($value !== '' && in_array($value, $allowed, true)) {
                $theme[$key] = $value;

                continue;
            }

            if (isset($hints[$key]) && in_array($hints[$key], $allowed, true)) {
                $theme[$key] = $hints[$key];

                continue;
            }

            $theme[$key] = $allowed[self::hashIndex(($userPrompt !== '' ? $userPrompt : $brandPrimary).'|theme|'.$key, count($allowed))];
        }

        $secondary = (string) ($fromAi['brand_secondary'] ?? '');
        $theme['brand_secondary'] = WebsiteLandingAssets::sanitizeHexPublic(
            $secondary !== '' ? $secondary : self::shiftHex($brandPrimary),
            self::shiftHex($brandPrimary)
        );

        return $theme;
    }

    public static function promptInstructions(): string
    {
        $options = collect(self::allowedOptions())
            ->map(fn (array $list, string $key) => $key.': '.implode('|', $list))
            ->implode('; ');

        return implode("\n", [
            'Required "theme" object derived ONLY from the owner brief (never from a template catalog): '.$options.'.',
            'theme.brand_secondary: hex color complementing brand_primary.',
            'Examples: luxury jewellery → surface dark, heading_weight black, card_style glass; kids party → surface warm, cta_style pill, section_rhythm airy; trades → surface light, card_style bordered, heading_weight bold.',
        ]);
    }

    /**
     * @param  array<string, mixed>  $theme
     * @return array<string, string>
     */
    public static function cssVariables(array $theme, string $brandPrimary): array
    {
        $secondary = (string) ($theme['brand_secondary'] ?? WebsiteLandingAssets::sanitizeHexPublic('', $brandPrimary));

        return [
            '--lp-brand' => $brandPrimary,
            '--lp-brand-secondary' => $secondary,
        ];
    }

    /**
     * @param  array<string, mixed>  $theme
     * @return list<string>
     */
    public static function bodyClasses(array $theme): array
    {
        $classes = ['lp-theme-prompt'];

        $surface = (string) ($theme['surface'] ?? 'light');
        $classes[] = 'lp-surface-'.$surface;

        foreach (['heading_weight', 'card_style', 'cta_style', 'section_rhythm'] as $key) {
            $value = (string) ($theme[$key] ?? '');
            if ($value !== '') {
                $classes[] = 'lp-'.$key.'-'.$value;
            }
        }

        return $classes;
    }

    /**
     * @return array<string, string>
     */
    protected static function inferFromPrompt(string $text): array
    {
        $blob = strtolower($text);
        $hints = [];

        if (preg_match('/\b(luxury|premium|elegant|exclusive|jewell?ery|night|dark|moody|editorial)\b/', $blob)) {
            $hints['surface'] = 'dark';
            $hints['heading_weight'] = 'black';
            $hints['card_style'] = 'glass';
            $hints['cta_style'] = 'square';
        }

        if (preg_match('/\b(kids?|playful|fun|party|bright|colorful|family)\b/', $blob)) {
            $hints['surface'] = 'warm';
            $hints['heading_weight'] = 'bold';
            $hints['card_style'] = 'elevated';
            $hints['cta_style'] = 'pill';
            $hints['section_rhythm'] = 'airy';
        }

        if (preg_match('/\b(cafe|bakery|artisan|handmade|organic|farm)\b/', $blob)) {
            $hints['surface'] = 'warm';
            $hints['card_style'] = 'bordered';
            $hints['section_rhythm'] = 'default';
        }

        if (preg_match('/\b(tech|saas|startup|software|platform|b2b)\b/', $blob)) {
            $hints['surface'] = 'cool';
            $hints['heading_weight'] = 'bold';
            $hints['card_style'] = 'flat';
            $hints['cta_style'] = 'rounded';
        }

        if (preg_match('/\b(spa|wellness|calm|yoga|meditation)\b/', $blob)) {
            $hints['surface'] = 'cool';
            $hints['card_style'] = 'flat';
            $hints['section_rhythm'] = 'airy';
            $hints['cta_style'] = 'pill';
        }

        if (preg_match('/\b(trad(es|e)|contractor|plumb|electric|cleaning|industrial)\b/', $blob)) {
            $hints['surface'] = 'light';
            $hints['heading_weight'] = 'bold';
            $hints['card_style'] = 'bordered';
            $hints['cta_style'] = 'rounded';
            $hints['section_rhythm'] = 'compact';
        }

        return $hints;
    }

    protected static function shiftHex(string $hex): string
    {
        $hex = WebsiteLandingAssets::sanitizeHexPublic($hex, '#5b4dff');
        $raw = ltrim($hex, '#');
        if (strlen($raw) !== 6) {
            return '#1e293b';
        }

        $r = hexdec(substr($raw, 0, 2));
        $g = hexdec(substr($raw, 2, 2));
        $b = hexdec(substr($raw, 4, 2));

        $r = max(0, min(255, (int) round($r * 0.55)));
        $g = max(0, min(255, (int) round($g * 0.55)));
        $b = max(0, min(255, (int) round($b * 0.55)));

        return sprintf('#%02x%02x%02x', $r, $g, $b);
    }

    protected static function hashIndex(string $seed, int $count): int
    {
        if ($count <= 0) {
            return 0;
        }

        return abs(crc32($seed)) % $count;
    }
}
