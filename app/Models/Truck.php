<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Collection;
use Carbon\Carbon;

class Truck extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * Operational status constants.
     */
    public const STATUS_OPERATIONAL = 'operational';
    public const STATUS_MAINTENANCE = 'maintenance';
    public const STATUS_OUT_OF_SERVICE = 'out_of_service';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'truck_number',
        'license_plate',
        'capacity',
        'operational_status',
        'notes',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'capacity' => 'decimal:2',
        ];
    }

    /**
     * Get all assignments for the truck.
     */
    public function assignments(): HasMany
    {
        return $this->hasMany(Assignment::class);
    }

    /**
     * Get active assignments for the truck.
     */
    public function activeAssignments(): HasMany
    {
        return $this->hasMany(Assignment::class)
            ->where('status', Assignment::STATUS_ACTIVE);
    }

    /**
     * Get status history for the truck.
     */
    public function statusHistory(): HasMany
    {
        return $this->hasMany(TruckStatusHistory::class);
    }

    /**
     * Check if the truck is operational.
     */
    public function isOperational(): bool
    {
        return $this->operational_status === self::STATUS_OPERATIONAL;
    }

    /**
     * Check if the truck has an assignment on a specific date.
     */
    public function hasAssignmentOn(Carbon $date): bool
    {
        return $this->activeAssignments()
            ->whereDate('assignment_date', $date->format('Y-m-d'))
            ->exists();
    }

    /**
     * Get the assignment for a specific date.
     */
    public function getAssignmentOn(Carbon $date): ?Assignment
    {
        return $this->activeAssignments()
            ->whereDate('assignment_date', $date->format('Y-m-d'))
            ->first();
    }

    /**
     * Check if the truck has future assignments.
     */
    public function hasFutureAssignments(): bool
    {
        return $this->activeAssignments()
            ->where('assignment_date', '>=', now()->format('Y-m-d'))
            ->exists();
    }

    /**
     * Get assignment history for a date range.
     */
    public function getAssignmentHistory(Carbon $start, Carbon $end): Collection
    {
        return $this->assignments()
            ->whereBetween('assignment_date', [
                $start->format('Y-m-d'),
                $end->format('Y-m-d')
            ])
            ->with(['route', 'user'])
            ->orderBy('assignment_date', 'desc')
            ->get();
    }

    /**
     * Calculate utilization rate for a date range.
     * Returns percentage of days with assignments.
     */
    public function getUtilizationRate(Carbon $start, Carbon $end): float
    {
        $totalDays = $start->diffInDays($end) + 1;
        
        if ($totalDays <= 0) {
            return 0.0;
        }

        $assignmentCount = $this->activeAssignments()
            ->whereBetween('assignment_date', [
                $start->format('Y-m-d'),
                $end->format('Y-m-d')
            ])
            ->count();

        return round(($assignmentCount / $totalDays) * 100, 2);
    }
}
