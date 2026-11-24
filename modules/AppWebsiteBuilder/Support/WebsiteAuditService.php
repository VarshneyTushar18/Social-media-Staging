<?php

namespace Modules\AppWebsiteBuilder\Support;

use Illuminate\Support\Facades\Http;
use Modules\AdminSettings\Support\OptionStore;
use Modules\AppWebsiteBuilder\Models\WebsiteSite;
use RuntimeException;
use Throwable;

class WebsiteAuditService
{
    public function __construct(
        protected OptionStore $options,
        protected WebsiteExternalPageSnapshot $snapshots,
        protected WebsiteTechnicalAudit $technicalAudit,
    ) {}

    /**
     * @return array{
     *     score: int,
     *     summary: string,
     *     strengths: list<string>,
     *     fixes: list<array{area: string, issue: string, recommendation: string}>,
     *     target_label: string,
     *     source: string,
     *     used_ai: bool
     * }
     */
    public function auditBuilderSite(WebsiteSite $site): array
    {
        $context = WebsiteAuditContext::fromBuilderSite($site);
        $builderSummary = json_decode($context['payload'], true);
        $builderSummary = is_array($builderSummary) ? $builderSummary : [];

        $slug = (string) ($site->subdomain_slug ?? '');
        $publicUrl = $slug !== '' ? WebsitePublishConfig::publicUrl($slug) : null;

        $snapshot = null;

        if ($site->status === WebsiteSite::STATUS_PUBLISHED && filled($publicUrl)) {
            try {
                $snapshot = $this->snapshots->fetch($publicUrl);
            } catch (Throwable) {
                $snapshot = null;
            }
        }

        return $this->analyzeForTarget(
            $context,
            $snapshot,
            $builderSummary,
            (string) $context['label'],
            'builder',
        );
    }

    /**
     * @return array{
     *     score: int,
     *     summary: string,
     *     strengths: list<string>,
     *     fixes: list<array{area: string, issue: string, recommendation: string}>,
     *     target_label: string,
     *     source: string,
     *     used_ai: bool
     * }
     */
    public function auditExternalUrl(string $url): array
    {
        $snapshot = $this->snapshots->fetch($url);
        $context = WebsiteAuditContext::fromExternalSnapshot($snapshot);

        return $this->analyzeForTarget(
            $context,
            $snapshot,
            null,
            (string) ($snapshot['url'] ?? $url),
            'external',
        );
    }

    /**
     * @param  array{source: string, label: string, payload: string}  $context
     * @return array{
     *     score: int,
     *     summary: string,
     *     strengths: list<string>,
     *     fixes: list<array{area: string, issue: string, recommendation: string}>,
     *     target_label: string,
     *     source: string,
     *     used_ai: bool
     * }
     */
    /**
     * @param  array{source: string, label: string, payload: string}  $context
     * @param  array<string, mixed>|null  $snapshot
     * @param  array<string, mixed>|null  $builderSummary
     */
    protected function analyzeForTarget(
        array $context,
        ?array $snapshot,
        ?array $builderSummary,
        string $targetLabel,
        string $source,
    ): array {
        $aiEnabled = (string) $this->options->get('ai_content_status', '1') === '1'
            && trim((string) $this->options->get('ai_openai_api_key', '')) !== '';

        $result = $snapshot !== null
            ? $this->technicalAudit->auditLivePage($snapshot, $builderSummary)
            : $this->technicalAudit->auditBuilderContent($builderSummary ?? []);

        if ($aiEnabled) {
            try {
                $result = $this->analyzeWithAi($context, $result);

                return array_merge($result, [
                    'target_label' => $targetLabel,
                    'source' => $source,
                    'used_ai' => true,
                ]);
            } catch (Throwable) {
                /* use technical result below */
            }
        }

        return array_merge($result, [
            'target_label' => $targetLabel,
            'source' => $source,
            'used_ai' => false,
        ]);
    }

    /**
     * @param  array{source: string, label: string, payload: string}  $context
     * @return array{score: int, summary: string, strengths: list<string>, fixes: list<array{area: string, issue: string, recommendation: string}>}
     */
    /**
     * @param  array{source: string, label: string, payload: string}  $context
     * @param  array<string, mixed>|null  $snapshot
     * @param  array<string, mixed>|null  $builderSummary
     */
    /**
     * @param  array{source: string, label: string, payload: string}  $context
     * @param  array{score: int, summary: string, strengths: list<string>, fixes: list<array{area: string, issue: string, recommendation: string}>, metrics?: array<string, mixed>}  $technical
     */
    protected function analyzeWithAi(array $context, array $technical): array
    {
        $provider = (string) $this->options->get('ai_content_provider', 'openai');

        if ($provider !== 'openai') {
            throw new RuntimeException(__('AI audit requires OpenAI as the content provider.'));
        }

        $model = (string) $this->options->get('ai_content_model', 'gpt-4o-mini');
        $apiKey = trim((string) $this->options->get('ai_openai_api_key', ''));
        $baseUrl = rtrim((string) $this->options->get('ai_openai_url', 'https://api.openai.com/v1'), '/');
        $startedAt = microtime(true);

        $areas = implode(', ', [
            'Website Performance Audit',
            'Mobile Responsiveness Audit',
            'Broken Links Detection',
            'Image Loading Check',
            'Page Design & Layout Audit',
            'Forms and Button Functionality Check',
            'Page Loading Speed',
            'HTTPS / SSL Check',
            'Accessibility Audit',
            'Technical Error Detection',
        ]);

        $userPrompt = implode("\n", [
            'Website Health & Quality audit for a small-business owner.',
            'Only evaluate these areas (no other categories): '.$areas.'.',
            'Use the automated metrics below as ground truth; refine score, strengths, and fixes.',
            'Return JSON only with keys: score (0-100 integer), summary (string), strengths (array of short strings), fixes (array of objects with area, issue, recommendation).',
            'area must be one of the ten audit names above.',
            'Automated metrics:',
            json_encode($technical['metrics'] ?? [], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT),
            'Website data:',
            $context['payload'],
        ]);

        try {
            $decoded = $this->requestJson($apiKey, $baseUrl, $model, $userPrompt);

            if (function_exists('log_ai_usage')) {
                log_ai_usage([
                    'provider' => 'openai',
                    'capability' => 'content',
                    'model' => $model,
                    'feature' => 'website-builder.audit',
                    'status' => 'success',
                    'latency_ms' => (int) round((microtime(true) - $startedAt) * 1000),
                    'metadata' => [
                        'source' => $context['source'],
                    ],
                ]);
            }

            $normalized = $this->normalizeResult($decoded);
            $normalized['metrics'] = is_array($technical['metrics'] ?? null) ? $technical['metrics'] : [];

            return $normalized;
        } catch (Throwable $exception) {
            if (function_exists('log_ai_usage')) {
                log_ai_usage([
                    'provider' => 'openai',
                    'capability' => 'content',
                    'model' => $model,
                    'feature' => 'website-builder.audit',
                    'status' => 'error',
                    'latency_ms' => (int) round((microtime(true) - $startedAt) * 1000),
                    'error_message' => $exception->getMessage(),
                    'metadata' => [
                        'source' => $context['source'],
                    ],
                ]);
            }

            throw $exception;
        }
    }

    /**
     * @param  array<string, mixed>  $decoded
     * @return array{score: int, summary: string, strengths: list<string>, fixes: list<array{area: string, issue: string, recommendation: string}>}
     */
    protected function normalizeResult(array $decoded): array
    {
        $strengths = collect(is_array($decoded['strengths'] ?? null) ? $decoded['strengths'] : [])
            ->map(fn ($item): string => trim((string) $item))
            ->filter()
            ->values()
            ->all();

        $fixes = collect(is_array($decoded['fixes'] ?? null) ? $decoded['fixes'] : [])
            ->map(function ($item): ?array {
                if (! is_array($item)) {
                    return null;
                }

                $issue = trim((string) ($item['issue'] ?? ''));
                $recommendation = trim((string) ($item['recommendation'] ?? ''));

                if ($issue === '' && $recommendation === '') {
                    return null;
                }

                return [
                    'area' => trim((string) ($item['area'] ?? 'General')),
                    'issue' => $issue !== '' ? $issue : __('Needs attention'),
                    'recommendation' => $recommendation,
                ];
            })
            ->filter()
            ->values()
            ->all();

        $metrics = is_array($decoded['metrics'] ?? null) ? $decoded['metrics'] : [];

        return [
            'score' => max(0, min(100, (int) ($decoded['score'] ?? 0))),
            'summary' => trim((string) ($decoded['summary'] ?? '')),
            'strengths' => $strengths,
            'fixes' => $fixes,
            'metrics' => $metrics,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function requestJson(string $apiKey, string $baseUrl, string $model, string $userPrompt): array
    {
        $verify = is_file(storage_path('ssl/cacert.pem'))
            ? (realpath(storage_path('ssl/cacert.pem')) ?: storage_path('ssl/cacert.pem'))
            : true;

        $response = Http::timeout(45)
            ->withOptions(['verify' => $verify])
            ->withToken($apiKey)
            ->acceptJson()
            ->post($baseUrl.'/chat/completions', [
                'model' => $model,
                'temperature' => 0.4,
                'response_format' => ['type' => 'json_object'],
                'messages' => [
                    [
                        'role' => 'system',
                        'content' => 'You are a website quality auditor. Return valid JSON only.',
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
            throw new RuntimeException(__('AI returned an invalid audit response.'));
        }

        return $decoded;
    }
}
