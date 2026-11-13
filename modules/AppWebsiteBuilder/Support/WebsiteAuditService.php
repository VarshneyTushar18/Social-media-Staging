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

        $slug = (string) ($site->subdomain_slug ?? '');
        $publicUrl = $slug !== '' ? WebsitePublishConfig::publicUrl($slug) : null;

        if ($site->status === WebsiteSite::STATUS_PUBLISHED && filled($publicUrl)) {
            try {
                $snapshot = $this->snapshots->fetch($publicUrl);
                $context['payload'] .= "\n\n--- LIVE PAGE SNAPSHOT ---\n".json_encode($snapshot, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
            } catch (Throwable) {
                /* builder JSON is enough */
            }
        }

        return $this->analyze($context);
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

        return $this->analyze($context);
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
    protected function analyze(array $context): array
    {
        $aiEnabled = (string) $this->options->get('ai_content_status', '1') === '1'
            && trim((string) $this->options->get('ai_openai_api_key', '')) !== '';

        if ($aiEnabled) {
            try {
                $result = $this->analyzeWithAi($context);

                return array_merge($result, [
                    'target_label' => $context['label'],
                    'source' => $context['source'],
                    'used_ai' => true,
                ]);
            } catch (Throwable) {
                /* fall through to rules */
            }
        }

        $result = $this->analyzeWithRules($context);

        return array_merge($result, [
            'target_label' => $context['label'],
            'source' => $context['source'],
            'used_ai' => false,
        ]);
    }

    /**
     * @param  array{source: string, label: string, payload: string}  $context
     * @return array{score: int, summary: string, strengths: list<string>, fixes: list<array{area: string, issue: string, recommendation: string}>}
     */
    protected function analyzeWithAi(array $context): array
    {
        $provider = (string) $this->options->get('ai_content_provider', 'openai');

        if ($provider !== 'openai') {
            throw new RuntimeException(__('AI audit requires OpenAI as the content provider.'));
        }

        $model = (string) $this->options->get('ai_content_model', 'gpt-4o-mini');
        $apiKey = trim((string) $this->options->get('ai_openai_api_key', ''));
        $baseUrl = rtrim((string) $this->options->get('ai_openai_url', 'https://api.openai.com/v1'), '/');
        $startedAt = microtime(true);

        $userPrompt = implode("\n", [
            'Audit this website for a small-business owner.',
            'Source type: '.$context['source'].'.',
            'Return JSON only with keys: score (0-100 integer), summary (string), strengths (array of short strings), fixes (array of objects with area, issue, recommendation).',
            'areas for fixes: Publishing, Content, SEO, Design & UX, Trust & contact.',
            'Be specific to the data below. strengths = what is already good; fixes = what should be corrected.',
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

            return $this->normalizeResult($decoded);
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

        return [
            'score' => max(0, min(100, (int) ($decoded['score'] ?? 0))),
            'summary' => trim((string) ($decoded['summary'] ?? '')),
            'strengths' => $strengths,
            'fixes' => $fixes,
        ];
    }

    /**
     * @param  array{source: string, label: string, payload: string}  $context
     * @return array{score: int, summary: string, strengths: list<string>, fixes: list<array{area: string, issue: string, recommendation: string}>}
     */
    protected function analyzeWithRules(array $context): array
    {
        $data = json_decode($context['payload'], true);
        $strengths = [];
        $fixes = [];
        $score = 70;

        if ($context['source'] === 'builder' && is_array($data)) {
            if (($data['status'] ?? '') === WebsiteSite::STATUS_PUBLISHED && filled($data['public_url'] ?? null)) {
                $strengths[] = __('Site is published with a public URL.');
            } else {
                $fixes[] = [
                    'area' => __('Publishing'),
                    'issue' => __('Site is not published yet.'),
                    'recommendation' => __('Publish with a platform subdomain from the dashboard.'),
                ];
                $score -= 15;
            }

            $sections = is_array($data['sections'] ?? null) ? $data['sections'] : [];
            if (count($sections) >= 3) {
                $strengths[] = __('Landing page has multiple content sections.');
            } else {
                $fixes[] = [
                    'area' => __('Content'),
                    'issue' => __('Few content sections on the page.'),
                    'recommendation' => __('Add hero, services, and contact sections in the site editor.'),
                ];
                $score -= 10;
            }

            $hero = collect($sections)->first(fn ($s) => is_array($s) && strtolower((string) data_get($s, 'type')) === 'hero');
            if (is_array($hero) && strlen(trim((string) data_get($hero, 'headline', ''))) >= 10) {
                $strengths[] = __('Hero headline is present.');
            } else {
                $fixes[] = [
                    'area' => __('Content'),
                    'issue' => __('Hero headline is missing or too short.'),
                    'recommendation' => __('Write a clear headline in the hero section.'),
                ];
                $score -= 10;
            }

            $contact = collect($sections)->first(fn ($s) => is_array($s) && strtolower((string) data_get($s, 'type')) === 'contact');
            if (is_array($contact)) {
                $strengths[] = __('Contact section is included.');
            } else {
                $fixes[] = [
                    'area' => __('Trust & contact'),
                    'issue' => __('No contact section.'),
                    'recommendation' => __('Add email or phone details so visitors can reach you.'),
                ];
                $score -= 10;
            }
        }

        if ($context['source'] === 'external' && is_array($data)) {
            if ((int) ($data['status'] ?? 0) === 200) {
                $strengths[] = __('Page responds with HTTP 200.');
            } else {
                $fixes[] = [
                    'area' => __('Publishing'),
                    'issue' => __('Page did not return HTTP 200.'),
                    'recommendation' => __('Check hosting and DNS for this URL.'),
                ];
                $score -= 20;
            }

            if (($data['https'] ?? false) === true) {
                $strengths[] = __('Site uses HTTPS.');
            } else {
                $fixes[] = [
                    'area' => __('Publishing'),
                    'issue' => __('Site is not using HTTPS.'),
                    'recommendation' => __('Enable SSL on your domain.'),
                ];
                $score -= 10;
            }

            if (strlen((string) ($data['title'] ?? '')) >= 10) {
                $strengths[] = __('HTML title tag is set.');
            } else {
                $fixes[] = [
                    'area' => __('SEO'),
                    'issue' => __('Title tag is missing or too short.'),
                    'recommendation' => __('Add a descriptive <title> in the page head.'),
                ];
                $score -= 10;
            }

            if (strlen((string) ($data['h1'] ?? '')) >= 5) {
                $strengths[] = __('Primary H1 heading found.');
            } else {
                $fixes[] = [
                    'area' => __('SEO'),
                    'issue' => __('No clear H1 heading.'),
                    'recommendation' => __('Use one main H1 that describes the page.'),
                ];
                $score -= 10;
            }
        }

        $score = max(0, min(100, $score));

        return [
            'score' => $score,
            'summary' => __('Automated checklist — enable OpenAI under Admin → AI Settings for a deeper AI review.'),
            'strengths' => $strengths,
            'fixes' => $fixes,
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

        $response = Http::timeout(90)
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
