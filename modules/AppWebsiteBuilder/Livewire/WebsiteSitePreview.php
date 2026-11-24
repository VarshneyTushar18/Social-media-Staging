<?php

namespace Modules\AppWebsiteBuilder\Livewire;

use Illuminate\Contracts\View\View;
use Livewire\Attributes\Title;
use Livewire\Component;
use Modules\AppAgencyOnboarding\Support\AgencyOnboarding;
use Modules\AppWebsiteBuilder\Models\WebsiteSite;
use Modules\AppWebsiteBuilder\Support\WebsiteLandingAssets;
use Modules\AppWebsiteBuilder\Support\WebsiteLandingHtmlExporter;

#[Title('Website preview')]
class WebsiteSitePreview extends Component
{
    public WebsiteSite $websiteSite;

    public bool $codeMode = false;

    public function mount(WebsiteSite $websiteSite): void
    {
        $this->guardWebsiteAgent();
        $this->guardSiteAccess($websiteSite);
        $this->websiteSite = $websiteSite;
    }

    public function setCodeMode(bool $codeMode = true): void
    {
        $this->codeMode = $codeMode;
    }

    public function render(): View
    {
        $this->syncWebsiteAgentContext();

        $content = $this->previewContent();

        $exportHtml = WebsiteLandingHtmlExporter::forSite($this->websiteSite, $content);

        return view('appwebsitebuilder::livewire.website-site-preview', [
            'site' => $this->websiteSite,
            'content' => $content,
            'exportHtml' => $exportHtml,
        ])->layout(theme_view('layouts.app', 'app'), [
            'shellArea' => 'user',
            'hideSidebar' => true,
            'title' => $this->websiteSite->name,
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    protected function previewContent(): array
    {
        $stored = is_array($this->websiteSite->generated_content)
            ? $this->websiteSite->generated_content
            : [];

        $siteType = (string) ($this->websiteSite->site_type ?? $this->websiteSite->name);
        $userPrompt = (string) ($stored['user_prompt'] ?? '');

        return WebsiteLandingAssets::enrich($stored, $siteType, $userPrompt);
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

    protected function syncWebsiteAgentContext(): void
    {
        $onboarding = app(AgencyOnboarding::class);
        $project = $onboarding->activeProject(auth()->user());

        if ($project && $project->active_agent !== 'website') {
            $project->forceFill(['active_agent' => 'website'])->save();
        }
    }
}