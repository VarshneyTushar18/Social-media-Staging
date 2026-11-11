<?php

namespace Modules\AppAgencyOnboarding\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controller;
use Modules\AppAgencyOnboarding\Support\AgencyOnboarding;

class OnboardingHomeController extends Controller
{
    public function __invoke(AgencyOnboarding $onboarding): RedirectResponse
    {
        $user = auth()->user();

        abort_unless($user, 401);

        if (! $onboarding->shouldEnforce($user)) {
            return redirect()->route('dashboard');
        }

        if ($onboarding->isComplete($user)) {
            if (\Illuminate\Support\Facades\Route::has('portal.main')) {
                return redirect()->route('portal.main');
            }

            $project = $onboarding->activeProject($user);

            if ($project) {
                return redirect()->route(
                    $onboarding->launchRoute($user, $project),
                    $onboarding->launchRouteParameters($user, $project),
                );
            }

            return redirect()->route('portal.agents.social.overview');
        }

        $step = $onboarding->currentStep($user);

        return redirect()->route($onboarding->routeForStep($step));
    }
}
