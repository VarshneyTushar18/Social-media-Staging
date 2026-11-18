<?php

namespace Modules\AppWebsiteBuilder\Support;

use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class HostingerDomainClient
{
    private const API_BASE_URL = 'https://developers.hostinger.com';

    private const DEFAULT_TLDS = ['com', 'net', 'org', 'io', 'co', 'app', 'site', 'online', 'store', 'xyz', 'info', 'biz', 'live'];

    public function isConfigured(): bool
    {
        return WebsiteDomainRegistrarConfig::hostingerConfigured();
    }

    /**
     * @return list<array{domain: string, display_host: string, available: bool, checkout_url: string, registerable?: bool, is_premium?: bool}>
     */
    public function searchWithAvailability(string $query, int $limit = 24): array
    {
        if (! $this->isConfigured()) {
            throw new \RuntimeException(__('Hostinger API is not configured. Add credentials under Settings → Domain registrars.'));
        }

        $seed = $this->normalizeSearchQuery($query);

        if ($seed === '') {
            return [];
        }

        [$slug, $primaryTld] = $this->splitSlugAndTld($seed);
        $tlds = $this->tldsForSearch($primaryTld);
        $withAlternatives = count($tlds) === 1;

        $rows = $this->mapAvailabilityRows($this->fetchAvailability($slug, $tlds, $withAlternatives));

        if ($rows === [] && ! $withAlternatives && $primaryTld !== null) {
            $rows = $this->mapAvailabilityRows($this->fetchAvailability($slug, [$primaryTld], true));
        }

        $formatted = [];

        foreach ($rows as $row) {
            if (! (bool) ($row['available'] ?? false)) {
                continue;
            }

            $domain = (string) ($row['domain'] ?? '');

            if ($domain === '') {
                continue;
            }

            $formatted[] = array_merge($row, [
                'display_host' => $this->displayHost($domain),
                'checkout_url' => $this->checkoutUrl($domain),
                'registerable' => (bool) ($row['registerable'] ?? true),
            ]);
        }

        return WebsiteDomainSearchResults::sortByPriceAsc($formatted, $limit);
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

        [$slug, $tld] = $this->splitSlugAndTld($domain);

        if ($slug === '' || $tld === null) {
            return false;
        }

        foreach ($this->mapAvailabilityRows($this->fetchAvailability($slug, [$tld], false)) as $row) {
            if ((string) ($row['domain'] ?? '') === $domain) {
                return (bool) ($row['available'] ?? false);
            }
        }

        return false;
    }

    public function checkoutUrl(string $domain): string
    {
        $domain = $this->normalizeDomain($domain);

        return 'https://www.hostinger.com/domain-name-results?domain='.urlencode($domain).'&from=domain-name-search';
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
    protected function tldsForSearch(?string $primaryTld): array
    {
        $tlds = self::DEFAULT_TLDS;

        if ($primaryTld !== null && $primaryTld !== '' && ! in_array($primaryTld, $tlds, true)) {
            array_unshift($tlds, $primaryTld);
        }

        return array_slice(array_values(array_unique($tlds)), 0, 14);
    }

    /**
     * @return array{0: string, 1: ?string}
     */
    protected function splitSlugAndTld(string $seed): array
    {
        $normalized = $this->normalizeDomain($seed);

        if (! str_contains($normalized, '.')) {
            return [$normalized, null];
        }

        $tld = strtolower((string) Str::afterLast($normalized, '.'));
        $slug = strtolower((string) Str::beforeLast($normalized, '.'));

        return [$slug, $tld !== '' ? $tld : null];
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
     * @param  list<string>  $tlds
     * @return list<array<string, mixed>>
     */
    protected function fetchAvailability(string $slug, array $tlds, bool $withAlternatives): array
    {
        $slug = strtolower(trim($slug));
        $tlds = array_values(array_filter(array_map(
            fn (string $tld): string => ltrim(strtolower(trim($tld)), '.'),
            $tlds
        )));

        if ($slug === '' || $tlds === []) {
            return [];
        }

        $response = Http::timeout(30)
            ->acceptJson()
            ->withToken($this->apiToken())
            ->post($this->baseUrl().'/api/domains/v1/availability', [
                'domain' => $slug,
                'tlds' => $tlds,
                'with_alternatives' => $withAlternatives,
            ]);

        if (! $response->successful()) {
            $this->throwFromResponse($response);
        }

        return $this->decodeAvailabilityList($response);
    }

    /**
     * @return list<array<string, mixed>>
     */
    protected function decodeAvailabilityList(Response $response): array
    {
        $decoded = $response->json();

        if (! is_array($decoded)) {
            return [];
        }

        if (isset($decoded['data']) && is_array($decoded['data'])) {
            return array_values(array_filter($decoded['data'], 'is_array'));
        }

        if (array_is_list($decoded)) {
            return array_values(array_filter($decoded, 'is_array'));
        }

        return [];
    }

    /**
     * @param  list<array<string, mixed>>  $items
     * @return list<array{domain: string, available: bool, registerable: bool, is_premium?: bool}>
     */
    protected function mapAvailabilityRows(array $items): array
    {
        $rows = [];

        foreach ($items as $item) {
            $domain = $this->normalizeDomain((string) ($item['domain'] ?? ''));

            if ($domain === '') {
                continue;
            }

            $available = (bool) ($item['is_available'] ?? false);
            $restriction = trim((string) ($item['restriction'] ?? ''));

            $rows[] = [
                'domain' => $domain,
                'available' => $available,
                'registerable' => $available && $restriction === '',
                'is_premium' => (bool) ($item['is_premium'] ?? false),
            ];
        }

        return $rows;
    }

    protected function apiToken(): string
    {
        return (string) (WebsiteDomainRegistrarConfig::hostinger()['api_token'] ?? '');
    }

    protected function baseUrl(): string
    {
        return self::API_BASE_URL;
    }

    protected function throwFromResponse(Response $response): void
    {
        $message = match ($response->status()) {
            401, 403 => __('Hostinger API rejected the credentials. Check Settings → Domain registrars.'),
            429 => __('Hostinger rate limit reached. Try again shortly.'),
            422 => __('Hostinger could not process the domain search request.'),
            default => __('Hostinger domain search failed (HTTP :status).', ['status' => $response->status()]),
        };

        $decoded = $response->json();

        if (is_array($decoded)) {
            $apiMessage = trim((string) ($decoded['message'] ?? $decoded['error'] ?? ''));

            if ($apiMessage !== '') {
                $message = $apiMessage;
            }
        }

        throw new \RuntimeException($message);
    }
}