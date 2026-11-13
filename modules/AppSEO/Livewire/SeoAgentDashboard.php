<?php

namespace Modules\AppSEO\Livewire;

use Illuminate\Contracts\View\View;
use Livewire\Attributes\Title;
use Livewire\Component;
use Modules\AppAgencyOnboarding\Support\AgencyOnboarding;
use Modules\AppAgencyOnboarding\Support\AgentCatalog;

#[Title('SEO dashboard')]
class SeoAgentDashboard extends Component
{
    public bool $showNewAuditModal = false;

    public string $auditSiteName = '';

    public function mount(): void
    {
        $this->guardAgent();
    }

    public function openNewAudit(): void
    {
        $this->auditSiteName = '';
        $this->showNewAuditModal = true;
    }

    public function closeNewAudit(): void
    {
        $this->showNewAuditModal = false;
        $this->auditSiteName = '';
        $this->resetErrorBag();
    }

    public function startAudit(): void
    {
        $this->validate([
            'auditSiteName' => ['required', 'string', 'min:2', 'max:120'],
        ]);

        $name = $this->auditSiteName;
        $this->closeNewAudit();

        session()->flash('success', __('Audit for :name is queued. Full crawls and scoring will run here in an upcoming release.', [
            'name' => $name,
        ]));
    }

    public function render(): View
    {
        $this->syncAgentContext();

        $onboarding = app(AgencyOnboarding::class);
        $agent = AgentCatalog::get('seo') ?? [];

        return view('appseo::livewire.seo-agent-dashboard', [
            'project' => $onboarding->activeProject(auth()->user()),
            'agent' => $agent,
            'metrics' => [
                'audits' => '0',
                'keywords' => '0',
                'briefs' => '0',
            ],
        ])->layout(theme_view('layouts.app', 'app'), [
            'shellArea' => 'user',
            'hideSidebar' => false,
            'title' => __('SEO dashboard'),
        ]);
    }

    protected function guardAgent(): void
    {
        $onboarding = app(AgencyOnboarding::class);
        $user = auth()->user();

        abort_unless($user && $onboarding->isComplete($user), 403);
        abort_unless(in_array('seo', $onboarding->enabledAgentsForProject($user), true), 403);
    }

    protected function syncAgentContext(): void
    {
        $project = app(AgencyOnboarding::class)->activeProject(auth()->user());

        if ($project && $project->active_agent !== 'seo') {
            $project->forceFill(['active_agent' => 'seo'])->save();
        }
    }
}
