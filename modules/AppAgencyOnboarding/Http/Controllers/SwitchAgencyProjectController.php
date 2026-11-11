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

        abort_unless($user !== null && $onboarding->isComplete($user), 403);

        $projectId = (int) $request->input('project_id');

        $project = AgencyProject::query()
            ->where('owner_user_id', $user->id)
            ->whereNotNull('setup_completed_at')
            ->whereKey($projectId)
            ->firstOrFail();

        if (\Illuminate\Support\Facades\Schema::hasColumn('users', 'active_agency_project_id')) {
            $user->forceFill(['active_agency_project_id' => $project->id])->save();
        }

        return redirect()->to($onboarding->postAuthRedirectPath($user->fresh()));
    }
}
