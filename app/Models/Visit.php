<?php

namespace App\Models;

use App\Enums\DeviceType;
use Database\Factories\VisitFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Prunable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * One page view of a public page. Rows are anonymous by design: a visitor is
 * identified only by a salted hash, and they are pruned after the retention
 * window configured in `kalaluang.visit_retention_days`.
 */
#[Fillable([
    'path',
    'route_name',
    'visitor_hash',
    'device',
    'referrer_host',
    'user_id',
])]
class Visit extends Model
{
    /** @use HasFactory<VisitFactory> */
    use HasFactory, Prunable;

    /**
     * Visits are written once and never updated.
     */
    public const UPDATED_AT = null;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'device' => DeviceType::class,
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Rows older than the retention window, removed by `model:prune`.
     */
    public function prunable(): Builder
    {
        return static::query()->where(
            'created_at',
            '<',
            now()->subDays((int) config('kalaluang.visit_retention_days')),
        );
    }
}
