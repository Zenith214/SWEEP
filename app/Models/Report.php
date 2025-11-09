<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;
use Carbon\Carbon;

class Report extends Model
{
    use HasFactory;

    /**
     * Report type constants.
     */
    public const TYPE_MISSED_PICKUP = 'missed_pickup';
    public const TYPE_UNCOLLECTED_WASTE = 'uncollected_waste';
    public const TYPE_ILLEGAL_DUMPING = 'illegal_dumping';
    public const TYPE_OTHER = 'other';

    public const REPORT_TYPES = [
        self::TYPE_MISSED_PICKUP => 'Missed Pickup',
        self::TYPE_UNCOLLECTED_WASTE => 'Uncollected Waste',
        self::TYPE_ILLEGAL_DUMPING => 'Illegal Dumping',
        self::TYPE_OTHER => 'Other'
    ];

    /**
     * Status constants.
     */
    public const STATUS_PENDING = 'pending';
    public const STATUS_IN_PROGRESS = 'in_progress';
    public const STATUS_RESOLVED = 'resolved';
    public const STATUS_CLOSED = 'closed';

    public const STATUSES = [
        self::STATUS_PENDING => 'Pending',
        self::STATUS_IN_PROGRESS => 'In Progress',
        self::STATUS_RESOLVED => 'Resolved',
        self::STATUS_CLOSED => 'Closed'
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'reference_number',
        'resident_id',
        'report_type',
        'location',
        'description',
        'status',
        'route_id',
        'assigned_to',
        'resolved_at'
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'resolved_at' => 'datetime'
        ];
    }

    /**
     * Get the resident who submitted this report.
     */
    public function resident(): BelongsTo
    {
        return $this->belongsTo(User::class, 'resident_id');
    }

    /**
     * Get the route associated with this report.
     */
    public function route(): BelongsTo
    {
        return $this->belongsTo(Route::class);
    }

    /**
     * Get the user assigned to this report.
     */
    public function assignedTo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    /**
     * Get the photos for this report.
     */
    public function photos(): HasMany
    {
        return $this->hasMany(ReportPhoto::class);
    }

    /**
     * Get the responses for this report.
     */
    public function responses(): HasMany
    {
        return $this->hasMany(ReportResponse::class);
    }

    /**
     * Get the status history for this report.
     */
    public function statusHistory(): HasMany
    {
        return $this->hasMany(ReportStatusHistory::class);
    }

    /**
     * Scope to filter pending reports.
     */
    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    /**
     * Scope to filter in-progress reports.
     */
    public function scopeInProgress(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_IN_PROGRESS);
    }

    /**
     * Scope to filter resolved reports.
     */
    public function scopeResolved(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_RESOLVED);
    }

    /**
     * Scope to filter reports for a date range.
     */
    public function scopeForDateRange(Builder $query, Carbon $start, Carbon $end): Builder
    {
        return $query->whereBetween('created_at', [$start, $end]);
    }

    /**
     * Check if this report is pending.
     */
    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    /**
     * Check if this report is resolved.
     */
    public function isResolved(): bool
    {
        return $this->status === self::STATUS_RESOLVED || $this->status === self::STATUS_CLOSED;
    }

    /**
     * Get the resolution time in hours.
     * Returns null if not resolved.
     */
    public function getResolutionTime(): ?int
    {
        if (!$this->isResolved() || !$this->resolved_at) {
            return null;
        }

        return (int) $this->created_at->diffInHours($this->resolved_at);
    }

    /**
     * Generate a unique reference number for a report.
     * Format: REP-YYYYMMDD-XXXX
     */
    public static function generateReferenceNumber(): string
    {
        $date = now()->format('Ymd');
        $prefix = "REP-{$date}-";

        // Get the last report created today
        $lastReport = self::where('reference_number', 'like', $prefix . '%')
            ->orderBy('reference_number', 'desc')
            ->first();

        if ($lastReport) {
            // Extract the sequence number and increment
            $lastSequence = (int) substr($lastReport->reference_number, -4);
            $newSequence = $lastSequence + 1;
        } else {
            // First report of the day
            $newSequence = 1;
        }

        return $prefix . str_pad($newSequence, 4, '0', STR_PAD_LEFT);
    }
}
