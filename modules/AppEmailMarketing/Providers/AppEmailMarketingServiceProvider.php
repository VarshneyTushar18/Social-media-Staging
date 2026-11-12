<?php

namespace Modules\AppEmailMarketing\Providers;

use Illuminate\Support\ServiceProvider;
use Modules\AppAgencyOnboarding\Support\AgencyOnboarding;

class AppEmailMarketingServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/config.php', 'modules.appemailmarketing');
    }

    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__.'/../Routes/web.php');
        $this->loadViewsFrom(__DIR__.'/../Resources/views', 'appemailmarketing');

        if (! class_exists(AgencyOnboarding::class)) {
            return;
        }

        register_user_sidebar_item('agents', [
            'label' => __('Email Marketing'),
            'key' => 'agent.email',
            'agent_key' => 'email',
            'route_name' => 'portal.agents.email.overview',
            'active_when' => ['portal.agents.email.*'],
            'icon' => 'fa-solid fa-envelope',
            'order' => 30,
            'visible' => fn (): bool => $this->projectHasEmailAgent(),
            'children' => $this->emailChildren(),
        ]);

        register_user_sidebar_item('workspace', [
            'label' => __('Dashboard'),
            'key' => 'email.workspace.dashboard',
            'route_name' => 'portal.agents.email.dashboard',
            'active_when' => ['portal.agents.email.dashboard'],
            'icon' => 'fa-solid fa-chart-pie',
            'order' => 5,
            'agent_contexts' => ['email'],
            'visible' => fn (): bool => $this->projectHasEmailAgent(),
        ]);

        register_user_sidebar_item('workspace', [
            'label' => __('Audiences'),
            'key' => 'email.workspace.audiences',
            'route_name' => 'portal.agents.email.audiences',
            'active_when' => ['portal.agents.email.audiences'],
            'icon' => 'fa-solid fa-users',
            'order' => 10,
            'agent_contexts' => ['email'],
            'visible' => fn (): bool => $this->projectHasEmailAgent(),
        ]);
    }

    protected function projectHasEmailAgent(): bool
    {
        $user = auth()->user();

        if ($user === null) {
            return false;
        }

        return in_array('email', app(AgencyOnboarding::class)->enabledAgentsForProject($user), true);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    protected function emailChildren(): array
    {
        $onboarding = app(AgencyOnboarding::class);

        return array_map(function (array $item) use ($onboarding): array {
            $item['visible'] = fn (): bool => $onboarding->currentAgentContext() === 'email';

            return $item;
        }, [
            [
                'label' => __('Overview'),
                'route_name' => 'portal.agents.email.overview',
                'active_when' => ['portal.agents.email.overview'],
                'order' => 10,
            ],
        ]);
    }
}
