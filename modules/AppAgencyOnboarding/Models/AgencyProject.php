<?php

namespace Modules\AppAgencyOnboarding\Models;

use Database\Factories\AgencyProjectFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\AdminUser\Models\User;

class AgencyProject extends Model
{
    /** @use HasFactory<\Database\Factories\AgencyProjectFactory> */
    use HasFactory;
    protected $table = 'agency_projects';

    protected $fillable = [
        'owner_user_id',
        'name',
        'website',
        'industry',
        'goals',
        'target_audience',
        'monthly_budget',
        'brand_tone',
        'enabled_agents',
        'active_agent',
        'setup_completed_at',
    ];

    protected function casts(): array
    {
        return [
            'enabled_agents' => 'array',
            'setup_completed_at' => 'datetime',
        ];
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_user_id');
    }

    public function isSetupComplete(): bool
    {
        return $this->setup_completed_at !== null;
    }

    protected static function newFactory(): AgencyProjectFactory
    {
        return AgencyProjectFactory::new();
    }

    public function initialLetter(): string
    {
        return strtoupper(substr(trim($this->name), 0, 1)) ?: 'P';
    }
}
