<?php

namespace Modules\AppBulkPosts\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\AdminUser\Models\User;

class BulkPostBatch extends Model
{
    protected $table = 'bulk_post_batches';

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'meta' => 'array',
            'stats' => 'array',
            'last_processed_at' => 'datetime',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_user_id');
    }

    public function rows(): HasMany
    {
        return $this->hasMany(BulkPostRow::class, 'batch_id');
    }

    public function scopeOwnedBy(Builder $query, int $userId): Builder
    {
        return $query->where('owner_user_id', $userId);
    }

    public function importStatusMessage(): string
    {
        $stats = (array) $this->stats;
        $created = (int) data_get($stats, 'created_posts', 0);
        $failed = (int) data_get($stats, 'failed_rows', 0);
        $invalid = (int) data_get($stats, 'invalid_rows', 0);
        $valid = (int) data_get($stats, 'valid_rows', 0);

        if ($created > 0 || $failed > 0) {
            return __('Bulk posts have been added to Publishing. Success: :success, Failed: :failed.', [
                'success' => number_format($created),
                'failed' => number_format($failed),
            ]);
        }

        if ($invalid > 0 && $valid === 0) {
            return __('No posts were created. All :count CSV row(s) failed validation. Check the caption column, schedule times, and URLs, then compare your file with the bulk template.', [
                'count' => number_format($invalid),
            ]);
        }

        return __('No posts were created. Valid rows: :valid, Invalid rows: :invalid.', [
            'valid' => number_format($valid),
            'invalid' => number_format($invalid),
        ]);
    }
}
