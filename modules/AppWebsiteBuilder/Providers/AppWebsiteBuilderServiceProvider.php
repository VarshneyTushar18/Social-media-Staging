<?php

namespace Modules\AppWebsiteBuilder\Providers;

use Illuminate\Support\ServiceProvider;
use Modules\AppAgencyOnboarding\Support\AgencyOnboarding;

class AppWebsiteBuilderServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/config.php', 'modules.appwebsitebuilder');
    }

    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__.'/../Routes/web.php');
        $this->loadViewsFrom(__DIR__.'/../Resources/views', 'appwebsitebuilder');

        if (! class_exists(AgencyOnboarding::class)) {
            return;
        }

        register_user_sidebar_item('agents', [
            'label' => __('Website Builder'),
            'key' => 'agent.website',
            'agent_key' => 'website',
            'route_name' => 'portal.agents.website.overview',
            'active_when' => ['portal.agents.website.*'],
            'icon' => 'fa-solid fa-globe',
            'order' => 40,
            'visible' => fn (): bool => $this->projectHasWebsiteAgent(),
            'children' => $this->websiteChildren(),
        ]);
    }

    protected function projectHasWebsiteAgent(): bool
    {
        $user = auth()->user();

        if ($user === null) {
            return false;
        }

        return in_array('website', app(AgencyOnboarding::class)->enabledAgentsForProject($user), true);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    protected function websiteChildren(): array
    {
        return [
            [
                'label' => __('Overview'),
                'route_name' => 'portal.agents.website.overview',
                'active_when' => ['portal.agents.website.*'],
                'order' => 10,
                'visible' => fn (): bool => app(AgencyOnboarding::class)->currentAgentContext() === 'website',
            ],
        ];
    }
}
