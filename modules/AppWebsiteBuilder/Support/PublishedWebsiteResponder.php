<?php

namespace Modules\AppWebsiteBuilder\Support;

use Illuminate\View\View;
use Modules\AppWebsiteBuilder\Models\WebsiteSite;

class PublishedWebsiteResponder
{
    public static function viewForSite(WebsiteSite $site): View
    {
        $raw = is_array($site->generated_content) ? $site->generated_content : [];

        $content = WebsiteLandingAssets::enrich(
            $raw,
            (string) ($site->site_type ?? $site->name),
            (string) ($raw['user_prompt'] ?? ''),
        );

        return view('appwebsitebuilder::published.page', [
            'site' => $site,
            'content' => $content,
        ]);
    }
}
