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

        abort_unless($user !== null && $onboarding->hasPortalProjectOnboardingAccess($user), 403);

        $request->merge([
            'name' => trim((string) $request->input('name', '')),
            'website' => trim((string) $request->input('website', '')),
            'industry' => trim((string) $request->input('industry', '')),
        ]);

        $requiredMessage = __('This field is required.');

        $validated = $request->validate(
            [
                'name' => ['required', 'string', 'max:255'],
                'website' => ['nullable', 'string', 'max:500'],
                'industry' => ['required', 'string', 'max:120'],
            ],
            [
                'name.required' => $requiredMessage,
                'industry.required' => $requiredMessage,
            ],
            [
                'name' => __('project name'),
                'industry' => __('industry'),
            ],
        );

        $validated['website'] = $validated['website'] !== '' ? $validated['website'] : null;
        $validated['industry'] = $validated['industry'] !== '' ? $validated['industry'] : 'ecommerce';

        $onboarding->persistProjectFromOnboarding($user, $validated);
        $onboarding->clearAddingAdditionalProject();

        session(['agency_onboarding_project_ready' => true]);
        $request->session()->save();

        return redirect()->route('portal.onboarding.setup');
    }
}
