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

    'godaddy' => [
        'env' => env('GODADDY_API_ENV', 'ote'),
        'api_key' => env('GODADDY_API_KEY'),
        'api_secret' => env('GODADDY_API_SECRET'),
        'api_token' => env('GODADDY_API_TOKEN'),
    ],

    'namecheap' => [
        'sandbox' => env('NAMECHEAP_SANDBOX', true),
        'api_user' => env('NAMECHEAP_API_USER'),
        'api_key' => env('NAMECHEAP_API_KEY'),
        'username' => env('NAMECHEAP_USERNAME'),
        'client_ip' => env('NAMECHEAP_CLIENT_IP'),
    ],
];
