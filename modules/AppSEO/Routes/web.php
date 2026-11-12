<?php

use Illuminate\Support\Facades\Route;
use Modules\AppSEO\Livewire\SeoAgentDashboard;
use Modules\AppSEO\Livewire\SeoAgentOverview;
use Modules\AppSEO\Livewire\SeoKeywords;

Route::middleware(['web', 'auth'])->group(function (): void {
    Route::livewire('portal/agents/seo', SeoAgentOverview::class)
        ->name('portal.agents.seo.overview');
    Route::livewire('portal/agents/seo/dashboard', SeoAgentDashboard::class)
        ->name('portal.agents.seo.dashboard');
    Route::livewire('portal/agents/seo/keywords', SeoKeywords::class)
        ->name('portal.agents.seo.keywords');
});
