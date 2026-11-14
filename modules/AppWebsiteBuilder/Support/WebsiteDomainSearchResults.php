<?php

namespace Modules\AppWebsiteBuilder\Support;

class WebsiteDomainSearchResults
{
    public const DEFAULT_LIMIT = 24;

    public const CANDIDATE_POOL_MAX = 50;

    /**
     * @param  list<array{domain: string, display_host: string, available: bool, checkout_url: string, price_usd?: ?float, price_label?: ?string, is_premium?: bool}>  $rows
     * @return list<array{domain: string, display_host: string, available: bool, checkout_url: string, price_usd?: ?float, price_label?: ?string, is_premium?: bool}>
     */
    public static function sortByPriceAsc(array $rows, int $limit): array
    {
        $available = [];

        foreach ($rows as $row) {
            if (! (bool) ($row['available'] ?? false)) {
                continue;
            }

            if (blank($row['domain'] ?? null)) {
                continue;
            }

            if (($row['registerable'] ?? true) === false) {
                continue;
            }

            $available[] = $row;
        }

        usort($available, function (array $a, array $b): int {
            $priceA = isset($a['price_usd']) && is_numeric($a['price_usd']) ? (float) $a['price_usd'] : null;
            $priceB = isset($b['price_usd']) && is_numeric($b['price_usd']) ? (float) $b['price_usd'] : null;

            if ($priceA !== null && $priceB !== null && $priceA !== $priceB) {
                return $priceA <=> $priceB;
            }

            if ($priceA !== null && $priceB === null) {
                return -1;
            }

            if ($priceA === null && $priceB !== null) {
                return 1;
            }

            return strcmp((string) ($a['domain'] ?? ''), (string) ($b['domain'] ?? ''));
        });

        return array_slice($available, 0, $limit);
    }

    public static function candidatePoolSize(int $limit): int
    {
        return min(self::CANDIDATE_POOL_MAX, max($limit + 16, 32));
    }
}