<?php

namespace Modules\AppWebsiteBuilder\Support;

use InvalidArgumentException;

class WebsiteDomainRegistrarResolver
{
    public static function supports(string $providerKey): bool
    {
        return in_array(strtolower(trim($providerKey)), self::keys(), true);
    }

    /**
     * @return list<string>
     */
    public static function keys(): array
    {
        return ['godaddy', 'namecheap', 'hostinger', 'namesilo'];
    }

    public static function resolve(string $providerKey): GoDaddyDomainClient|NamecheapDomainClient|HostingerDomainClient|NameSiloDomainClient
    {
        return match (strtolower(trim($providerKey))) {
            'namecheap' => app(NamecheapDomainClient::class),
            'hostinger' => app(HostingerDomainClient::class),
            'namesilo' => app(NameSiloDomainClient::class),
            'godaddy' => app(GoDaddyDomainClient::class),
            default => throw new InvalidArgumentException(__('Unknown domain provider: :key', ['key' => $providerKey])),
        };
    }

    public static function label(string $providerKey): string
    {
        $provider = WebsiteDomainProviders::find($providerKey);

        return $provider['name'] ?? ucfirst($providerKey);
    }
}