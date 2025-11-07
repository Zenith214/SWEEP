<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;
use Carbon\Carbon;

class Assignment extends Model
{
    use HasFactory;

    /**
     * Assignment status constants.
     */
    public const STATUS_ACTIVE = 'active';
    public const STATUS_CANCELLED = 'cancelled';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'truck_id',
        'user_id',
        'route_id',
        'assignment_date',
        'status',
        'notes',
        'cancellation_reason',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'assignment_date' => 'date',
        ];
    }

    /**
     * Get the truck for this assignment.
     */
    public function truck(): BelongsTo
    {
        return $this->belongsTo(Truck::class);
    }

    /**
     * Get the user (crew member) for this assignment.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the route for this assignment.
     */
    public function route(): BelongsTo
    {
        return $this->belongsTo(Route::class);
    }

    /**
     * Scope to filter active assignments.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    /**
     * Scope to filter assignments for a specific date.
     */
    public function scopeForDate(Builder $query, Carbon $date): Builder
    {
        return $query->whereDate('assignment_date', $date->format('Y-m-d'));
    }

    /**
     * Scope to filter upcoming assignments.
     */
    public function scopeUpcoming(Builder $query): Builder
    {
        return $query->where('assignment_date', '>=', now()->format('Y-m-d'));
    }

    /**
     * Check if the assignment is active.
     */
    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    /**
     * Cancel the assignment.
     */
    public function cancel(): void
    {
        $this->status = self::STATUS_CANCELLED;
        $this->save();
    }

    /**
     * Check if this assignment has a conflict with another assignment.
     * Conflicts occur when:
     * - Same truck on same date
     * - Same user on same date
     */
    public function hasConflictWith(Assignment $other): bool
    {
        // Don't check conflict with self
        if ($this->id === $other->id) {
            return false;
        }

        // Only check conflicts for active assignments on the same date
        if (!$this->isActive() || !$other->isActive()) {
            return false;
        }

        if (!$this->assignment_date->isSameDay($other->assignment_date)) {
            return false;
        }

        // Check for truck or user conflict
        return $this->truck_id === $other->truck_id || $this->user_id === $other->user_id;
    }
}
