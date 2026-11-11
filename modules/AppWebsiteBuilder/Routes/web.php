<?php

use Illuminate\Support\Facades\Route;
use Modules\AppWebsiteBuilder\Livewire\WebsiteAgentOverview;

Route::middleware(['web', 'auth'])->group(function (): void {
    Route::livewire('portal/agents/website', WebsiteAgentOverview::class)
        ->name('portal.agents.website.overview');
});
