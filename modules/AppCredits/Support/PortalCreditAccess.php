<?php

namespace Modules\AppCredits\Support;

use Modules\AppAgencyOnboarding\Support\AgencyOnboarding;
use Modules\AppAgencyOnboarding\Support\AgentCatalog;
use Modules\AdminUser\Models\User;
use Modules\AppTeams\Support\TeamWorkspaceAccess;

class PortalCreditAccess
{
    /**
     * @var array<string, list<string>>
     */
    public const AGENT_ACTION_PREFIXES = [
        'social' => ['ai_studio_%'],
        'website' => ['website_builder_%'],
    ];

    public static function userCanViewPortalCredits(?User $user): bool
    {
        if ($user === null) {
            return false;
        }

        $team = TeamWorkspaceAccess::activeTeam($user);
        $planOwner = $team?->owner ?: $user;

        $hasAiStudioPortal = TeamWorkspaceAccess::teamHasModule($team, 'publishing')
            && (! $planOwner?->plan || $planOwner->hasPlanFeature('ai_studio'));

        if ($hasAiStudioPortal) {
            return true;
        }

        if (! class_exists(AgencyOnboarding::class)) {
            return false;
        }

        $enabled = app(AgencyOnboarding::class)->enabledAgentsForProject($user);

        foreach (array_keys(self::AGENT_ACTION_PREFIXES) as $agentKey) {
            if (in_array($agentKey, $enabled, true)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return array<string, string>
     */
    public static function agentFilterOptions(?User $user): array
    {
        $options = [
            'all' => __('All agents'),
        ];

        if ($user === null || ! class_exists(AgencyOnboarding::class)) {
            return $options;
        }

        $enabled = app(AgencyOnboarding::class)->enabledAgentsForProject($user);

        foreach ($enabled as $agentKey) {
            if (! isset(self::AGENT_ACTION_PREFIXES[$agentKey])) {
                continue;
            }

            $definition = AgentCatalog::get((string) $agentKey);

            $options[(string) $agentKey] = (string) ($definition['label'] ?? ucfirst((string) $agentKey));
        }

        return $options;
    }

    /**
     * @return list<string>
     */
    public static function actionPrefixesForFilter(string $agentFilter, ?User $user): array
    {
        if ($agentFilter !== 'all' && isset(self::AGENT_ACTION_PREFIXES[$agentFilter])) {
            return self::AGENT_ACTION_PREFIXES[$agentFilter];
        }

        if ($user === null || ! class_exists(AgencyOnboarding::class)) {
            return collect(self::AGENT_ACTION_PREFIXES)->flatten()->unique()->values()->all();
        }

        $prefixes = [];

        foreach (app(AgencyOnboarding::class)->enabledAgentsForProject($user) as $agentKey) {
            foreach (self::AGENT_ACTION_PREFIXES[$agentKey] ?? [] as $prefix) {
                $prefixes[] = $prefix;
            }
        }

        if ($prefixes !== []) {
            return array_values(array_unique($prefixes));
        }

        return collect(self::AGENT_ACTION_PREFIXES)->flatten()->unique()->values()->all();
    }

    /**
     * @param  list<string>  $prefixes
     * @return array<string, string>
     */
    public static function actionOptionsForPrefixes(array $prefixes): array
    {
        $fromRegistry = collect(credit_actions())
            ->filter(function (array $definition, string $key) use ($prefixes): bool {
                foreach ($prefixes as $prefix) {
                    if (self::keyMatchesPrefix($key, $prefix)) {
                        return true;
                    }
                }

                return false;
            })
            ->mapWithKeys(fn (array $definition, string $key): array => [
                $key => (string) ($definition['label'] ?? $key),
            ]);

        $defaults = [
            'ai_studio_generate_captions' => __('Caption Generator'),
            'ai_studio_rss_rewrite' => __('RSS AI Rewrite'),
            'ai_studio_repurpose_content' => __('Repurpose'),
            'ai_studio_plan_calendar' => __('Content Planner'),
            'ai_studio_best_time' => __('Best Time'),
            'ai_studio_generate_image' => __('AI Image'),
            'ai_studio_generate_video' => __('AI Video'),
            'ai_studio_review_content' => __('AI Review'),
            'ai_studio_semantic_search' => __('Semantic Search'),
            'website_builder_ai_generate' => __('Website Builder AI site generation'),
            'website_builder_ai_audit' => __('Website Builder AI site audit'),
        ];

        return collect($defaults)
            ->filter(function (string $label, string $key) use ($prefixes): bool {
                foreach ($prefixes as $prefix) {
                    if (self::keyMatchesPrefix($key, $prefix)) {
                        return true;
                    }
                }

                return false;
            })
            ->merge($fromRegistry)
            ->all();
    }

    public static function keyMatchesPrefix(string $key, string $prefix): bool
    {
        $needle = rtrim($prefix, '%');

        if (str_ends_with($prefix, '%')) {
            return str_starts_with($key, $needle);
        }

        return $key === $prefix;
    }
}
