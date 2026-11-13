<?php

namespace Modules\AppWebsiteBuilder\Support;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use RuntimeException;

class WebsiteExternalPageSnapshot
{
    public function assertSafePublicUrl(string $url): string
    {
        $normalized = trim($url);

        if (! Str::startsWith($normalized, ['http://', 'https://'])) {
            $normalized = 'https://'.$normalized;
        }

        if (filter_var($normalized, FILTER_VALIDATE_URL) === false) {
            throw new RuntimeException(__('Enter a valid website URL.'));
        }

        $host = strtolower((string) parse_url($normalized, PHP_URL_HOST));

        if ($host === '' || $host === 'localhost') {
            throw new RuntimeException(__('That URL is not allowed.'));
        }

        if (filter_var($host, FILTER_VALIDATE_IP)) {
            $this->assertPublicIp($host);
        } else {
            $resolved = gethostbyname($host);

            if ($resolved === $host) {
                throw new RuntimeException(__('Could not resolve that domain.'));
            }

            $this->assertPublicIp($resolved);
        }

        return $normalized;
    }

    /**
     * @return array{url: string, status: int, title: string, meta_description: string, h1: string, excerpt: string, https: bool}
     */
    public function fetch(string $url): array
    {
        $safeUrl = $this->assertSafePublicUrl($url);

        $verify = is_file(storage_path('ssl/cacert.pem'))
            ? (realpath(storage_path('ssl/cacert.pem')) ?: storage_path('ssl/cacert.pem'))
            : true;

        $response = Http::timeout(15)
            ->withOptions(['verify' => $verify, 'allow_redirects' => ['max' => 5]])
            ->withHeaders([
                'User-Agent' => 'Stackposts-WebsiteAudit/1.0',
                'Accept' => 'text/html,application/xhtml+xml',
            ])
            ->get($safeUrl);

        $status = $response->status();
        $body = (string) $response->body();
        $body = Str::limit($body, 200000, '');

        $title = $this->matchTag($body, 'title');
        $metaDescription = $this->matchMeta($body, 'description');
        $h1 = $this->matchFirstTagText($body, 'h1');
        $text = trim(preg_replace('/\s+/', ' ', strip_tags($body)) ?? '');

        return [
            'url' => $safeUrl,
            'status' => $status,
            'title' => $title,
            'meta_description' => $metaDescription,
            'h1' => $h1,
            'excerpt' => Str::limit($text, 4000, '…'),
            'https' => Str::startsWith(strtolower($safeUrl), 'https://'),
        ];
    }

    protected function assertPublicIp(string $ip): void
    {
        if (! filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
            throw new RuntimeException(__('That URL is not allowed.'));
        }
    }

    protected function matchTag(string $html, string $tag): string
    {
        if (preg_match('/<'.$tag.'[^>]*>(.*?)<\/'.$tag.'>/is', $html, $matches) === 1) {
            return trim(html_entity_decode(strip_tags($matches[1]), ENT_QUOTES | ENT_HTML5, 'UTF-8'));
        }

        return '';
    }

    protected function matchMeta(string $html, string $name): string
    {
        if (preg_match('/<meta[^>]+name=["\']'.preg_quote($name, '/').'["\'][^>]+content=["\']([^"\']*)["\']/i', $html, $matches) === 1) {
            return trim(html_entity_decode($matches[1], ENT_QUOTES | ENT_HTML5, 'UTF-8'));
        }

        if (preg_match('/<meta[^>]+content=["\']([^"\']*)["\'][^>]+name=["\']'.preg_quote($name, '/').'["\']/i', $html, $matches) === 1) {
            return trim(html_entity_decode($matches[1], ENT_QUOTES | ENT_HTML5, 'UTF-8'));
        }

        return '';
    }

    protected function matchFirstTagText(string $html, string $tag): string
    {
        if (preg_match('/<'.$tag.'[^>]*>(.*?)<\/'.$tag.'>/is', $html, $matches) === 1) {
            return trim(html_entity_decode(strip_tags($matches[1]), ENT_QUOTES | ENT_HTML5, 'UTF-8'));
        }

        return '';
    }
}
