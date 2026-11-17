<?php

namespace Modules\AppWebsiteBuilder\Support;

class WebsiteDomainProviders
{
    /**
     * @return list<array{key: string, name: string, url: string}>
     */
    public static function all(): array
    {
        return [
            ['key' => 'godaddy', 'name' => 'GoDaddy', 'url' => 'https://www.godaddy.com/domains'],
            ['key' => 'namecheap', 'name' => 'Namecheap', 'url' => 'https://www.namecheap.com/domains/'],
        ];
    }

    /**
     * @return array{key: string, name: string, url: string}|null
     */
    public static function find(string $key): ?array
    {
        $key = strtolower(trim($key));

        foreach (self::all() as $provider) {
            if ($provider['key'] === $key) {
                return $provider;
            }
        }

        return null;
    }
}
