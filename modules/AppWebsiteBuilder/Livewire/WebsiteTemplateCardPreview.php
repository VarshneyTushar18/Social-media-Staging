<?php

namespace Modules\AppWebsiteBuilder\Livewire;

use Illuminate\Contracts\View\View;
use Livewire\Component;
use Modules\AppAgencyOnboarding\Support\AgencyOnboarding;
use Modules\AppWebsiteBuilder\Support\WebsiteTemplateGalleryCatalog;
use Modules\AppWebsiteBuilder\Support\WebsiteTemplates;

class WebsiteTemplateCardPreview extends Component
{
    public string $templateId;

    public string $siteType = '';

    /** @var array<string, mixed> */
    public array $content = [];

    public function mount(string $templateId): void
    {
        $this->guardWebsiteAgent();

        abort_unless(WebsiteTemplates::find($templateId) !== null, 404);

        $this->templateId = $templateId;
        $this->siteType = trim((string) request()->query('type', ''));

        if ($this->siteType === '') {
            $slug = WebsiteTemplateGalleryCatalog::slugForTemplateId($templateId);
            $labels = WebsiteTemplates::galleryExampleSlugLabels();
            $this->siteType = $slug !== null && isset($labels[$slug])
                ? (string) $labels[$slug]
                : __('My website');
        }

        $this->content = WebsiteTemplates::buildContent($templateId, $this->siteType);
    }

    public function render(): View
    {
        $site = (object) [
            'name' => (string) ($this->content['site_name'] ?? $this->siteType),
            'site_type' => $this->siteType,
        ];

        return view('appwebsitebuilder::livewire.website-template-card-preview', [
            'site' => $site,
            'content' => $this->content,
        ])->layout('appwebsitebuilder::layouts.template-card-embed', [
            'title' => (string) ($this->content['site_name'] ?? __('Preview')),
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
