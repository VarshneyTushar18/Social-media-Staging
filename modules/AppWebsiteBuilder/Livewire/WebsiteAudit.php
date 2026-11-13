<?php

namespace Modules\AppWebsiteBuilder\Livewire;

use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Title;
use Livewire\Component;
use Modules\AdminSettings\Support\OptionStore;
use Modules\AppAgencyOnboarding\Support\AgencyOnboarding;
use Modules\AppWebsiteBuilder\Models\WebsiteSite;
use Modules\AppWebsiteBuilder\Support\WebsiteAuditService;
use Modules\AppWebsiteBuilder\Support\WebsitePublishConfig;
use RuntimeException;
use Throwable;

#[Title('Website audit')]
class WebsiteAudit extends Component
{
    public string $auditUrl = '';

    /** @var array<string, mixed>|null */
    public ?array $auditResult = null;

    public function mount(): void
    {
        $this->guardWebsiteAgent();
    }

    public function runWebsiteAudit(WebsiteAuditService $auditor): void
    {
        $user = auth()->user();
        abort_unless($user, 403);

        $this->auditResult = null;
        $this->resetErrorBag();

        $this->validate([
            'auditUrl' => ['required', 'string', 'max:2048'],
        ]);

        $actionKey = 'website_builder_ai_audit';
        $options = app(OptionStore::class);
        $aiEnabled = (string) $options->get('ai_content_status', '1') === '1'
            && trim((string) $options->get('ai_openai_api_key', '')) !== '';

        try {
            if ($aiEnabled && function_exists('credit_service')) {
                credit_service()->ensureCanConsume($user, $actionKey);
            }

            $ownedSite = $this->resolveBuilderSiteFromUrl($this->auditUrl, $user);

            $this->auditResult = $ownedSite
                ? $auditor->auditBuilderSite($ownedSite)
                : $auditor->auditExternalUrl($this->auditUrl);

            if ($this->auditResult['used_ai'] ?? false) {
                if (function_exists('consume_credits')) {
                    consume_credits($user, $actionKey, [
                        'feature' => 'website-builder.audit',
                        'source' => $ownedSite ? 'builder' : 'external',
                    ]);
                }
            }

            session()->flash('success', __('Website audit complete.'));
        } catch (ValidationException $exception) {
            throw $exception;
        } catch (RuntimeException $exception) {
            $this->addError('auditUrl', $exception->getMessage());
        } catch (Throwable $exception) {
            report($exception);
            $this->addError('auditUrl', __('Could not complete the audit. Try again.'));
        }
    }

    public function render(): View
    {
        $this->syncWebsiteAgentContext();

        $onboarding = app(AgencyOnboarding::class);
        $project = $onboarding->activeProject(auth()->user());

        return view('appwebsitebuilder::livewire.website-audit', [
            'project' => $project,
        ])->layout(theme_view('layouts.app', 'app'), [
            'shellArea' => 'user',
            'hideSidebar' => false,
            'title' => __('Website audit'),
        ]);
    }

    protected function resolveBuilderSiteFromUrl(string $url, $user): ?WebsiteSite
    {
        if (! Schema::hasTable('website_sites')) {
            return null;
        }

        $onboarding = app(AgencyOnboarding::class);
        $project = $onboarding->activeProject($user);
        $needle = rtrim(trim($url), '/');

        $sites = WebsiteSite::query()
            ->forProject($project?->id, (int) $user->id)
            ->whereNotNull('subdomain_slug')
            ->where('subdomain_slug', '!=', '')
            ->get();

        foreach ($sites as $site) {
            $public = WebsitePublishConfig::publicUrl((string) $site->subdomain_slug);

            if ($public !== null && rtrim($public, '/') === $needle) {
                return $site;
            }
        }

        return null;
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
}
