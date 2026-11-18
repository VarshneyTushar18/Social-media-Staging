<section class="w-full">
    <x-settings.layout :heading="__('Domain registrars')" :subheading="''">
        <form wire:submit="save" class="my-6 w-full space-y-6">
            <x-theme.section-card :title="__('GoDaddy')" body-class="space-y-5 p-6">
                <x-ui.radio-group
                    name="domain_godaddy_env"
                    wire:model.live="domain_godaddy_env"
                    :label="__('API environment')"
                    :options="$godaddyEnvOptions"
                    :value="$domain_godaddy_env"
                />

                <x-ui.input wire:model="domain_godaddy_api_key" :label="__('API key')" autocomplete="off" />
                <x-ui.input wire:model="domain_godaddy_api_secret" :label="__('API secret')" type="password" autocomplete="new-password" />
            </x-theme.section-card>

            <x-theme.section-card :title="__('Namecheap')" body-class="space-y-5 p-6">
                <x-ui.radio-group
                    name="domain_namecheap_sandbox"
                    wire:model.live="domain_namecheap_sandbox"
                    :label="__('API environment')"
                    :options="$sandboxOptions"
                    :value="$domain_namecheap_sandbox"
                />

                <x-ui.input wire:model="domain_namecheap_api_user" :label="__('API user')" autocomplete="off" />
                <x-ui.input wire:model="domain_namecheap_api_key" :label="__('API key')" type="password" autocomplete="new-password" />
                <x-ui.input wire:model="domain_namecheap_username" :label="__('Account username')" autocomplete="off" />
                <x-ui.input wire:model="domain_namecheap_client_ip" :label="__('Client IP (whitelisted)')" placeholder="203.0.113.10" autocomplete="off" />
            </x-theme.section-card>

            <x-theme.section-card :title="__('Hostinger')" body-class="space-y-5 p-6">
                <x-ui.input
                    wire:model="domain_hostinger_api_token"
                    :label="__('API token')"
                    type="password"
                    autocomplete="new-password"
                />
            </x-theme.section-card>

            <x-theme.section-card :title="__('NameSilo')" body-class="space-y-5 p-6">
                <x-ui.input
                    wire:model="domain_namesilo_api_key"
                    :label="__('API key')"
                    type="password"
                    autocomplete="new-password"
                />
            </x-theme.section-card>

            <button type="submit" class="hidden"></button>
            <div class="flex justify-end">
                <x-ui.button type="submit">
                    <i class="fa-solid fa-check mr-2 text-[11px]"></i>
                    {{ __('Save settings') }}
                </x-ui.button>
            </div>
        </form>
    </x-settings.layout>
</section>