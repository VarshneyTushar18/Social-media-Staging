<?php

namespace Modules\AppWebsiteBuilder\Support;

use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class GoDaddyDomainClient
{
    public function isConfigured(): bool
    {
        if ($this->apiKey() !== '' && $this->apiSecret() !== '') {
            return true;
        }

        return $this->apiToken() !== '';
    }

    public function isOte(): bool
    {
        return strtolower((string) config('modules.appwebsitebuilder.godaddy.env', 'ote')) !== 'production';
    }

    /**
     * @return list<array{domain: string, display_host: string, available: bool, price: ?float, currency: string, price_label: ?string, checkout_url: string}>
     */
    public function searchWithAvailability(string $query, int $limit = 12): array
    {
        if (! $this->isConfigured()) {
            throw new \RuntimeException(__('GoDaddy API credentials are not configured.'));
        }

        $seed = $this->normalizeSearchQuery($query);

        if ($seed === '') {
            return [];
        }

        $candidates = $this->collectCandidates($seed, $limit);
        $rows = $this->availabilityForDomains($candidates);

        return array_map(fn (array $row): array => $this->formatRow($row), $this->sortRows($rows, $limit));
    }

    public function checkoutUrl(string $domain): string
    {
        return 'https://www.godaddy.com/domainsearch/find?checkAvail=1&domainToCheck='.urlencode($this->normalizeDomain($domain));
    }

    public function displayHost(string $domain): string
    {
        $domain = $this->normalizeDomain($domain);

        return str_starts_with($domain, 'www.') ? $domain : 'www.'.$domain;
    }

    /**
     * @return list<string>
     */
    protected function collectCandidates(string $seed, int $limit): array
    {
        $domains = [];
        $primary = str_contains($seed, '.') ? $this->normalizeDomain($seed) : $this->normalizeDomain($seed.'.com');

        if ($primary !== '') {
            $domains[] = $primary;
        }

        $response = $this->request('get', '/v1/domains/suggest', [
            'query' => $seed,
            'limit' => $limit,
            'waitMs' => 2000,
        ]);

        if ($response->successful()) {
            $body = $response->json();

            if (is_array($body)) {
                foreach ($body as $row) {
                    $name = $this->normalizeDomain((string) ($row['domain'] ?? ''));

                    if ($name !== '' && ! in_array($name, $domains, true)) {
                        $domains[] = $name;
                    }
                }
            }
        }

        return array_slice($domains, 0, $limit + 1);
    }

    /**
     * @param  list<string>  $domains
     * @return list<array<string, mixed>>
     */
    protected function availabilityForDomains(array $domains): array
    {
        if ($domains === []) {
            return [];
        }

        $headers = array_merge($this->authHeaders(), ['Accept' => 'application/json']);

        $responses = Http::pool(function ($pool) use ($domains, $headers) {
            foreach ($domains as $index => $domain) {
                $pool->as((string) $index)
                    ->timeout(25)
                    ->withHeaders($headers)
                    ->get($this->baseUrl().'/v1/domains/available', [
                        'domain' => $domain,
                        'checkType' => 'FAST',
                    ]);
            }
        });

        $rows = [];
        $failure = null;

        foreach ($domains as $index => $domain) {
            /** @var Response|null $response */
            $response = $responses[(string) $index] ?? null;

            if ($response === null) {
                continue;
            }

            if ($response->successful()) {
                $row = $response->json();

                if (is_array($row)) {
                    $rows[] = $row;
                }

                continue;
            }

            $failure ??= $response;
        }

        if ($rows === [] && $failure !== null) {
            $this->throwFromResponse($failure);
        }

        return $rows;
    }

    /**
     * @param  array<string, mixed>  $row
     * @return array{domain: string, display_host: string, available: bool, price: ?float, currency: string, price_label: ?string, checkout_url: string}
     */
    protected function formatRow(array $row): array
    {
        $domain = $this->normalizeDomain((string) ($row['domain'] ?? ''));
        $available = (bool) ($row['available'] ?? false);
        $currency = strtoupper((string) ($row['currency'] ?? 'USD'));
        $priceMicro = isset($row['price']) ? (int) $row['price'] : null;
        $price = $priceMicro !== null ? round($priceMicro / 1_000_000, 2) : null;

        return [
            'domain' => $domain,
            'display_host' => $this->displayHost($domain),
            'available' => $available,
            'price' => $price,
            'currency' => $currency,
            'price_label' => $available && $price !== null ? $this->formatMoney($price, $currency) : null,
            'checkout_url' => $this->checkoutUrl($domain),
        ];
    }

    /**
     * @param  list<array<string, mixed>>  $rows
     * @return list<array<string, mixed>>
     */
    protected function sortRows(array $rows, int $limit): array
    {
        usort($rows, function (array $a, array $b): int {
            $aAvail = (bool) ($a['available'] ?? false);
            $bAvail = (bool) ($b['available'] ?? false);

            if ($aAvail !== $bAvail) {
                return $aAvail ? -1 : 1;
            }

            return strcmp((string) ($a['domain'] ?? ''), (string) ($b['domain'] ?? ''));
        });

        return array_slice($rows, 0, $limit);
    }

    protected function formatMoney(float $amount, string $currency): string
    {
        $symbol = match ($currency) {
            'USD' => '$',
            'EUR' => '€',
            'GBP' => '£',
            default => $currency.' ',
        };

        return $symbol.number_format($amount, 2);
    }

    protected function normalizeSearchQuery(string $query): string
    {
        $query = strtolower(trim($query));
        $query = preg_replace('/^(https?:\/\/)?(www\.)?/', '', $query) ?? $query;
        $query = preg_replace('/\s+/', '', $query) ?? '';
        $query = preg_replace('/[^a-z0-9.\-]/', '', $query) ?? '';

        return Str::limit(trim($query, '.-'), 253, '');
    }

    public function normalizeDomain(string $domain): string
    {
        $domain = strtolower(trim($domain));
        $domain = preg_replace('/^(https?:\/\/)?(www\.)?/', '', $domain) ?? $domain;

        return Str::limit(trim($domain, '.-'), 253, '');
    }

    /**
     * @return array<string, string>
     */
    protected function authHeaders(): array
    {
        if ($this->apiKey() !== '' && $this->apiSecret() !== '') {
            return ['Authorization' => 'sso-key '.$this->apiKey().':'.$this->apiSecret()];
        }

        return ['Authorization' => 'Bearer '.$this->apiToken()];
    }

    protected function apiToken(): string
    {
        return trim((string) config('modules.appwebsitebuilder.godaddy.api_token', ''));
    }

    protected function apiKey(): string
    {
        return trim((string) config('modules.appwebsitebuilder.godaddy.api_key', ''));
    }

    protected function apiSecret(): string
    {
        return trim((string) config('modules.appwebsitebuilder.godaddy.api_secret', ''));
    }

    protected function baseUrl(): string
    {
        return $this->isOte()
            ? 'https://api.ote-godaddy.com'
            : 'https://api.godaddy.com';
    }

  /**
     * @param  array<string, mixed>  $query
     */
    protected function request(string $method, string $path, array $query = []): Response
    {
        return Http::timeout(25)
            ->acceptJson()
            ->withHeaders($this->authHeaders())
            ->get($this->baseUrl().$path, $query);
    }

    protected function throwFromResponse(Response $response): void
    {
        $message = match ($response->status()) {
            401, 403 => __('GoDaddy API rejected the credentials. Check GODADDY_API_* and GODADDY_API_ENV in .env.'),
            429 => __('GoDaddy rate limit reached. Try again shortly.'),
            default => __('GoDaddy domain search failed (HTTP :status).', ['status' => $response->status()]),
        };

        $body = $response->json();

        if (is_array($body) && filled($body['message'] ?? null)) {
            $message = (string) $body['message'];
        }

        throw new \RuntimeException($message);
    }
}
