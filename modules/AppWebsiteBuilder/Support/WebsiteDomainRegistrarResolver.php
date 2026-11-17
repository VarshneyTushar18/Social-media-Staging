<?php

namespace Modules\AppWebsiteBuilder\Support;

use InvalidArgumentException;

class WebsiteDomainRegistrarResolver
{
    public static function supports(string $providerKey): bool
    {
        return in_array(strtolower(trim($providerKey)), ['godaddy', 'namecheap'], true);
    }

    public static function resolve(string $providerKey): GoDaddyDomainClient|NamecheapDomainClient
    {
        return match (strtolower(trim($providerKey))) {
            'namecheap' => app(NamecheapDomainClient::class),
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