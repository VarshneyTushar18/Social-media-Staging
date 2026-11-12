<?php

namespace Modules\AppWebsiteBuilder\Livewire;

use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Schema;
use Livewire\Attributes\Title;
use Livewire\Component;
use Modules\AppAgencyOnboarding\Support\AgencyOnboarding;
use Modules\AppWebsiteBuilder\Models\WebsiteSite;
use Modules\AppWebsiteBuilder\Support\WebsiteSiteCard;

#[Title('Website Builder dashboard')]
class WebsiteAgentDashboard extends Component
{
    public function mount(): void
    {
        $this->guardWebsiteAgent();
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
