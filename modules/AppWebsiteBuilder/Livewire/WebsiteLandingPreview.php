<?php

namespace Modules\AppWebsiteBuilder\Livewire;

use Illuminate\Contracts\View\View;
use Livewire\Attributes\Title;
use Livewire\Component;
use Modules\AppAgencyOnboarding\Support\AgencyOnboarding;
use Modules\AppWebsiteBuilder\Models\WebsiteSite;
use Modules\AppWebsiteBuilder\Support\WebsiteLandingAssets;

#[Title('Website preview')]
class WebsiteLandingPreview extends Component
{
    public WebsiteSite $websiteSite;

    public function mount(WebsiteSite $websiteSite): void
    {
        $this->guardWebsiteAgent();
        $this->guardSiteAccess($websiteSite);
        $this->websiteSite = $websiteSite;
    }

    public function render(): View
    {
        $raw = is_array($this->websiteSite->generated_content)
            ? $this->websiteSite->generated_content
            : [];

        $content = WebsiteLandingAssets::enrich(
            $raw,
            (string) ($this->websiteSite->site_type ?? $this->websiteSite->name),
            (string) ($raw['user_prompt'] ?? ''),
        );

        return view('appwebsitebuilder::livewire.website-landing-preview', [
            'site' => $this->websiteSite,
            'content' => $content,
        ])->layout('appwebsitebuilder::layouts.landing-preview', [
            'title' => $this->websiteSite->name,
        ]);
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
