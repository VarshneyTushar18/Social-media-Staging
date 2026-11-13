<?php

namespace Modules\AppWebsiteBuilder\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\AdminUser\Models\User;
use Modules\AppAgencyOnboarding\Models\AgencyProject;

class WebsiteSite extends Model
{
    public const STATUS_DRAFT = 'draft';

    public const STATUS_PUBLISHED = 'published';

    protected $fillable = [
        'owner_user_id',
        'agency_project_id',
        'name',
        'site_type',
        'build_method',
        'status',
        'subdomain_slug',
        'generated_content',
        'published_at',
    ];

    protected function casts(): array
    {
        return [
            'generated_content' => 'array',
            'published_at' => 'datetime',
        ];
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_user_id');
    }

    public function agencyProject(): BelongsTo
    {
        return $this->belongsTo(AgencyProject::class, 'agency_project_id');
    }

    public function scopeForProject(Builder $query, ?int $projectId, int $ownerUserId): Builder
    {
        return $query->where('owner_user_id', $ownerUserId)
            ->when(
                $projectId !== null,
                fn (Builder $builder): Builder => $builder->where('agency_project_id', $projectId),
                fn (Builder $builder): Builder => $builder->whereNull('agency_project_id'),
            );
    }

    public function isPublished(): bool
    {
        return $this->status === self::STATUS_PUBLISHED;
    }
}
