<?php

namespace Modules\AppAgencyOnboarding\Livewire;

use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Modules\AppAgencyOnboarding\Models\AgencyProject;
use Modules\AppAgencyOnboarding\Support\AgencyOnboarding;
use Modules\AppAgencyOnboarding\Support\AgentCatalog;

#[Layout('appagencyonboarding::layouts.portal-shell')]
#[Title('Set up project')]
class OnboardingSetup extends Component
{
    public string $goals = '';

    public string $target_audience = '';

    public string $monthly_budget = '1k_5k';

    public string $brand_tone = 'friendly';

    /** @var array<int, string> */
    public array $selectedAgents = ['social'];

    public ?AgencyProject $setupProject = null;

    public function mount(): void
    {
        $onboarding = app(AgencyOnboarding::class);
        $user = auth()->user();
        abort_unless($user?->hasActivePlan(), 403);

        if (! $onboarding->hasChosenOnboardingPlan($user)) {
            $this->redirectRoute('portal.onboarding.plan', navigate: false);

            return;
        }

        $project = $onboarding->resolveSetupProject($user);

        if ($project === null) {
            $this->redirectRoute('portal.onboarding.project', navigate: false);

            return;
        }

        $this->setupProject = $project;

        $this->goals = (string) ($project->goals ?? '');
        $this->target_audience = (string) ($project->target_audience ?? '');
        $this->monthly_budget = (string) ($project->monthly_budget ?? '1k_5k');
        $this->brand_tone = (string) ($project->brand_tone ?? 'friendly');
        $this->selectedAgents = array_values(array_filter((array) ($project->enabled_agents ?? ['social'])));

        if ($this->selectedAgents === []) {
            $this->selectedAgents = ['social'];
        }

        if (old('goals') !== null || old('target_audience') !== null || old('monthly_budget') !== null) {
            $this->goals = (string) old('goals', $this->goals);
            $this->target_audience = (string) old('target_audience', $this->target_audience);
            $this->monthly_budget = (string) old('monthly_budget', $this->monthly_budget);
            $this->brand_tone = (string) old('brand_tone', $this->brand_tone);
        }

        $oldAgents = old('selected_agents');
        if (is_array($oldAgents) && $oldAgents !== []) {
            $this->selectedAgents = array_values(array_filter($oldAgents));
        }
    }

    public function render(): View
    {
        $onboarding = app(AgencyOnboarding::class);
        $user = auth()->user();
        $project = $this->setupProject ?? $onboarding->resolveSetupProject($user);

        return view('appagencyonboarding::livewire.onboarding-setup', [
            'currentStep' => 3,
            'project' => $project,
            'agents' => AgentCatalog::all(),
            'allowedAgents' => $onboarding->agentsSelectableForSetup($user),
            'maxAgents' => $onboarding->maxSelectableAgents($user),
            'budgetOptions' => config('modules.appagencyonboarding.budget_options', []),
            'toneOptions' => config('modules.appagencyonboarding.brand_tone_options', []),
            'goals' => $this->goals,
            'target_audience' => $this->target_audience,
            'monthly_budget' => $this->monthly_budget,
            'brand_tone' => $this->brand_tone,
            'selectedAgents' => $this->selectedAgents,
        ]);
    }
}
