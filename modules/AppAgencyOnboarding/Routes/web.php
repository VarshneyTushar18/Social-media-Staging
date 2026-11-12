<?php

use Illuminate\Support\Facades\Route;
use Modules\AppAgencyOnboarding\Http\Controllers\OnboardingHomeController;
use Modules\AppAgencyOnboarding\Http\Controllers\StoreOnboardingPlanController;
use Modules\AppAgencyOnboarding\Http\Controllers\StoreOnboardingProjectController;
use Modules\AppAgencyOnboarding\Http\Controllers\StartAgencyProjectController;
use Modules\AppAgencyOnboarding\Http\Controllers\StoreOnboardingSetupController;
use Modules\AppAgencyOnboarding\Http\Controllers\SwitchAgencyProjectController;
use Modules\AppAgencyOnboarding\Livewire\OnboardingPlan;
use Modules\AppAgencyOnboarding\Livewire\OnboardingProject;
use Modules\AppAgencyOnboarding\Livewire\OnboardingSetup;

Route::middleware(['web', 'auth'])->group(function (): void {
    Route::redirect('portal/main', '/portal/dashboard')->name('portal.main');

    Route::get('portal/onboarding', OnboardingHomeController::class)->name('portal.onboarding');

    Route::prefix('portal/onboarding')->name('portal.onboarding.')->group(function (): void {
        Route::post('plan', StoreOnboardingPlanController::class)->name('plan.store');
        Route::livewire('plan', OnboardingPlan::class)->name('plan');
        Route::post('project', StoreOnboardingProjectController::class)->name('project.store');
        Route::livewire('project', OnboardingProject::class)->name('project');
        Route::post('setup', StoreOnboardingSetupController::class)->name('setup.store');
        Route::livewire('setup', OnboardingSetup::class)->name('setup');
    });

    Route::post('portal/projects/switch', SwitchAgencyProjectController::class)->name('portal.projects.switch');
    Route::get('portal/projects/new', StartAgencyProjectController::class)->name('portal.projects.create');
});
