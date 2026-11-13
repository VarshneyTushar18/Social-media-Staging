<?php

namespace Modules\AppWebsiteBuilder\Livewire;

use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Title;
use Livewire\Component;
use Modules\AppAgencyOnboarding\Support\AgencyOnboarding;
use Modules\AppWebsiteBuilder\Models\WebsiteSite;
use Modules\AppWebsiteBuilder\Support\WebsiteDomainProviders;
use Modules\AppWebsiteBuilder\Support\WebsitePublishConfig;
use Modules\AppWebsiteBuilder\Support\WebsiteSiteCard;
use Modules\AppWebsiteBuilder\Support\WebsiteSubdomain;

#[Title('Website Builder dashboard')]
class WebsiteAgentDashboard extends Component
{
    public function mount(): void
    {
        $this->guardWebsiteAgent();
    }

    public function continuePublish(int $siteId, string $mode, ?string $providerKey = null): void
    {
        $this->guardWebsiteAgent();

        abort_unless(in_array($mode, ['custom', 'subdomain'], true), 422);

        $site = WebsiteSite::query()->find($siteId);
        abort_unless($site, 404);
        $this->guardSiteAccess($site);

        $providerKey = strtolower(trim((string) $providerKey));

        if ($mode === 'custom') {
            abort_unless(WebsiteDomainProviders::find($providerKey) !== null, 422);
        } else {
            $providerKey = '';
        }

        session([
            'website_publish_site_id' => $siteId,
            'website_publish_mode' => $mode,
            'website_publish_queued_at' => now()->toIso8601String(),
            'website_publish_provider' => $providerKey !== '' ? $providerKey : null,
        ]);

        $this->redirectRoute('portal.agents.website.domains', [
            'site' => $siteId,
            'mode' => $mode,
        ], navigate: true);
    }

    public function publishSubdomain(int $siteId, string $slug): void
    {
        $this->guardWebsiteAgent();

        $site = WebsiteSite::query()->find($siteId);
        abort_unless($site, 404);
        $this->guardSiteAccess($site);

        $normalized = WebsiteSubdomain::normalize($slug);
        $validationMessage = WebsiteSubdomain::validationError($normalized);

        if ($validationMessage !== null) {
            throw ValidationException::withMessages([
                'subdomain_slug' => $validationMessage,
            ]);
        }

        $taken = WebsiteSite::query()
            ->where('subdomain_slug', $normalized)
            ->whereKeyNot($site->id)
            ->exists();

        if ($taken) {
            throw ValidationException::withMessages([
                'subdomain_slug' => __('This subdomain is already taken.'),
            ]);
        }

        $site->forceFill([
            'subdomain_slug' => $normalized,
            'status' => WebsiteSite::STATUS_PUBLISHED,
            'published_at' => now(),
        ])->save();

        session()->forget([
            'website_publish_site_id',
            'website_publish_mode',
            'website_publish_queued_at',
            'website_publish_provider',
        ]);

        $publicUrl = WebsitePublishConfig::publicUrl($normalized);

        $this->dispatch(
            'app-toast',
            type: 'success',
            title: __('Site published'),
            message: $publicUrl
                ? __('Your site is live at :url', ['url' => $publicUrl])
                : __('Your site is now published.'),
        );
    }

    public function deleteSite(int $siteId): void
    {
        $this->guardWebsiteAgent();

        $user = auth()->user();
        abort_unless($user, 403);

        $site = WebsiteSite::query()->find($siteId);
        abort_unless($site, 404);
        $this->guardSiteAccess($site);

        $site->delete();
    }

    public function render(): View
    {
        $this->syncWebsiteAgentContext();

        $onboarding = app(AgencyOnboarding::class);
        $user = auth()->user();
        $project = $onboarding->activeProject($user);

        $sites = collect();

        if (Schema::hasTable('website_sites') && $user) {
            $sites = WebsiteSite::query()
                ->forProject($project?->id, (int) $user->id)
                ->orderByDesc('created_at')
                ->limit(20)
                ->get();
        }

        return view('appwebsitebuilder::livewire.website-agent-dashboard', [
            'project' => $project,
            'sites' => $sites,
            'siteCards' => WebsiteSiteCard::fromCollection($sites),
            'domainProviders' => WebsiteDomainProviders::all(),
            'publishUrlPattern' => WebsitePublishConfig::urlPattern(),
            'publishDomain' => WebsitePublishConfig::publishDomain(),
        ])->layout(theme_view('layouts.app', 'app'), [
            'shellArea' => 'user',
            'hideSidebar' => false,
            'title' => __('Website Builder dashboard'),
        ]);
    }

    protected function guardWebsiteAgent(): void
    {
        $onboarding = app(AgencyOnboarding::class);
        $user = auth()->user();

        abort_unless($user && $onboarding->isComplete($user), 403);
        abort_unless(in_array('website', $onboarding->enabledAgentsForProject($user), true), 403);
    }

    protected function syncWebsiteAgentContext(): void
    {
        $onboarding = app(AgencyOnboarding::class);
        $project = $onboarding->activeProject(auth()->user());

        if ($project && $project->active_agent !== 'website') {
            $project->forceFill(['active_agent' => 'website'])->save();
        }
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
