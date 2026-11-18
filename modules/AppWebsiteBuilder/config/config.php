<?php

return [
    'name' => 'AppWebsiteBuilder',

    /*
    |--------------------------------------------------------------------------
    | Published sites (Vercel-style subdomains)
    |--------------------------------------------------------------------------
    |
    | When publish_domain is set (e.g. sites.yourproduct.com), live sites are
    | served at {slug}.sites.yourproduct.com — configure wildcard DNS to your app.
    |
    | When empty, sites use path URLs: {APP_URL}/sites/{slug} (handy for local dev).
    |
    */
    'publish_domain' => env('WEBSITE_PUBLISH_DOMAIN'),
    'publish_path_prefix' => env('WEBSITE_PUBLISH_PATH_PREFIX', 'sites'),
    'publish_a_record' => env('WEBSITE_PUBLISH_A_RECORD'),

    /*
    | GoDaddy, Namecheap, and Hostinger API credentials are in Admin → Settings → Domain registrars.
    */
];
