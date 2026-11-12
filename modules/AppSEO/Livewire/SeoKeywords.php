<?php

namespace Modules\AppSEO\Livewire;

use Illuminate\Contracts\View\View;
use Livewire\Attributes\Title;
use Livewire\Component;
use Modules\AppAgencyOnboarding\Support\AgencyOnboarding;
use Modules\AppAgencyOnboarding\Support\AgentCatalog;

#[Title('Keywords')]
class SeoKeywords extends Component
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
        $project = $onboarding->activeProject(auth()->user());

        if ($project && $project->active_agent !== 'seo') {
            $project->forceFill(['active_agent' => 'seo'])->save();
        }

        return view('appseo::livewire.seo-keywords', [
            'project' => $project,
            'agent' => AgentCatalog::get('seo') ?? [],
        ])->layout(theme_view('layouts.app', 'app'), [
            'shellArea' => 'user',
            'hideSidebar' => false,
            'title' => __('Keywords'),
        ]);
    }
}
