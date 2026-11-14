<?php

namespace Modules\AppWebsiteBuilder\Providers;

use Illuminate\Routing\Router;
use Illuminate\Support\ServiceProvider;
use Modules\AppWebsiteBuilder\Http\Middleware\ServePublishedWebsiteByCustomDomain;
use Modules\AppAgencyOnboarding\Support\AgencyOnboarding;
use Modules\AppWebsiteBuilder\Support\WebsiteAiGenerator;
use Modules\AppWebsiteBuilder\Support\WebsiteAuditService;
use Modules\AppWebsiteBuilder\Support\WebsiteExternalPageSnapshot;
use Modules\AppWebsiteBuilder\Support\WebsiteSiteCreator;

class AppWebsiteBuilderServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/config.php', 'modules.appwebsitebuilder');
        $this->app->singleton(WebsiteAiGenerator::class);
        $this->app->singleton(WebsiteAuditService::class);
        $this->app->singleton(WebsiteExternalPageSnapshot::class);
        $this->app->singleton(WebsiteSiteCreator::class);
    }

    public function boot(): void
    {
        /** @var Router $router */
        $router = $this->app->make(Router::class);
        $router->pushMiddlewareToGroup('web', ServePublishedWebsiteByCustomDomain::class);

        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
        $this->loadRoutesFrom(__DIR__.'/../Routes/publish.php');
        $this->loadRoutesFrom(__DIR__.'/../Routes/web.php');
        $this->loadViewsFrom(__DIR__.'/../Resources/views', 'appwebsitebuilder');

        register_credit_action([
            'key' => 'website_builder_ai_generate',
            'label' => __('Website Builder AI site generation'),
            'default_cost' => 2,
            'order' => 22,
            'description' => __('Credits deducted each time AI generates a website draft.'),
        ]);

        register_credit_action([
            'key' => 'website_builder_ai_audit',
            'label' => __('Website Builder AI site audit'),
            'default_cost' => 1,
            'order' => 23,
            'description' => __('Credits deducted each time AI audits a website.'),
        ]);

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

        register_user_sidebar_item('workspace', [
            'label' => __('Dashboard'),
            'key' => 'website.workspace.dashboard',
            'route_name' => 'portal.agents.website.dashboard',
            'active_when' => ['portal.agents.website.dashboard'],
            'icon' => 'fa-solid fa-chart-pie',
            'order' => 5,
            'agent_contexts' => ['website'],
            'visible' => fn (): bool => $this->projectHasWebsiteAgent(),
        ]);

        register_user_sidebar_item('workspace', [
            'label' => __('Templates'),
            'key' => 'website.workspace.templates',
            'route_name' => 'portal.agents.website.templates',
            'active_when' => ['portal.agents.website.templates'],
            'icon' => 'fa-solid fa-layer-group',
            'order' => 7,
            'agent_contexts' => ['website'],
            'visible' => fn (): bool => $this->projectHasWebsiteAgent(),
        ]);

        register_user_sidebar_item('workspace', [
            'label' => __('Domains'),
            'key' => 'website.workspace.domains',
            'route_name' => 'portal.agents.website.domains',
            'active_when' => ['portal.agents.website.domains'],
            'icon' => 'fa-solid fa-link',
            'order' => 10,
            'agent_contexts' => ['website'],
            'visible' => fn (): bool => $this->projectHasWebsiteAgent(),
        ]);

        register_user_sidebar_item('workspace', [
            'label' => __('Website audit'),
            'key' => 'website.workspace.audit',
            'route_name' => 'portal.agents.website.audit',
            'active_when' => ['portal.agents.website.audit'],
            'icon' => 'fa-solid fa-magnifying-glass-chart',
            'order' => 12,
            'agent_contexts' => ['website'],
            'visible' => fn (): bool => $this->projectHasWebsiteAgent(),
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
        $onboarding = app(AgencyOnboarding::class);

        return array_map(function (array $item) use ($onboarding): array {
            $item['visible'] = fn (): bool => $onboarding->currentAgentContext() === 'website';

            return $item;
        }, [
            [
                'label' => __('Overview'),
                'route_name' => 'portal.agents.website.overview',
                'active_when' => ['portal.agents.website.overview'],
                'order' => 10,
            ],
        ]);
    }
}
