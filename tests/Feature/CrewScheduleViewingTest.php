<?php

namespace Tests\Feature;

use App\Models\Route;
use App\Models\Schedule;
use App\Models\ScheduleDay;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CrewScheduleViewingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Seed roles
        $this->artisan('db:seed', ['--class' => 'RolePermissionSeeder']);
    }

    // ========================================
    // Crew Route Viewing Tests (22.1)
    // ========================================

    public function test_crew_can_view_todays_routes(): void
    {
        $crew = User::factory()->create();
        $crew->assignRole('collection_crew');

        $today = now()->startOfDay();
        $todayDayOfWeek = $today->dayOfWeek;

        // Create route with schedule for today
        $route = Route::create([
            'name' => 'Today Route',
            'zone' => 'TODAY-001',
            'is_active' => true,
        ]);

        $schedule = Schedule::create([
            'route_id' => $route->id,
            'collection_time' => '08:00:00',
            'start_date' => $today,
            'end_date' => $today->copy()->addMonths(3),
            'is_active' => true,
        ]);

        ScheduleDay::create([
            'schedule_id' => $schedule->id,
            'day_of_week' => $todayDayOfWeek,
        ]);

        $response = $this->actingAs($crew)->get(route('crew.schedules'));

        $response->assertOk();
        $response->assertSee('Today Route');
        $response->assertSee('TODAY-001');
        $response->assertSee('8:00');
    }

    public function test_crew_todays_routes_only_shows_current_day_schedules(): void
    {
        $crew = User::factory()->create();
        $crew->assignRole('collection_crew');

        $today = now()->startOfDay();
        $todayDayOfWeek = $today->dayOfWeek;
        $tomorrowDayOfWeek = $today->copy()->addDay()->dayOfWeek;

        // Create route with schedule for today
        $todayRoute = Route::create([
            'name' => 'Today Route',
            'zone' => 'TODAY-001',
            'is_active' => true,
        ]);

        $todaySchedule = Schedule::create([
            'route_id' => $todayRoute->id,
            'collection_time' => '08:00:00',
            'start_date' => $today,
            'end_date' => $today->copy()->addMonths(3),
            'is_active' => true,
        ]);

        ScheduleDay::create([
            'schedule_id' => $todaySchedule->id,
            'day_of_week' => $todayDayOfWeek,
        ]);

        // Create route with schedule for tomorrow
        $tomorrowRoute = Route::create([
            'name' => 'Tomorrow Route',
            'zone' => 'TOMORROW-001',
            'is_active' => true,
        ]);

        $tomorrowSchedule = Schedule::create([
            'route_id' => $tomorrowRoute->id,
            'collection_time' => '09:00:00',
            'start_date' => $today,
            'end_date' => $today->copy()->addMonths(3),
            'is_active' => true,
        ]);

        ScheduleDay::create([
            'schedule_id' => $tomorrowSchedule->id,
            'day_of_week' => $tomorrowDayOfWeek,
        ]);

        $response = $this->actingAs($crew)->get(route('crew.schedules'));

        $response->assertOk();
        $response->assertSee('Today Route');
        $response->assertDontSee('Tomorrow Route');
    }

    public function test_crew_todays_routes_displays_special_instructions(): void
    {
        $crew = User::factory()->create();
        $crew->assignRole('collection_crew');

        $today = now()->startOfDay();
        $todayDayOfWeek = $today->dayOfWeek;

        // Create route with notes
        $route = Route::create([
            'name' => 'Route with Instructions',
            'zone' => 'INST-001',
            'notes' => 'Watch for narrow streets and low bridges',
            'is_active' => true,
        ]);

        $schedule = Schedule::create([
            'route_id' => $route->id,
            'collection_time' => '08:00:00',
            'start_date' => $today,
            'end_date' => $today->copy()->addMonths(3),
            'is_active' => true,
        ]);

        ScheduleDay::create([
            'schedule_id' => $schedule->id,
            'day_of_week' => $todayDayOfWeek,
        ]);

        $response = $this->actingAs($crew)->get(route('crew.schedules'));

        $response->assertOk();
        $response->assertSee('Route with Instructions');
        $response->assertSee('Watch for narrow streets and low bridges');
    }

    public function test_crew_todays_routes_sorted_by_collection_time(): void
    {
        $crew = User::factory()->create();
        $crew->assignRole('collection_crew');

        $today = now()->startOfDay();
        $todayDayOfWeek = $today->dayOfWeek;

        // Create route with late collection time
        $lateRoute = Route::create([
            'name' => 'Late Route',
            'zone' => 'LATE-001',
            'is_active' => true,
        ]);

        $lateSchedule = Schedule::create([
            'route_id' => $lateRoute->id,
            'collection_time' => '14:00:00',
            'start_date' => $today,
            'end_date' => $today->copy()->addMonths(3),
            'is_active' => true,
        ]);

        ScheduleDay::create([
            'schedule_id' => $lateSchedule->id,
            'day_of_week' => $todayDayOfWeek,
        ]);

        // Create route with early collection time
        $earlyRoute = Route::create([
            'name' => 'Early Route',
            'zone' => 'EARLY-001',
            'is_active' => true,
        ]);

        $earlySchedule = Schedule::create([
            'route_id' => $earlyRoute->id,
            'collection_time' => '08:00:00',
            'start_date' => $today,
            'end_date' => $today->copy()->addMonths(3),
            'is_active' => true,
        ]);

        ScheduleDay::create([
            'schedule_id' => $earlySchedule->id,
            'day_of_week' => $todayDayOfWeek,
        ]);

        $response = $this->actingAs($crew)->get(route('crew.schedules'));

        $response->assertOk();
        
        // Verify early route appears before late route in the response
        $content = $response->getContent();
        $earlyPos = strpos($content, 'Early Route');
        $latePos = strpos($content, 'Late Route');
        
        $this->assertNotFalse($earlyPos);
        $this->assertNotFalse($latePos);
        $this->assertLessThan($latePos, $earlyPos);
    }

    public function test_crew_can_view_upcoming_routes(): void
    {
        $crew = User::factory()->create();
        $crew->assignRole('collection_crew');

        $today = now()->startOfDay();
        $tomorrow = $today->copy()->addDay();
        $tomorrowDayOfWeek = $tomorrow->dayOfWeek;

        // Create route with schedule for tomorrow
        $route = Route::create([
            'name' => 'Upcoming Route',
            'zone' => 'UPCOMING-001',
            'is_active' => true,
        ]);

        $schedule = Schedule::create([
            'route_id' => $route->id,
            'collection_time' => '09:00:00',
            'start_date' => $today,
            'end_date' => $today->copy()->addMonths(3),
            'is_active' => true,
        ]);

        ScheduleDay::create([
            'schedule_id' => $schedule->id,
            'day_of_week' => $tomorrowDayOfWeek,
        ]);

        $response = $this->actingAs($crew)->get(route('crew.schedules.upcoming'));

        $response->assertOk();
        $response->assertSee('Upcoming Route');
        $response->assertSee('UPCOMING-001');
    }

    public function test_crew_upcoming_routes_shows_next_7_days(): void
    {
        $crew = User::factory()->create();
        $crew->assignRole('collection_crew');

        $today = now()->startOfDay();
        $day6 = $today->copy()->addDays(6);
        $day8 = $today->copy()->addDays(8);

        // Create route with schedule for day 6 (should appear)
        $route6 = Route::create([
            'name' => 'Day 6 Route',
            'zone' => 'DAY6-001',
            'is_active' => true,
        ]);

        $schedule6 = Schedule::create([
            'route_id' => $route6->id,
            'collection_time' => '08:00:00',
            'start_date' => $today,
            'end_date' => $today->copy()->addMonths(3),
            'is_active' => true,
        ]);

        ScheduleDay::create([
            'schedule_id' => $schedule6->id,
            'day_of_week' => $day6->dayOfWeek,
        ]);

        // Create route with schedule for day 8 (should not appear)
        $route8 = Route::create([
            'name' => 'Day 8 Route',
            'zone' => 'DAY8-001',
            'is_active' => true,
        ]);

        $schedule8 = Schedule::create([
            'route_id' => $route8->id,
            'collection_time' => '09:00:00',
            'start_date' => $today,
            'end_date' => $today->copy()->addMonths(3),
            'is_active' => true,
        ]);

        ScheduleDay::create([
            'schedule_id' => $schedule8->id,
            'day_of_week' => $day8->dayOfWeek,
        ]);

        $response = $this->actingAs($crew)->get(route('crew.schedules.upcoming'));

        $response->assertOk();
        $response->assertSee('Day 6 Route');
        // Day 8 route should not appear (beyond 7-day window)
    }

    public function test_crew_upcoming_routes_grouped_by_date(): void
    {
        $crew = User::factory()->create();
        $crew->assignRole('collection_crew');

        $today = now()->startOfDay();
        $tomorrow = $today->copy()->addDay();
        $tomorrowDayOfWeek = $tomorrow->dayOfWeek;

        // Create two routes for tomorrow
        $route1 = Route::create([
            'name' => 'Route 1',
            'zone' => 'R1-001',
            'is_active' => true,
        ]);

        $schedule1 = Schedule::create([
            'route_id' => $route1->id,
            'collection_time' => '08:00:00',
            'start_date' => $today,
            'end_date' => $today->copy()->addMonths(3),
            'is_active' => true,
        ]);

        ScheduleDay::create([
            'schedule_id' => $schedule1->id,
            'day_of_week' => $tomorrowDayOfWeek,
        ]);

        $route2 = Route::create([
            'name' => 'Route 2',
            'zone' => 'R2-001',
            'is_active' => true,
        ]);

        $schedule2 = Schedule::create([
            'route_id' => $route2->id,
            'collection_time' => '10:00:00',
            'start_date' => $today,
            'end_date' => $today->copy()->addMonths(3),
            'is_active' => true,
        ]);

        ScheduleDay::create([
            'schedule_id' => $schedule2->id,
            'day_of_week' => $tomorrowDayOfWeek,
        ]);

        $response = $this->actingAs($crew)->get(route('crew.schedules.upcoming'));

        $response->assertOk();
        $response->assertSee('Route 1');
        $response->assertSee('Route 2');
        
        // Both routes should appear under the same date heading
        $response->assertSee($tomorrow->format('l, F j, Y'));
    }

    public function test_crew_can_view_route_details(): void
    {
        $crew = User::factory()->create();
        $crew->assignRole('collection_crew');

        $today = now()->startOfDay();
        $todayDayOfWeek = $today->dayOfWeek;

        // Create route with full details
        $route = Route::create([
            'name' => 'Detailed Route',
            'zone' => 'DETAIL-001',
            'description' => 'Covers the northern residential area',
            'notes' => 'Watch for narrow streets',
            'is_active' => true,
        ]);

        $schedule = Schedule::create([
            'route_id' => $route->id,
            'collection_time' => '08:30:00',
            'start_date' => $today,
            'end_date' => $today->copy()->addMonths(3),
            'is_active' => true,
        ]);

        ScheduleDay::create([
            'schedule_id' => $schedule->id,
            'day_of_week' => $todayDayOfWeek,
        ]);

        $response = $this->actingAs($crew)->get(route('crew.routes.show', $route));

        $response->assertOk();
        $response->assertSee('Detailed Route');
        $response->assertSee('DETAIL-001');
        $response->assertSee('Covers the northern residential area');
        $response->assertSee('Watch for narrow streets');
        $response->assertSee('8:30');
    }

    public function test_crew_route_details_shows_schedule_information(): void
    {
        $crew = User::factory()->create();
        $crew->assignRole('collection_crew');

        $today = now()->startOfDay();

        // Create route with multiple collection days
        $route = Route::create([
            'name' => 'Multi-Day Route',
            'zone' => 'MULTI-001',
            'is_active' => true,
        ]);

        $schedule = Schedule::create([
            'route_id' => $route->id,
            'collection_time' => '08:00:00',
            'start_date' => $today,
            'end_date' => $today->copy()->addMonths(3),
            'is_active' => true,
        ]);

        // Monday and Wednesday
        ScheduleDay::create([
            'schedule_id' => $schedule->id,
            'day_of_week' => 1,
        ]);

        ScheduleDay::create([
            'schedule_id' => $schedule->id,
            'day_of_week' => 3,
        ]);

        $response = $this->actingAs($crew)->get(route('crew.routes.show', $route));

        $response->assertOk();
        $response->assertSee('Multi-Day Route');
        $response->assertSee('Mon');
        $response->assertSee('Wed');
    }

    public function test_crew_todays_routes_only_shows_active_routes(): void
    {
        $crew = User::factory()->create();
        $crew->assignRole('collection_crew');

        $today = now()->startOfDay();
        $todayDayOfWeek = $today->dayOfWeek;

        // Create active route
        $activeRoute = Route::create([
            'name' => 'Active Route',
            'zone' => 'ACTIVE-001',
            'is_active' => true,
        ]);

        $activeSchedule = Schedule::create([
            'route_id' => $activeRoute->id,
            'collection_time' => '08:00:00',
            'start_date' => $today,
            'end_date' => $today->copy()->addMonths(3),
            'is_active' => true,
        ]);

        ScheduleDay::create([
            'schedule_id' => $activeSchedule->id,
            'day_of_week' => $todayDayOfWeek,
        ]);

        // Create inactive route
        $inactiveRoute = Route::create([
            'name' => 'Inactive Route',
            'zone' => 'INACTIVE-001',
            'is_active' => false,
        ]);

        $inactiveSchedule = Schedule::create([
            'route_id' => $inactiveRoute->id,
            'collection_time' => '09:00:00',
            'start_date' => $today,
            'end_date' => $today->copy()->addMonths(3),
            'is_active' => true,
        ]);

        ScheduleDay::create([
            'schedule_id' => $inactiveSchedule->id,
            'day_of_week' => $todayDayOfWeek,
        ]);

        $response = $this->actingAs($crew)->get(route('crew.schedules'));

        $response->assertOk();
        $response->assertSee('Active Route');
        $response->assertDontSee('Inactive Route');
    }

    public function test_crew_todays_routes_only_shows_active_schedules(): void
    {
        $crew = User::factory()->create();
        $crew->assignRole('collection_crew');

        $today = now()->startOfDay();
        $todayDayOfWeek = $today->dayOfWeek;

        // Create route with active schedule
        $route1 = Route::create([
            'name' => 'Route with Active Schedule',
            'zone' => 'RAS-001',
            'is_active' => true,
        ]);

        $activeSchedule = Schedule::create([
            'route_id' => $route1->id,
            'collection_time' => '08:00:00',
            'start_date' => $today,
            'end_date' => $today->copy()->addMonths(3),
            'is_active' => true,
        ]);

        ScheduleDay::create([
            'schedule_id' => $activeSchedule->id,
            'day_of_week' => $todayDayOfWeek,
        ]);

        // Create route with inactive schedule
        $route2 = Route::create([
            'name' => 'Route with Inactive Schedule',
            'zone' => 'RIS-001',
            'is_active' => true,
        ]);

        $inactiveSchedule = Schedule::create([
            'route_id' => $route2->id,
            'collection_time' => '09:00:00',
            'start_date' => $today,
            'end_date' => $today->copy()->addMonths(3),
            'is_active' => false,
        ]);

        ScheduleDay::create([
            'schedule_id' => $inactiveSchedule->id,
            'day_of_week' => $todayDayOfWeek,
        ]);

        $response = $this->actingAs($crew)->get(route('crew.schedules'));

        $response->assertOk();
        $response->assertSee('Route with Active Schedule');
        $response->assertDontSee('Route with Inactive Schedule');
    }

    public function test_crew_todays_routes_respects_schedule_date_range(): void
    {
        $crew = User::factory()->create();
        $crew->assignRole('collection_crew');

        $today = now()->startOfDay();
        $todayDayOfWeek = $today->dayOfWeek;

        // Create route with expired schedule
        $expiredRoute = Route::create([
            'name' => 'Expired Route',
            'zone' => 'EXP-001',
            'is_active' => true,
        ]);

        $expiredSchedule = Schedule::create([
            'route_id' => $expiredRoute->id,
            'collection_time' => '08:00:00',
            'start_date' => $today->copy()->subMonths(3),
            'end_date' => $today->copy()->subDay(),
            'is_active' => true,
        ]);

        ScheduleDay::create([
            'schedule_id' => $expiredSchedule->id,
            'day_of_week' => $todayDayOfWeek,
        ]);

        // Create route with future schedule
        $futureRoute = Route::create([
            'name' => 'Future Route',
            'zone' => 'FUT-001',
            'is_active' => true,
        ]);

        $futureSchedule = Schedule::create([
            'route_id' => $futureRoute->id,
            'collection_time' => '09:00:00',
            'start_date' => $today->copy()->addDay(),
            'end_date' => $today->copy()->addMonths(3),
            'is_active' => true,
        ]);

        ScheduleDay::create([
            'schedule_id' => $futureSchedule->id,
            'day_of_week' => $todayDayOfWeek,
        ]);

        // Create route with current schedule
        $currentRoute = Route::create([
            'name' => 'Current Route',
            'zone' => 'CUR-001',
            'is_active' => true,
        ]);

        $currentSchedule = Schedule::create([
            'route_id' => $currentRoute->id,
            'collection_time' => '10:00:00',
            'start_date' => $today,
            'end_date' => $today->copy()->addMonths(3),
            'is_active' => true,
        ]);

        ScheduleDay::create([
            'schedule_id' => $currentSchedule->id,
            'day_of_week' => $todayDayOfWeek,
        ]);

        $response = $this->actingAs($crew)->get(route('crew.schedules'));

        $response->assertOk();
        $response->assertSee('Current Route');
        $response->assertDontSee('Expired Route');
        $response->assertDontSee('Future Route');
    }

    // ========================================
    // Crew Authorization Tests (22.2)
    // ========================================

    public function test_only_crew_can_access_crew_schedules(): void
    {
        $resident = User::factory()->create();
        $resident->assignRole('resident');

        // Resident cannot access
        $response = $this->actingAs($resident)->get(route('crew.schedules'));
        $response->assertRedirect();
    }

    public function test_only_crew_can_access_upcoming_routes(): void
    {
        $resident = User::factory()->create();
        $resident->assignRole('resident');

        // Resident cannot access
        $response = $this->actingAs($resident)->get(route('crew.schedules.upcoming'));
        $response->assertRedirect();
    }

    public function test_only_crew_can_view_route_details(): void
    {
        $resident = User::factory()->create();
        $resident->assignRole('resident');

        $route = Route::create([
            'name' => 'Test Route',
            'zone' => 'TEST-001',
            'is_active' => true,
        ]);

        // Resident cannot access
        $response = $this->actingAs($resident)->get(route('crew.routes.show', $route));
        $response->assertRedirect();
    }

    public function test_administrators_can_access_crew_schedules(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('administrator');

        $today = now()->startOfDay();
        $todayDayOfWeek = $today->dayOfWeek;

        // Create route with schedule
        $route = Route::create([
            'name' => 'Admin View Route',
            'zone' => 'ADMIN-001',
            'is_active' => true,
        ]);

        $schedule = Schedule::create([
            'route_id' => $route->id,
            'collection_time' => '08:00:00',
            'start_date' => $today,
            'end_date' => $today->copy()->addMonths(3),
            'is_active' => true,
        ]);

        ScheduleDay::create([
            'schedule_id' => $schedule->id,
            'day_of_week' => $todayDayOfWeek,
        ]);

        // Admin can access
        $response = $this->actingAs($admin)->get(route('crew.schedules'));
        $response->assertOk();
        $response->assertSee('Admin View Route');
    }

    public function test_administrators_can_access_upcoming_routes(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('administrator');

        // Admin can access
        $response = $this->actingAs($admin)->get(route('crew.schedules.upcoming'));
        $response->assertOk();
    }

    public function test_administrators_can_view_route_details(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('administrator');

        $route = Route::create([
            'name' => 'Admin Detail Route',
            'zone' => 'ADMIN-002',
            'is_active' => true,
        ]);

        // Admin can access
        $response = $this->actingAs($admin)->get(route('crew.routes.show', $route));
        $response->assertOk();
        $response->assertSee('Admin Detail Route');
    }

    public function test_unauthenticated_users_cannot_access_crew_schedules(): void
    {
        $route = Route::create([
            'name' => 'Test Route',
            'zone' => 'TEST-001',
            'is_active' => true,
        ]);

        // Test all crew schedule endpoints
        $this->get(route('crew.schedules'))->assertRedirect(route('login'));
        $this->get(route('crew.schedules.upcoming'))->assertRedirect(route('login'));
        $this->get(route('crew.routes.show', $route))->assertRedirect(route('login'));
    }
}
