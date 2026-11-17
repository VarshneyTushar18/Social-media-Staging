<?php

namespace Modules\AppAgencyOnboarding\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Modules\AppAgencyOnboarding\Support\AgencyOnboarding;

class StartAgencyProjectController
{
    public function __invoke(Request $request, AgencyOnboarding $onboarding): RedirectResponse
    {
        $user = $request->user();

        abort_unless($user !== null && $onboarding->hasPortalProjectOnboardingAccess($user), 403);

        $onboarding->beginAdditionalProject($user);

        return redirect()->route('portal.onboarding.project');
    }
}
