<?php

namespace Modules\AdminDomainRegistrars\Livewire;

use Illuminate\Contracts\View\View;
use Livewire\Attributes\Title;
use Livewire\Component;
use Modules\AdminDomainRegistrars\Support\DomainRegistrarConfigurator;
use Modules\AdminSettings\Support\OptionStore;

#[Title('Domain registrars')]
class DomainRegistrarSettings extends Component
{
    protected OptionStore $options;

    protected DomainRegistrarConfigurator $configurator;

    public string $domain_godaddy_env = 'ote';

    public string $domain_godaddy_api_key = '';

    public string $domain_godaddy_api_secret = '';

    public string $domain_namecheap_sandbox = '1';

    public string $domain_namecheap_api_user = '';

    public string $domain_namecheap_api_key = '';

    public string $domain_namecheap_username = '';

    public string $domain_namecheap_client_ip = '';

    public string $domain_hostinger_api_token = '';

    public string $domain_namesilo_api_key = '';

    public function boot(OptionStore $options, DomainRegistrarConfigurator $configurator): void
    {
        $this->options = $options;
        $this->configurator = $configurator;
    }

    public function mount(): void
    {
        foreach ($this->configurator->stateKeys() as $key => $default) {
            if (property_exists($this, $key)) {
                $this->{$key} = (string) $this->options->get($key, $default);
            }
        }
    }

    public function save(): void
    {
        $validated = $this->validate([
            'domain_godaddy_env' => ['required', 'in:ote,production'],
            'domain_godaddy_api_key' => ['nullable', 'string', 'max:500'],
            'domain_godaddy_api_secret' => ['nullable', 'string', 'max:500'],
            'domain_namecheap_sandbox' => ['required', 'in:0,1'],
            'domain_namecheap_api_user' => ['nullable', 'string', 'max:255'],
            'domain_namecheap_api_key' => ['nullable', 'string', 'max:500'],
            'domain_namecheap_username' => ['nullable', 'string', 'max:255'],
            'domain_namecheap_client_ip' => ['nullable', 'string', 'max:45'],
            'domain_hostinger_api_token' => ['nullable', 'string', 'max:2000'],
            'domain_namesilo_api_key' => ['nullable', 'string', 'max:2000'],
        ]);

        $this->configurator->persist($validated);

        $this->dispatch('settings-saved');
    }

    public function render(): View
    {
        return view('admindomainregistrars::index', [
            'godaddyEnvOptions' => [
                ['value' => 'ote', 'label' => __('OTE')],
                ['value' => 'production', 'label' => __('Production')],
            ],
            'sandboxOptions' => [
                ['value' => '1', 'label' => __('Sandbox')],
                ['value' => '0', 'label' => __('Production')],
            ],
        ])->layout(theme_view('layouts.app', 'app'), [
            'title' => __('Domain registrars'),
        ]);
    }
}