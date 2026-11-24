<?php

use Illuminate\Http\Request;
use Modules\AdminUser\Models\User;
use Modules\AppAgencyOnboarding\Http\Controllers\StoreOnboardingProjectController;
use Modules\AppAgencyOnboarding\Models\AgencyProject;
use Modules\AppAgencyOnboarding\Support\AgencyOnboarding;

test('create project requires project name', function () {
    $user = User::factory()->create(['is_super_admin' => true]);

    AgencyProject::factory()->completed()->create([
        'owner_user_id' => $user->id,
    ]);

    session(['agency_onboarding_adding_project' => true]);

    $request = Request::create('/portal/onboarding/project', 'POST', [
        'name' => '   ',
        'website' => '',
        'industry' => 'ecommerce',
    ]);
    $request->setUserResolver(fn () => $user);

    try {
        app(StoreOnboardingProjectController::class)->__invoke($request, app(AgencyOnboarding::class));
        expect(false)->toBeTrue('Expected validation exception');
    } catch (\Illuminate\Validation\ValidationException $exception) {
        expect($exception->errors()['name'][0])->toBe(__('This field is required.'));
    }
});