<?php

namespace Modules\AppWebsiteBuilder\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\AdminUser\Models\User;
use Modules\AppAgencyOnboarding\Models\AgencyProject;

class WebsiteAuditRun extends Model
{
    protected $fillable = [
        'owner_user_id',
        'agency_project_id',
        'result',
    ];

    protected function casts(): array
    {
        return [
            'result' => 'array',
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

    public function scopeOwnedBy(Builder $query, int $userId): Builder
    {
        return $query->where('owner_user_id', $userId);
    }
}