<?php

namespace Modules\AppAgencyOnboarding\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\AppAgencyOnboarding\Support\AgencyOnboarding;

class StoreOnboardingProjectController extends Controller
{
    public function __invoke(Request $request, AgencyOnboarding $onboarding): RedirectResponse
    {
        $user = $request->user();

        abort_unless($user?->hasActivePlan(), 403);
        abort_unless($onboarding->hasChosenOnboardingPlan($user), 403);

        $validated = $request->validate(
            [
                'name' => ['required', 'string', 'max:255'],
                'website' => ['nullable', 'string', 'max:500'],
                'industry' => ['required', 'string', 'max:120'],
            ],
            [],
            [
                'name' => __('project name'),
            ],
        );

        $validated['name'] = trim($validated['name']);
        $validated['website'] = isset($validated['website']) ? trim((string) $validated['website']) : null;
        $validated['website'] = $validated['website'] !== '' ? $validated['website'] : null;
        $validated['industry'] = trim($validated['industry']) !== '' ? trim($validated['industry']) : 'ecommerce';

        if ($validated['name'] === '') {
            return redirect()
                ->route('portal.onboarding.project')
                ->withInput()
                ->withErrors(['name' => __('Enter a project name before continuing.')]);
        }

        $onboarding->persistProjectFromOnboarding($user, $validated);
        $onboarding->clearAddingAdditionalProject();

        session(['agency_onboarding_project_ready' => true]);
        $request->session()->save();

        return redirect()->route('portal.onboarding.setup');
    }
}
