<?php

namespace Modules\AdminDomainRegistrars\Support;

use Modules\AdminSettings\Support\OptionStore;
use Modules\AppWebsiteBuilder\Support\WebsiteDomainRegistrarConfig;

class DomainRegistrarConfigurator
{
    public function __construct(
        protected OptionStore $options,
    ) {}

    /**
     * @return array<string, string>
     */
    public function stateKeys(): array
    {
        return [
            'domain_godaddy_env' => 'ote',
            'domain_godaddy_api_key' => '',
            'domain_godaddy_api_secret' => '',
            'domain_namecheap_sandbox' => '1',
            'domain_namecheap_api_user' => '',
            'domain_namecheap_api_key' => '',
            'domain_namecheap_username' => '',
            'domain_namecheap_client_ip' => '',
            'domain_hostinger_api_token' => '',
            'domain_namesilo_api_key' => '',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function toFormState(): array
    {
        $state = [];

        foreach ($this->stateKeys() as $key => $default) {
            $state[$key] = (string) $this->options->get($key, $default);
        }

        return $state;
    }

    /**
     * @param  array<string, string>  $validated
     */
    public function persist(array $validated): void
    {
        $this->options->set('domain_godaddy_env', strtolower(trim($validated['domain_godaddy_env'])) === 'production' ? 'production' : 'ote');
        $this->options->set('domain_godaddy_api_key', trim($validated['domain_godaddy_api_key']));
        $this->options->set('domain_godaddy_api_secret', trim($validated['domain_godaddy_api_secret']));
        $this->options->set('domain_namecheap_sandbox', $validated['domain_namecheap_sandbox'] === '1' ? '1' : '0');
        $this->options->set('domain_namecheap_api_user', trim($validated['domain_namecheap_api_user']));
        $this->options->set('domain_namecheap_api_key', trim($validated['domain_namecheap_api_key']));
        $this->options->set('domain_namecheap_username', trim($validated['domain_namecheap_username']));
        $this->options->set('domain_namecheap_client_ip', trim($validated['domain_namecheap_client_ip']));
        $this->options->set('domain_hostinger_api_token', trim($validated['domain_hostinger_api_token']));
        $this->options->set('domain_namesilo_api_key', trim($validated['domain_namesilo_api_key']));
    }

    public function godaddyConfigured(): bool
    {
        return WebsiteDomainRegistrarConfig::godaddyConfigured();
    }

    public function namecheapConfigured(): bool
    {
        return WebsiteDomainRegistrarConfig::namecheapConfigured();
    }

    public function hostingerConfigured(): bool
    {
        return WebsiteDomainRegistrarConfig::hostingerConfigured();
    }

    public function namesiloConfigured(): bool
    {
        return WebsiteDomainRegistrarConfig::namesiloConfigured();
    }
}