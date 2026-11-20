<?php

namespace Modules\AppWebsiteBuilder\Support;

use Modules\AdminSettings\Support\OptionStore;
use Modules\AdminUser\Models\User;
use Modules\AppAIStudio\Support\AiContentStudioService;
use RuntimeException;
use Throwable;

class WebsitePromptImagePipeline
{
    /**
     * Upper bound for credit pre-check before generation (actual count may be lower per layout).
     */
    public const MAX_IMAGE_CREDITS_PER_SITE = 8;

    public const PARALLEL_IMAGE_BATCH = 3;

    public static function isAdminAiImagesEnabled(): bool
    {
        return (string) app(OptionStore::class)->get('ai_image_status', '1') === '1'
            && class_exists(AiContentStudioService::class);
    }

    /**
     * @param  array<string, mixed>  $content
     */
    public static function plannedImageSlotCount(array $content): int
    {
        return count(self::imageSlots($content));
    }

    /**
     * @param  array<string, mixed>  $content
     * @return list<array{label: string, prompt: string, ratio: string, style: string, apply: callable(string): void}>
     */
    protected static function imageSlots(array &$content): array
    {
        $userPrompt = trim((string) ($content['user_prompt'] ?? ''));
        $heroQuery = (string) ($content['hero_image_query'] ?? 'hero');
        $layout = is_array($content['layout_config'] ?? null) ? $content['layout_config'] : [];
        $heroStyle = (string) ($layout['hero'] ?? 'split');
        $servicesStyle = (string) ($layout['services'] ?? 'image-cards');
        $servicesUseImages = in_array($servicesStyle, ['image-cards', 'product-grid'], true);

        $sections = is_array($content['sections'] ?? null) ? $content['sections'] : [];
        $aboutQuery = 'about us';
        $serviceItems = [];

        foreach ($sections as $section) {
            if (! is_array($section)) {
                continue;
            }

            $type = strtolower((string) ($section['type'] ?? ''));
            if ($type === 'about') {
                $aboutQuery = (string) ($section['image_query'] ?? $aboutQuery);
            }

            if ($type === 'services' && is_array($section['items'] ?? null)) {
                $serviceItems = $section['items'];
            }
        }

        /** @var list<array{label: string, prompt: string, ratio: string, style: string, apply: callable(string): void}> $slots */
        $slots = [];

        $slots[] = [
            'label' => 'hero_background',
            'prompt' => self::slotPrompt($userPrompt, $heroQuery, __('Wide cinematic hero background, no text or logos.')),
            'ratio' => '16:9',
            'style' => 'lifestyle',
            'apply' => static function (string $url) use (&$content): void {
                $content['hero_image_url'] = $url;
            },
        ];

        if (in_array($heroStyle, ['split', 'overlap'], true)) {
            $slots[] = [
                'label' => 'hero_feature',
                'prompt' => self::slotPrompt($userPrompt, $heroQuery, __('Distinct hero foreground photo — different scene from the background banner.')),
                'ratio' => '4:3',
                'style' => 'lifestyle',
                'apply' => static function (string $url) use (&$content): void {
                    $sections = is_array($content['sections'] ?? null) ? $content['sections'] : [];
                    foreach ($sections as $index => $section) {
                        if (! is_array($section) || strtolower((string) ($section['type'] ?? '')) !== 'hero') {
                            continue;
                        }
                        $section['image_url'] = $url;
                        $sections[$index] = $section;
                        break;
                    }
                    $content['sections'] = $sections;
                },
            ];
        }

        $slots[] = [
            'label' => 'about',
            'prompt' => self::slotPrompt($userPrompt, $aboutQuery, __('About us section — team, workspace, or authentic business moment.')),
            'ratio' => '16:9',
            'style' => 'lifestyle',
            'apply' => static function (string $url) use (&$content): void {
                $sections = is_array($content['sections'] ?? null) ? $content['sections'] : [];
                foreach ($sections as $index => $section) {
                    if (! is_array($section) || strtolower((string) ($section['type'] ?? '')) !== 'about') {
                        continue;
                    }
                    $section['image_url'] = $url;
                    $sections[$index] = $section;
                    break;
                }
                $content['sections'] = $sections;
            },
        ];

        if ($servicesUseImages) {
            foreach ($serviceItems as $itemIndex => $item) {
                if (! is_array($item)) {
                    continue;
                }

                $itemQuery = (string) ($item['image_query'] ?? ($item['title'] ?? 'service'));
                $itemTitle = (string) ($item['title'] ?? 'service');

                $slots[] = [
                    'label' => 'service_'.$itemIndex,
                    'prompt' => self::slotPrompt(
                        $userPrompt,
                        $itemQuery.' '.$itemTitle,
                        __('Service offering photo #'.($itemIndex + 1).': '.$itemTitle),
                    ),
                    'ratio' => '1:1',
                    'style' => 'lifestyle',
                    'apply' => static function (string $url) use (&$content, $itemIndex): void {
                        $sections = is_array($content['sections'] ?? null) ? $content['sections'] : [];
                        foreach ($sections as $sectionIndex => $section) {
                            if (! is_array($section) || strtolower((string) ($section['type'] ?? '')) !== 'services') {
                                continue;
                            }
                            if (! is_array($section['items'][$itemIndex] ?? null)) {
                                break;
                            }
                            $section['items'][$itemIndex]['image_url'] = $url;
                            $sections[$sectionIndex] = $section;
                            break;
                        }
                        $content['sections'] = $sections;
                    },
                ];
            }
        }

        $slots[] = [
            'label' => 'contact',
            'prompt' => self::slotPrompt($userPrompt, 'contact location', __('Contact section — welcoming storefront or reception, distinct from other photos.')),
            'ratio' => '16:9',
            'style' => 'lifestyle',
            'apply' => static function (string $url) use (&$content): void {
                $sections = is_array($content['sections'] ?? null) ? $content['sections'] : [];
                foreach ($sections as $index => $section) {
                    if (! is_array($section) || strtolower((string) ($section['type'] ?? '')) !== 'contact') {
                        continue;
                    }
                    $section['image_url'] = $url;
                    $sections[$index] = $section;
                    break;
                }
                $content['sections'] = $sections;
            },
        ];

        return $slots;
    }

    /**
     * @param  array<string, mixed>  $content
     * @return array<string, mixed>
     */
    public static function generateForContent(User $owner, array $content): array
    {
        if (! self::isAdminAiImagesEnabled()) {
            return $content;
        }

        $userPrompt = trim((string) ($content['user_prompt'] ?? ''));
        if ($userPrompt === '') {
            return $content;
        }

        if (function_exists('set_time_limit')) {
            @set_time_limit(600);
        }

        /** @var AiContentStudioService $studio */
        $studio = app(AiContentStudioService::class);

        $slots = self::imageSlots($content);
        $totalSlots = count($slots);

        /** @var array<string, true> $usedKeys */
        $usedKeys = [];
        $generated = 0;

        foreach (array_chunk($slots, self::PARALLEL_IMAGE_BATCH) as $batch) {
            $batchSpecs = [];
            foreach ($batch as $slot) {
                $batchSpecs[] = [
                    'prompt' => $slot['prompt'],
                    'style' => $slot['style'],
                    'ratio' => $slot['ratio'],
                ];
            }

            try {
                $urls = $studio->generateOwnedImagesParallel(
                    $owner,
                    $batchSpecs,
                    function () use (&$generated): void {
                        $generated++;
                    },
                );
            } catch (Throwable) {
                $urls = [];
                foreach ($batch as $slot) {
                    $urls[] = self::generateUniqueUrl(
                        $studio,
                        $owner,
                        $slot['prompt'],
                        $slot['style'],
                        $slot['ratio'],
                        $slot['label'],
                        $usedKeys,
                    );
                    $generated++;
                }
            }

            foreach ($batch as $index => $slot) {
                $url = trim((string) ($urls[$index] ?? ''));
                if ($url === '') {
                    continue;
                }

                $url = self::ensureUrlIsUnique(
                    $studio,
                    $owner,
                    $url,
                    $slot,
                    $usedKeys,
                );

                ($slot['apply'])($url);
            }
        }

        $content['images_source'] = 'ai';
        $content['ai_images_generated'] = max($generated, $totalSlots);
        $content['use_ai_images'] = true;

        return self::assertAllSlotsUnique($content);
    }

    /**
     * @param  array<string, true>  $usedKeys
     */
    protected static function ensureUrlIsUnique(
        AiContentStudioService $studio,
        User $owner,
        string $url,
        array $slot,
        array &$usedKeys,
    ): string {
        $key = WebsiteLandingAssets::normalizePhotoKeyPublic($url);

        if ($key !== '' && ! isset($usedKeys[$key])) {
            $usedKeys[$key] = true;

            return $url;
        }

        return self::generateUniqueUrl(
            $studio,
            $owner,
            $slot['prompt'],
            $slot['style'],
            $slot['ratio'],
            (string) ($slot['label'] ?? 'slot'),
            $usedKeys,
        );
    }

    /**
     * @param  array<string, true>  $usedKeys
     */
    protected static function generateUniqueUrl(
        AiContentStudioService $studio,
        User $owner,
        string $prompt,
        string $style,
        string $ratio,
        string $slotLabel,
        array &$usedKeys,
    ): string {
        $attemptPrompt = $prompt;

        for ($attempt = 0; $attempt < 3; $attempt++) {
            $url = self::generateUrl($studio, $owner, $attemptPrompt, $style, $ratio);
            $key = WebsiteLandingAssets::normalizePhotoKeyPublic($url);

            if ($key !== '' && ! isset($usedKeys[$key])) {
                $usedKeys[$key] = true;

                return $url;
            }

            $attemptPrompt = $prompt.' '.__('Unique variation for :slot.', ['slot' => $slotLabel]).' '.__('Attempt').' '.($attempt + 2);
        }

        throw new RuntimeException(__('Could not generate a unique image for slot :slot.', ['slot' => $slotLabel]));
    }

    /**
     * @param  array<string, mixed>  $content
     * @return array<string, mixed>
     */
    protected static function assertAllSlotsUnique(array $content): array
    {
        /** @var array<string, true> $seen */
        $seen = [];

        $check = static function (string $url) use (&$seen): void {
            $url = trim($url);
            if ($url === '') {
                return;
            }

            $key = WebsiteLandingAssets::normalizePhotoKeyPublic($url);
            if ($key === '') {
                return;
            }

            if (isset($seen[$key])) {
                throw new RuntimeException(__('Landing page images must all be unique. A duplicate was detected.'));
            }

            $seen[$key] = true;
        };

        $check((string) ($content['hero_image_url'] ?? ''));

        $sections = is_array($content['sections'] ?? null) ? $content['sections'] : [];
        foreach ($sections as $section) {
            if (! is_array($section)) {
                continue;
            }

            $type = strtolower((string) ($section['type'] ?? ''));
            if (in_array($type, ['hero', 'about', 'contact'], true) && filled($section['image_url'] ?? null)) {
                $check((string) $section['image_url']);
            }

            if ($type === 'services' && is_array($section['items'] ?? null)) {
                foreach ($section['items'] as $item) {
                    if (is_array($item) && filled($item['image_url'] ?? null)) {
                        $check((string) $item['image_url']);
                    }
                }
            }
        }

        return $content;
    }

    protected static function slotPrompt(string $userPrompt, string $imageQuery, ?string $hint = null): string
    {
        $parts = array_filter([
            $userPrompt,
            trim($imageQuery),
            $hint,
        ]);

        return implode('. ', $parts);
    }

    protected static function generateUrl(
        AiContentStudioService $studio,
        User $owner,
        string $prompt,
        string $style,
        string $ratio,
    ): string {
        try {
            $result = $studio->generateOwnedImage($owner, $prompt, $style, $ratio);

            $url = trim((string) ($result['url'] ?? ''));
            if ($url === '') {
                throw new RuntimeException(__('AI image was saved but has no public URL.'));
            }

            return $url;
        } catch (Throwable $exception) {
            throw new RuntimeException(
                __('Website image generation failed: :message', ['message' => $exception->getMessage()]),
                0,
                $exception
            );
        }
    }
}