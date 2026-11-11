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
        $this->loadRoutesFrom(__DIR__.'/../Routes/web.php');
        $this->loadViewsFrom(__DIR__.'/../Resources/views', 'appagencyonboarding');

        register_user_sidebar_section('agents', __('Agents'), 8);

        register_user_sidebar_item('agents', [
            'label' => __('Social Media'),
            'key' => 'agent.social',
            'agent_key' => 'social',
            'route_name' => 'portal.agents.social.overview',
            'active_when' => ['portal.dashboard', 'portal.dashboard.*', 'portal.agents.social.*', 'portal.publishing.*', 'portal.channels', 'portal.channels.*'],
            'icon' => 'fa-solid fa-share-nodes',
            'order' => 10,
            'visible' => fn () => $this->projectHasAgent('social'),
            'children' => $this->socialChildren(),
        ]);

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
            if (in_array($key, ['social', 'website'], true)) {
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
                        'active_when' => (array) ($agent['sidebar_active_when'] ?? ['portal.agents.placeholder']),
                        'visible' => fn () => app(AgencyOnboarding::class)->currentAgentContext() === $key,
                    ],
                ],
            ];
        }

        return $items;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    protected function socialChildren(): array
    {
        $items = [
            [
                'label' => __('Overview'),
                'route_name' => 'portal.agents.social.overview',
                'active_when' => ['portal.agents.social.overview'],
                'order' => 10,
            ],
        ];

        if (Route::has('portal.publishing.calendar')) {
            $items[] = [
                'label' => __('Content calendar'),
                'route_name' => 'portal.publishing.calendar',
                'active_when' => ['portal.publishing.calendar'],
                'order' => 20,
            ];
        }

        if (Route::has('portal.channels')) {
            $items[] = [
                'label' => __('Accounts'),
                'route_name' => 'portal.channels',
                'active_when' => ['portal.channels', 'portal.channels.*'],
                'order' => 40,
            ];
        }

        $onboarding = app(AgencyOnboarding::class);

        return array_map(function (array $item) use ($onboarding): array {
            $item['visible'] = fn (): bool => $onboarding->currentAgentContext() === 'social';

            return $item;
        }, $items);
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
