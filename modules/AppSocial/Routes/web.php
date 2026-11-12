<?php

use Illuminate\Support\Facades\Route;
use Modules\AppSocial\Livewire\SocialAgentOverview;

Route::middleware(['web', 'auth'])->group(function (): void {
    Route::livewire('portal/agents/social', SocialAgentOverview::class)
        ->name('portal.agents.social.overview');
});
