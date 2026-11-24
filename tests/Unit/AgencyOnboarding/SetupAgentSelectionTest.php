<?php

use Modules\AdminUser\Models\User;
use Modules\AppAgencyOnboarding\Models\AgencyProject;
use Modules\AppAgencyOnboarding\Support\AgencyOnboarding;
use Modules\AppAgencyOnboarding\Support\AgentCatalog;

test('setup defaults to all agents when project has no selection yet', function () {
    $project = AgencyProject::factory()->make(['enabled_agents' => null]);

    expect(app(AgencyOnboarding::class)->resolveSetupSelectedAgents($project))
        ->toBe(array_keys(AgentCatalog::all()));
});

test('enabled agents for super admin respects project selection', function () {
    $user = User::factory()->create(['is_super_admin' => true]);

    $project = AgencyProject::factory()->completed()->create([
        'owner_user_id' => $user->id,
        'enabled_agents' => ['social', 'email'],
    ]);

    $user->forceFill(['active_agency_project_id' => $project->id])->save();

    expect(app(AgencyOnboarding::class)->enabledAgentsForProject($user->fresh()))
        ->toBe(['social', 'email']);
});