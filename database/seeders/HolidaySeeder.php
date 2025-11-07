<?php

namespace Database\Seeders;

use App\Models\Holiday;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class HolidaySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $currentYear = Carbon::now()->year;

        $holidays = [
            // New Year's Day - Collection skipped
            [
                'name' => 'New Year\'s Day',
                'date' => Carbon::create($currentYear, 1, 1),
                'is_collection_skipped' => true,
                'reschedule_date' => null,
            ],
            // Martin Luther King Jr. Day - Collection rescheduled
            [
                'name' => 'Martin Luther King Jr. Day',
                'date' => Carbon::create($currentYear, 1, 15),
                'is_collection_skipped' => false,
                'reschedule_date' => Carbon::create($currentYear, 1, 16),
            ],
            // Memorial Day - Collection rescheduled to next day
            [
                'name' => 'Memorial Day',
                'date' => Carbon::create($currentYear, 5, 27),
                'is_collection_skipped' => false,
                'reschedule_date' => Carbon::create($currentYear, 5, 28),
            ],
            // Independence Day - Collection skipped
            [
                'name' => 'Independence Day',
                'date' => Carbon::create($currentYear, 7, 4),
                'is_collection_skipped' => true,
                'reschedule_date' => null,
            ],
            // Labor Day - Collection rescheduled
            [
                'name' => 'Labor Day',
                'date' => Carbon::create($currentYear, 9, 2),
                'is_collection_skipped' => false,
                'reschedule_date' => Carbon::create($currentYear, 9, 3),
            ],
            // Thanksgiving Day - Collection skipped
            [
                'name' => 'Thanksgiving Day',
                'date' => Carbon::create($currentYear, 11, 28),
                'is_collection_skipped' => true,
                'reschedule_date' => null,
            ],
            // Day after Thanksgiving - Collection rescheduled to Saturday
            [
                'name' => 'Day After Thanksgiving',
                'date' => Carbon::create($currentYear, 11, 29),
                'is_collection_skipped' => false,
                'reschedule_date' => Carbon::create($currentYear, 11, 30),
            ],
            // Christmas Day - Collection skipped
            [
                'name' => 'Christmas Day',
                'date' => Carbon::create($currentYear, 12, 25),
                'is_collection_skipped' => true,
                'reschedule_date' => null,
            ],
            // New Year's Eve - Collection rescheduled to earlier in the day
            [
                'name' => 'New Year\'s Eve',
                'date' => Carbon::create($currentYear, 12, 31),
                'is_collection_skipped' => false,
                'reschedule_date' => Carbon::create($currentYear, 12, 30),
            ],
        ];

        foreach ($holidays as $holiday) {
            Holiday::create($holiday);
        }
    }
}
