<?php

namespace Modules\AppWebsiteBuilder\Livewire;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Schema;
use Livewire\Attributes\Title;
use Livewire\Component;
use Modules\AppAgencyOnboarding\Support\AgencyOnboarding;
use Modules\AppAgencyOnboarding\Support\AgentCatalog;
use Modules\AppWebsiteBuilder\Models\WebsiteSite;
use Modules\AppWebsiteBuilder\Support\WebsiteCustomDomain;
use Modules\AppWebsiteBuilder\Support\WebsiteDomainProviders;
use Modules\AppWebsiteBuilder\Support\WebsitePublishConfig;
use Modules\AppWebsiteBuilder\Support\WebsiteSiteCard;

#[Title('Domains')]
class WebsiteDomains extends Component
{
    public function mount(): void
    {
        $onboarding = app(AgencyOnboarding::class);
        $user = auth()->user();

        abort_unless($user && $onboarding->isComplete($user), 403);
        abort_unless(in_array('website', $onboarding->enabledAgentsForProject($user), true), 403);
    }

    public function dismissQueueItem(int $siteId): void
    {
        $user = auth()->user();
        abort_unless($user, 403);

        if ((int) session('website_publish_site_id') !== $siteId) {
            return;
        }

        $site = WebsiteSite::query()
            ->where('owner_user_id', $user->id)
            ->find($siteId);

        abort_unless($site, 404);

        session()->forget([
            'website_publish_site_id',
            'website_publish_mode',
            'website_publish_queued_at',
            'website_publish_provider',
        ]);
    }

    public function unpublishSubdomain(int $siteId): void
    {
        $this->guardWebsiteAgent();

        $user = auth()->user();
        abort_unless($user, 403);

        $site = WebsiteSite::query()
            ->where('owner_user_id', $user->id)
            ->find($siteId);

        abort_unless($site, 404);
        $this->guardSiteAccess($site);

        $slug = trim((string) $site->subdomain_slug);

        if ($slug === '' || filled($site->custom_domain)) {
            return;
        }

        $siteName = (string) $site->name;

        $site->forceFill([
            'subdomain_slug' => null,
            'status' => WebsiteSite::STATUS_DRAFT,
            'published_at' => null,
        ])->save();

        $this->dispatch(
            'app-toast',
            type: 'success',
            title: __('Subdomain removed'),
            message: __(':name is no longer public at /sites/:slug. It remains as a draft on your dashboard.', [
                'name' => $siteName,
                'slug' => $slug,
            ]),
        );
    }

    public function render(): View
    {
        $onboarding = app(AgencyOnboarding::class);
        $user = auth()->user();
        $project = $onboarding->activeProject($user);

        if ($project && $project->active_agent !== 'website') {
            $project->forceFill(['active_agent' => 'website'])->save();
        }

        $agent = AgentCatalog::get('website') ?? [];
        $queueItems = $this->domainSetupQueueItems($user);
        $publishedSubdomains = $this->publishedSubdomainItems($user, $project?->id);
        $publishedCustomDomains = $this->publishedCustomDomainItems($user, $project?->id);
        $queueSummary = [
            'total' => count($queueItems),
            'pending_dns' => count($queueItems),
            'published' => count($publishedSubdomains),
        ];

        return view('appwebsitebuilder::livewire.website-domains', [
            'agent' => $agent,
            'project' => $project,
            'domainSetupQueue' => $queueItems,
            'publishedSubdomains' => $publishedSubdomains,
            'publishedCustomDomains' => $publishedCustomDomains,
            'queueSummary' => $queueSummary,
        ])->layout(theme_view('layouts.app', 'app'), [
            'shellArea' => 'user',
            'hideSidebar' => false,
            'title' => __('Domains'),
        ]);
    }

    /**
     * @return list<array<string, mixed>>
     */
    protected function publishedSubdomainItems(?Authenticatable $user, ?int $projectId): array
    {
        if (! $user || ! Schema::hasTable('website_sites')) {
            return [];
        }

        $sites = WebsiteSite::query()
            ->forProject($projectId, (int) $user->getAuthIdentifier())
            ->where('status', WebsiteSite::STATUS_PUBLISHED)
            ->whereNull('custom_domain')
            ->whereNotNull('subdomain_slug')
            ->where('subdomain_slug', '!=', '')
            ->orderByDesc('published_at')
            ->limit(50)
            ->get();

        return $sites
            ->map(function (WebsiteSite $site): array {
                $card = WebsiteSiteCard::fromSite($site);
                $slug = (string) $site->subdomain_slug;

                return [
                    'id' => (int) $site->id,
                    'name' => (string) $site->name,
                    'subdomain_slug' => $slug,
                    'public_url' => WebsitePublishConfig::publicUrl($slug),
                    'status_label' => __('Published'),
                    'published_label' => $site->published_at?->diffForHumans() ?? '',
                    'preview_url' => $card['preview_url'],
                    'preview_fallback' => $card['preview_fallback'],
                    'accent' => $card['accent'],
                ];
            })
            ->values()
            ->all();
    }

    /**
     * @return list<array<string, mixed>>
     */
    protected function publishedCustomDomainItems(?Authenticatable $user, ?int $projectId): array
    {
        if (! $user || ! Schema::hasTable('website_sites')) {
            return [];
        }

        $sites = WebsiteSite::query()
            ->forProject($projectId, (int) $user->getAuthIdentifier())
            ->where('status', WebsiteSite::STATUS_PUBLISHED)
            ->whereNotNull('custom_domain')
            ->where('custom_domain', '!=', '')
            ->orderByDesc('published_at')
            ->limit(50)
            ->get();

        return $sites
            ->map(function (WebsiteSite $site): array {
                $card = WebsiteSiteCard::fromSite($site);
                $domain = (string) $site->custom_domain;

                return [
                    'id' => (int) $site->id,
                    'name' => (string) $site->name,
                    'custom_domain' => $domain,
                    'public_url' => WebsiteCustomDomain::publicUrl($domain),
                    'status_label' => __('Published'),
                    'published_label' => $site->published_at?->diffForHumans() ?? '',
                    'preview_url' => $card['preview_url'],
                    'preview_fallback' => $card['preview_fallback'],
                    'accent' => $card['accent'],
                ];
            })
            ->values()
            ->all();
    }

    /**
     * @return list<array<string, mixed>>
     */
    protected function domainSetupQueueItems(?Authenticatable $user): array
    {
        if (! $user || ! Schema::hasTable('website_sites')) {
            return [];
        }

        $siteId = (int) (request()->query('site') ?? session('website_publish_site_id') ?? 0);
        $mode = (string) (request()->query('mode') ?? session('website_publish_mode') ?? '');
        $mode = in_array($mode, ['custom', 'subdomain'], true) ? $mode : '';

        if ($siteId <= 0 || $mode === '') {
            return [];
        }

        if ($mode === 'subdomain') {
            return [];
        }

        $site = WebsiteSite::query()
            ->where('owner_user_id', $user->getAuthIdentifier())
            ->find($siteId);

        if (! $site) {
            return [];
        }

        $card = WebsiteSiteCard::fromSite($site);
        $queuedAtRaw = session('website_publish_queued_at');
        $queuedAt = filled($queuedAtRaw)
            ? Carbon::parse($queuedAtRaw)
            : now();

        $providerKey = (string) (session('website_publish_provider') ?? '');
        $selectedProvider = $providerKey !== '' ? WebsiteDomainProviders::find($providerKey) : null;

        $detail = $mode === 'custom'
            ? ($selectedProvider
                ? __('Finish at :provider, then map DNS to this site.', ['provider' => $selectedProvider['name']])
                : __('Buy or connect a domain, then map DNS to this site.'))
            : __('Choose a subdomain slug for this site.');

        return [[
            'id' => (int) $site->id,
            'name' => (string) $site->name,
            'mode' => $mode,
            'mode_label' => $mode === 'custom' ? __('Custom domain') : __('Subdomain'),
            'status' => 'queued',
            'status_label' => __('Queued'),
            'detail' => $detail,
            'selected_provider' => $selectedProvider,
            'domain_providers' => ($mode === 'custom' && $selectedProvider === null) ? WebsiteDomainProviders::all() : [],
            'preview_url' => $card['preview_url'],
            'preview_fallback' => $card['preview_fallback'],
            'accent' => $card['accent'],
            'queued_label' => $queuedAt->diffForHumans(),
            'preview_route' => route('portal.agents.website.sites.preview', $site->id),
        ]];
    }

    protected function guardWebsiteAgent(): void
    {
        $onboarding = app(AgencyOnboarding::class);
        $user = auth()->user();

        abort_unless($user && $onboarding->isComplete($user), 403);
        abort_unless(in_array('website', $onboarding->enabledAgentsForProject($user), true), 403);
    }

    protected function guardSiteAccess(WebsiteSite $site): void
    {
        $user = auth()->user();
        abort_unless($user && (int) $site->owner_user_id === (int) $user->id, 403);

        $onboarding = app(AgencyOnboarding::class);
        $project = $onboarding->activeProject($user);
        $projectId = $project?->id;

        if ($site->agency_project_id !== null && $projectId !== null && (int) $site->agency_project_id !== (int) $projectId) {
            abort(403);
        }
    }
}
