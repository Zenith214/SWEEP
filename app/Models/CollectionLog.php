<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;
use Carbon\Carbon;

class CollectionLog extends Model
{
    use HasFactory;

    /**
     * Status constants.
     */
    public const STATUS_PENDING = 'pending';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_INCOMPLETE = 'incomplete';
    public const STATUS_ISSUE_REPORTED = 'issue_reported';

    /**
     * Issue type constants.
     */
    public const ISSUE_TYPES = [
        'blocked_road' => 'Blocked Road',
        'truck_problem' => 'Truck Problem',
        'weather' => 'Weather Conditions',
        'no_access' => 'No Access to Area',
        'safety_concern' => 'Safety Concern',
        'other' => 'Other'
    ];

    /**
     * Edit time window in hours.
     */
    public const EDIT_WINDOW_HOURS = 2;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'assignment_id',
        'completion_time',
        'status',
        'issue_type',
        'issue_description',
        'completion_percentage',
        'crew_notes',
        'created_by',
        'edited_at'
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'completion_time' => 'datetime',
            'edited_at' => 'datetime',
            'completion_percentage' => 'integer'
        ];
    }

    /**
     * Get the assignment for this collection log.
     */
    public function assignment(): BelongsTo
    {
        return $this->belongsTo(Assignment::class);
    }

    /**
     * Get the user who created this collection log.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the photos for this collection log.
     */
    public function photos(): HasMany
    {
        return $this->hasMany(CollectionPhoto::class);
    }

    /**
     * Get the admin notes for this collection log.
     */
    public function adminNotes(): HasMany
    {
        return $this->hasMany(AdminNote::class);
    }

    /**
     * Scope to filter completed collection logs.
     */
    public function scopeCompleted(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_COMPLETED);
    }

    /**
     * Scope to filter collection logs with issues.
     */
    public function scopeWithIssues(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_ISSUE_REPORTED);
    }

    /**
     * Scope to filter collection logs for a date range.
     */
    public function scopeForDateRange(Builder $query, Carbon $start, Carbon $end): Builder
    {
        return $query->whereHas('assignment', function($q) use ($start, $end) {
            $q->whereBetween('assignment_date', [$start, $end]);
        });
    }

    /**
     * Check if this collection log is editable (within 2-hour window).
     */
    public function isEditable(): bool
    {
        if (!$this->created_at) {
            return false;
        }

        $editDeadline = $this->created_at->copy()->addHours(self::EDIT_WINDOW_HOURS);
        return now()->lessThan($editDeadline);
    }

    /**
     * Check if this collection log can be edited by the given user.
     */
    public function canBeEditedBy(User $user): bool
    {
        // Must be within edit window
        if (!$this->isEditable()) {
            return false;
        }

        // Must be the creator
        return $this->created_by === $user->id;
    }

    /**
     * Check if this collection log is completed.
     */
    public function isCompleted(): bool
    {
        return $this->status === self::STATUS_COMPLETED;
    }

    /**
     * Check if this collection log has an issue.
     */
    public function hasIssue(): bool
    {
        return $this->status === self::STATUS_ISSUE_REPORTED;
    }

    /**
     * Get the remaining edit time in minutes.
     * Returns null if not editable.
     */
    public function getEditTimeRemaining(): ?int
    {
        if (!$this->created_at) {
            return null;
        }

        $editDeadline = $this->created_at->copy()->addHours(self::EDIT_WINDOW_HOURS);
        
        if (now()->greaterThanOrEqualTo($editDeadline)) {
            return null;
        }

        return (int) now()->diffInMinutes($editDeadline);
    }
}
