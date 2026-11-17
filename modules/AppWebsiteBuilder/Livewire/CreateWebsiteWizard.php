<?php

namespace Modules\AppWebsiteBuilder\Livewire;

use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Livewire\Attributes\Title;
use Livewire\Component;
use Modules\AppAgencyOnboarding\Support\AgencyOnboarding;
use Modules\AdminSettings\Support\OptionStore;
use Modules\AppWebsiteBuilder\Support\WebsiteSiteCreator;
use Modules\AppWebsiteBuilder\Support\WebsiteTemplates;
use Throwable;

#[Title('Add new website')]
class CreateWebsiteWizard extends Component
{
    public int $step = 1;

    /**
     * @var ''|'ai'|'template'
     */
    public string $buildPath = '';

    public string $siteType = '';

    public bool $isGenerating = false;

    public bool $isCreatingFromTemplate = false;

    public string $userPrompt = '';

    /**
     * @return list<string>
     */
    public function exampleSiteTypes(): array
    {
        return [
            __('Cleaning Service'),
            __('Beauty Salon'),
            __('E-commerce Store'),
            __('Pet Grooming'),
            __('Local Restaurant'),
        ];
    }

    public function mount(): void
    {
        $this->guardWebsiteAgent();
    }

    public function chooseAiPath(): void
    {
        $this->choosePath('ai');
    }

    public function chooseTemplatePath(): void
    {
        $this->choosePath('template');
    }

    public function choosePath(string $path): void
    {
        abort_unless(in_array($path, ['ai', 'template'], true), 404);

        $this->buildPath = $path;
        $this->step = 2;
        $this->resetErrorBag(['generate', 'userPrompt', 'siteType']);
    }

    public function backToMethodStep(): void
    {
        $this->step = 1;
        $this->buildPath = '';
        $this->resetErrorBag(['generate', 'userPrompt', 'siteType']);
    }

    public function continueFromType(): void
    {
        abort_unless($this->buildPath === 'template', 403);

        $this->validate([
            'siteType' => ['required', 'string', 'min:2', 'max:120'],
        ]);

        if (! WebsiteTemplates::supportsTemplateCatalog(trim($this->siteType))) {
            $this->addError('siteType', __('Templates not available. Pick an example below.'));

            return;
        }

        $this->step = 3;
        $this->resetErrorBag('generate');

        $this->js(<<<'JS'
            requestAnimationFrame(() => {
                requestAnimationFrame(() => {
                    window.TemplateCardPreview?.scheduleBoot?.(true);
                });
            });
        JS);
    }

    public function backToTypeStep(): void
    {
        $this->step = 2;
        $this->resetErrorBag(['generate', 'userPrompt']);
    }

    public function updatedSiteType(): void
    {
        $this->resetErrorBag('siteType');
    }

    public function generateFromUserPrompt(WebsiteSiteCreator $creator): void
    {
        abort_unless($this->buildPath === 'ai' && $this->step === 2, 403);

        $this->validate([
            'userPrompt' => ['required', 'string', 'min:20', 'max:4000'],
        ]);

        $prompt = trim($this->userPrompt);
        $type = $this->siteTypeFromPrompt($prompt);

        $onboarding = app(AgencyOnboarding::class);
        $user = auth()->user();
        $project = $onboarding->activeProject($user);

        $this->generateWithAi($creator, $user, $project, $type, $prompt);
    }

    public function selectTemplate(string $templateId, WebsiteSiteCreator $creator): void
    {
        abort_unless($this->buildPath === 'template', 403);

        $type = trim($this->siteType);
        abort_if($type === '', 403);

        abort_unless(WebsiteTemplates::find($templateId) !== null, 404);
        abort_unless(WebsiteTemplates::isAllowedForSiteType($templateId, $type), 403);

        if (! Schema::hasTable('website_sites')) {
            $this->addError('generate', __('Website storage is not ready. Run database migrations.'));

            return;
        }

        $this->resetErrorBag('generate');
        $this->isCreatingFromTemplate = true;

        try {
            $onboarding = app(AgencyOnboarding::class);
            $user = auth()->user();
            $project = $onboarding->activeProject($user);

            $site = $creator->createFromTemplate($user, $project, $type, $templateId);

            $this->redirectRoute('portal.agents.website.sites.preview', ['websiteSite' => $site->id], navigate: true);
        } catch (Throwable $exception) {
            $this->addError('generate', $exception->getMessage());
        } finally {
            $this->isCreatingFromTemplate = false;
        }
    }

    public function render(): View
    {
        $this->syncWebsiteAgentContext();

        $project = app(AgencyOnboarding::class)->activeProject(auth()->user());

        return view('appwebsitebuilder::livewire.create-website-wizard', [
            'examples' => $this->exampleSiteTypes(),
            'projectName' => $project?->name ?? __('Project'),
            'templates' => WebsiteTemplates::catalogFor($this->siteType),
            'templateCategory' => WebsiteTemplates::resolveCategory($this->siteType),
        ])->layout(theme_view('layouts.app', 'app'), [
            'shellArea' => 'user',
            'hideSidebar' => $this->step === 3 && $this->buildPath === 'template',
            'title' => __('Add new website'),
        ]);
    }

    protected function generateWithAi(
        WebsiteSiteCreator $creator,
        $user,
        $project,
        string $type,
        string $userPrompt,
    ): void {
        $this->resetErrorBag('generate');

        if (! Schema::hasTable('website_sites')) {
            $this->addError('generate', __('Website storage is not ready. Run database migrations.'));

            return;
        }

        $options = app(OptionStore::class);
        if ((string) $options->get('ai_content_status', '1') !== '1') {
            $this->addError('generate', __('AI content generation is disabled. Ask your administrator to enable Content under AI Settings.'));

            return;
        }

        try {
            if (function_exists('credit_service')) {
                credit_service()->ensureCanConsume($user, 'website_builder_ai_generate');
            }
        } catch (Throwable $exception) {
            $this->addError('generate', $exception->getMessage());

            return;
        }

        $this->isGenerating = true;

        try {
            $site = $creator->createWithAi($user, $project, $type, [
                'user_prompt' => $userPrompt,
            ]);

            if (function_exists('consume_credits')) {
                consume_credits($user, 'website_builder_ai_generate', [
                    'feature' => 'website-builder.generate',
                    'metadata' => [
                        'site_id' => $site->id,
                        'site_type' => $type,
                        'user_prompt_chars' => strlen($userPrompt),
                    ],
                ]);
            }

            $this->redirectRoute('portal.agents.website.sites.preview', ['websiteSite' => $site->id], navigate: true);
        } catch (Throwable $exception) {
            $this->addError('generate', $exception->getMessage());
        } finally {
            $this->isGenerating = false;
        }
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

    protected function siteTypeFromPrompt(string $prompt): string
    {
        $firstLine = trim(strtok($prompt, "\n") ?: $prompt);

        if ($firstLine !== '') {
            return Str::limit($firstLine, 120, '');
        }

        return __('Custom website');
    }
}
