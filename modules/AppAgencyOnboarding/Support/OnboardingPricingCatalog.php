<?php

namespace Modules\AppAgencyOnboarding\Support;

class OnboardingPricingCatalog
{
    /**
     * @return array{
     *     pricing: array<int|string, mixed>,
     *     planTypes: \Illuminate\Support\Collection<int|string, mixed>,
     *     defaultType: int,
     *     plans: \Illuminate\Support\Collection<int, mixed>
     * }
     */
    public function snapshot(): array
    {
        $pricing = \Pricing::plansWithFeatures();
        $planTypes = collect(\Plan::getTypes())
            ->filter(fn ($label, $key) => ! empty($pricing[$key] ?? []));
        $plans = collect($pricing)->flatten(1)->values();

        return [
            'pricing' => $pricing,
            'planTypes' => $planTypes,
            'defaultType' => (int) ($planTypes->keys()->first() ?? 1),
            'plans' => $plans,
        ];
    }
}
