<?php

namespace Modules\AdminDomainRegistrars\Providers;

use Illuminate\Support\ServiceProvider;
use Modules\AdminDomainRegistrars\Support\DomainRegistrarConfigurator;

class AdminDomainRegistrarsServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/config.php', 'modules.admindomainregistrars');
        $this->app->singleton(DomainRegistrarConfigurator::class);
    }

    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__.'/../Routes/web.php');
        $this->loadViewsFrom(__DIR__.'/../Resources/views', 'admindomainregistrars');

        register_setting_item('general', [
            'label' => 'Domain registrars',
            'description' => 'GoDaddy, Namecheap, and Hostinger API credentials for Website Builder domain search.',
            'route_name' => 'settings.domain-registrars',
            'active_when' => ['settings.domain-registrars'],
            'order' => 16,
        ]);
    }
}