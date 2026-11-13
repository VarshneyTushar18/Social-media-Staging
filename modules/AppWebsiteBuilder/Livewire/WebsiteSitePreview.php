<?php

namespace Modules\AppWebsiteBuilder\Livewire;

use Illuminate\Contracts\View\View;
use Livewire\Attributes\Title;
use Livewire\Component;
use Modules\AppAgencyOnboarding\Support\AgencyOnboarding;
use Modules\AppWebsiteBuilder\Models\WebsiteSite;
use Modules\AppWebsiteBuilder\Support\WebsiteBuilderUi;
use Modules\AppWebsiteBuilder\Support\WebsiteContentForm;
use Modules\AppWebsiteBuilder\Support\WebsiteLandingAssets;

#[Title('Website preview')]
class WebsiteSitePreview extends Component
{
    public WebsiteSite $websiteSite;

    public bool $editMode = false;

    public string $siteName = '';

    public string $tagline = '';

    public string $brandPrimary = '#5b4dff';

    public string $heroHeadline = '';

    public string $heroSubheadline = '';

    public string $heroCtaText = '';

    public string $heroImageUrl = '';

    public string $aboutHeadline = '';

    public string $aboutBody = '';

    public string $aboutImageUrl = '';

    public string $servicesHeadline = '';

    /** @var array<int, array{title: string, description: string, image_url: string}> */
    public array $serviceItems = [];

    public string $highlightsHeadline = '';

    public string $highlightsSubheadline = '';

    /** @var array<int, array{title: string, description: string, image_url: string}> */
    public array $highlightItems = [];

    public string $contactHeadline = '';

    public string $contactImageUrl = '';

    public string $contactBody = '';

    public string $contactEmail = '';

    public string $contactPhone = '';

    public function mount(WebsiteSite $websiteSite): void
    {
        $this->guardWebsiteAgent();
        $this->guardSiteAccess($websiteSite);
        $this->websiteSite = $websiteSite;
        $this->hydrateFormFromSite();
    }

    public function setEditMode(bool $editMode): void
    {
        $this->editMode = $editMode;
        $this->hydrateFormFromSite();
    }

    public function saveContent(): void
    {
        $this->validate(WebsiteContentForm::validationRules());

        $stored = is_array($this->websiteSite->generated_content)
            ? $this->websiteSite->generated_content
            : [];

        $form = $this->editorFormState();

        $siteType = (string) ($this->websiteSite->site_type ?? $this->websiteSite->name);
        $userPrompt = (string) ($stored['user_prompt'] ?? '');
        $enriched = WebsiteLandingAssets::enrich($stored, $siteType, $userPrompt);
        $stored = WebsiteContentForm::toStoredContent($enriched, $form);
        $stored = WebsiteLandingAssets::ensureUniqueLandingImages($stored);

        $this->websiteSite->forceFill([
            'name' => (string) ($stored['site_name'] ?? $this->siteName),
            'generated_content' => $stored,
        ])->save();

        $this->websiteSite->refresh();
        $this->hydrateFormFromSite();

        WebsiteBuilderUi::toastSuccess($this, __('Website content saved.'));
    }

    public function render(): View
    {
        $this->syncWebsiteAgentContext();

        $content = $this->previewContent();

        return view('appwebsitebuilder::livewire.website-site-preview', [
            'site' => $this->websiteSite,
            'content' => $content,
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

        $content = WebsiteLandingAssets::enrich($stored, $siteType, $userPrompt);

        if ($this->editMode) {
            $content = WebsiteContentForm::toStoredContent($content, $this->editorFormState());
        }

        return $content;
    }

    /**
     * @return array<string, mixed>
     */
    protected function editorFormState(): array
    {
        return [
            'siteName' => $this->siteName,
            'tagline' => $this->tagline,
            'brandPrimary' => $this->brandPrimary,
            'heroHeadline' => $this->heroHeadline,
            'heroSubheadline' => $this->heroSubheadline,
            'heroCtaText' => $this->heroCtaText,
            'heroImageUrl' => $this->heroImageUrl,
            'aboutHeadline' => $this->aboutHeadline,
            'aboutBody' => $this->aboutBody,
            'aboutImageUrl' => $this->aboutImageUrl,
            'servicesHeadline' => $this->servicesHeadline,
            'serviceItems' => $this->serviceItems,
            'highlightsHeadline' => $this->highlightsHeadline,
            'highlightsSubheadline' => $this->highlightsSubheadline,
            'highlightItems' => $this->highlightItems,
            'contactHeadline' => $this->contactHeadline,
            'contactBody' => $this->contactBody,
            'contactImageUrl' => $this->contactImageUrl,
            'contactEmail' => $this->contactEmail,
            'contactPhone' => $this->contactPhone,
        ];
    }

    protected function hydrateFormFromSite(): void
    {
        $stored = is_array($this->websiteSite->generated_content)
            ? $this->websiteSite->generated_content
            : [];

        $enriched = WebsiteLandingAssets::enrich(
            $stored,
            (string) ($this->websiteSite->site_type ?? $this->websiteSite->name),
            (string) ($stored['user_prompt'] ?? ''),
        );

        $form = WebsiteContentForm::fromStoredContent(
            $enriched,
            (string) ($enriched['site_name'] ?? $this->websiteSite->name),
        );

        $this->siteName = $form['siteName'];
        $this->tagline = $form['tagline'];
        $this->brandPrimary = $form['brandPrimary'];
        $this->heroHeadline = $form['heroHeadline'];
        $this->heroSubheadline = $form['heroSubheadline'];
        $this->heroCtaText = $form['heroCtaText'];
        $this->heroImageUrl = $form['heroImageUrl'];
        $this->aboutHeadline = $form['aboutHeadline'];
        $this->aboutBody = $form['aboutBody'];
        $this->aboutImageUrl = $form['aboutImageUrl'];
        $this->servicesHeadline = $form['servicesHeadline'];
        $this->serviceItems = $form['serviceItems'];
        $this->highlightsHeadline = $form['highlightsHeadline'];
        $this->highlightsSubheadline = $form['highlightsSubheadline'];
        $this->highlightItems = $form['highlightItems'];
        $this->contactHeadline = $form['contactHeadline'];
        $this->contactBody = $form['contactBody'];
        $this->contactImageUrl = $form['contactImageUrl'];
        $this->contactEmail = $form['contactEmail'];
        $this->contactPhone = $form['contactPhone'];
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
