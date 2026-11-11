<?php

namespace Modules\AppAgencyOnboarding\Livewire;

use Illuminate\Contracts\View\View;
use Livewire\Attributes\Title;
use Livewire\Component;
use Modules\AppAgencyOnboarding\Support\AgencyOnboarding;
use Modules\AppAgencyOnboarding\Support\AgentCatalog;

class AgentPlaceholder extends Component
{
    public string $agentKey;

    public function mount(?string $agent = null): void
    {
        $onboarding = app(AgencyOnboarding::class);
        $user = auth()->user();

        abort_unless($user && $onboarding->isComplete($user), 403);

        $agent = (string) ($agent ?? request()->route('agent', 'seo'));

        $definition = AgentCatalog::get($agent);

        abort_unless($definition, 404);

        $enabled = $onboarding->enabledAgentsForProject($user);

        abort_unless(in_array($agent, $enabled, true), 403);

        $this->agentKey = $agent;

        $project = $onboarding->activeProject($user);

        if ($project) {
            $project->forceFill(['active_agent' => $agent])->save();
        }
    }

    public function render(): View
    {
        $agent = AgentCatalog::get($this->agentKey) ?? [];
        $onboarding = app(AgencyOnboarding::class);

        $user = auth()->user();

        return view('appagencyonboarding::livewire.agent-placeholder', [
            'agent' => $agent,
            'project' => $onboarding->activeProject($user),
            'user' => $user,
        ])->layout(theme_view('layouts.app', 'app'), [
            'shellArea' => 'user',
            'hideSidebar' => false,
            'title' => (string) ($agent['label'] ?? __('Agent')),
        ]);
    }
}
