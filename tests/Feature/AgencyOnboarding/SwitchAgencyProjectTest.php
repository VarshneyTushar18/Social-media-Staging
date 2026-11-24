<?php

use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Modules\AdminUser\Models\User;
use Modules\AppAgencyOnboarding\Http\Controllers\SwitchAgencyProjectController;
use Modules\AppAgencyOnboarding\Models\AgencyProject;
use Modules\AppAgencyOnboarding\Support\AgencyOnboarding;

test('user can switch project while active selection is incomplete', function () {
    $user = User::factory()->create(['is_super_admin' => true]);

    $completed = AgencyProject::factory()->completed()->create([
        'owner_user_id' => $user->id,
        'name' => 'Completed workspace',
    ]);

    $incomplete = AgencyProject::factory()->incomplete()->create([
        'owner_user_id' => $user->id,
        'name' => 'stuck draft',
    ]);

    $user->forceFill(['active_agency_project_id' => $incomplete->id])->save();

    expect(app(AgencyOnboarding::class)->isComplete($user->fresh()))->toBeTrue();

    $request = Request::create('/portal/projects/switch', 'POST', ['project_id' => $completed->id]);
    $request->setUserResolver(fn () => $user->fresh());

    app(SwitchAgencyProjectController::class)->__invoke($request, app(AgencyOnboarding::class));

    expect($user->fresh()->active_agency_project_id)->toBe($completed->id);
});

test('cannot switch to incomplete project', function () {
    $user = User::factory()->create(['is_super_admin' => true]);

    AgencyProject::factory()->completed()->create(['owner_user_id' => $user->id]);
    $incomplete = AgencyProject::factory()->incomplete()->create(['owner_user_id' => $user->id]);

    $user->forceFill(['active_agency_project_id' => $incomplete->id])->save();

    $request = Request::create('/portal/projects/switch', 'POST', ['project_id' => $incomplete->id]);
    $request->setUserResolver(fn () => $user->fresh());

    expect(fn () => app(SwitchAgencyProjectController::class)->__invoke($request, app(AgencyOnboarding::class)))
        ->toThrow(ModelNotFoundException::class);
});

test('cannot switch to another users project', function () {
    $user = User::factory()->create(['is_super_admin' => true]);
    $other = User::factory()->create();

    AgencyProject::factory()->completed()->create(['owner_user_id' => $user->id]);
    $foreign = AgencyProject::factory()->completed()->create(['owner_user_id' => $other->id]);

    $request = Request::create('/portal/projects/switch', 'POST', ['project_id' => $foreign->id]);
    $request->setUserResolver(fn () => $user);

    expect(fn () => app(SwitchAgencyProjectController::class)->__invoke($request, app(AgencyOnboarding::class)))
        ->toThrow(ModelNotFoundException::class);
});

test('portal home redirect for admin does not send user to admin dashboard', function () {
    $user = User::factory()->create(['is_super_admin' => true]);

    AgencyProject::factory()->completed()->create(['owner_user_id' => $user->id]);

    $path = app(AgencyOnboarding::class)->portalHomeRedirectPath($user->fresh());

    expect($path)->toContain('/portal');
    if (Route::has('dashboard')) {
        expect($path)->not->toBe(route('dashboard'));
    }
});