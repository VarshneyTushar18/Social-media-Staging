<?php

namespace Modules\AppWebsiteBuilder\Support;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use SimpleXMLElement;

class NameSiloDomainClient
{
    public function isConfigured(): bool
    {
        return WebsiteDomainRegistrarConfig::namesiloConfigured();
    }

    /**
     * @return list<array{domain: string, display_host: string, available: bool, checkout_url: string, registerable?: bool, is_premium?: bool, price_usd?: ?float}>
     */
    public function searchWithAvailability(string $query, int $limit = 24): array
    {
        if (! $this->isConfigured()) {
            throw new \RuntimeException(__('NameSilo API is not configured. Add credentials under Settings → Domain registrars.'));
        }

        $seed = $this->normalizeSearchQuery($query);

        if ($seed === '') {
            return [];
        }

        $candidates = $this->collectCandidates($seed, WebsiteDomainSearchResults::candidatePoolSize($limit));

        if ($candidates === []) {
            return [];
        }

        $rows = [];

        foreach ($this->checkAvailabilityRows($candidates) as $row) {
            if (! (bool) ($row['available'] ?? false)) {
                continue;
            }

            $domain = (string) ($row['domain'] ?? '');
            $rows[] = array_merge($row, [
                'display_host' => $this->displayHost($domain),
                'checkout_url' => $this->checkoutUrl($domain),
                'registerable' => (bool) ($row['registerable'] ?? true),
            ]);
        }

        return WebsiteDomainSearchResults::sortByPriceAsc($rows, $limit);
    }

    public function isDomainAvailable(string $domain): bool
    {
        if (! $this->isConfigured()) {
            return false;
        }

        $domain = $this->normalizeDomain($domain);

        if ($domain === '') {
            return false;
        }

        foreach ($this->checkAvailabilityRows([$domain]) as $row) {
            if ((string) ($row['domain'] ?? '') === $domain) {
                return (bool) ($row['available'] ?? false);
            }
        }

        return false;
    }

    public function checkoutUrl(string $domain): string
    {
        $domain = $this->normalizeDomain($domain);

        return 'https://www.namesilo.com/domain/search-domains?query='.urlencode($domain);
    }

    public function displayHost(string $domain): string
    {
        $domain = $this->normalizeDomain($domain);

        return str_starts_with($domain, 'www.') ? $domain : 'www.'.$domain;
    }

    public function normalizeDomain(string $domain): string
    {
        $domain = strtolower(trim($domain));
        $domain = preg_replace('/^(https?:\/\/)?(www\.)?/', '', $domain) ?? $domain;

        return Str::limit(trim($domain, '.-'), 253, '');
    }

    /**
     * @return list<string>
     */
    protected function collectCandidates(string $seed, int $limit): array
    {
        $domains = [];
        $base = str_contains($seed, '.') ? $this->normalizeDomain($seed) : $this->normalizeDomain($seed);
        $slug = str_contains($base, '.') ? Str::beforeLast($base, '.') : $base;

        if ($slug === '') {
            return [];
        }

        $tlds = ['com', 'net', 'org', 'io', 'co', 'app', 'site', 'online', 'store', 'xyz', 'info', 'biz', 'live'];

        foreach ($tlds as $tld) {
            $candidate = $this->normalizeDomain($slug.'.'.$tld);

            if ($candidate !== '' && ! in_array($candidate, $domains, true)) {
                $domains[] = $candidate;
            }

            if (count($domains) >= $limit + 2) {
                break;
            }
        }

        if (str_contains($base, '.')) {
            array_unshift($domains, $base);
            $domains = array_values(array_unique($domains));
        }

        return array_slice($domains, 0, min(50, max($limit, 12)));
    }

    /**
     * @param  list<string>  $domains
     * @return list<array{domain: string, available: bool, registerable: bool, price_usd?: ?float, is_premium?: bool}>
     */
    protected function checkAvailabilityRows(array $domains): array
    {
        $domains = array_values(array_unique(array_filter(array_map(
            fn (string $d): string => $this->normalizeDomain($d),
            $domains
        ))));

        if ($domains === []) {
            return [];
        }

        $byDomain = [];

        foreach (array_chunk($domains, 40) as $chunk) {
            foreach ($this->parseAvailabilityResponse($this->request('checkRegisterAvailability', [
                'domains' => implode(',', $chunk),
            ])) as $row) {
                $byDomain[(string) $row['domain']] = $row;
            }
        }

        $rows = [];

        foreach ($domains as $domain) {
            $rows[] = $byDomain[$domain] ?? [
                'domain' => $domain,
                'available' => false,
                'registerable' => false,
            ];
        }

        return $rows;
    }

    /**
     * @return list<array{domain: string, available: bool, registerable: bool, price_usd?: ?float, is_premium?: bool}>
     */
    protected function parseAvailabilityResponse(SimpleXMLElement $reply): array
    {
        $rows = [];
        $availableSet = [];

        if (isset($reply->available)) {
            foreach ($reply->available->domain as $node) {
                $domain = $this->normalizeDomain($this->domainNameFromNode($node));

                if ($domain === '') {
                    continue;
                }

                $attrs = $node->attributes();
                $price = (float) ($attrs['price'] ?? 0);
                $isPremium = ((string) ($attrs['premium'] ?? '0')) === '1';

                $availableSet[$domain] = true;
                $rows[] = [
                    'domain' => $domain,
                    'available' => true,
                    'registerable' => true,
                    'price_usd' => $price > 0 ? round($price, 2) : null,
                    'is_premium' => $isPremium,
                ];
            }
        }

        if (isset($reply->unavailable)) {
            foreach ($reply->unavailable->domain as $node) {
                $domain = $this->normalizeDomain($this->domainNameFromNode($node));

                if ($domain === '' || isset($availableSet[$domain])) {
                    continue;
                }

                $rows[] = [
                    'domain' => $domain,
                    'available' => false,
                    'registerable' => false,
                ];
            }
        }

        return $rows;
    }

    protected function domainNameFromNode(SimpleXMLElement $node): string
    {
        $text = trim((string) $node);

        if ($text !== '') {
            return $text;
        }

        return trim((string) ($node[0] ?? ''));
    }

    protected function normalizeSearchQuery(string $query): string
    {
        $query = strtolower(trim($query));
        $query = preg_replace('/^(https?:\/\/)?(www\.)?/', '', $query) ?? $query;
        $query = preg_replace('/\s+/', '', $query) ?? '';
        $query = preg_replace('/[^a-z0-9.\-]/', '', $query) ?? '';

        return Str::limit(trim($query, '.-'), 253, '');
    }

    /**
     * @param  array<string, string>  $params
     */
    protected function request(string $command, array $params = []): SimpleXMLElement
    {
        $query = array_merge([
            'version' => '1',
            'type' => 'xml',
            'key' => $this->apiKey(),
        ], $params);

        $response = Http::timeout(30)
            ->get($this->baseUrl().$command, $query);

        if (! $response->successful()) {
            throw new \RuntimeException(__('NameSilo API request failed (HTTP :status).', [
                'status' => $response->status(),
            ]));
        }

        $body = trim($response->body());

        if ($body === '') {
            throw new \RuntimeException(__('NameSilo API returned an empty response.'));
        }

        $xml = @simplexml_load_string($body);

        if (! $xml instanceof SimpleXMLElement) {
            throw new \RuntimeException(__('NameSilo API returned invalid XML.'));
        }

        $reply = isset($xml->reply) ? $xml->reply : $xml;
        $code = (string) ($reply->code ?? '');

        if ($code !== '300') {
            $detail = trim((string) ($reply->detail ?? ''));

            throw new \RuntimeException($detail !== ''
                ? $detail
                : __('NameSilo API error (code :code).', ['code' => $code !== '' ? $code : '?']));
        }

        return $reply;
    }

    protected function baseUrl(): string
    {
        return 'https://www.namesilo.com/api/';
    }

    protected function apiKey(): string
    {
        return (string) (WebsiteDomainRegistrarConfig::namesilo()['api_key'] ?? '');
    }
}