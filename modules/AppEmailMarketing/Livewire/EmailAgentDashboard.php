<?php

namespace Modules\AppEmailMarketing\Livewire;

use Illuminate\Contracts\View\View;
use Livewire\Attributes\Title;
use Livewire\Component;
use Modules\AppAgencyOnboarding\Support\AgencyOnboarding;
use Modules\AppAgencyOnboarding\Support\AgentCatalog;

#[Title('Email Marketing dashboard')]
class EmailAgentDashboard extends Component
{
    public bool $showNewCampaignModal = false;

    public string $campaignName = '';

    public function mount(): void
    {
        $this->guardAgent();
    }

    public function openNewCampaign(): void
    {
        $this->campaignName = '';
        $this->showNewCampaignModal = true;
    }

    public function closeNewCampaign(): void
    {
        $this->showNewCampaignModal = false;
        $this->campaignName = '';
        $this->resetErrorBag();
    }

    public function createCampaign(): void
    {
        $this->validate([
            'campaignName' => ['required', 'string', 'min:2', 'max:120'],
        ]);

        $name = $this->campaignName;
        $this->closeNewCampaign();

        session()->flash('success', __('Campaign :name is queued. Sending, templates, and automations will connect here in an upcoming release.', [
            'name' => $name,
        ]));
    }

    public function render(): View
    {
        $this->syncAgentContext();

        $onboarding = app(AgencyOnboarding::class);

        return view('appemailmarketing::livewire.email-agent-dashboard', [
            'project' => $onboarding->activeProject(auth()->user()),
            'agent' => AgentCatalog::get('email') ?? [],
            'metrics' => [
                'campaigns' => '0',
                'subscribers' => '0',
                'automations' => '0',
            ],
        ])->layout(theme_view('layouts.app', 'app'), [
            'shellArea' => 'user',
            'hideSidebar' => false,
            'title' => __('Email Marketing dashboard'),
        ]);
    }

    protected function guardAgent(): void
    {
        $onboarding = app(AgencyOnboarding::class);
        $user = auth()->user();

        abort_unless($user && $onboarding->isComplete($user), 403);
        abort_unless(in_array('email', $onboarding->enabledAgentsForProject($user), true), 403);
    }

    protected function syncAgentContext(): void
    {
        $project = app(AgencyOnboarding::class)->activeProject(auth()->user());

        if ($project && $project->active_agent !== 'email') {
            $project->forceFill(['active_agent' => 'email'])->save();
        }
    }
}
