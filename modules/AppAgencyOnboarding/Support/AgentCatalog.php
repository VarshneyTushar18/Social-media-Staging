<?php

namespace Modules\AppAgencyOnboarding\Support;

class AgentCatalog
{
    /**
     * @return array<string, array<string, mixed>>
     */
    public static function all(): array
    {
        return [
            'social' => [
                'key' => 'social',
                'label' => __('Social Media'),
                'short' => 'SM',
                'description' => __('Plans, writes and schedules posts, and replies to comments.'),
                'plan_feature' => 'agent_social',
                'route' => 'portal.agents.social.overview',
                'color' => '#2563eb',
                'sidebar_sections' => ['overview', 'agents', 'workspace', 'content-tools', 'library', 'help-desk', 'portal'],
                'sidebar_active_when' => [
                    'portal.dashboard',
                    'portal.dashboard.*',
                    'portal.agents.social.*',
                    'portal.publishing.*',
                    'portal.channels',
                    'portal.channels.*',
                ],
            ],
            'seo' => [
                'key' => 'seo',
                'label' => __('SEO'),
                'short' => 'SE',
                'description' => __('Tracks rankings, audits the site and writes content briefs.'),
                'plan_feature' => 'agent_seo',
                'route' => 'portal.agents.placeholder',
                'route_params' => ['agent' => 'seo'],
                'color' => '#0d9488',
                'sidebar_sections' => ['agents'],
                'sidebar_active_when' => ['portal.agents.placeholder'],
            ],
            'email' => [
                'key' => 'email',
                'label' => __('Email Marketing'),
                'short' => 'EM',
                'description' => __('Builds segments, writes campaigns and runs automations.'),
                'plan_feature' => 'agent_email',
                'route' => 'portal.agents.placeholder',
                'route_params' => ['agent' => 'email'],
                'color' => '#ea580c',
                'sidebar_sections' => ['agents'],
                'sidebar_active_when' => ['portal.agents.placeholder'],
            ],
            'website' => [
                'key' => 'website',
                'label' => __('Website Builder'),
                'short' => 'WB',
                'description' => __('Designs pages, publishes sites and manages domains.'),
                'plan_feature' => 'agent_website',
                'route' => 'portal.agents.website.overview',
                'color' => '#7c3aed',
                'sidebar_sections' => ['agents'],
                'sidebar_active_when' => [
                    'portal.agents.website.*',
                ],
            ],
        ];
    }

    public static function get(string $key): ?array
    {
        return self::all()[$key] ?? null;
    }
}
