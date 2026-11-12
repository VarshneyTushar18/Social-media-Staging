<?php

namespace Modules\AppWebsiteBuilder\Livewire;

use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Schema;
use Livewire\Attributes\Title;
use Livewire\Component;
use Modules\AppAgencyOnboarding\Support\AgencyOnboarding;
use Modules\AppWebsiteBuilder\Support\WebsiteSiteCreator;
use Modules\AppWebsiteBuilder\Support\WebsiteTemplateGalleryCatalog;
use Modules\AppWebsiteBuilder\Support\WebsiteTemplates;
use Throwable;

#[Title('Website templates')]
class WebsiteTemplateGallery extends Component
{
    public string $searchQuery = '';

    public string $activeCategorySlug = '';

    public ?string $previewTemplateId = null;

    /** @var array<string, mixed> */
    public array $previewTemplateContent = [];

    public string $previewTemplateName = '';

    public bool $previewContentReady = false;

    public function mount(): void
    {
        $this->guardWebsiteAgent();
    }

    public function filterCategory(string $slug): void
    {
        $labels = WebsiteTemplates::galleryExampleSlugLabels();

        if (! array_key_exists($slug, $labels)) {
            return;
        }

        $this->activeCategorySlug = $slug;
        $this->searchQuery = '';
    }

    public function clearTemplateFilter(): void
    {
        $this->activeCategorySlug = '';
        $this->searchQuery = '';
    }

    public function updatedSearchQuery(): void
    {
        if (trim($this->searchQuery) !== '') {
            $this->activeCategorySlug = '';
        }
    }

    public function openTemplatePreview(string $templateId): void
    {
        $template = WebsiteTemplates::find($templateId);

        if ($template === null) {
            return;
        }

        if ($this->previewTemplateId === $templateId && $this->previewContentReady) {
            return;
        }

        $this->previewTemplateId = $templateId;
        $this->previewTemplateName = (string) ($template['name'] ?? '');
        $this->previewTemplateContent = [];
        $this->previewContentReady = false;
    }

    public function loadPreviewTemplateContent(): void
    {
        $templateId = (string) ($this->previewTemplateId ?? '');

        if ($templateId === '' || $this->previewContentReady) {
            return;
        }

        if (WebsiteTemplates::find($templateId) === null) {
            $this->closeTemplatePreview();

            return;
        }

        $type = $this->resolvedSiteTypeForTemplate($templateId);
        $this->previewTemplateContent = WebsiteTemplates::buildContent($templateId, $type);
        $this->previewContentReady = true;
    }

    public function closeTemplatePreview(): void
    {
        $this->previewTemplateId = null;
        $this->previewTemplateContent = [];
        $this->previewTemplateName = '';
        $this->previewContentReady = false;
        $this->resetErrorBag('useTemplate');
    }

    public function usePreviewedTemplate(WebsiteSiteCreator $creator): void
    {
        $type = $this->resolvedSiteType();
        $templateId = (string) ($this->previewTemplateId ?? '');

        abort_if($templateId === '', 403);

        abort_unless(WebsiteTemplates::find($templateId) !== null, 404);

        if (! Schema::hasTable('website_sites')) {
            $this->addError('useTemplate', __('Website storage is not ready. Run database migrations.'));

            return;
        }

        $this->resetErrorBag('useTemplate');

        try {
            $onboarding = app(AgencyOnboarding::class);
            $user = auth()->user();
            $project = $onboarding->activeProject($user);

            $site = $creator->createFromTemplate($user, $project, $type, $templateId);

            $this->redirectRoute('portal.agents.website.sites.preview', ['websiteSite' => $site->id], navigate: true);
        } catch (Throwable $exception) {
            $this->addError('useTemplate', $exception->getMessage());
        }
    }

    public function render(): View
    {
        $project = app(AgencyOnboarding::class)->activeProject(auth()->user());
        $templates = $this->templatesForGallery();
        $type = $this->resolvedSiteType();

        return view('appwebsitebuilder::livewire.website-template-gallery', [
            'project' => $project,
            'exampleFilters' => WebsiteTemplates::galleryExampleSlugLabels(),
            'templates' => $templates,
            'awaitingCategory' => $this->activeCategorySlug === '' && trim($this->searchQuery) === '',
            'previewSite' => (object) ['name' => $type],
        ])->layout(theme_view('layouts.app', 'app'), [
            'shellArea' => 'user',
            'hideSidebar' => false,
            'title' => __('Templates'),
        ]);
    }

    /**
     * @return list<array<string, mixed>>
     */
    protected function templatesForGallery(): array
    {
        if ($this->activeCategorySlug !== '') {
            $labels = WebsiteTemplates::galleryExampleSlugLabels();
            $label = $labels[$this->activeCategorySlug] ?? '';

            return WebsiteTemplates::galleryForCategorySlug($this->activeCategorySlug, $label);
        }

        $search = trim($this->searchQuery);

        if ($search !== '') {
            return WebsiteTemplates::galleryTextSearch($search);
        }

        return [];
    }

    protected function guardWebsiteAgent(): void
    {
        $onboarding = app(AgencyOnboarding::class);
        $user = auth()->user();

        abort_unless($user && $onboarding->isComplete($user), 403);
        abort_unless(in_array('website', $onboarding->enabledAgentsForProject($user), true), 403);
    }

    protected function resolvedSiteType(): string
    {
        if ($this->activeCategorySlug !== '') {
            $labels = WebsiteTemplates::galleryExampleSlugLabels();

            return $labels[$this->activeCategorySlug] ?? __('My website');
        }

        $search = trim($this->searchQuery);

        return $search !== '' ? $search : __('My website');
    }

    protected function resolvedSiteTypeForTemplate(string $templateId): string
    {
        if ($this->activeCategorySlug !== '') {
            $labels = WebsiteTemplates::galleryExampleSlugLabels();

            return $labels[$this->activeCategorySlug] ?? __('My website');
        }

        $search = trim($this->searchQuery);

        if ($search !== '') {
            return $search;
        }

        $categorySlug = WebsiteTemplateGalleryCatalog::slugForTemplateId($templateId);

        if ($categorySlug === null) {
            return __('My website');
        }

        $labels = WebsiteTemplates::galleryExampleSlugLabels();

        return $labels[$categorySlug] ?? __('My website');
    }
}
