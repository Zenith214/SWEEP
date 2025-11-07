<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Carbon\Carbon;

class Holiday extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'name',
        'date',
        'is_collection_skipped',
        'reschedule_date',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'date' => 'date',
            'reschedule_date' => 'date',
            'is_collection_skipped' => 'boolean',
        ];
    }

    /**
     * Check if a given date is a holiday.
     */
    public static function isHoliday(Carbon $date): bool
    {
        return self::whereDate('date', $date->format('Y-m-d'))->exists();
    }

    /**
     * Get the rescheduled date for a holiday, if any.
     */
    public static function getRescheduledDate(Carbon $date): ?Carbon
    {
        $holiday = self::whereDate('date', $date->format('Y-m-d'))->first();

        if (!$holiday || $holiday->is_collection_skipped) {
            return null;
        }

        return $holiday->reschedule_date;
    }

    /**
     * Get all holidays in a given date range.
     */
    public static function getHolidaysInRange(Carbon $start, Carbon $end): Collection
    {
        return self::whereDate('date', '>=', $start->format('Y-m-d'))
            ->whereDate('date', '<=', $end->format('Y-m-d'))
            ->get();
    }
}
