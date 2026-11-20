<?php

namespace Modules\AppWebsiteBuilder\Support;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Modules\AdminSettings\Support\OptionStore;
use RuntimeException;
use Throwable;

class WebsitePromptSuggester
{
    public function __construct(
        protected OptionStore $options,
    ) {}

    public function suggest(string $seed, string $projectName = ''): string
    {
        if ((string) $this->options->get('ai_content_status', '1') !== '1') {
            throw new RuntimeException(__('AI content generation is disabled. Ask your administrator to enable Content under AI Settings.'));
        }

        $provider = (string) $this->options->get('ai_content_provider', 'openai');
        $model = (string) $this->options->get('ai_content_model', 'gpt-4o-mini');
        $seed = trim($seed);
        $projectName = trim($projectName);

        if ($seed === '' && $projectName === '') {
            throw new RuntimeException(__('Enter a few words about your business, or set your project name, before generating a prompt.'));
        }

        $startedAt = microtime(true);

        try {
            $text = match ($provider) {
                'openai' => $this->suggestWithOpenAi($model, $seed, $projectName),
                default => throw new RuntimeException(__('Website prompt suggestions currently support OpenAI as the content provider. Set AI Settings → content provider to OpenAI.')),
            };

            if (function_exists('log_ai_usage')) {
                log_ai_usage([
                    'provider' => $provider,
                    'capability' => 'content',
                    'model' => $model,
                    'feature' => 'website-builder.prompt-suggest',
                    'status' => 'success',
                    'latency_ms' => (int) round((microtime(true) - $startedAt) * 1000),
                ]);
            }

            return $text;
        } catch (Throwable $exception) {
            if (function_exists('log_ai_usage')) {
                log_ai_usage([
                    'provider' => $provider,
                    'capability' => 'content',
                    'model' => $model,
                    'feature' => 'website-builder.prompt-suggest',
                    'status' => 'error',
                    'latency_ms' => (int) round((microtime(true) - $startedAt) * 1000),
                    'error_message' => $exception->getMessage(),
                ]);
            }

            throw $exception;
        }
    }

    protected function suggestWithOpenAi(string $model, string $seed, string $projectName): string
    {
        $apiKey = trim((string) $this->options->get('ai_openai_api_key', ''));
        $baseUrl = rtrim((string) $this->options->get('ai_openai_url', 'https://api.openai.com/v1'), '/');

        if ($apiKey === '') {
            throw new RuntimeException(__('OpenAI API key is missing. Add it under Settings → AI Settings in the admin panel.'));
        }

        $verify = is_file(storage_path('ssl/cacert.pem'))
            ? (realpath(storage_path('ssl/cacert.pem')) ?: storage_path('ssl/cacert.pem'))
            : true;

        $response = Http::timeout(60)
            ->withOptions(['verify' => $verify])
            ->withToken($apiKey)
            ->acceptJson()
            ->post($baseUrl.'/chat/completions', [
                'model' => $model,
                'temperature' => 0.85,
                'messages' => [
                    [
                        'role' => 'system',
                        'content' => 'You help business owners write briefs for an AI website builder. Reply with plain text only: one concise paragraph (4-8 sentences). Include business type, tone, key offerings, differentiators, and a clear call-to-action. No markdown, bullets, or JSON.',
                    ],
                    [
                        'role' => 'user',
                        'content' => $this->buildUserMessage($seed, $projectName),
                    ],
                ],
            ]);

        if (! $response->successful()) {
            $message = (string) data_get($response->json(), 'error.message', $response->body());

            throw new RuntimeException($message !== '' ? $message : __('OpenAI request failed.'));
        }

        $content = trim((string) data_get($response->json(), 'choices.0.message.content', ''));

        if ($content === '') {
            throw new RuntimeException(__('AI returned an empty prompt. Try again.'));
        }

        return Str::limit($content, 4000, '');
    }

    protected function buildUserMessage(string $seed, string $projectName): string
    {
        $parts = ['Write a website owner brief I can paste into an AI website generator.'];

        if ($projectName !== '') {
            $parts[] = 'Business or project name: '.$projectName.'.';
        }

        if ($seed !== '') {
            $parts[] = 'Starting idea or rough notes from the owner: '.$seed;
        }

        $parts[] = 'Match the style of this example: "Online fashion boutique for women. Elegant tone. Highlight new arrivals, free returns, and style guide. CTA: Shop the collection."';

        return implode("\n", $parts);
    }
}