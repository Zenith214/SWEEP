<?php

namespace Database\Seeders;

use App\Models\Route;
use App\Models\Schedule;
use App\Models\ScheduleDay;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class ScheduleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get routes by name for easier reference
        $routes = Route::all()->keyBy('name');

        // Schedule 1: North District Route A - Monday and Thursday
        $schedule1 = Schedule::create([
            'route_id' => $routes['North District Route A']->id,
            'collection_time' => '07:00:00',
            'start_date' => Carbon::now()->startOfMonth(),
            'end_date' => null,
            'is_active' => true,
        ]);
        ScheduleDay::create(['schedule_id' => $schedule1->id, 'day_of_week' => 1]); // Monday
        ScheduleDay::create(['schedule_id' => $schedule1->id, 'day_of_week' => 4]); // Thursday

        // Schedule 2: North District Route B - Tuesday and Friday
        $schedule2 = Schedule::create([
            'route_id' => $routes['North District Route B']->id,
            'collection_time' => '08:00:00',
            'start_date' => Carbon::now()->startOfMonth(),
            'end_date' => null,
            'is_active' => true,
        ]);
        ScheduleDay::create(['schedule_id' => $schedule2->id, 'day_of_week' => 2]); // Tuesday
        ScheduleDay::create(['schedule_id' => $schedule2->id, 'day_of_week' => 5]); // Friday

        // Schedule 3: South District Route A - Wednesday only
        $schedule3 = Schedule::create([
            'route_id' => $routes['South District Route A']->id,
            'collection_time' => '09:00:00',
            'start_date' => Carbon::now()->startOfMonth(),
            'end_date' => null,
            'is_active' => true,
        ]);
        ScheduleDay::create(['schedule_id' => $schedule3->id, 'day_of_week' => 3]); // Wednesday

        // Schedule 4: East District Route A - Monday, Wednesday, Friday
        $schedule4 = Schedule::create([
            'route_id' => $routes['East District Route A']->id,
            'collection_time' => '06:30:00',
            'start_date' => Carbon::now()->startOfMonth(),
            'end_date' => null,
            'is_active' => true,
        ]);
        ScheduleDay::create(['schedule_id' => $schedule4->id, 'day_of_week' => 1]); // Monday
        ScheduleDay::create(['schedule_id' => $schedule4->id, 'day_of_week' => 3]); // Wednesday
        ScheduleDay::create(['schedule_id' => $schedule4->id, 'day_of_week' => 5]); // Friday

        // Schedule 5: West District Route A - Tuesday and Thursday
        $schedule5 = Schedule::create([
            'route_id' => $routes['West District Route A']->id,
            'collection_time' => '08:30:00',
            'start_date' => Carbon::now()->startOfMonth(),
            'end_date' => null,
            'is_active' => true,
        ]);
        ScheduleDay::create(['schedule_id' => $schedule5->id, 'day_of_week' => 2]); // Tuesday
        ScheduleDay::create(['schedule_id' => $schedule5->id, 'day_of_week' => 4]); // Thursday

        // Schedule 6: Central District Route A - Every weekday
        $schedule6 = Schedule::create([
            'route_id' => $routes['Central District Route A']->id,
            'collection_time' => '05:30:00',
            'start_date' => Carbon::now()->startOfMonth(),
            'end_date' => null,
            'is_active' => true,
        ]);
        ScheduleDay::create(['schedule_id' => $schedule6->id, 'day_of_week' => 1]); // Monday
        ScheduleDay::create(['schedule_id' => $schedule6->id, 'day_of_week' => 2]); // Tuesday
        ScheduleDay::create(['schedule_id' => $schedule6->id, 'day_of_week' => 3]); // Wednesday
        ScheduleDay::create(['schedule_id' => $schedule6->id, 'day_of_week' => 4]); // Thursday
        ScheduleDay::create(['schedule_id' => $schedule6->id, 'day_of_week' => 5]); // Friday

        // Schedule 7: East District Route B - Inactive schedule (route is also inactive)
        $schedule7 = Schedule::create([
            'route_id' => $routes['East District Route B']->id,
            'collection_time' => '10:00:00',
            'start_date' => Carbon::now()->subMonths(2)->startOfMonth(),
            'end_date' => Carbon::now()->subMonth()->endOfMonth(),
            'is_active' => false,
        ]);
        ScheduleDay::create(['schedule_id' => $schedule7->id, 'day_of_week' => 2]); // Tuesday

        // Schedule 8: Special Events Route - Temporary schedule with end date
        $schedule8 = Schedule::create([
            'route_id' => $routes['Special Events Route']->id,
            'collection_time' => '14:00:00',
            'start_date' => Carbon::now()->addWeek(),
            'end_date' => Carbon::now()->addWeeks(2),
            'is_active' => true,
        ]);
        ScheduleDay::create(['schedule_id' => $schedule8->id, 'day_of_week' => 6]); // Saturday

        // Note: Central District Route B and Rural Route A are intentionally left without schedules
        // for testing routes without schedules functionality
    }
}
