<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;

class RecyclingLog extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'user_id',
        'assignment_id',
        'route_id',
        'collection_date',
        'notes',
        'quality_issue',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'collection_date' => 'date',
            'quality_issue' => 'boolean',
        ];
    }

    /**
     * Get the user who created this log.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the assignment associated with this log.
     */
    public function assignment(): BelongsTo
    {
        return $this->belongsTo(Assignment::class);
    }

    /**
     * Get the route associated with this log.
     */
    public function route(): BelongsTo
    {
        return $this->belongsTo(Route::class);
    }

    /**
     * Get the materials for this log.
     */
    public function materials(): HasMany
    {
        return $this->hasMany(RecyclingLogMaterial::class);
    }

    /**
     * Scope to filter logs by user.
     */
    public function scopeForUser(Builder $query, User $user): Builder
    {
        return $query->where('user_id', $user->id);
    }

    /**
     * Scope to filter logs by date range.
     */
    public function scopeForDateRange(Builder $query, $startDate, $endDate): Builder
    {
        return $query->whereBetween('collection_date', [$startDate, $endDate]);
    }

    /**
     * Scope to filter logs by route.
     */
    public function scopeForRoute(Builder $query, Route $route): Builder
    {
        return $query->where('route_id', $route->id);
    }

    /**
     * Scope to filter logs by zone.
     */
    public function scopeForZone(Builder $query, string $zone): Builder
    {
        return $query->whereHas('route', function ($q) use ($zone) {
            $q->where('zone', $zone);
        });
    }

    /**
     * Scope to filter logs with quality issues.
     */
    public function scopeWithQualityIssues(Builder $query): Builder
    {
        return $query->where('quality_issue', true);
    }

    /**
     * Scope to order logs by most recent collection date.
     */
    public function scopeRecent(Builder $query): Builder
    {
        return $query->orderBy('collection_date', 'desc');
    }

    /**
     * Check if the log is within the 2-hour edit window.
     */
    public function isWithinEditWindow(): bool
    {
        return $this->created_at->diffInHours(now()) < 2;
    }

    /**
     * Check if the log can be edited by the given user.
     */
    public function canBeEditedBy(User $user): bool
    {
        return $this->user_id === $user->id && $this->isWithinEditWindow();
    }

    /**
     * Get the total weight of all materials in this log.
     */
    public function getTotalWeight(): float
    {
        return (float) $this->materials()->sum('weight');
    }

    /**
     * Get the material breakdown with weights and percentages.
     *
     * @return array<array{material_type: string, weight: float, percentage: float}>
     */
    public function getMaterialBreakdown(): array
    {
        $materials = $this->materials()->get();
        $totalWeight = $this->getTotalWeight();

        if ($totalWeight == 0) {
            return [];
        }

        return $materials->map(function ($material) use ($totalWeight) {
            return [
                'material_type' => $material->material_type,
                'weight' => (float) $material->weight,
                'percentage' => round(($material->weight / $totalWeight) * 100, 2),
            ];
        })->toArray();
    }
}
