<?php

namespace Modules\AppAgencyOnboarding\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Modules\AppAgencyOnboarding\Support\AgencyOnboarding;

class CancelAdditionalProjectController
{
    public function __invoke(Request $request, AgencyOnboarding $onboarding): RedirectResponse
    {
        $user = $request->user();

        abort_unless($user !== null && $onboarding->hasPortalProjectOnboardingAccess($user), 403);

        if ($onboarding->isAddingAdditionalProject()) {
            $onboarding->clearAddingAdditionalProject();

            session()->forget([
                'agency_onboarding_project_ready',
                'agency_onboarding_project_draft',
                'agency_onboarding_active_project_id',
            ]);
        }

        return redirect()->to($onboarding->portalHomeRedirectPath($user));
    }
}