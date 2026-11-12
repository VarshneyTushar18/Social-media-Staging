<?php

namespace Modules\AppSEO\Livewire;

use Illuminate\Contracts\View\View;
use Livewire\Attributes\Title;
use Livewire\Component;
use Modules\AppAgencyOnboarding\Support\AgencyOnboarding;
use Modules\AppAgencyOnboarding\Support\AgentCatalog;

#[Title('SEO')]
class SeoAgentOverview extends Component
{
    public function mount(): void
    {
        $onboarding = app(AgencyOnboarding::class);
        $user = auth()->user();

        abort_unless($user && $onboarding->isComplete($user), 403);
        abort_unless(in_array('seo', $onboarding->enabledAgentsForProject($user), true), 403);
    }

    public function render(): View
    {
        $onboarding = app(AgencyOnboarding::class);
        $user = auth()->user();
        $project = $onboarding->activeProject($user);

        if ($project && $project->active_agent !== 'seo') {
            $project->forceFill(['active_agent' => 'seo'])->save();
        }

        $agent = AgentCatalog::get('seo') ?? [];

        return view('appseo::livewire.seo-agent-overview', [
            'agent' => $agent,
            'project' => $project,
            'user' => $user,
        ])->layout(theme_view('layouts.app', 'app'), [
            'shellArea' => 'user',
            'hideSidebar' => false,
            'title' => (string) ($agent['label'] ?? __('SEO')),
        ]);
    }
}
