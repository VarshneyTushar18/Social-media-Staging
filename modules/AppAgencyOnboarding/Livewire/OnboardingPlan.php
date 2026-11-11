<?php

namespace Modules\AppAgencyOnboarding\Livewire;

use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Modules\AppAgencyOnboarding\Support\AgencyOnboarding;
use Modules\AppAgencyOnboarding\Support\OnboardingPricingCatalog;

#[Layout('appagencyonboarding::layouts.portal-shell')]
#[Title('Choose plan')]
class OnboardingPlan extends Component
{
    public int $billingType = 1;

    public function mount(): void
    {
        $user = auth()->user();
        $onboarding = app(AgencyOnboarding::class);

        if ($user && $onboarding->hasChosenOnboardingPlan($user)) {
            $this->redirectRoute('portal.onboarding.project', navigate: false);

            return;
        }

        $catalog = app(OnboardingPricingCatalog::class);
        $this->billingType = $catalog->snapshot()['defaultType'];
    }

    public function render(): View
    {
        $catalog = app(OnboardingPricingCatalog::class);
        $snapshot = $catalog->snapshot();

        return view('appagencyonboarding::livewire.onboarding-plan', [
            'currentStep' => 1,
            'user' => auth()->user(),
            'pricing' => $snapshot['pricing'],
            'planTypes' => $snapshot['planTypes'],
            'defaultType' => $snapshot['defaultType'],
            'hasPlans' => $snapshot['plans']->isNotEmpty(),
        ]);
    }
}
