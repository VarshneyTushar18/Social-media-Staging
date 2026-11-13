<?php

namespace Modules\AppWebsiteBuilder\Support;

use Illuminate\Support\Facades\Schema;
use Modules\AdminUser\Models\User;
use Modules\AppAgencyOnboarding\Models\AgencyProject;
use Modules\AppWebsiteBuilder\Models\WebsiteSite;
use RuntimeException;

class WebsiteSiteCreator
{
    public function __construct(
        protected WebsiteAiGenerator $aiGenerator,
    ) {}

    /**
     * @param  array<string, mixed>  $context
     */
    public function createWithAi(User $user, ?AgencyProject $project, string $siteType, array $context = []): WebsiteSite
    {
        $this->ensureTableExists();

        $content = $this->aiGenerator->generate($siteType, [
            'project_name' => $project?->name,
            'brand_tone' => $project?->brand_tone,
            'user_prompt' => (string) ($context['user_prompt'] ?? ''),
        ]);

        $userPrompt = trim((string) ($context['user_prompt'] ?? ''));
        if ($userPrompt === '') {
            throw new RuntimeException(__('A prompt is required to generate a website with AI.'));
        }

        $content['user_prompt'] = $userPrompt;
        $content['generation_mode'] = 'prompt';

        return WebsiteSite::query()->create([
            'owner_user_id' => $user->id,
            'agency_project_id' => $project?->id,
            'name' => (string) ($content['site_name'] ?? $siteType),
            'site_type' => $siteType,
            'build_method' => 'ai',
            'status' => WebsiteSite::STATUS_DRAFT,
            'generated_content' => $content,
        ]);
    }

    public function createFromTemplate(User $user, ?AgencyProject $project, string $siteType, string $templateId): WebsiteSite
    {
        $this->ensureTableExists();

        $content = WebsiteTemplates::buildContent($templateId, $siteType);

        return WebsiteSite::query()->create([
            'owner_user_id' => $user->id,
            'agency_project_id' => $project?->id,
            'name' => (string) ($content['site_name'] ?? $siteType),
            'site_type' => $siteType,
            'build_method' => 'template',
            'status' => WebsiteSite::STATUS_DRAFT,
            'generated_content' => $content,
        ]);
    }

    public function createManual(User $user, ?AgencyProject $project, string $siteType, string $buildMethod): WebsiteSite
    {
        $this->ensureTableExists();

        abort_unless(in_array($buildMethod, ['template', 'scratch'], true), 404);

        return WebsiteSite::query()->create([
            'owner_user_id' => $user->id,
            'agency_project_id' => $project?->id,
            'name' => $siteType,
            'site_type' => $siteType,
            'build_method' => $buildMethod,
            'status' => WebsiteSite::STATUS_DRAFT,
            'generated_content' => [
                'site_name' => $siteType,
                'tagline' => '',
                'sections' => [],
                'source' => $buildMethod,
            ],
        ]);
    }

    protected function ensureTableExists(): void
    {
        if (! Schema::hasTable('website_sites')) {
            throw new RuntimeException(__('Website storage is not ready. Run database migrations.'));
        }
    }
}
