<?php

namespace Modules\AppAgencyOnboarding\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Modules\AppAgencyOnboarding\Support\AgencyOnboarding;
use Modules\AppAgencyOnboarding\Support\AgentCatalog;

class AppAgencyOnboardingServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/config.php', 'modules.appagencyonboarding');
        $this->app->singleton(AgencyOnboarding::class);
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
        $this->loadRoutesFrom(__DIR__.'/../Routes/web.php');
        $this->loadViewsFrom(__DIR__.'/../Resources/views', 'appagencyonboarding');

        register_user_sidebar_section('agents', __('Agents'), 8);

        foreach ($this->enabledAgentSidebarItems() as $item) {
            register_user_sidebar_item('agents', $item);
        }

        $this->registerPlanPermissions();
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    protected function enabledAgentSidebarItems(): array
    {
        $items = [];

        foreach (AgentCatalog::all() as $key => $agent) {
            if (in_array($key, ['social', 'website', 'seo', 'email'], true)) {
                continue;
            }

            $params = (array) ($agent['route_params'] ?? []);
            $routeName = (string) $agent['route'];

            $items[] = [
                'key' => 'agent.'.$key,
                'agent_key' => $key,
                'label' => (string) $agent['label'],
                'route_name' => $routeName,
                'route_params' => $params,
                'route' => Route::has($routeName) ? route($routeName, $params) : url('/portal/agents/'.$key),
                'active_when' => (array) ($agent['sidebar_active_when'] ?? ['portal.agents.placeholder']),
                'icon' => match ($key) {
                    'seo' => 'fa-solid fa-magnifying-glass-chart',
                    'email' => 'fa-solid fa-envelope',
                    'website' => 'fa-solid fa-globe',
                    default => 'fa-solid fa-robot',
                },
                'order' => match ($key) {
                    'seo' => 20,
                    'email' => 30,
                    'website' => 40,
                    default => 50,
                },
                'visible' => fn () => $this->projectHasAgent($key),
                'children' => [
                    [
                        'label' => __('Overview'),
                        'route_name' => $routeName,
                        'route_params' => $params,
                        'agent_key' => $key,
                        'order' => 10,
                        'active_when' => match ($key) {
                            'seo' => ['portal.agents.seo.overview'],
                            'email' => ['portal.agents.email.overview'],
                            default => ['portal.agents.placeholder'],
                        },
                        'visible' => fn () => app(AgencyOnboarding::class)->currentAgentContext() === $key,
                    ],
                ],
            ];
        }

        return $items;
    }

    protected function projectHasAgent(string $agentKey): bool
    {
        $user = auth()->user();

        if (! $user || ! app(AgencyOnboarding::class)->isComplete($user)) {
            return false;
        }

        $project = app(AgencyOnboarding::class)->activeProject($user);

        if (! $project) {
            return $agentKey === 'social';
        }

        $enabled = app(AgencyOnboarding::class)->enabledAgentsForProject($user);

        return in_array($agentKey, $enabled, true);
    }

    protected function registerPlanPermissions(): void
    {
        register_plan_permission([
            'key' => 'agency_workspace',
            'label' => __('Agency workspace'),
            'type' => 'config',
            'toggleable' => false,
            'order' => 5,
            'fields' => [
                [
                    'key' => 'max_agents_per_project',
                    'label' => __('Max agents per project'),
                    'type' => 'number',
                    'default' => 4,
                    'description' => __('How many agents a project can enable during setup.'),
                ],
            ],
        ]);

        foreach ([
            ['key' => 'agent_social', 'label' => __('Social Media agent'), 'order' => 6],
            ['key' => 'agent_seo', 'label' => __('SEO agent'), 'order' => 7],
            ['key' => 'agent_email', 'label' => __('Email Marketing agent'), 'order' => 8],
            ['key' => 'agent_website', 'label' => __('Website Builder agent'), 'order' => 9],
        ] as $permission) {
            register_plan_permission([
                'key' => $permission['key'],
                'label' => $permission['label'],
                'type' => 'toggle',
                'default' => $permission['key'] === 'agent_social',
                'order' => $permission['order'],
            ]);
        }
    }
}
