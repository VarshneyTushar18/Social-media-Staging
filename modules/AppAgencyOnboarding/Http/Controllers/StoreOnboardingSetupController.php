<?php

namespace Modules\AppAgencyOnboarding\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\AppAgencyOnboarding\Support\AgencyOnboarding;

class StoreOnboardingSetupController extends Controller
{
    public function __invoke(Request $request, AgencyOnboarding $onboarding): RedirectResponse
    {
        $user = $request->user();

        abort_unless($user?->hasActivePlan(), 403);
        abort_unless($onboarding->hasChosenOnboardingPlan($user), 403);

        $validated = $request->validate(
            [
                'goals' => ['nullable', 'string', 'max:5000'],
                'target_audience' => ['nullable', 'string', 'max:500'],
                'monthly_budget' => ['required', 'string', 'max:120'],
                'brand_tone' => ['required', 'string', 'max:120'],
                'selected_agents' => ['required', 'array', 'min:1'],
                'selected_agents.*' => ['string', 'in:social,seo,email,website'],
            ],
            [
                'selected_agents.required' => __('Choose at least one agent.'),
                'selected_agents.min' => __('Choose at least one agent.'),
            ],
        );

        $validated['selected_agents'] = array_values(array_unique($validated['selected_agents']));

        try {
            $project = $onboarding->completeProjectSetup($user, $validated);
        } catch (\InvalidArgumentException $exception) {
            return redirect()
                ->route('portal.onboarding.setup')
                ->withInput()
                ->withErrors(['selected_agents' => $exception->getMessage()]);
        } catch (\Throwable $exception) {
            report($exception);

            return redirect()
                ->route('portal.onboarding.setup')
                ->withInput()
                ->withErrors(['selected_agents' => __('Could not launch. Try again or finish creating your project first.')]);
        }

        $request->session()->save();

        return redirect()->route(
            \Illuminate\Support\Facades\Route::has('portal.main')
                ? 'portal.main'
                : $onboarding->launchRoute($user, $project),
            $onboarding->launchRouteParameters($user, $project),
        );
    }
}
