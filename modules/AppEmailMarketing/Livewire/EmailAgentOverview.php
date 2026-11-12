<?php

namespace Modules\AppEmailMarketing\Livewire;

use Illuminate\Contracts\View\View;
use Livewire\Attributes\Title;
use Livewire\Component;
use Modules\AppAgencyOnboarding\Support\AgencyOnboarding;
use Modules\AppAgencyOnboarding\Support\AgentCatalog;

#[Title('Email Marketing')]
class EmailAgentOverview extends Component
{
    public function mount(): void
    {
        $onboarding = app(AgencyOnboarding::class);
        $user = auth()->user();

        abort_unless($user && $onboarding->isComplete($user), 403);
        abort_unless(in_array('email', $onboarding->enabledAgentsForProject($user), true), 403);
    }

    public function render(): View
    {
        $onboarding = app(AgencyOnboarding::class);
        $user = auth()->user();
        $project = $onboarding->activeProject($user);

        if ($project && $project->active_agent !== 'email') {
            $project->forceFill(['active_agent' => 'email'])->save();
        }

        $agent = AgentCatalog::get('email') ?? [];

        return view('appemailmarketing::livewire.email-agent-overview', [
            'agent' => $agent,
            'project' => $project,
            'user' => $user,
        ])->layout(theme_view('layouts.app', 'app'), [
            'shellArea' => 'user',
            'hideSidebar' => false,
            'title' => (string) ($agent['label'] ?? __('Email Marketing')),
        ]);
    }
}
