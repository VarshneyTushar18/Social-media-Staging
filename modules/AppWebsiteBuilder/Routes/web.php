<?php

use Illuminate\Support\Facades\Route;
use Modules\AppWebsiteBuilder\Http\Controllers\WebsiteTemplateCardEmbedController;
use Modules\AppWebsiteBuilder\Livewire\CreateWebsiteWizard;
use Modules\AppWebsiteBuilder\Livewire\WebsiteAgentDashboard;
use Modules\AppWebsiteBuilder\Livewire\WebsiteAgentOverview;
use Modules\AppWebsiteBuilder\Livewire\WebsiteAudit;
use Modules\AppWebsiteBuilder\Livewire\WebsiteDomains;
use Modules\AppWebsiteBuilder\Livewire\WebsiteLandingPreview;
use Modules\AppWebsiteBuilder\Livewire\WebsiteSitePreview;
use Modules\AppWebsiteBuilder\Livewire\WebsiteTemplateGallery;

Route::middleware(['web', 'auth'])->group(function (): void {
    Route::livewire('portal/agents/website', WebsiteAgentOverview::class)
        ->name('portal.agents.website.overview');
    Route::livewire('portal/agents/website/dashboard', WebsiteAgentDashboard::class)
        ->name('portal.agents.website.dashboard');
    Route::livewire('portal/agents/website/templates', WebsiteTemplateGallery::class)
        ->name('portal.agents.website.templates');
    Route::get('portal/agents/website/templates/{templateId}/card', WebsiteTemplateCardEmbedController::class)
        ->name('portal.agents.website.templates.card-preview');
    Route::livewire('portal/agents/website/sites/create', CreateWebsiteWizard::class)
        ->name('portal.agents.website.sites.create');
    Route::livewire('portal/agents/website/sites/{websiteSite}', WebsiteSitePreview::class)
        ->name('portal.agents.website.sites.preview');
    Route::livewire('portal/agents/website/sites/{websiteSite}/open', WebsiteLandingPreview::class)
        ->name('portal.agents.website.sites.open');
    Route::livewire('portal/agents/website/domains', WebsiteDomains::class)
        ->name('portal.agents.website.domains');
    Route::livewire('portal/agents/website/audit', WebsiteAudit::class)
        ->name('portal.agents.website.audit');
});
