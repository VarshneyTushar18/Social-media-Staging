<?php

use Illuminate\Support\Facades\Route;
use Modules\AppWebsiteBuilder\Http\Controllers\PublishedWebsiteController;
use Modules\AppWebsiteBuilder\Support\WebsitePublishConfig;

Route::middleware(['web'])->group(function (): void {
    $slugPattern = '[a-z0-9](?:[a-z0-9-]*[a-z0-9])?';

    $publishDomain = WebsitePublishConfig::publishDomain();

    if ($publishDomain !== '') {
        Route::domain('{subdomainSlug}.'.$publishDomain)
            ->get('/', PublishedWebsiteController::class)
            ->where('subdomainSlug', $slugPattern)
            ->name('websites.published.subdomain');
    }

    Route::get(WebsitePublishConfig::pathPrefix().'/{subdomainSlug}', PublishedWebsiteController::class)
        ->where('subdomainSlug', $slugPattern)
        ->name('websites.published.show');
});
