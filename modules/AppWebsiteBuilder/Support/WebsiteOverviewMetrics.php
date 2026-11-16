<?php

namespace Modules\AppWebsiteBuilder\Support;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Schema;
use Modules\AdminUser\Models\User;
use Modules\AppAgencyOnboarding\Support\AgencyOnboarding;
use Modules\AppCredits\Models\CreditUsageLog;
use Modules\AppTeams\Support\TeamWorkspaceAccess;
use Modules\AppWebsiteBuilder\Models\WebsiteAuditRun;
use Modules\AppWebsiteBuilder\Models\WebsiteSite;

class WebsiteOverviewMetrics
{
    public static function userHasWebsiteAgent(?Authenticatable $user): bool
    {
        if ($user === null || ! class_exists(AgencyOnboarding::class)) {
            return false;
        }

        return in_array('website', app(AgencyOnboarding::class)->enabledAgentsForProject($user), true);
    }

    /**
     * @return Builder<WebsiteSite>|null
     */
    public static function sitesQuery(?Authenticatable $user): ?Builder
    {
        if ($user === null || ! Schema::hasTable('website_sites')) {
            return null;
        }

        $onboarding = app(AgencyOnboarding::class);
        $project = $onboarding->activeProject($user);

        return WebsiteSite::query()->forProject($project?->id, (int) $user->id);
    }

    /**
     * @return array<string, mixed>
     */
    public static function sitesSummary(?Authenticatable $user): array
    {
        $query = self::sitesQuery($user);

        if ($query === null) {
            return [
                'metrics' => [
                    'sites' => 0,
                    'published' => 0,
                    'drafts' => 0,
                    'ai_built' => 0,
                    'template_built' => 0,
                ],
                'recentSites' => [],
            ];
        }

        $sites = (clone $query)->latest('updated_at')->get();

        return [
            'metrics' => [
                'sites' => $sites->count(),
                'published' => $sites->where('status', WebsiteSite::STATUS_PUBLISHED)->count(),
                'drafts' => $sites->where('status', WebsiteSite::STATUS_DRAFT)->count(),
                'ai_built' => $sites->where('build_method', 'ai')->count(),
                'template_built' => $sites->where('build_method', 'template')->count(),
            ],
            'recentSites' => $sites->take(5)->map(fn (WebsiteSite $site): array => [
                'id' => (int) $site->id,
                'name' => (string) $site->name,
                'status' => (string) $site->status,
                'build_method' => (string) ($site->build_method ?? 'scratch'),
                'updated_at' => $site->updated_at?->format('M d, H:i'),
            ])->values()->all(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function templatesSummary(): array
    {
        return [
            'metrics' => [
                'templates' => count(WebsiteTemplateGalleryCatalog::definitions()),
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function domainsSummary(?Authenticatable $user): array
    {
        $query = self::sitesQuery($user);

        if ($query === null) {
            return [
                'metrics' => [
                    'subdomain_live' => 0,
                    'custom_connected' => 0,
                    'pending_custom' => 0,
                    'published' => 0,
                ],
            ];
        }

        $sites = (clone $query)->get();

        return [
            'metrics' => [
                'subdomain_live' => $sites
                    ->filter(fn (WebsiteSite $site): bool => filled($site->subdomain_slug))
                    ->count(),
                'custom_connected' => $sites
                    ->filter(fn (WebsiteSite $site): bool => filled($site->custom_domain) && $site->custom_domain_connected_at !== null)
                    ->count(),
                'pending_custom' => $sites
                    ->filter(fn (WebsiteSite $site): bool => filled($site->pending_custom_domain))
                    ->count(),
                'published' => $sites->where('status', WebsiteSite::STATUS_PUBLISHED)->count(),
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function auditSummary(?Authenticatable $user): array
    {
        $metrics = [
            'audits_total' => 0,
            'audits_this_month' => 0,
        ];
        $lastAudit = null;
        $creditActions = self::creditActionsSummary($user);

        if ($user === null || ! Schema::hasTable('website_audit_runs')) {
            return [
                'metrics' => $metrics,
                'lastAudit' => $lastAudit,
                'creditActions' => $creditActions,
            ];
        }

        $onboarding = app(AgencyOnboarding::class);
        $project = $onboarding->activeProject($user);

        $runsQuery = WebsiteAuditRun::query()
            ->where('owner_user_id', (int) $user->id)
            ->when(
                $project !== null,
                fn (Builder $builder): Builder => $builder->where('agency_project_id', $project->id),
                fn (Builder $builder): Builder => $builder->whereNull('agency_project_id'),
            );

        $startOfMonth = now()->startOfMonth();
        $metrics['audits_total'] = (int) (clone $runsQuery)->count();
        $metrics['audits_this_month'] = (int) (clone $runsQuery)
            ->where('created_at', '>=', $startOfMonth)
            ->count();

        $latest = (clone $runsQuery)->latest('id')->first();

        if ($latest !== null) {
            $result = is_array($latest->result) ? $latest->result : [];
            $lastAudit = [
                'target_label' => (string) ($result['target_label'] ?? ''),
                'score' => (int) ($result['score'] ?? 0),
                'used_ai' => ! empty($result['used_ai']),
                'created_at' => $latest->created_at?->format('M d, Y H:i'),
                'run_id' => (int) $latest->id,
            ];
        }

        return [
            'metrics' => $metrics,
            'lastAudit' => $lastAudit,
            'creditActions' => $creditActions,
        ];
    }

    /**
     * @return list<array<string, mixed>>
     */
    public static function recentAuditHistory(?Authenticatable $user, int $limit = 20): array
    {
        if ($user === null || ! Schema::hasTable('website_audit_runs')) {
            return [];
        }

        $onboarding = app(AgencyOnboarding::class);
        $project = $onboarding->activeProject($user);

        return WebsiteAuditRun::query()
            ->where('owner_user_id', (int) $user->id)
            ->when(
                $project !== null,
                fn (Builder $builder): Builder => $builder->where('agency_project_id', $project->id),
                fn (Builder $builder): Builder => $builder->whereNull('agency_project_id'),
            )
            ->latest('id')
            ->limit(max(1, min($limit, 50)))
            ->get()
            ->map(function (WebsiteAuditRun $run): array {
                $result = is_array($run->result) ? $run->result : [];

                return [
                    'run_id' => (int) $run->id,
                    'target_label' => (string) ($result['target_label'] ?? ''),
                    'score' => (int) ($result['score'] ?? 0),
                    'used_ai' => ! empty($result['used_ai']),
                    'created_at' => $run->created_at?->format('M d, Y H:i'),
                ];
            })
            ->values()
            ->all();
    }

    /**
     * @return list<array<string, mixed>>
     */
    protected static function creditActionsSummary(?Authenticatable $user): array
    {
        $keys = [
            'website_builder_ai_generate' => __('AI site generation'),
            'website_builder_ai_audit' => __('AI site audit'),
        ];

        $planOwner = self::planOwner($user);
        $usedByAction = [];

        if ($planOwner !== null && Schema::hasTable('credit_usage_logs')) {
            $usedByAction = CreditUsageLog::query()
                ->where('user_id', (int) $planOwner->id)
                ->whereIn('action_key', array_keys($keys))
                ->selectRaw('action_key, SUM(amount) as total')
                ->groupBy('action_key')
                ->pluck('total', 'action_key')
                ->map(fn ($total): int => (int) $total)
                ->all();
        }

        $actions = [];

        foreach ($keys as $key => $label) {
            $cost = 0;

            if ($planOwner !== null && function_exists('credit_service')) {
                $cost = max(0, (int) credit_service()->costFor($planOwner, $key));
            } else {
                $cost = $key === 'website_builder_ai_generate' ? 2 : 1;
            }

            $actions[] = [
                'key' => $key,
                'label' => $label,
                'cost' => $cost,
                'used' => (int) ($usedByAction[$key] ?? 0),
            ];
        }

        return $actions;
    }

    protected static function planOwner(?Authenticatable $user): ?User
    {
        if (! $user instanceof User) {
            return null;
        }

        $team = TeamWorkspaceAccess::activeTeam($user);

        return $team?->owner ?: $user;
    }
}
