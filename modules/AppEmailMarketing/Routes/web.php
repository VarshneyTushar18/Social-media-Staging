<?php

use Illuminate\Support\Facades\Route;
use Modules\AppEmailMarketing\Livewire\EmailAgentDashboard;
use Modules\AppEmailMarketing\Livewire\EmailAgentOverview;
use Modules\AppEmailMarketing\Livewire\EmailAudiences;

Route::middleware(['web', 'auth'])->group(function (): void {
    Route::livewire('portal/agents/email', EmailAgentOverview::class)
        ->name('portal.agents.email.overview');
    Route::livewire('portal/agents/email/dashboard', EmailAgentDashboard::class)
        ->name('portal.agents.email.dashboard');
    Route::livewire('portal/agents/email/audiences', EmailAudiences::class)
        ->name('portal.agents.email.audiences');
});
