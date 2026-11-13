<?php

namespace Modules\AppWebsiteBuilder\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;
use Modules\AppWebsiteBuilder\Models\WebsiteSite;
use Modules\AppWebsiteBuilder\Support\PublishedWebsiteResponder;
use Modules\AppWebsiteBuilder\Support\WebsiteSubdomain;

class PublishedWebsiteController
{
    public function __invoke(Request $request, string $subdomainSlug): View
    {
        if (! Schema::hasTable('website_sites')) {
            abort(404);
        }

        $slug = WebsiteSubdomain::normalize($subdomainSlug);

        if ($slug === '' || WebsiteSubdomain::validationError($slug) !== null) {
            abort(404);
        }

        $site = WebsiteSite::query()
            ->where('subdomain_slug', $slug)
            ->where('status', WebsiteSite::STATUS_PUBLISHED)
            ->first();

        if (! $site) {
            abort(404);
        }

        return PublishedWebsiteResponder::viewForSite($site);
    }
}
