<?php

namespace Modules\AppWebsiteBuilder\Support;

use DOMDocument;
use DOMElement;
use DOMXPath;
use Illuminate\Http\Client\Pool;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Throwable;
use Modules\AppWebsiteBuilder\Models\WebsiteSite;

class WebsiteTechnicalAudit
{
    /**
     * Canonical audit areas (fixed product scope).
     *
     * @return list<string>
     */
    public static function auditAreaKeys(): array
    {
        return [
            'performance',
            'mobile_responsiveness',
            'broken_links',
            'image_loading',
            'design_layout',
            'forms_buttons',
            'page_loading_speed',
            'https_ssl',
            'accessibility',
            'technical_errors',
        ];
    }

    /**
     * @param  array<string, mixed>|null  $builderSummary  Decoded builder JSON when source is builder
     * @return array{
     *     score: int,
     *     summary: string,
     *     strengths: list<string>,
     *     fixes: list<array{area: string, issue: string, recommendation: string}>,
     *     metrics: array<string, mixed>
     * }
     */
    public function auditLivePage(array $snapshot, ?array $builderSummary = null): array
    {
        $html = (string) ($snapshot['html'] ?? '');
        $parsed = $this->parseHtml($html);

        $metrics = [
            'broken_links' => 0,
            'missing_images' => 0,
            'mobile_responsiveness' => 'unknown',
            'performance' => 'unknown',
            'forms' => 'unknown',
            'page_loading_speed' => 'unknown',
            'https_ssl' => 'unknown',
            'accessibility' => 'unknown',
            'technical_errors' => 'unknown',
            'design_layout' => 'unknown',
            'image_loading' => 'unknown',
        ];

        $responseMs = (int) ($snapshot['response_time_ms'] ?? 0);
        $htmlBytes = (int) ($snapshot['html_bytes'] ?? strlen($html));
        $status = (int) ($snapshot['status'] ?? 0);
        $https = (bool) ($snapshot['https'] ?? false);
        $baseUrl = (string) ($snapshot['url'] ?? '');

        // 7 & 1 — Page loading speed / performance
        if ($responseMs > 0) {
            if ($responseMs <= 800) {
                $metrics['page_loading_speed'] = 'passed';
                $metrics['performance'] = 'passed';
            } elseif ($responseMs <= 2000) {
                $metrics['page_loading_speed'] = 'needs_improvement';
                $metrics['performance'] = 'needs_improvement';
            } else {
                $metrics['page_loading_speed'] = 'needs_improvement';
                $metrics['performance'] = 'needs_improvement';
            }
        }

        if ($htmlBytes > 350000) {
            $metrics['performance'] = 'needs_improvement';
        }

        // 8 — HTTPS / SSL
        $metrics['https_ssl'] = $https && Str::startsWith(strtolower($baseUrl), 'https://') ? 'passed' : 'failed';

        // 10 — Technical errors
        $metrics['technical_errors'] = $status === 200 ? 'passed' : 'failed';

        // 2 — Mobile responsiveness
        $hasViewport = (bool) ($parsed['has_viewport'] ?? false);
        $metrics['mobile_responsiveness'] = $hasViewport ? 'passed' : 'failed';

        // 3 — Broken links
        $brokenLinks = $this->countBrokenLinks($baseUrl, $parsed['links'] ?? []);
        $metrics['broken_links'] = $brokenLinks;

        // 4 — Image loading
        $missingImages = (int) ($parsed['images_missing_src'] ?? 0);
        $brokenImages = $this->countBrokenImages($baseUrl, $parsed['image_srcs'] ?? []);
        $metrics['missing_images'] = $missingImages + $brokenImages;
        $metrics['image_loading'] = $metrics['missing_images'] === 0 ? 'passed' : 'failed';

        // 5 — Design & layout
        $hasH1 = trim((string) ($snapshot['h1'] ?? '')) !== '' || trim((string) ($parsed['h1'] ?? '')) !== '';
        $hasTitle = trim((string) ($snapshot['title'] ?? '')) !== '';
        $metrics['design_layout'] = ($hasH1 && $hasTitle) ? 'passed' : 'needs_improvement';

        // 6 — Forms and buttons
        $formCount = (int) ($parsed['forms'] ?? 0);
        $buttonCount = (int) ($parsed['buttons'] ?? 0);
        $metrics['forms'] = ($formCount > 0 || $buttonCount > 0) ? 'passed' : 'needs_improvement';

        // 9 — Accessibility
        $metrics['accessibility'] = $this->accessibilityStatus($parsed);

        if (is_array($builderSummary)) {
            $metrics = array_merge($metrics, $this->builderOverlayMetrics($builderSummary, $metrics));
        }

        return $this->composeResult($metrics);
    }

    /**
     * Builder JSON-only when live page is unavailable.
     *
     * @param  array<string, mixed>  $builderSummary
     * @return array{
     *     score: int,
     *     summary: string,
     *     strengths: list<string>,
     *     fixes: list<array{area: string, issue: string, recommendation: string}>,
     *     metrics: array<string, mixed>
     * }
     */
    public function auditBuilderContent(array $builderSummary): array
    {
        $metrics = [
            'broken_links' => 0,
            'missing_images' => $this->missingImagesInBuilderJson($builderSummary),
            'mobile_responsiveness' => 'passed',
            'performance' => 'needs_improvement',
            'forms' => $this->builderHasContact($builderSummary) ? 'passed' : 'needs_improvement',
            'page_loading_speed' => 'needs_improvement',
            'https_ssl' => filled($builderSummary['public_url'] ?? null) ? 'needs_improvement' : 'failed',
            'accessibility' => 'needs_improvement',
            'technical_errors' => ($builderSummary['status'] ?? '') === WebsiteSite::STATUS_PUBLISHED ? 'passed' : 'needs_improvement',
            'design_layout' => $this->builderDesignStatus($builderSummary),
            'image_loading' => $this->missingImagesInBuilderJson($builderSummary) === 0 ? 'passed' : 'failed',
        ];

        if (($builderSummary['status'] ?? '') === WebsiteSite::STATUS_PUBLISHED && filled($builderSummary['public_url'] ?? null)) {
            $metrics['https_ssl'] = Str::startsWith(strtolower((string) $builderSummary['public_url']), 'https://') ? 'passed' : 'failed';
        }

        return $this->composeResult($metrics);
    }

    /**
     * @param  array<string, mixed>  $metrics
     * @return array{
     *     score: int,
     *     summary: string,
     *     strengths: list<string>,
     *     fixes: list<array{area: string, issue: string, recommendation: string}>,
     *     metrics: array<string, mixed>
     * }
     */
    protected function composeResult(array $metrics): array
    {
        $strengths = [];
        $fixes = [];
        $score = 100;
        $penalty = [
            'failed' => 12,
            'needs_improvement' => 6,
        ];

        $areaLabels = [
            'performance' => __('Website Performance Audit'),
            'mobile_responsiveness' => __('Mobile Responsiveness Audit'),
            'broken_links' => __('Broken Links Detection'),
            'image_loading' => __('Image Loading Check'),
            'design_layout' => __('Page Design & Layout Audit'),
            'forms_buttons' => __('Forms and Button Functionality Check'),
            'page_loading_speed' => __('Page Loading Speed'),
            'https_ssl' => __('HTTPS / SSL Check'),
            'accessibility' => __('Accessibility Audit'),
            'technical_errors' => __('Technical Error Detection'),
        ];

        foreach (self::auditAreaKeys() as $key) {
            $label = $areaLabels[$key] ?? $key;

            if ($key === 'broken_links') {
                $count = (int) ($metrics['broken_links'] ?? 0);
                if ($count === 0) {
                    $strengths[] = __('Broken Links: :count', ['count' => 0]);
                } else {
                    $fixes[] = [
                        'area' => $label,
                        'issue' => __('Broken Links: :count', ['count' => $count]),
                        'recommendation' => __('Fix or remove links that return errors.'),
                    ];
                    $score -= min(15, $count * 4);
                }

                continue;
            }

            if ($key === 'image_loading') {
                $missing = (int) ($metrics['missing_images'] ?? 0);
                if ($missing === 0) {
                    $strengths[] = __('Missing Images: :count', ['count' => 0]);
                } else {
                    $fixes[] = [
                        'area' => $label,
                        'issue' => __('Missing Images: :count', ['count' => $missing]),
                        'recommendation' => __('Add valid image URLs and alt text for every image.'),
                    ];
                    $score -= min(15, $missing * 5);
                }

                continue;
            }

            $metricKey = $key === 'forms_buttons' ? 'forms' : $key;
            $status = (string) ($metrics[$metricKey] ?? $metrics[$key] ?? 'unknown');
            $readable = $this->statusLabel($status);

            if ($status === 'passed') {
                $strengths[] = $this->metricLine($key, $readable);
            } elseif ($status === 'failed' || $status === 'needs_improvement') {
                $fixes[] = [
                    'area' => $label,
                    'issue' => $this->metricLine($key, $readable),
                    'recommendation' => $this->recommendationFor($key),
                ];
                $score -= $penalty[$status] ?? 8;
            }
        }

        $score = max(0, min(100, $score));

        $summary = __('Website Health & Quality — automated checks across performance, mobile, links, images, layout, forms, SSL, accessibility, and errors.');

        return [
            'score' => $score,
            'summary' => $summary,
            'strengths' => $strengths,
            'fixes' => $fixes,
            'metrics' => $metrics,
        ];
    }

    protected function metricLine(string $key, string $statusLabel): string
    {
        return match ($key) {
            'mobile_responsiveness' => __('Mobile Responsiveness: :status', ['status' => $statusLabel]),
            'performance' => __('Performance: :status', ['status' => $statusLabel]),
            'forms_buttons', 'forms' => __('Forms: :status', ['status' => $statusLabel]),
            'page_loading_speed' => __('Page Loading Speed: :status', ['status' => $statusLabel]),
            'https_ssl' => __('HTTPS / SSL: :status', ['status' => $statusLabel]),
            'accessibility' => __('Accessibility: :status', ['status' => $statusLabel]),
            'technical_errors' => __('Technical Errors: :status', ['status' => $statusLabel]),
            'design_layout' => __('Design & Layout: :status', ['status' => $statusLabel]),
            default => ucfirst(str_replace('_', ' ', $key)).': '.$statusLabel,
        };
    }

    protected function statusLabel(string $status): string
    {
        return match ($status) {
            'passed' => __('Passed'),
            'failed' => __('Failed'),
            'needs_improvement' => __('Needs Improvement'),
            default => __('Unknown'),
        };
    }

    protected function recommendationFor(string $key): string
    {
        return match ($key) {
            'performance', 'page_loading_speed' => __('Optimize images, reduce page weight, and improve server response time.'),
            'mobile_responsiveness' => __('Add a mobile viewport meta tag and test layout on phone breakpoints.'),
            'design_layout' => __('Ensure a clear title, H1, and balanced section structure.'),
            'forms_buttons' => __('Add working contact forms or primary call-to-action buttons.'),
            'https_ssl' => __('Serve the site over HTTPS with a valid SSL certificate.'),
            'accessibility' => __('Add page language, image alt text, and accessible labels on controls.'),
            'technical_errors' => __('Resolve HTTP errors and fix broken page responses.'),
            default => __('Review this area and apply the suggested fix.'),
        };
    }

    /**
     * @return array<string, mixed>
     */
    protected function parseHtml(string $html): array
    {
        if ($html === '') {
            return [
                'has_viewport' => false,
                'links' => [],
                'image_srcs' => [],
                'images_missing_src' => 0,
                'forms' => 0,
                'buttons' => 0,
                'h1' => '',
                'images_without_alt' => 0,
                'has_lang' => false,
            ];
        }

        $dom = new DOMDocument();
        libxml_use_internal_errors(true);
        $dom->loadHTML($html, LIBXML_NOWARNING | LIBXML_NOERROR);
        libxml_clear_errors();

        $xpath = new DOMXPath($dom);

        $hasViewport = false;
        foreach ($xpath->query('//meta[@name="viewport"]') ?: [] as $meta) {
            if ($meta instanceof DOMElement) {
                $hasViewport = true;
                break;
            }
        }

        $links = [];
        foreach ($xpath->query('//a[@href]') ?: [] as $anchor) {
            if ($anchor instanceof DOMElement) {
                $href = trim((string) $anchor->getAttribute('href'));
                if ($href !== '' && ! Str::startsWith($href, ['#', 'mailto:', 'tel:', 'javascript:'])) {
                    $links[] = $href;
                }
            }
        }

        $imageSrcs = [];
        $imagesMissingSrc = 0;
        $imagesWithoutAlt = 0;
        foreach ($xpath->query('//img') ?: [] as $img) {
            if (! $img instanceof DOMElement) {
                continue;
            }

            $src = trim((string) $img->getAttribute('src'));
            if ($src === '') {
                $imagesMissingSrc++;
            } else {
                $imageSrcs[] = $src;
            }

            if (trim((string) $img->getAttribute('alt')) === '') {
                $imagesWithoutAlt++;
            }
        }

        $formNodes = $xpath->query('//form');
        $forms = $formNodes instanceof \DOMNodeList ? $formNodes->length : 0;
        $buttonNodes = $xpath->query('//button');
        $submitNodes = $xpath->query('//input[@type="submit"]');
        $buttons = ($buttonNodes instanceof \DOMNodeList ? $buttonNodes->length : 0)
            + ($submitNodes instanceof \DOMNodeList ? $submitNodes->length : 0);

        $h1 = '';
        $h1List = $xpath->query('//h1');
        $h1Node = $h1List instanceof \DOMNodeList ? $h1List->item(0) : null;
        if ($h1Node instanceof DOMElement) {
            $h1 = trim((string) $h1Node->textContent);
        }

        $htmlList = $xpath->query('/html');
        $htmlEl = $htmlList instanceof \DOMNodeList ? $htmlList->item(0) : null;
        $hasLang = $htmlEl instanceof DOMElement && trim((string) $htmlEl->getAttribute('lang')) !== '';

        return [
            'has_viewport' => $hasViewport,
            'links' => array_values(array_unique($links)),
            'image_srcs' => array_values(array_unique($imageSrcs)),
            'images_missing_src' => $imagesMissingSrc,
            'forms' => $forms,
            'buttons' => $buttons,
            'h1' => $h1,
            'images_without_alt' => $imagesWithoutAlt,
            'has_lang' => $hasLang,
        ];
    }

    /**
     * @param  list<string>  $hrefs
     */
    protected function countBrokenLinks(string $baseUrl, array $hrefs): int
    {
        $pageHost = strtolower((string) parse_url($baseUrl, PHP_URL_HOST));
        $urls = [];

        foreach ($hrefs as $href) {
            $absolute = $this->absoluteUrl($baseUrl, $href);

            if ($absolute === null) {
                continue;
            }

            $linkHost = strtolower((string) parse_url($absolute, PHP_URL_HOST));

            if ($pageHost !== '' && $linkHost !== '' && $linkHost !== $pageHost) {
                continue;
            }

            $urls[$absolute] = true;

            if (count($urls) >= 5) {
                break;
            }
        }

        return $this->probeRemoteFailures(array_keys($urls), useHead: false);
    }

    /**
     * @param  list<string>  $srcs
     */
    protected function countBrokenImages(string $baseUrl, array $srcs): int
    {
        $urls = [];

        foreach (array_slice($srcs, 0, 8) as $src) {
            $absolute = $this->absoluteUrl($baseUrl, $src);

            if ($absolute === null) {
                continue;
            }

            $urls[$absolute] = true;

            if (count($urls) >= 3) {
                break;
            }
        }

        if ($urls === []) {
            return 0;
        }

        $brokenSample = $this->probeRemoteFailures(array_keys($urls), useHead: true);

        return $brokenSample > 0 ? $brokenSample : 0;
    }

    /**
     * @param  list<string>  $urls
     */
    protected function probeRemoteFailures(array $urls, bool $useHead = true): int
    {
        if ($urls === []) {
            return 0;
        }

        $verify = is_file(storage_path('ssl/cacert.pem'))
            ? (realpath(storage_path('ssl/cacert.pem')) ?: storage_path('ssl/cacert.pem'))
            : true;

        $broken = 0;

        try {
            $responses = Http::pool(function (Pool $pool) use ($urls, $verify, $useHead): void {
                foreach ($urls as $index => $url) {
                    $request = $pool->as((string) $index)
                        ->connectTimeout(3)
                        ->timeout(4)
                        ->withOptions(['verify' => $verify, 'allow_redirects' => ['max' => 2]])
                        ->withHeaders(['User-Agent' => 'Stackposts-WebsiteAudit/1.0']);

                    if ($useHead) {
                        $request->head($url);
                    } else {
                        $request->get($url);
                    }
                }
            });

            foreach ($responses as $response) {
                if ($response instanceof Throwable) {
                    $broken++;

                    continue;
                }

                if ($response instanceof Response && $response->status() >= 400) {
                    $broken++;
                }
            }
        } catch (Throwable) {
            return 0;
        }

        return $broken;
    }

    protected function absoluteUrl(string $baseUrl, string $href): ?string
    {
        $href = trim($href);
        if ($href === '') {
            return null;
        }

        if (Str::startsWith($href, ['http://', 'https://'])) {
            return $href;
        }

        if ($baseUrl === '') {
            return null;
        }

        $base = parse_url($baseUrl);
        if (! is_array($base) || empty($base['scheme']) || empty($base['host'])) {
            return null;
        }

        $origin = $base['scheme'].'://'.$base['host'];

        if (Str::startsWith($href, '//')) {
            return $base['scheme'].':'.$href;
        }

        if (Str::startsWith($href, '/')) {
            return $origin.$href;
        }

        $path = (string) ($base['path'] ?? '/');
        $dir = rtrim(dirname($path), '/\\');

        return $origin.($dir !== '' ? $dir.'/' : '/').$href;
    }

    /**
     * @param  array<string, mixed>  $parsed
     */
    protected function accessibilityStatus(array $parsed): string
    {
        $hasLang = (bool) ($parsed['has_lang'] ?? false);
        $withoutAlt = (int) ($parsed['images_without_alt'] ?? 0);
        $totalImages = count($parsed['image_srcs'] ?? []) + (int) ($parsed['images_missing_src'] ?? 0);

        if (! $hasLang) {
            return 'needs_improvement';
        }

        if ($totalImages > 0 && $withoutAlt > max(1, (int) floor($totalImages * 0.5))) {
            return 'needs_improvement';
        }

        return 'passed';
    }

    /**
     * @param  array<string, mixed>  $builderSummary
     */
    protected function missingImagesInBuilderJson(array $builderSummary): int
    {
        $missing = 0;
        $sections = is_array($builderSummary['sections'] ?? null) ? $builderSummary['sections'] : [];

        foreach ($sections as $section) {
            if (! is_array($section)) {
                continue;
            }

            foreach (['image_url', 'hero_image_url'] as $key) {
                if (array_key_exists($key, $section) && trim((string) $section[$key]) === '') {
                    $missing++;
                }
            }

            foreach ((array) ($section['items'] ?? []) as $item) {
                if (is_array($item) && array_key_exists('image_url', $item) && trim((string) $item['image_url']) === '') {
                    $missing++;
                }
            }
        }

        return $missing;
    }

    /**
     * @param  array<string, mixed>  $builderSummary
     */
    protected function builderHasContact(array $builderSummary): bool
    {
        $sections = is_array($builderSummary['sections'] ?? null) ? $builderSummary['sections'] : [];

        return collect($sections)->contains(
            fn ($s) => is_array($s) && strtolower((string) ($s['type'] ?? '')) === 'contact',
        );
    }

    /**
     * @param  array<string, mixed>  $builderSummary
     */
    protected function builderDesignStatus(array $builderSummary): string
    {
        $sections = is_array($builderSummary['sections'] ?? null) ? $builderSummary['sections'] : [];
        if (count($sections) < 2) {
            return 'needs_improvement';
        }

        $hasHero = collect($sections)->contains(fn ($s) => is_array($s) && strtolower((string) ($s['type'] ?? '')) === 'hero');

        return $hasHero ? 'passed' : 'needs_improvement';
    }

    /**
     * @param  array<string, mixed>  $builderSummary
     * @param  array<string, mixed>  $metrics
     * @return array<string, mixed>
     */
    protected function builderOverlayMetrics(array $builderSummary, array $metrics): array
    {
        if ($metrics['design_layout'] === 'unknown' || $metrics['design_layout'] === 'needs_improvement') {
            $metrics['design_layout'] = $this->builderDesignStatus($builderSummary);
        }

        return $metrics;
    }
}