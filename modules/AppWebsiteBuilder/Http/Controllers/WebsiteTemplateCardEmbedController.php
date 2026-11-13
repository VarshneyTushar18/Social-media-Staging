<?php

namespace Modules\AppWebsiteBuilder\Http\Controllers;

use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;
use Modules\AppAgencyOnboarding\Support\AgencyOnboarding;
use Modules\AppWebsiteBuilder\Support\WebsiteTemplateGalleryCatalog;
use Modules\AppWebsiteBuilder\Support\WebsiteTemplates;

class WebsiteTemplateCardEmbedController
{
    public function __invoke(Request $request, string $templateId): View|Response
    {
        $this->guardWebsiteAgent();

        abort_unless(WebsiteTemplates::find($templateId) !== null, 404);

        $cardMode = $request->boolean('card', true);

        $siteType = trim((string) $request->query('type', ''));

        if ($siteType === '') {
            $slug = WebsiteTemplateGalleryCatalog::slugForTemplateId($templateId);
            $labels = WebsiteTemplates::galleryExampleSlugLabels();
            $siteType = $slug !== null && isset($labels[$slug])
                ? (string) $labels[$slug]
                : __('My website');
        }

        if ($cardMode) {
            $cacheKey = sprintf(
                'website_template_card_embed:v2:%s:%s:%s',
                $templateId,
                md5($siteType),
                app()->getLocale()
            );

            $html = Cache::remember($cacheKey, now()->addHours(12), function () use ($templateId, $siteType): string {
                return $this->renderEmbed($templateId, $siteType, true)->render();
            });

            return response($html, 200, [
                'Content-Type' => 'text/html; charset=UTF-8',
                'Cache-Control' => 'private, max-age=300',
            ]);
        }

        return $this->renderEmbed($templateId, $siteType, false);
    }

    protected function renderEmbed(string $templateId, string $siteType, bool $cardMode): View
    {
        $content = WebsiteTemplates::buildContent($templateId, $siteType);

        $site = (object) [
            'name' => (string) ($content['site_name'] ?? $siteType),
            'site_type' => $siteType,
        ];

        return view('appwebsitebuilder::embeds.template-card', [
            'site' => $site,
            'content' => $content,
            'title' => (string) ($content['site_name'] ?? __('Preview')),
            'cardMode' => $cardMode,
        ]);
    }

    protected function guardWebsiteAgent(): void
    {
        $onboarding = app(AgencyOnboarding::class);
        $user = auth()->user();

        abort_unless($user && $onboarding->isComplete($user), 403);
        abort_unless(in_array('website', $onboarding->enabledAgentsForProject($user), true), 403);
    }
}
