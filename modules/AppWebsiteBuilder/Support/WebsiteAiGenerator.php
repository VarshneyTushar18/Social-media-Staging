<?php

namespace Modules\AppWebsiteBuilder\Support;

use Illuminate\Support\Facades\Http;
use Modules\AdminSettings\Support\OptionStore;
use RuntimeException;
use Throwable;

class WebsiteAiGenerator
{
    public function __construct(
        protected OptionStore $options,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function generate(string $siteType, array $context = []): array
    {
        if ((string) $this->options->get('ai_content_status', '1') !== '1') {
            throw new RuntimeException(__('AI content generation is disabled. Enable Content under Admin → AI Settings to generate websites from a prompt.'));
        }

        $provider = (string) $this->options->get('ai_content_provider', 'openai');
        $model = (string) $this->options->get('ai_content_model', 'gpt-4o-mini');
        $startedAt = microtime(true);
        $hasUserPrompt = trim((string) ($context['user_prompt'] ?? '')) !== '';

        try {
            $result = match ($provider) {
                'openai' => $this->generateWithOpenAi($siteType, $model, $context),
                default => throw new RuntimeException(__('Website AI generation currently supports OpenAI as the content provider. Set AI Settings → content provider to OpenAI.')),
            };

            if (function_exists('log_ai_usage')) {
                log_ai_usage([
                    'provider' => $provider,
                    'capability' => 'content',
                    'model' => $model,
                    'feature' => 'website-builder.generate',
                    'status' => 'success',
                    'latency_ms' => (int) round((microtime(true) - $startedAt) * 1000),
                    'metadata' => [
                        'site_type' => $siteType,
                        'has_user_prompt' => $hasUserPrompt,
                    ],
                ]);
            }

            return $result;
        } catch (Throwable $exception) {
            if (function_exists('log_ai_usage')) {
                log_ai_usage([
                    'provider' => $provider,
                    'capability' => 'content',
                    'model' => $model,
                    'feature' => 'website-builder.generate',
                    'status' => 'error',
                    'latency_ms' => (int) round((microtime(true) - $startedAt) * 1000),
                    'error_message' => $exception->getMessage(),
                    'metadata' => [
                        'site_type' => $siteType,
                    ],
                ]);
            }

            throw $exception;
        }
    }

    /**
     * @param  array<string, mixed>  $context
     * @return array<string, mixed>
     */
    protected function generateWithOpenAi(string $siteType, string $model, array $context): array
    {
        $apiKey = trim((string) $this->options->get('ai_openai_api_key', ''));
        $baseUrl = rtrim((string) $this->options->get('ai_openai_url', 'https://api.openai.com/v1'), '/');

        if ($apiKey === '') {
            throw new RuntimeException(__('OpenAI API key is missing. Add it under Settings → AI Settings in the admin panel.'));
        }

        $projectName = (string) ($context['project_name'] ?? '');
        $brandTone = (string) ($context['brand_tone'] ?? 'professional');
        $ownerPrompt = trim((string) ($context['user_prompt'] ?? ''));
        $promptMode = $ownerPrompt !== '';

        $userPrompt = $this->buildUserPrompt($siteType, $projectName, $brandTone, $ownerPrompt);

        $decoded = $this->requestJson($apiKey, $baseUrl, $model, $userPrompt, 0.75);

        if ($promptMode && ! WebsiteAiLayout::isComplete(is_array($decoded['layout'] ?? null) ? $decoded['layout'] : [])) {
            $retryPrompt = $userPrompt."\n\n"
                .'Your previous JSON was missing a complete "layout" object (every layout key and layout.sections) or "theme" object. '
                .'Return the full JSON again with complete layout and theme matching the owner brief.';

            $decoded = $this->requestJson($apiKey, $baseUrl, $model, $retryPrompt, 0.55);
        }

        if (! is_array($decoded)) {
            throw new RuntimeException(__('AI returned an invalid website structure. Try again.'));
        }

        $layoutInput = is_array($decoded['layout'] ?? null) ? $decoded['layout'] : [];
        $brandPrimary = (string) ($decoded['brand_primary'] ?? '#5b4dff');

        $payload = [
            'site_name' => (string) ($decoded['site_name'] ?? $siteType),
            'tagline' => (string) ($decoded['tagline'] ?? ''),
            'brand_primary' => $brandPrimary,
            'hero_image_query' => (string) ($decoded['hero_image_query'] ?? $siteType),
            'hero_image_alt' => (string) ($decoded['hero_image_alt'] ?? ''),
            'sections' => is_array($decoded['sections'] ?? null) ? $decoded['sections'] : [],
            'source' => 'ai',
            'generation_mode' => $promptMode ? 'prompt' : 'ai',
        ];

        if ($promptMode) {
            $payload['user_prompt'] = $ownerPrompt;
        }

        $enriched = WebsiteLandingAssets::enrich($payload, $siteType, $ownerPrompt);
        $enriched['layout_config'] = WebsiteAiLayout::resolve($layoutInput, $siteType, $ownerPrompt);
        $enriched['theme'] = WebsiteAiTheme::resolve(
            is_array($decoded['theme'] ?? null) ? $decoded['theme'] : [],
            $ownerPrompt,
            (string) $enriched['brand_primary'],
        );
        $enriched['generation_mode'] = $promptMode ? 'prompt' : 'ai';

        if ($promptMode) {
            $enriched['user_prompt'] = $ownerPrompt;
        }

        return $enriched;
    }

    protected function buildUserPrompt(string $siteType, string $projectName, string $brandTone, string $ownerPrompt): string
    {
        return implode("\n", array_filter([
            'Create a single-page business landing page driven entirely by the owner brief below — not by any template gallery.',
            'Site label (short): '.$siteType.'.',
            $projectName !== '' ? 'Business / project name: '.$projectName.'.' : null,
            'Brand tone: '.$brandTone.'.',
            $ownerPrompt !== '' ? "OWNER BRIEF (copy, layout, theme, and images must follow this):\n".$ownerPrompt : null,
            'Return JSON with keys: site_name, tagline, brand_primary (hex), brand_secondary (hex in theme object), hero_image_query (4-8 english words), hero_image_alt, layout, theme, sections.',
            'Sections array must include exactly one of each type in any order: hero, about, services, highlights, contact.',
            'Each section needs a "type" field matching its role.',
            'hero: headline, subheadline, cta_text.',
            'about: headline, body, image_query (3-6 words from brief).',
            'services: headline, items array of 3 objects with title, description, icon (fontawesome 6 solid without fa- prefix), image_query.',
            'highlights: headline, subheadline, items array of 4 objects with title, description, icon, image_query — must be specific to the brief, not generic SaaS builder bullets.',
            'contact: headline, body, email_placeholder, phone_placeholder.',
            $ownerPrompt !== '' ? 'Every image_query must reflect the owner brief — not generic stock labels.' : null,
            WebsiteAiLayout::promptInstructions(),
            'Write compelling marketing copy for a public landing page.',
        ]));
    }

    /**
     * @return array<string, mixed>
     */
    protected function requestJson(string $apiKey, string $baseUrl, string $model, string $userPrompt, float $temperature): array
    {
        $verify = is_file(storage_path('ssl/cacert.pem'))
            ? (realpath(storage_path('ssl/cacert.pem')) ?: storage_path('ssl/cacert.pem'))
            : true;

        $response = Http::timeout(120)
            ->withOptions(['verify' => $verify])
            ->withToken($apiKey)
            ->acceptJson()
            ->post($baseUrl.'/chat/completions', [
                'model' => $model,
                'temperature' => $temperature,
                'response_format' => ['type' => 'json_object'],
                'messages' => [
                    [
                        'role' => 'system',
                        'content' => 'You are a landing-page designer. Return valid JSON only. No markdown. Never reference template gallery names or ids.',
                    ],
                    ['role' => 'user', 'content' => $userPrompt],
                ],
            ]);

        if (! $response->successful()) {
            $message = (string) data_get($response->json(), 'error.message', $response->body());

            throw new RuntimeException($message !== '' ? $message : __('OpenAI request failed.'));
        }

        $content = (string) data_get($response->json(), 'choices.0.message.content', '');
        $decoded = json_decode($content, true);

        if (! is_array($decoded)) {
            throw new RuntimeException(__('AI returned an invalid website structure. Try again.'));
        }

        return $decoded;
    }
}
