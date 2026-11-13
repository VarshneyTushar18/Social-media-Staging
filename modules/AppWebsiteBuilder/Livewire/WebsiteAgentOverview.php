<?php

namespace Modules\AppWebsiteBuilder\Livewire;

use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Schema;
use Livewire\Attributes\Title;
use Livewire\Component;
use Modules\AppAgencyOnboarding\Support\AgencyOnboarding;
use Modules\AppAgencyOnboarding\Support\AgentCatalog;
use Modules\AppWebsiteBuilder\Models\WebsiteSite;

#[Title('Website Builder')]
class WebsiteAgentOverview extends Component
{
    public function mount(): void
    {
        $onboarding = app(AgencyOnboarding::class);
        $user = auth()->user();

        abort_unless($user && $onboarding->isComplete($user), 403);
        abort_unless(in_array('website', $onboarding->enabledAgentsForProject($user), true), 403);
    }

    public function render(): View
    {
        $onboarding = app(AgencyOnboarding::class);
        $user = auth()->user();
        $project = $onboarding->activeProject($user);

        if ($project && $project->active_agent !== 'website') {
            $project->forceFill(['active_agent' => 'website'])->save();
        }

        $agent = AgentCatalog::get('website') ?? [];

        $metrics = [
            'sites' => '0',
            'published' => '0',
            'drafts' => '0',
        ];

        if (Schema::hasTable('website_sites') && $user) {
            $sitesQuery = WebsiteSite::query()->forProject($project?->id, (int) $user->id);

            $metrics = [
                'sites' => (string) (clone $sitesQuery)->count(),
                'published' => (string) (clone $sitesQuery)->where('status', WebsiteSite::STATUS_PUBLISHED)->count(),
                'drafts' => (string) (clone $sitesQuery)->where('status', WebsiteSite::STATUS_DRAFT)->count(),
            ];
        }

        return view('appwebsitebuilder::livewire.website-agent-overview', [
            'agent' => $agent,
            'project' => $project,
            'user' => $user,
            'metrics' => $metrics,
        ])->layout(theme_view('layouts.app', 'app'), [
            'shellArea' => 'user',
            'hideSidebar' => false,
            'title' => (string) ($agent['label'] ?? __('Website Builder')),
        ]);
    }
}
