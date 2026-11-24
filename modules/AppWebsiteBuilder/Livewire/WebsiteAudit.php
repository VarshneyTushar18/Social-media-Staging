<?php

namespace Modules\AppWebsiteBuilder\Livewire;

use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Title;
use Livewire\Component;
use Modules\AdminSettings\Support\OptionStore;
use Modules\AppAgencyOnboarding\Support\AgencyOnboarding;
use Modules\AppWebsiteBuilder\Models\WebsiteAuditRun;
use Modules\AppWebsiteBuilder\Models\WebsiteSite;
use Modules\AppWebsiteBuilder\Support\WebsiteAuditService;
use Modules\AppWebsiteBuilder\Support\WebsiteOverviewMetrics;
use Modules\AppWebsiteBuilder\Support\WebsitePublishConfig;
use RuntimeException;
use Throwable;

#[Title('Website audit')]
class WebsiteAudit extends Component
{
    public string $auditUrl = '';

    /** @var array<string, mixed>|null */
    public ?array $auditResult = null;

    public ?int $auditRunId = null;

    public ?string $auditReportGeneratedAt = null;

    public bool $deleteAuditModalOpen = false;

    public ?int $deleteAuditRunId = null;

    public string $deleteAuditLabel = '';

    public function mount(): void
    {
        $this->guardWebsiteAgent();

        $runId = (int) request()->query('run', 0);

        if ($runId > 0) {
            $run = $this->findAuditRunForUser($runId);

            if ($run !== null) {
                $this->hydrateFromAuditRun($run, scroll: false);
            }
        }
    }

    public function loadAuditRun(int $runId): void
    {
        $this->guardWebsiteAgent();

        $run = $this->findAuditRunForUser($runId);
        abort_unless($run, 404);

        $this->hydrateFromAuditRun($run, scroll: true);
    }

    public function openDeleteAuditModal(int $runId): void
    {
        $this->guardWebsiteAgent();

        $run = $this->findAuditRunForUser($runId);
        abort_unless($run, 404);

        $result = is_array($run->result) ? $run->result : [];
        $this->deleteAuditRunId = (int) $run->id;
        $this->deleteAuditLabel = (string) ($result['target_label'] ?? __('This audit report'));
        $this->deleteAuditModalOpen = true;
    }

    public function closeDeleteAuditModal(): void
    {
        $this->deleteAuditModalOpen = false;
        $this->deleteAuditRunId = null;
        $this->deleteAuditLabel = '';
    }

    public function closeAuditReport(): void
    {
        $this->auditResult = null;
        $this->auditRunId = null;
        $this->auditReportGeneratedAt = null;
        $this->js('const u = new URL(window.location.href); if (u.searchParams.has("run")) { u.searchParams.delete("run"); window.history.replaceState({}, "", u); }');
    }

    public function deleteAuditRun(): void
    {
        $this->guardWebsiteAgent();

        $runId = (int) $this->deleteAuditRunId;
        abort_unless($runId > 0, 422);

        $run = $this->findAuditRunForUser($runId);
        abort_unless($run, 404);

        $run->delete();

        if ((int) $this->auditRunId === $runId) {
            $this->auditResult = null;
            $this->auditRunId = null;
            $this->auditReportGeneratedAt = null;
        }

        $this->closeDeleteAuditModal();

        session()->flash('success', __('Audit report removed.'));
    }

    protected function findAuditRunForUser(int $runId): ?WebsiteAuditRun
    {
        $user = auth()->user();

        if ($user === null || ! Schema::hasTable('website_audit_runs')) {
            return null;
        }

        $onboarding = app(AgencyOnboarding::class);
        $project = $onboarding->activeProject($user);

        return WebsiteAuditRun::query()
            ->where('owner_user_id', (int) $user->id)
            ->when(
                $project !== null,
                fn ($query) => $query->where('agency_project_id', $project->id),
                fn ($query) => $query->whereNull('agency_project_id'),
            )
            ->find($runId);
    }

    protected function hydrateFromAuditRun(WebsiteAuditRun $run, bool $scroll = false): void
    {
        $result = $run->result;

        if (! is_array($result)) {
            $this->auditResult = null;
            $this->auditRunId = null;
            $this->auditReportGeneratedAt = null;

            return;
        }

        $this->auditResult = $result;
        $this->auditRunId = (int) $run->id;
        $this->auditReportGeneratedAt = $run->created_at?->format('M d, Y H:i');

        if ($scroll) {
            $this->js('document.getElementById("website-audit-report")?.scrollIntoView({ behavior: "smooth", block: "start" })');
        }
    }

    public function runWebsiteAudit(WebsiteAuditService $auditor): void
    {
        $user = auth()->user();
        abort_unless($user, 403);

        $this->auditResult = null;
        $this->auditRunId = null;
        $this->auditReportGeneratedAt = null;
        $this->resetErrorBag();

        $this->validate([
            'auditUrl' => ['required', 'string', 'max:2048'],
        ]);

        $actionKey = 'website_builder_ai_audit';
        $options = app(OptionStore::class);
        $aiEnabled = (string) $options->get('ai_content_status', '1') === '1'
            && trim((string) $options->get('ai_openai_api_key', '')) !== '';

        try {
            if (function_exists('set_time_limit')) {
                @set_time_limit(90);
            }

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

            if (Schema::hasTable('website_audit_runs')) {
                $onboarding = app(AgencyOnboarding::class);
                $project = $onboarding->activeProject($user);

                $run = WebsiteAuditRun::query()->create([
                    'owner_user_id' => (int) $user->id,
                    'agency_project_id' => $project?->id,
                    'result' => $this->auditResult,
                ]);

                $this->auditRunId = (int) $run->id;
                $this->auditReportGeneratedAt = $run->created_at?->format('M d, Y H:i')
                    ?? now()->format('M d, Y H:i');
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

        $user = auth()->user();

        return view('appwebsitebuilder::livewire.website-audit', [
            'project' => $project,
            'auditHistory' => WebsiteOverviewMetrics::recentAuditHistory($user),
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
