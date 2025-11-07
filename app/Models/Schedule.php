<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Collection;
use Carbon\Carbon;

class Schedule extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'route_id',
        'collection_time',
        'start_date',
        'end_date',
        'is_active',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'collection_time' => 'datetime:H:i',
            'start_date' => 'date',
            'end_date' => 'date',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Get the route that owns the schedule.
     */
    public function route(): BelongsTo
    {
        return $this->belongsTo(Route::class);
    }

    /**
     * Get the schedule days for the schedule.
     */
    public function scheduleDays(): HasMany
    {
        return $this->hasMany(ScheduleDay::class);
    }

    /**
     * Get array of days of week for this schedule.
     */
    public function getDaysOfWeek(): array
    {
        return $this->scheduleDays->pluck('day_of_week')->toArray();
    }

    /**
     * Check if the schedule is active on a given date.
     */
    public function isActiveOn(Carbon $date): bool
    {
        // Check if schedule is active
        if (!$this->is_active) {
            return false;
        }

        // Check if date is within schedule range
        if ($date->lt($this->start_date)) {
            return false;
        }

        if ($this->end_date && $date->gt($this->end_date)) {
            return false;
        }

        // Check if the day of week matches
        $dayOfWeek = $date->dayOfWeek;
        $daysOfWeek = $this->getDaysOfWeek();

        return in_array($dayOfWeek, $daysOfWeek);
    }

    /**
     * Get collection dates in a given range.
     */
    public function getCollectionDatesInRange(Carbon $start, Carbon $end): Collection
    {
        $dates = collect();
        $current = $start->copy()->startOfDay();
        $endDate = $end->copy()->startOfDay();

        while ($current->lte($endDate)) {
            if ($this->isActiveOn($current)) {
                $dates->push($current->copy());
            }
            $current->addDay();
        }

        return $dates;
    }

    /**
     * Check if this schedule has a conflict with another schedule.
     */
    public function hasConflictWith(Schedule $other): bool
    {
        // Must be on the same route
        if ($this->route_id !== $other->route_id) {
            return false;
        }

        // Check if date ranges overlap
        $thisStart = $this->start_date;
        $thisEnd = $this->end_date ?? Carbon::parse('2099-12-31');
        $otherStart = $other->start_date;
        $otherEnd = $other->end_date ?? Carbon::parse('2099-12-31');

        $datesOverlap = $thisStart->lte($otherEnd) && $otherStart->lte($thisEnd);

        if (!$datesOverlap) {
            return false;
        }

        // Check if any days of week overlap
        $thisDays = $this->getDaysOfWeek();
        $otherDays = $other->getDaysOfWeek();

        return !empty(array_intersect($thisDays, $otherDays));
    }
}
