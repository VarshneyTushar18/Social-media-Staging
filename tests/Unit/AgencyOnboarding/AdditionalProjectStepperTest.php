<?php

use Modules\AdminUser\Models\User;
use Modules\AppAgencyOnboarding\Support\AgencyOnboarding;

test('additional project flow uses two-step stepper', function () {
    $user = User::factory()->create();
    $onboarding = app(AgencyOnboarding::class);

    session(['agency_onboarding_adding_project' => true]);

    expect($onboarding->stepperForPage($user, 'project'))->toBe([
        'variant' => 'additional',
        'current' => 1,
    ]);

    expect($onboarding->stepperForPage($user, 'setup'))->toBe([
        'variant' => 'additional',
        'current' => 2,
    ]);
});

test('first-time onboarding uses three-step stepper', function () {
    $user = User::factory()->create();
    $onboarding = app(AgencyOnboarding::class);

    session()->forget('agency_onboarding_adding_project');

    expect($onboarding->stepperForPage($user, 'plan')['variant'])->toBe('full');
    expect($onboarding->stepperForPage($user, 'project')['current'])->toBe(2);
    expect($onboarding->stepperForPage($user, 'setup')['current'])->toBe(3);
});

test('project back url returns portal home when adding another project', function () {
    $user = User::factory()->create();
    $onboarding = app(AgencyOnboarding::class);

    session(['agency_onboarding_adding_project' => true]);

    expect($onboarding->projectStepBackUrl($user))->toBe(route('portal.projects.create.cancel'));
});