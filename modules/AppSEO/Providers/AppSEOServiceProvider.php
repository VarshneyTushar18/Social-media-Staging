<?php

namespace Modules\AppSEO\Providers;

use Illuminate\Support\ServiceProvider;
use Modules\AppAgencyOnboarding\Support\AgencyOnboarding;

class AppSEOServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/config.php', 'modules.appseo');
    }

    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__.'/../Routes/web.php');
        $this->loadViewsFrom(__DIR__.'/../Resources/views', 'appseo');

        if (! class_exists(AgencyOnboarding::class)) {
            return;
        }

        register_user_sidebar_item('agents', [
            'label' => __('SEO'),
            'key' => 'agent.seo',
            'agent_key' => 'seo',
            'route_name' => 'portal.agents.seo.overview',
            'active_when' => ['portal.agents.seo.*'],
            'icon' => 'fa-solid fa-magnifying-glass-chart',
            'order' => 20,
            'visible' => fn (): bool => $this->projectHasSeoAgent(),
            'children' => $this->seoChildren(),
        ]);

        register_user_sidebar_item('workspace', [
            'label' => __('Dashboard'),
            'key' => 'seo.workspace.dashboard',
            'route_name' => 'portal.agents.seo.dashboard',
            'active_when' => ['portal.agents.seo.dashboard'],
            'icon' => 'fa-solid fa-chart-pie',
            'order' => 5,
            'agent_contexts' => ['seo'],
            'visible' => fn (): bool => $this->projectHasSeoAgent(),
        ]);

        register_user_sidebar_item('workspace', [
            'label' => __('Keywords'),
            'key' => 'seo.workspace.keywords',
            'route_name' => 'portal.agents.seo.keywords',
            'active_when' => ['portal.agents.seo.keywords'],
            'icon' => 'fa-solid fa-key',
            'order' => 10,
            'agent_contexts' => ['seo'],
            'visible' => fn (): bool => $this->projectHasSeoAgent(),
        ]);
    }

    protected function projectHasSeoAgent(): bool
    {
        $user = auth()->user();

        if ($user === null) {
            return false;
        }

        return in_array('seo', app(AgencyOnboarding::class)->enabledAgentsForProject($user), true);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    protected function seoChildren(): array
    {
        $onboarding = app(AgencyOnboarding::class);

        return array_map(function (array $item) use ($onboarding): array {
            $item['visible'] = fn (): bool => $onboarding->currentAgentContext() === 'seo';

            return $item;
        }, [
            [
                'label' => __('Overview'),
                'route_name' => 'portal.agents.seo.overview',
                'active_when' => ['portal.agents.seo.overview'],
                'order' => 10,
            ],
        ]);
    }
}
