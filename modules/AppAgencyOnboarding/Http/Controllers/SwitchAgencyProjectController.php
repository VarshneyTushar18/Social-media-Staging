<?php

namespace Modules\AppAgencyOnboarding\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Modules\AppAgencyOnboarding\Models\AgencyProject;
use Modules\AppAgencyOnboarding\Support\AgencyOnboarding;

class SwitchAgencyProjectController
{
    public function __invoke(Request $request, AgencyOnboarding $onboarding): RedirectResponse
    {
        $user = $request->user();

        abort_unless($user !== null && $onboarding->canSwitchBetweenProjects($user), 403);

        $projectId = (int) $request->input('project_id');

        $project = AgencyProject::query()
            ->where('owner_user_id', $user->id)
            ->whereNotNull('setup_completed_at')
            ->whereKey($projectId)
            ->firstOrFail();

        $user->forceFill(['active_agency_project_id' => $project->id])->save();

        session()->forget([
            'agency_onboarding_project_ready',
            'agency_onboarding_project_draft',
            'agency_onboarding_active_project_id',
        ]);

        $user = $user->fresh();

        $previous = (string) url()->previous();
        $previousPath = (string) parse_url($previous, PHP_URL_PATH);

        if ($previous !== '' && str_contains($previousPath, '/portal')) {
            return redirect()->to($previous);
        }

        return redirect()->to($onboarding->portalHomeRedirectPath($user));
    }
}
