<?php

namespace Modules\AppWebsiteBuilder\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Modules\AppWebsiteBuilder\Models\WebsiteSite;
use Modules\AppWebsiteBuilder\Support\PublishedWebsiteResponder;
use Modules\AppWebsiteBuilder\Support\WebsiteCustomDomain;
use Modules\AppWebsiteBuilder\Support\WebsitePublishConfig;
use Symfony\Component\HttpFoundation\Response;

class ServePublishedWebsiteByCustomDomain
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->isMethodSafe()) {
            return $next($request);
        }

        $host = strtolower($request->getHost());
        $appHost = strtolower((string) (parse_url((string) config('app.url'), PHP_URL_HOST) ?: ''));

        if ($host === $appHost || $host === '') {
            return $next($request);
        }

        $publishDomain = WebsitePublishConfig::publishDomain();

        if ($publishDomain !== '') {
            if ($host === $publishDomain || str_ends_with($host, '.'.$publishDomain)) {
                return $next($request);
            }
        }

        $path = trim($request->path(), '/');

        foreach (['livewire', 'portal', 'admin', 'api', 'install', WebsitePublishConfig::pathPrefix()] as $prefix) {
            if ($path === $prefix || str_starts_with($path, $prefix.'/')) {
                return $next($request);
            }
        }

        if (! Schema::hasTable('website_sites')) {
            return $next($request);
        }

        $apex = WebsiteCustomDomain::normalize($host);
        $withWww = 'www.'.$apex;

        $site = WebsiteSite::query()
            ->where('status', WebsiteSite::STATUS_PUBLISHED)
            ->whereNotNull('custom_domain')
            ->where(function ($query) use ($apex, $withWww, $host): void {
                $query->where('custom_domain', $apex)
                    ->orWhere('custom_domain', $withWww)
                    ->orWhere('custom_domain', $host);
            })
            ->first();

        if (! $site) {
            return $next($request);
        }

        return PublishedWebsiteResponder::viewForSite($site);
    }
}
