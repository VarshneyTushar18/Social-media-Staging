<?php

use Modules\AdminUser\Models\User;
use Modules\AppAgencyOnboarding\Models\AgencyProject;
use Modules\AppAgencyOnboarding\Support\AgencyOnboarding;

test('active project returns selected row when completed', function () {
    $user = User::factory()->create(['is_super_admin' => true]);
    $project = AgencyProject::factory()->completed()->create([
        'owner_user_id' => $user->id,
        'name' => 'Completed',
    ]);

    $user->forceFill(['active_agency_project_id' => $project->id])->save();

    $active = app(AgencyOnboarding::class)->activeProject($user->fresh());

    expect($active?->id)->toBe($project->id);
    expect(app(AgencyOnboarding::class)->isComplete($user->fresh()))->toBeTrue();
});

test('incomplete active falls back to latest completed when not onboarding', function () {
    $user = User::factory()->create(['is_super_admin' => true]);

    $completed = AgencyProject::factory()->completed()->create([
        'owner_user_id' => $user->id,
        'name' => 'Completed workspace',
        'setup_completed_at' => now()->subDay(),
    ]);

    $incomplete = AgencyProject::factory()->incomplete()->create([
        'owner_user_id' => $user->id,
        'name' => 'tech2globe draft',
    ]);

    $user->forceFill(['active_agency_project_id' => $incomplete->id])->save();

    $onboarding = app(AgencyOnboarding::class);

    expect($onboarding->activeProject($user->fresh())?->id)->toBe($completed->id);
    expect($onboarding->isComplete($user->fresh()))->toBeTrue();
    expect($user->fresh()->active_agency_project_id)->toBe($incomplete->id);
});

test('incomplete active kept during onboarding session for that project', function () {
    $user = User::factory()->create(['is_super_admin' => true]);

    AgencyProject::factory()->completed()->create(['owner_user_id' => $user->id]);

    $draft = AgencyProject::factory()->incomplete()->create([
        'owner_user_id' => $user->id,
        'name' => 'New project',
    ]);

    $user->forceFill(['active_agency_project_id' => $draft->id])->save();

    session([
        'agency_onboarding_project_ready' => true,
        'agency_onboarding_active_project_id' => $draft->id,
        'agency_onboarding_project_draft' => [
            'id' => $draft->id,
            'name' => $draft->name,
            'industry' => 'other',
        ],
    ]);

    $onboarding = app(AgencyOnboarding::class);

    expect($onboarding->activeProject($user->fresh())?->id)->toBe($draft->id);
    expect($onboarding->isComplete($user->fresh()))->toBeFalse();
});

test('wrong owner pointer falls back to latest completed', function () {
    $user = User::factory()->create(['is_super_admin' => true]);
    $other = User::factory()->create();

    $otherProject = AgencyProject::factory()->completed()->create(['owner_user_id' => $other->id]);

    $owned = AgencyProject::factory()->completed()->create(['owner_user_id' => $user->id]);

    $user->forceFill(['active_agency_project_id' => $otherProject->id])->save();

    expect(app(AgencyOnboarding::class)->activeProject($user->fresh())?->id)->toBe($owned->id);
});

test('only incomplete projects means onboarding incomplete', function () {
    $user = User::factory()->create(['is_super_admin' => true]);

    $incomplete = AgencyProject::factory()->incomplete()->create(['owner_user_id' => $user->id]);
    $user->forceFill(['active_agency_project_id' => $incomplete->id])->save();

    expect(app(AgencyOnboarding::class)->isComplete($user->fresh()))->toBeFalse();
});