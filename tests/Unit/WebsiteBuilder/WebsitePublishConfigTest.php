<?php

use Illuminate\Support\Facades\Config;
use Modules\AppWebsiteBuilder\Support\WebsitePublishConfig;

test('local app url uses path publishing even when publish domain is configured', function () {
    Config::set('app.url', 'http://127.0.0.1:8000');
    Config::set('modules.appwebsitebuilder.publish_domain', 'sites.example.com');

    expect(WebsitePublishConfig::usesPathUrls())->toBeTrue();
    expect(WebsitePublishConfig::publishDomain())->toBe('');
    expect(WebsitePublishConfig::publicUrl('pet-grooming'))
        ->toBe('http://127.0.0.1:8000/sites/pet-grooming');
});