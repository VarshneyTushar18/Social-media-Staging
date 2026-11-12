<?php

namespace Modules\AppSocial\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Modules\AppAgencyOnboarding\Support\AgencyOnboarding;

class AppSocialServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/config.php', 'modules.appsocial');
    }

    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__.'/../Routes/web.php');
        $this->loadViewsFrom(__DIR__.'/../Resources/views', 'appsocial');

        if (! class_exists(AgencyOnboarding::class)) {
            return;
        }

        register_user_sidebar_item('agents', [
            'label' => __('Social Media'),
            'key' => 'agent.social',
            'agent_key' => 'social',
            'route_name' => 'portal.agents.social.overview',
            'active_when' => ['portal.dashboard', 'portal.dashboard.*', 'portal.agents.social.*', 'portal.publishing.*', 'portal.channels', 'portal.channels.*'],
            'icon' => 'fa-solid fa-share-nodes',
            'order' => 10,
            'visible' => fn (): bool => $this->projectHasSocialAgent(),
            'children' => $this->socialChildren(),
        ]);
    }

    protected function projectHasSocialAgent(): bool
    {
        $user = auth()->user();

        if (! $user || ! app(AgencyOnboarding::class)->isComplete($user)) {
            return false;
        }

        $project = app(AgencyOnboarding::class)->activeProject($user);

        if (! $project) {
            return true;
        }

        $enabled = app(AgencyOnboarding::class)->enabledAgentsForProject($user);

        return in_array('social', $enabled, true);
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
}
