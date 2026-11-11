<?php

namespace Modules\AppAgencyOnboarding\Support;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Modules\AdminPlans\Models\AdminPlan;
use Modules\AdminUser\Models\User;
use Modules\AppAgencyOnboarding\Models\AgencyProject;
use Modules\AppPayments\Support\UserPlanTransitionService;

class AgencyOnboarding
{
    public function shouldEnforce(User $user): bool
    {
        if ($user->canAccessAdmin()) {
            return false;
        }

        return true;
    }

    public function hasChosenOnboardingPlan(User $user): bool
    {
        if (session('agency_onboarding_plan_chosen', false)) {
            return true;
        }

        if (! Schema::hasColumn('users', 'onboarding_plan_chosen_at')) {
            return false;
        }

        return $user->onboarding_plan_chosen_at !== null;
    }

    public function completeOnboardingPlanSelection(User $user, int $planId, bool $requireFree = false): void
    {
        $transitions = app(UserPlanTransitionService::class);

        $plan = AdminPlan::query()
            ->whereKey($planId)
            ->where('status', true)
            ->when($requireFree, fn ($query) => $query->where('free_plan', true))
            ->firstOrFail();

        if ((int) $user->plan_id !== (int) $plan->id) {
            $transitions->applyPurchasedPlan($user->loadMissing('plan'), $plan);
        }

        $user->refresh();

        if (! $user->hasActivePlan()) {
            $user->forceFill([
                'plan_id' => $plan->id,
                'plan_started_at' => $user->plan_started_at ?? now(),
                'plan_expires_at' => $user->plan_expires_at,
            ])->save();
            $user->refresh();
        }

        $this->markOnboardingPlanChosen($user);
    }

    public function markOnboardingPlanChosen(User $user): void
    {
        session(['agency_onboarding_plan_chosen' => true]);

        if (! Schema::hasColumn('users', 'onboarding_plan_chosen_at')) {
            return;
        }

        if ($user->onboarding_plan_chosen_at !== null) {
            return;
        }

        $user->forceFill(['onboarding_plan_chosen_at' => now()])->save();
    }

    public function postAuthRedirectPath(User $user): string
    {
        if (! $this->shouldEnforce($user)) {
            return route('dashboard');
        }

        if ($this->isComplete($user)) {
            if (\Illuminate\Support\Facades\Route::has('portal.main')) {
                return route('portal.main');
            }

            $project = $this->activeProject($user);

            if ($project !== null) {
                return route(
                    $this->launchRoute($user, $project),
                    $this->launchRouteParameters($user, $project),
                );
            }

            return route('portal.agents.social.overview');
        }

        return route('portal.onboarding');
    }

    public function isComplete(User $user): bool
    {
        if ($user->isSuperAdmin()) {
            $this->ensureSuperAdminWorkspace($user);

            return $this->activeProject($user)?->isSetupComplete() ?? false;
        }

        if (! $this->hasChosenOnboardingPlan($user) || ! $user->hasActivePlan()) {
            return false;
        }

        $project = $this->activeProject($user);

        return $project !== null && $project->isSetupComplete();
    }

    public function ensureSuperAdminWorkspace(User $user): void
    {
        if (! $user->isSuperAdmin() || ! Schema::hasTable('agency_projects')) {
            return;
        }

        $this->markOnboardingPlanChosen($user);

        $allAgents = array_keys(AgentCatalog::all());
        $project = $this->activeProject($user);

        if ($project?->isSetupComplete()) {
            $enabled = array_values(array_filter((array) $project->enabled_agents));
            $merged = array_values(array_unique(array_merge($enabled, $allAgents)));

            if ($merged !== $enabled) {
                $project->forceFill(['enabled_agents' => $merged])->save();
            }

            return;
        }

        $project = AgencyProject::query()
            ->where('owner_user_id', $user->id)
            ->whereNotNull('setup_completed_at')
            ->first();

        if ($project === null) {
            $project = AgencyProject::query()->create([
                'owner_user_id' => $user->id,
                'name' => __('Admin workspace'),
                'industry' => 'other',
                'monthly_budget' => 'flexible',
                'brand_tone' => 'professional',
                'enabled_agents' => $allAgents,
                'active_agent' => 'main',
                'setup_completed_at' => now(),
            ]);
        }

        $this->setActiveProject($user, $project);
    }

    public function currentStep(User $user): string
    {
        if (! $this->hasChosenOnboardingPlan($user) || ! $user->hasActivePlan()) {
            return 'plan';
        }

        $draft = $this->draftProject($user);

        if ($draft === null) {
            return 'project';
        }

        if (! $draft->isSetupComplete()) {
            return 'setup';
        }

        return 'done';
    }

    public function routeForStep(string $step): string
    {
        return match ($step) {
            'plan' => 'portal.onboarding.plan',
            'project' => 'portal.onboarding.project',
            'setup' => 'portal.onboarding.setup',
            default => 'portal.main',
        };
    }

    /**
     * @return list<string>
     */
    public function mainHubRouteNames(): array
    {
        return [
            'portal.main',
            'portal.main.*',
            'portal.billing',
            'portal.billing.*',
            'portal.packages',
            'portal.packages.*',
            'portal.invoices',
            'portal.invoices.*',
        ];
    }

    public function isMainHubRequest(?Request $request = null): bool
    {
        $request ??= request();

        return $request instanceof Request && $request->routeIs(...$this->mainHubRouteNames());
    }

    public function activeProject(User $user): ?AgencyProject
    {
        if ($user->active_agency_project_id) {
            $project = AgencyProject::query()
                ->whereKey($user->active_agency_project_id)
                ->where('owner_user_id', $user->id)
                ->first();

            if ($project) {
                return $project;
            }
        }

        return AgencyProject::query()
            ->where('owner_user_id', $user->id)
            ->whereNotNull('setup_completed_at')
            ->latest('setup_completed_at')
            ->first();
    }

    public function draftProject(User $user): ?AgencyProject
    {
        if (! Schema::hasTable('agency_projects')) {
            return $this->draftProjectFromSession($user);
        }

        if (session('agency_onboarding_project_ready', false)) {
            $sessionDraft = $this->draftProjectFromSession($user);

            if ($sessionDraft !== null) {
                $sessionProjectId = (int) session('agency_onboarding_active_project_id', 0);

                if ($sessionProjectId > 0) {
                    $project = AgencyProject::query()
                        ->whereKey($sessionProjectId)
                        ->where('owner_user_id', $user->id)
                        ->first();

                    if ($project && ! $project->isSetupComplete()) {
                        return $project;
                    }
                }

                return $sessionDraft;
            }
        }

        $sessionProjectId = (int) session('agency_onboarding_active_project_id', 0);

        if ($sessionProjectId > 0) {
            $project = AgencyProject::query()
                ->whereKey($sessionProjectId)
                ->where('owner_user_id', $user->id)
                ->first();

            if ($project && ! $project->isSetupComplete()) {
                return $project;
            }
        }

        $project = AgencyProject::query()
            ->where('owner_user_id', $user->id)
            ->whereNull('setup_completed_at')
            ->whereNotNull('name')
            ->where('name', '!=', '')
            ->latest('id')
            ->first();

        if ($project) {
            return $project;
        }

        if (Schema::hasColumn('users', 'active_agency_project_id') && $user->active_agency_project_id) {
            $project = AgencyProject::query()
                ->whereKey($user->active_agency_project_id)
                ->where('owner_user_id', $user->id)
                ->first();

            if ($project && ! $project->isSetupComplete()) {
                return $project;
            }
        }

        return $this->draftProjectFromSession($user);
    }

    /**
     * @param  array{name: string, website?: string|null, industry: string}  $validated
     */
    public function persistProjectFromOnboarding(User $user, array $validated): AgencyProject
    {
        if (! Schema::hasTable('agency_projects')) {
            $project = new AgencyProject([
                'owner_user_id' => $user->id,
                'name' => $validated['name'],
                'website' => $validated['website'] ?? null,
                'industry' => $validated['industry'],
                'active_agent' => 'social',
            ]);

            $this->rememberProjectDraft($user, $project);

            return $project;
        }

        try {
            $project = $this->draftProject($user);

            if ($project?->isSetupComplete()) {
                $project = null;
            }

            if (! $project) {
                $project = new AgencyProject([
                    'owner_user_id' => $user->id,
                    'active_agent' => 'social',
                ]);
            }

            $project->fill([
                'name' => $validated['name'],
                'website' => $validated['website'] ?? null,
                'industry' => $validated['industry'],
            ])->save();

            $project = $project->fresh() ?? $project;
            $this->setActiveProject($user, $project);

            return $project;
        } catch (\Throwable $exception) {
            report($exception);

            $project = new AgencyProject([
                'owner_user_id' => $user->id,
                'name' => $validated['name'],
                'website' => $validated['website'] ?? null,
                'industry' => $validated['industry'],
                'active_agent' => 'social',
            ]);

            $this->rememberProjectDraft($user, $project);

            return $project;
        }
    }

    public function rememberProjectDraft(User $user, AgencyProject $project): void
    {
        $projectId = $project->getKey();
        $projectId = is_numeric($projectId) && (int) $projectId > 0 ? (int) $projectId : null;

        session([
            'agency_onboarding_active_project_id' => $projectId,
            'agency_onboarding_project_draft' => [
                'id' => $projectId,
                'name' => (string) $project->name,
                'website' => $project->website,
                'industry' => (string) ($project->industry ?? 'ecommerce'),
            ],
            'agency_onboarding_project_ready' => true,
        ]);
    }

    /**
     * @param  array{
     *     goals?: string|null,
     *     target_audience?: string|null,
     *     monthly_budget: string,
     *     brand_tone: string,
     *     selected_agents: array<int, string>,
     * }  $validated
     */
    public function completeProjectSetup(User $user, array $validated): AgencyProject
    {
        $project = $this->resolveSetupProject($user);

        if ($project === null || trim((string) ($project->name ?? '')) === '') {
            throw new \RuntimeException(__('Create a project before launching agents.'));
        }

        if (! $project->exists || ! $project->getKey()) {
            $project->owner_user_id = $user->id;
            $project->save();
        }

        $validKeys = array_keys(AgentCatalog::all());
        $selected = array_values(array_unique(array_intersect($validated['selected_agents'], $validKeys)));

        if ($selected === []) {
            throw new \InvalidArgumentException(__('Choose at least one agent.'));
        }

        $max = $this->maxSelectableAgents($user);
        if (count($selected) > $max) {
            throw new \InvalidArgumentException(__('You can select up to :count agents.', ['count' => $max]));
        }

        $activeAgent = in_array('social', $selected, true) ? 'social' : $selected[0];

        $project->forceFill([
            'goals' => $validated['goals'] ?? null,
            'target_audience' => $validated['target_audience'] ?? null,
            'monthly_budget' => $validated['monthly_budget'],
            'brand_tone' => $validated['brand_tone'],
            'enabled_agents' => $selected,
            'active_agent' => $activeAgent,
            'setup_completed_at' => now(),
        ])->save();

        $this->setActiveProject($user, $project);

        session()->forget([
            'agency_onboarding_project_ready',
            'agency_onboarding_project_draft',
        ]);

        return $project->fresh() ?? $project;
    }

    public function resolveSetupProject(User $user): ?AgencyProject
    {
        if (session('agency_onboarding_project_ready', false)) {
            $sessionDraft = session('agency_onboarding_project_draft');

            if (is_array($sessionDraft) && trim((string) ($sessionDraft['name'] ?? '')) !== '') {
                $sessionProjectId = (int) ($sessionDraft['id'] ?? session('agency_onboarding_active_project_id', 0));

                if ($sessionProjectId > 0 && Schema::hasTable('agency_projects')) {
                    $project = AgencyProject::query()
                        ->whereKey($sessionProjectId)
                        ->where('owner_user_id', $user->id)
                        ->whereNull('setup_completed_at')
                        ->first();

                    if ($project && trim((string) $project->name) !== '') {
                        return $project;
                    }
                }
            }
        }

        $project = $this->draftProject($user);

        if ($project !== null && trim((string) ($project->name ?? '')) !== '' && ! $project->isSetupComplete()) {
            return $project;
        }

        $sessionDraft = session('agency_onboarding_project_draft');

        if (! is_array($sessionDraft) || trim((string) ($sessionDraft['name'] ?? '')) === '') {
            return null;
        }

        $model = new AgencyProject([
            'owner_user_id' => $user->id,
            'name' => (string) $sessionDraft['name'],
            'website' => $sessionDraft['website'] ?? null,
            'industry' => (string) ($sessionDraft['industry'] ?? 'ecommerce'),
            'active_agent' => 'social',
        ]);

        if (! empty($sessionDraft['id'])) {
            $model->setAttribute('id', (int) $sessionDraft['id']);
            $model->exists = true;
        }

        return $model;
    }

    public function hasProjectDraftReady(User $user): bool
    {
        $draft = $this->draftProject($user);

        if ($draft === null) {
            if (session('agency_onboarding_project_ready', false)) {
                session()->forget([
                    'agency_onboarding_project_ready',
                    'agency_onboarding_project_draft',
                    'agency_onboarding_active_project_id',
                ]);
            }

            return false;
        }

        return trim((string) $draft->name) !== '';
    }

    protected function draftProjectFromSession(User $user): ?AgencyProject
    {
        $payload = session('agency_onboarding_project_draft');

        if (! is_array($payload) || trim((string) ($payload['name'] ?? '')) === '') {
            return null;
        }

        $attributes = [
            'owner_user_id' => $user->id,
            'name' => (string) $payload['name'],
            'website' => $payload['website'] ?? null,
            'industry' => (string) ($payload['industry'] ?? 'ecommerce'),
            'active_agent' => 'social',
        ];

        $project = new AgencyProject($attributes);

        if (! empty($payload['id']) && Schema::hasTable('agency_projects')) {
            $existing = AgencyProject::query()
                ->whereKey((int) $payload['id'])
                ->where('owner_user_id', $user->id)
                ->first();

            if ($existing) {
                return $existing;
            }
        }

        return $project;
    }

    public function setActiveProject(User $user, AgencyProject $project): void
    {
        $this->rememberProjectDraft($user, $project);

        if (! Schema::hasColumn('users', 'active_agency_project_id')) {
            return;
        }

        try {
            $user->forceFill(['active_agency_project_id' => $project->id])->save();
        } catch (\Throwable $exception) {
            report($exception);
        }
    }

    public function maxSelectableAgents(User $user): int
    {
        $catalogCount = count(AgentCatalog::all());
        $limit = (int) $user->planLimit('max_agents_per_project', $catalogCount);

        if ($limit < 1) {
            $limit = 1;
        }

        return min($limit, max(1, $catalogCount));
    }

    /**
     * Agents shown as selectable tiles on the onboarding setup step.
     *
     * @return list<string>
     */
    public function agentsSelectableForSetup(User $user): array
    {
        if (config('modules.appagencyonboarding.offer_all_agents_in_setup', true)) {
            return array_keys(AgentCatalog::all());
        }

        return $this->agentsAllowedByPlan($user);
    }

    /**
     * @return list<string>
     */
    public function enabledAgentsForProject(User $user): array
    {
        if ($user->isSuperAdmin()) {
            $this->ensureSuperAdminWorkspace($user);

            return array_keys(AgentCatalog::all());
        }

        $project = $this->activeProject($user);

        if ($project === null) {
            return ['social'];
        }

        $enabled = array_values(array_filter((array) $project->enabled_agents));

        return $enabled !== [] ? $enabled : ['social'];
    }

    /**
     * @return list<string>
     */
    public function agentsAllowedByPlan(User $user): array
    {
        if ($user->isSuperAdmin()) {
            return array_keys(AgentCatalog::all());
        }

        $permissions = $user->plan?->permissions ?? [];
        $hasAgentToggles = collect(AgentCatalog::all())
            ->contains(fn (array $agent): bool => array_key_exists((string) ($agent['plan_feature'] ?? ''), $permissions));

        if (! $hasAgentToggles) {
            return ['social'];
        }

        $keys = [];

        foreach (AgentCatalog::all() as $key => $agent) {
            if ($this->canUseAgent($user, $key)) {
                $keys[] = $key;
            }
        }

        return $keys;
    }

    public function canUseAgent(User $user, string $agentKey): bool
    {
        if ($user->isSuperAdmin()) {
            return AgentCatalog::get($agentKey) !== null;
        }

        $agent = AgentCatalog::get($agentKey);

        if ($agent === null) {
            return false;
        }

        $feature = (string) ($agent['plan_feature'] ?? '');

        if ($feature === '') {
            return true;
        }

        if (! $user->hasActivePlan()) {
            return false;
        }

        $permissions = $user->plan?->permissions ?? [];

        if (! array_key_exists($feature, $permissions ?? [])) {
            return $agentKey === 'social';
        }

        return $user->hasPlanFeature($feature);
    }

    public function launchRoute(User $user, AgencyProject $project): string
    {
        if (\Illuminate\Support\Facades\Route::has('portal.main')) {
            return 'portal.main';
        }

        $active = (string) ($project->active_agent ?: 'social');
        $enabled = array_values(array_filter((array) $project->enabled_agents));

        if ($enabled !== [] && ! in_array($active, $enabled, true)) {
            $active = (string) $enabled[0];
        }

        $agent = AgentCatalog::get($active);
        $routeName = (string) ($agent['route'] ?? 'portal.agents.social.overview');

        if (! \Illuminate\Support\Facades\Route::has($routeName)) {
            return 'portal.agents.social.overview';
        }

        return $routeName;
    }

    /**
     * @return array<string, mixed>
     */
    public function launchRouteParameters(User $user, AgencyProject $project): array
    {
        $active = (string) ($project->active_agent ?: 'social');
        $agent = AgentCatalog::get($active);

        return (array) ($agent['route_params'] ?? []);
    }

    public function resolveActiveAgentFromRequest(?Request $request = null): ?string
    {
        $request ??= request();

        if (! $request instanceof Request) {
            return null;
        }

        if ($request->routeIs(
            'portal.dashboard',
            'portal.dashboard.*',
            'portal.agents.social.*',
            'portal.publishing.*',
            'portal.channels',
            'portal.channels.*',
        )) {
            return 'social';
        }

        if ($request->routeIs('portal.agents.website.*')) {
            return 'website';
        }

        if ($request->routeIs('portal.agents.placeholder')) {
            $routeAgent = (string) $request->route('agent', '');

            return in_array($routeAgent, array_keys(AgentCatalog::all()), true) ? $routeAgent : null;
        }

        return null;
    }

    public function currentAgentContext(?User $user = null): string
    {
        $user ??= auth()->user();

        if ($user === null || ! $this->isComplete($user)) {
            return 'social';
        }

        if ($this->isMainHubRequest()) {
            return 'main';
        }

        $fromRequest = $this->resolveActiveAgentFromRequest();

        if ($fromRequest !== null) {
            $enabled = $this->enabledAgentsForProject($user);

            if (in_array($fromRequest, $enabled, true)) {
                return $fromRequest;
            }
        }

        $project = $this->activeProject($user);
        $active = (string) ($project?->active_agent ?: 'social');
        $enabled = $this->enabledAgentsForProject($user);

        if (in_array($active, $enabled, true)) {
            return $active;
        }

        return $enabled[0] ?? 'social';
    }

    /**
     * @return list<string>
     */
    public function sidebarSectionsForActiveAgent(?User $user = null): array
    {
        if ($this->isMainHubRequest() || $this->currentAgentContext($user) === 'main') {
            return ['agents'];
        }

        $agentKey = $this->currentAgentContext($user);
        $agent = AgentCatalog::get($agentKey);

        $sections = (array) ($agent['sidebar_sections'] ?? ['agents']);

        return array_values(array_filter($sections));
    }

    /**
     * @return \Illuminate\Support\Collection<int, AgencyProject>
     */
    public function projectsForUser(User $user): \Illuminate\Support\Collection
    {
        if (! Schema::hasTable('agency_projects')) {
            return collect();
        }

        return AgencyProject::query()
            ->where('owner_user_id', $user->id)
            ->whereNotNull('setup_completed_at')
            ->orderBy('name')
            ->get();
    }

    /**
     * @return array{project: AgencyProject, projects: \Illuminate\Support\Collection<int, AgencyProject>}|null
     */
    public function sidebarProjectContext(?User $user = null): ?array
    {
        $user ??= auth()->user();

        if ($user === null || ! $this->isComplete($user)) {
            return null;
        }

        $project = $this->activeProject($user);

        if ($project === null) {
            return null;
        }

        return [
            'project' => $project,
            'projects' => $this->projectsForUser($user),
        ];
    }

    public function beginAdditionalProject(User $user): void
    {
        session()->forget([
            'agency_onboarding_project_ready',
            'agency_onboarding_project_draft',
            'agency_onboarding_active_project_id',
        ]);

        session(['agency_onboarding_adding_project' => true]);
    }

    public function isAddingAdditionalProject(): bool
    {
        return (bool) session('agency_onboarding_adding_project', false);
    }

    public function clearAddingAdditionalProject(): void
    {
        session()->forget('agency_onboarding_adding_project');
    }

    public function shouldShowUserSidebarSection(string $sectionKey, ?User $user = null): bool
    {
        $user ??= auth()->user();

        if ($user === null || ! $this->shouldEnforce($user) || ! $this->isComplete($user)) {
            return true;
        }

        if (! request()->routeIs('portal.*', 'dashboard', 'dashboard.*')) {
            return true;
        }

        return in_array($sectionKey, $this->sidebarSectionsForActiveAgent($user), true);
    }

    public function syncActiveAgentFromRequest(?User $user = null): void
    {
        $user ??= auth()->user();

        if ($user === null || ! $this->isComplete($user)) {
            return;
        }

        $project = $this->activeProject($user);

        if ($project === null) {
            return;
        }

        if ($this->isMainHubRequest()) {
            if ((string) $project->active_agent !== 'main') {
                $project->forceFill(['active_agent' => 'main'])->save();
            }

            return;
        }

        $agent = $this->resolveActiveAgentFromRequest();

        if ($agent === null) {
            return;
        }

        if (! in_array($agent, $this->enabledAgentsForProject($user), true)) {
            return;
        }

        if ((string) $project->active_agent === $agent) {
            return;
        }

        $project->forceFill(['active_agent' => $agent])->save();
    }

    public function requestExemptFromOnboarding(Request $request): bool
    {
        if (! $request->user()) {
            return true;
        }

        if ($request->routeIs(
            'portal.onboarding',
            'portal.onboarding.*',
            'portal.onboarding.plan',
            'portal.onboarding.plan.store',
            'portal.onboarding.project',
            'portal.onboarding.project.store',
            'portal.onboarding.setup.store',
            'portal.onboarding.setup',
            'portal.projects.switch',
            'portal.projects.create',
            'portal.packages',
            'payment.*',
            'login',
            'register',
            'logout',
            'password.*',
            'verification.*',
            'two-factor.*',
            'auth.social.*',
        )) {
            return true;
        }

        if ($request->is(
            'livewire/*',
            'logout',
            'up',
        )) {
            return true;
        }

        if ($request->routeIs('dashboard', 'dashboard.*', 'admin.*', 'admin-*', 'settings.*')) {
            return true;
        }

        if ($request->is('admin', 'admin/*')) {
            return true;
        }

        return false;
    }
}
