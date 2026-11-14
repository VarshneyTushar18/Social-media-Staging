<?php

namespace Modules\AppWebsiteBuilder\Support;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use SimpleXMLElement;

class NamecheapDomainClient
{
    private const XML_NS = 'http://api.namecheap.com/xml.response';

    public function isConfigured(): bool
    {
        return $this->apiUser() !== ''
            && $this->apiKey() !== ''
            && $this->username() !== ''
            && $this->clientIp() !== '';
    }

    public function isSandbox(): bool
    {
        return filter_var(config('modules.appwebsitebuilder.namecheap.sandbox', true), FILTER_VALIDATE_BOOLEAN);
    }

    /**
     * @return list<array{domain: string, display_host: string, available: bool, checkout_url: string}>
     */
    public function searchWithAvailability(string $query, int $limit = 24): array
    {
        if (! $this->isConfigured()) {
            throw new \RuntimeException(__('Namecheap API is not configured. Add NAMECHEAP_API_* and NAMECHEAP_CLIENT_IP to .env.'));
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
        $host = $this->isSandbox()
            ? 'https://www.sandbox.namecheap.com'
            : 'https://www.namecheap.com';

        return $host.'/domains/registration/results.aspx?domain='.urlencode($domain);
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
     * @return list<array{domain: string, available: bool, price_usd?: ?float, price_label?: ?string, is_premium?: bool}>
     */
    protected function checkAvailabilityRows(array $domains): array
    {
        $response = $this->request('namecheap.domains.check', [
            'DomainList' => implode(',', $domains),
        ]);

        $rows = [];

        foreach ($this->domainCheckResults($response) as $result) {
            $attrs = $result->attributes();
            $domain = $this->normalizeDomain((string) ($attrs['Domain'] ?? ''));

            if ($domain === '') {
                continue;
            }

            $isAvailable = strtolower((string) ($attrs['Available'] ?? 'false')) === 'true';
            $isPremium = strtolower((string) ($attrs['IsPremiumName'] ?? 'false')) === 'true';
            $premiumPrice = (float) ($attrs['PremiumRegistrationPrice'] ?? 0);
            $priceUsd = $isPremium && $premiumPrice > 0 ? round($premiumPrice, 2) : null;

            $rows[] = [
                'domain' => $domain,
                'available' => $isAvailable,
                'registerable' => $isAvailable,
                'price_usd' => $priceUsd,
                'is_premium' => $isPremium,
            ];
        }

        return $rows;
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
     * @param  array<string, string|int>  $params
     */
    protected function request(string $command, array $params = []): SimpleXMLElement
    {
        $query = array_merge([
            'ApiUser' => $this->apiUser(),
            'ApiKey' => $this->apiKey(),
            'UserName' => $this->username(),
            'ClientIp' => $this->clientIp(),
            'Command' => $command,
        ], $params);

        $response = Http::timeout(30)
            ->get($this->baseUrl(), $query);

        if (! $response->successful()) {
            throw new \RuntimeException(__('Namecheap API request failed (HTTP :status).', [
                'status' => $response->status(),
            ]));
        }

        $body = trim($response->body());

        if ($body === '') {
            throw new \RuntimeException(__('Namecheap API returned an empty response.'));
        }

        $xml = @simplexml_load_string($body);

        if (! $xml instanceof SimpleXMLElement) {
            throw new \RuntimeException(__('Namecheap API returned invalid XML.'));
        }

        $status = (string) ($xml['Status'] ?? '');

        if (strcasecmp($status, 'OK') !== 0) {
            throw new \RuntimeException($this->extractErrors($xml));
        }

        return $xml;
    }

  /**
     * @return list<SimpleXMLElement>
     */
    protected function domainCheckResults(SimpleXMLElement $response): array
    {
        $command = $this->commandResponse($response);

        if ($command === null) {
            return [];
        }

        $results = [];

        foreach ($command->DomainCheckResult as $result) {
            $results[] = $result;
        }

        return $results;
    }

    protected function commandResponse(SimpleXMLElement $response): ?SimpleXMLElement
    {
        $children = $response->children(self::XML_NS);

        return isset($children->CommandResponse) ? $children->CommandResponse : null;
    }

    protected function extractErrors(SimpleXMLElement $response): string
    {
        $errors = [];
        $errorNodes = $response->children(self::XML_NS)->Errors ?? null;

        if ($errorNodes !== null) {
            foreach ($errorNodes->Error as $error) {
                $errors[] = (string) $error;
            }
        }

        return $errors !== [] ? implode(' ', $errors) : __('Namecheap API error.');
    }

    protected function baseUrl(): string
    {
        return $this->isSandbox()
            ? 'https://api.sandbox.namecheap.com/xml.response'
            : 'https://api.namecheap.com/xml.response';
    }

    protected function apiUser(): string
    {
        return trim((string) config('modules.appwebsitebuilder.namecheap.api_user', ''));
    }

    protected function apiKey(): string
    {
        return trim((string) config('modules.appwebsitebuilder.namecheap.api_key', ''));
    }

    protected function username(): string
    {
        return trim((string) config('modules.appwebsitebuilder.namecheap.username', ''));
    }

    protected function clientIp(): string
    {
        return trim((string) config('modules.appwebsitebuilder.namecheap.client_ip', ''));
    }
}