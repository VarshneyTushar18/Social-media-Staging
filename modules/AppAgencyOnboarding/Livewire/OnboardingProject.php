<?php

namespace Modules\AppAgencyOnboarding\Livewire;

use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Modules\AppAgencyOnboarding\Support\AgencyOnboarding;

#[Layout('appagencyonboarding::layouts.portal-shell')]
#[Title('Add a project')]
class OnboardingProject extends Component
{
    public string $name = '';

    public string $website = '';

    public string $industry = 'ecommerce';

    public function mount(): void
    {
        $onboarding = app(AgencyOnboarding::class);
        $user = auth()->user();
        abort_unless($user !== null && $onboarding->hasPortalProjectOnboardingAccess($user), 403);

        if ($user && ! $user->isSuperAdmin() && ! $onboarding->hasChosenOnboardingPlan($user)) {
            $this->redirectRoute('portal.onboarding.plan', navigate: true);

            return;
        }

        $step = $onboarding->currentStep($user);

        if ($step === 'done' && ! $onboarding->isAddingAdditionalProject()) {
            $this->redirectRoute('portal.agents.social.overview', navigate: false);

            return;
        }

        $draft = $onboarding->draftProject($user);

        if ($draft && trim((string) $draft->name) !== '') {
            $this->name = (string) $draft->name;
            $this->website = (string) ($draft->website ?? '');
            $this->industry = (string) ($draft->industry ?? 'ecommerce');
        }

        if (old('name') !== null) {
            $this->name = (string) old('name', $this->name);
            $this->website = (string) old('website', $this->website);
            $this->industry = (string) old('industry', $this->industry);
        }
    }

    public function back(): mixed
    {
        $user = auth()->user();
        $onboarding = app(AgencyOnboarding::class);

        abort_unless($user !== null, 403);

        return $this->redirect($onboarding->projectStepBackUrl($user), navigate: false);
    }

    public function render(): View
    {
        $industries = config('modules.appagencyonboarding.industry_options', []);
        $user = auth()->user();
        $onboarding = app(AgencyOnboarding::class);
        $stepper = $user !== null
            ? $onboarding->stepperForPage($user, 'project')
            : ['variant' => 'full', 'current' => 2];

        return view('appagencyonboarding::livewire.onboarding-project', [
            'currentStep' => $stepper['current'],
            'stepperVariant' => $stepper['variant'],
            'projectStepBackUrl' => $user !== null ? $onboarding->projectStepBackUrl($user) : route('portal.onboarding.plan'),
            'industries' => $industries,
            'planName' => $user?->plan?->name ?? __('No plan'),
        ]);
    }
}
