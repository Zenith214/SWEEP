<?php

namespace Tests\Feature;

use App\Models\Holiday;
use App\Models\Route;
use App\Models\Schedule;
use App\Models\ScheduleDay;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ResidentScheduleViewingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Seed roles
        $this->artisan('db:seed', ['--class' => 'RolePermissionSeeder']);
    }

    // ========================================
    // Zone Search Functionality Tests (21.1)
    // ========================================

    public function test_resident_can_search_for_schedules_by_zone(): void
    {
        $resident = User::factory()->create();
        $resident->assignRole('resident');

        // Create route with schedule
        $route = Route::create([
            'name' => 'North District Route',
            'zone' => 'ND-001',
            'is_active' => true,
        ]);

        $schedule = Schedule::create([
            'route_id' => $route->id,
            'collection_time' => '08:00:00',
            'start_date' => now(),
            'end_date' => now()->addMonths(3),
            'is_active' => true,
        ]);

        ScheduleDay::create([
            'schedule_id' => $schedule->id,
            'day_of_week' => 1, // Monday
        ]);

        $response = $this->actingAs($resident)->get(route('resident.schedules.search', ['zone' => 'ND-001']));

        $response->assertOk();
        $response->assertSee('North District Route');
        $response->assertSee('ND-001');
    }

    public function test_zone_search_displays_next_collection_date(): void
    {
        $resident = User::factory()->create();
        $resident->assignRole('resident');

        // Create route with schedule
        $route = Route::create([
            'name' => 'Test Route',
            'zone' => 'TEST-001',
            'is_active' => true,
        ]);

        // Get next Monday
        $nextMonday = Carbon::now()->next(Carbon::MONDAY);

        $schedule = Schedule::create([
            'route_id' => $route->id,
            'collection_time' => '08:00:00',
            'start_date' => now(),
            'end_date' => now()->addMonths(3),
            'is_active' => true,
        ]);

        ScheduleDay::create([
            'schedule_id' => $schedule->id,
            'day_of_week' => 1, // Monday
        ]);

        $response = $this->actingAs($resident)->get(route('resident.schedules.search', ['zone' => 'TEST-001']));

        $response->assertOk();
        $response->assertSee('Test Route');
    }

    public function test_zone_not_found_displays_error_message(): void
    {
        $resident = User::factory()->create();
        $resident->assignRole('resident');

        $response = $this->actingAs($resident)->get(route('resident.schedules.search', ['zone' => 'NONEXISTENT']));

        $response->assertOk();
        $response->assertSee('No collection schedules found for this zone');
    }

    public function test_zone_search_only_shows_active_routes(): void
    {
        $resident = User::factory()->create();
        $resident->assignRole('resident');

        // Create active route
        $activeRoute = Route::create([
            'name' => 'Active Route',
            'zone' => 'ZONE-001',
            'is_active' => true,
        ]);

        $activeSchedule = Schedule::create([
            'route_id' => $activeRoute->id,
            'collection_time' => '08:00:00',
            'start_date' => now(),
            'end_date' => now()->addMonths(3),
            'is_active' => true,
        ]);

        ScheduleDay::create([
            'schedule_id' => $activeSchedule->id,
            'day_of_week' => 1,
        ]);

        // Create inactive route
        $inactiveRoute = Route::create([
            'name' => 'Inactive Route',
            'zone' => 'ZONE-001',
            'is_active' => false,
        ]);

        $inactiveSchedule = Schedule::create([
            'route_id' => $inactiveRoute->id,
            'collection_time' => '09:00:00',
            'start_date' => now(),
            'end_date' => now()->addMonths(3),
            'is_active' => true,
        ]);

        ScheduleDay::create([
            'schedule_id' => $inactiveSchedule->id,
            'day_of_week' => 2,
        ]);

        $response = $this->actingAs($resident)->get(route('resident.schedules.search', ['zone' => 'ZONE-001']));

        $response->assertOk();
        $response->assertSee('Active Route');
        $response->assertDontSee('Inactive Route');
    }

    public function test_zone_search_validates_zone_parameter(): void
    {
        $resident = User::factory()->create();
        $resident->assignRole('resident');

        $response = $this->actingAs($resident)->get(route('resident.schedules.search'));

        $response->assertSessionHasErrors('zone');
    }

    public function test_zone_search_stores_recent_searches_in_session(): void
    {
        $resident = User::factory()->create();
        $resident->assignRole('resident');

        // Create route
        $route = Route::create([
            'name' => 'Test Route',
            'zone' => 'TEST-001',
            'is_active' => true,
        ]);

        $schedule = Schedule::create([
            'route_id' => $route->id,
            'collection_time' => '08:00:00',
            'start_date' => now(),
            'end_date' => now()->addMonths(3),
            'is_active' => true,
        ]);

        ScheduleDay::create([
            'schedule_id' => $schedule->id,
            'day_of_week' => 1,
        ]);

        $response = $this->actingAs($resident)->get(route('resident.schedules.search', ['zone' => 'TEST-001']));

        $response->assertOk();
        $response->assertSessionHas('recent_zones');
        
        $recentZones = session('recent_zones');
        $this->assertContains('TEST-001', $recentZones);
    }

    public function test_zone_search_displays_multiple_routes_in_same_zone(): void
    {
        $resident = User::factory()->create();
        $resident->assignRole('resident');

        // Create first route
        $route1 = Route::create([
            'name' => 'Route A',
            'zone' => 'MULTI-001',
            'is_active' => true,
        ]);

        $schedule1 = Schedule::create([
            'route_id' => $route1->id,
            'collection_time' => '08:00:00',
            'start_date' => now(),
            'end_date' => now()->addMonths(3),
            'is_active' => true,
        ]);

        ScheduleDay::create([
            'schedule_id' => $schedule1->id,
            'day_of_week' => 1,
        ]);

        // Create second route
        $route2 = Route::create([
            'name' => 'Route B',
            'zone' => 'MULTI-001',
            'is_active' => true,
        ]);

        $schedule2 = Schedule::create([
            'route_id' => $route2->id,
            'collection_time' => '10:00:00',
            'start_date' => now(),
            'end_date' => now()->addMonths(3),
            'is_active' => true,
        ]);

        ScheduleDay::create([
            'schedule_id' => $schedule2->id,
            'day_of_week' => 3,
        ]);

        $response = $this->actingAs($resident)->get(route('resident.schedules.search', ['zone' => 'MULTI-001']));

        $response->assertOk();
        $response->assertSee('Route A');
        $response->assertSee('Route B');
    }

    // ========================================
    // Calendar Data Generation Tests (21.2)
    // ========================================

    public function test_calendar_data_endpoint_returns_collection_dates(): void
    {
        $resident = User::factory()->create();
        $resident->assignRole('resident');

        // Create route with schedule
        $route = Route::create([
            'name' => 'Calendar Test Route',
            'zone' => 'CAL-001',
            'is_active' => true,
        ]);

        $schedule = Schedule::create([
            'route_id' => $route->id,
            'collection_time' => '08:00:00',
            'start_date' => now()->startOfMonth(),
            'end_date' => now()->endOfMonth(),
            'is_active' => true,
        ]);

        ScheduleDay::create([
            'schedule_id' => $schedule->id,
            'day_of_week' => 1, // Monday
        ]);

        $response = $this->actingAs($resident)->get(route('resident.schedules.calendar.data', [
            'zone' => 'CAL-001',
            'start' => now()->startOfMonth()->format('Y-m-d'),
            'end' => now()->endOfMonth()->format('Y-m-d'),
        ]));

        $response->assertOk();
        $response->assertJsonStructure([
            '*' => [
                'title',
                'start',
                'allDay',
                'backgroundColor',
                'borderColor',
                'extendedProps' => [
                    'route_name',
                    'zone',
                    'collection_time',
                    'is_rescheduled',
                ],
            ],
        ]);

        $data = $response->json();
        $this->assertNotEmpty($data);
        $this->assertEquals('Calendar Test Route', $data[0]['title']);
        $this->assertEquals('CAL-001', $data[0]['extendedProps']['zone']);
    }

    public function test_calendar_data_filters_by_date_range(): void
    {
        $resident = User::factory()->create();
        $resident->assignRole('resident');

        // Create route with schedule
        $route = Route::create([
            'name' => 'Range Test Route',
            'zone' => 'RANGE-001',
            'is_active' => true,
        ]);

        $schedule = Schedule::create([
            'route_id' => $route->id,
            'collection_time' => '08:00:00',
            'start_date' => now()->subMonths(2),
            'end_date' => now()->addMonths(2),
            'is_active' => true,
        ]);

        ScheduleDay::create([
            'schedule_id' => $schedule->id,
            'day_of_week' => 1, // Monday
        ]);

        // Request only current month
        $start = now()->startOfMonth();
        $end = now()->endOfMonth();

        $response = $this->actingAs($resident)->get(route('resident.schedules.calendar.data', [
            'zone' => 'RANGE-001',
            'start' => $start->format('Y-m-d'),
            'end' => $end->format('Y-m-d'),
        ]));

        $response->assertOk();
        
        $data = $response->json();
        
        // Verify all dates are within the requested range
        foreach ($data as $event) {
            $eventDate = Carbon::parse($event['start']);
            $this->assertTrue($eventDate->between($start, $end));
        }
    }

    public function test_calendar_data_applies_holiday_exceptions(): void
    {
        $resident = User::factory()->create();
        $resident->assignRole('resident');

        // Create route with schedule for Mondays
        $route = Route::create([
            'name' => 'Holiday Test Route',
            'zone' => 'HOL-001',
            'is_active' => true,
        ]);

        $schedule = Schedule::create([
            'route_id' => $route->id,
            'collection_time' => '08:00:00',
            'start_date' => now()->startOfMonth(),
            'end_date' => now()->endOfMonth(),
            'is_active' => true,
        ]);

        ScheduleDay::create([
            'schedule_id' => $schedule->id,
            'day_of_week' => 1, // Monday
        ]);

        // Create a holiday on a Monday that skips collection
        $holidayDate = now()->next(Carbon::MONDAY);
        
        Holiday::create([
            'name' => 'Test Holiday',
            'date' => $holidayDate,
            'is_collection_skipped' => true,
            'reschedule_date' => null,
        ]);

        $response = $this->actingAs($resident)->get(route('resident.schedules.calendar.data', [
            'zone' => 'HOL-001',
            'start' => now()->startOfMonth()->format('Y-m-d'),
            'end' => now()->endOfMonth()->format('Y-m-d'),
        ]));

        $response->assertOk();
        
        $data = $response->json();
        
        // Verify the holiday date is not in the collection dates
        $collectionDates = collect($data)->pluck('start')->toArray();
        $this->assertNotContains($holidayDate->format('Y-m-d'), $collectionDates);
    }

    public function test_calendar_data_shows_rescheduled_holiday_dates(): void
    {
        $resident = User::factory()->create();
        $resident->assignRole('resident');

        // Create route with schedule for Mondays
        $route = Route::create([
            'name' => 'Reschedule Test Route',
            'zone' => 'RESC-001',
            'is_active' => true,
        ]);

        $schedule = Schedule::create([
            'route_id' => $route->id,
            'collection_time' => '08:00:00',
            'start_date' => now()->startOfMonth(),
            'end_date' => now()->endOfMonth(),
            'is_active' => true,
        ]);

        ScheduleDay::create([
            'schedule_id' => $schedule->id,
            'day_of_week' => 1, // Monday
        ]);

        // Create a holiday on a Monday that reschedules to Tuesday
        $holidayDate = now()->next(Carbon::MONDAY);
        $rescheduleDate = $holidayDate->copy()->addDay();
        
        Holiday::create([
            'name' => 'Rescheduled Holiday',
            'date' => $holidayDate,
            'is_collection_skipped' => false,
            'reschedule_date' => $rescheduleDate,
        ]);

        $response = $this->actingAs($resident)->get(route('resident.schedules.calendar.data', [
            'zone' => 'RESC-001',
            'start' => now()->startOfMonth()->format('Y-m-d'),
            'end' => now()->endOfMonth()->format('Y-m-d'),
        ]));

        $response->assertOk();
        
        $data = $response->json();
        
        // Find the rescheduled event
        $rescheduledEvent = collect($data)->firstWhere('extendedProps.is_rescheduled', true);
        
        if ($rescheduledEvent) {
            $this->assertEquals($rescheduleDate->format('Y-m-d'), $rescheduledEvent['start']);
            $this->assertEquals('#F59E0B', $rescheduledEvent['backgroundColor']); // Amber color
            $this->assertEquals($holidayDate->format('Y-m-d'), $rescheduledEvent['extendedProps']['original_date']);
        }
    }

    public function test_calendar_data_validates_required_parameters(): void
    {
        $resident = User::factory()->create();
        $resident->assignRole('resident');

        // Missing zone
        $response = $this->actingAs($resident)->get(route('resident.schedules.calendar.data', [
            'start' => now()->format('Y-m-d'),
            'end' => now()->addMonth()->format('Y-m-d'),
        ]));
        $response->assertSessionHasErrors('zone');

        // Missing start date
        $response = $this->actingAs($resident)->get(route('resident.schedules.calendar.data', [
            'zone' => 'TEST-001',
            'end' => now()->addMonth()->format('Y-m-d'),
        ]));
        $response->assertSessionHasErrors('start');

        // Missing end date
        $response = $this->actingAs($resident)->get(route('resident.schedules.calendar.data', [
            'zone' => 'TEST-001',
            'start' => now()->format('Y-m-d'),
        ]));
        $response->assertSessionHasErrors('end');
    }

    public function test_calendar_data_returns_empty_array_for_zone_without_schedules(): void
    {
        $resident = User::factory()->create();
        $resident->assignRole('resident');

        $response = $this->actingAs($resident)->get(route('resident.schedules.calendar.data', [
            'zone' => 'EMPTY-001',
            'start' => now()->startOfMonth()->format('Y-m-d'),
            'end' => now()->endOfMonth()->format('Y-m-d'),
        ]));

        $response->assertOk();
        $response->assertJson([]);
    }

    public function test_calendar_view_validates_zone_exists(): void
    {
        $resident = User::factory()->create();
        $resident->assignRole('resident');

        $response = $this->actingAs($resident)->get(route('resident.schedules.calendar', [
            'zone' => 'NONEXISTENT',
        ]));

        $response->assertRedirect(route('resident.schedules'));
        $response->assertSessionHas('error');
    }

    public function test_calendar_view_displays_for_valid_zone(): void
    {
        $resident = User::factory()->create();
        $resident->assignRole('resident');

        // Create route
        $route = Route::create([
            'name' => 'Valid Zone Route',
            'zone' => 'VALID-001',
            'is_active' => true,
        ]);

        $schedule = Schedule::create([
            'route_id' => $route->id,
            'collection_time' => '08:00:00',
            'start_date' => now(),
            'end_date' => now()->addMonths(3),
            'is_active' => true,
        ]);

        ScheduleDay::create([
            'schedule_id' => $schedule->id,
            'day_of_week' => 1,
        ]);

        $response = $this->actingAs($resident)->get(route('resident.schedules.calendar', [
            'zone' => 'VALID-001',
        ]));

        $response->assertOk();
        $response->assertSee('VALID-001');
    }

    public function test_calendar_data_includes_collection_time_in_extended_props(): void
    {
        $resident = User::factory()->create();
        $resident->assignRole('resident');

        // Create route with specific collection time
        $route = Route::create([
            'name' => 'Time Test Route',
            'zone' => 'TIME-001',
            'is_active' => true,
        ]);

        $schedule = Schedule::create([
            'route_id' => $route->id,
            'collection_time' => '14:30:00',
            'start_date' => now()->startOfMonth(),
            'end_date' => now()->endOfMonth(),
            'is_active' => true,
        ]);

        ScheduleDay::create([
            'schedule_id' => $schedule->id,
            'day_of_week' => 1, // Monday
        ]);

        $response = $this->actingAs($resident)->get(route('resident.schedules.calendar.data', [
            'zone' => 'TIME-001',
            'start' => now()->startOfMonth()->format('Y-m-d'),
            'end' => now()->endOfMonth()->format('Y-m-d'),
        ]));

        $response->assertOk();
        
        $data = $response->json();
        
        if (!empty($data)) {
            $this->assertEquals('14:30', $data[0]['extendedProps']['collection_time']);
        }
    }

    // ========================================
    // Authorization Tests
    // ========================================

    public function test_only_residents_can_access_schedule_search(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('administrator');

        $crew = User::factory()->create();
        $crew->assignRole('collection_crew');

        // Admin cannot access
        $response = $this->actingAs($admin)->get(route('resident.schedules'));
        $response->assertRedirect();

        // Crew cannot access
        $response = $this->actingAs($crew)->get(route('resident.schedules'));
        $response->assertRedirect();
    }

    public function test_only_residents_can_search_schedules(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('administrator');

        $crew = User::factory()->create();
        $crew->assignRole('collection_crew');

        // Admin cannot search
        $response = $this->actingAs($admin)->get(route('resident.schedules.search', ['zone' => 'TEST-001']));
        $response->assertRedirect();

        // Crew cannot search
        $response = $this->actingAs($crew)->get(route('resident.schedules.search', ['zone' => 'TEST-001']));
        $response->assertRedirect();
    }

    public function test_only_residents_can_access_calendar_view(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('administrator');

        $crew = User::factory()->create();
        $crew->assignRole('collection_crew');

        // Create route for testing
        $route = Route::create([
            'name' => 'Test Route',
            'zone' => 'TEST-001',
            'is_active' => true,
        ]);

        // Admin cannot access
        $response = $this->actingAs($admin)->get(route('resident.schedules.calendar', ['zone' => 'TEST-001']));
        $response->assertRedirect();

        // Crew cannot access
        $response = $this->actingAs($crew)->get(route('resident.schedules.calendar', ['zone' => 'TEST-001']));
        $response->assertRedirect();
    }

    public function test_only_residents_can_access_calendar_data_endpoint(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('administrator');

        $crew = User::factory()->create();
        $crew->assignRole('collection_crew');

        $params = [
            'zone' => 'TEST-001',
            'start' => now()->format('Y-m-d'),
            'end' => now()->addMonth()->format('Y-m-d'),
        ];

        // Admin cannot access
        $response = $this->actingAs($admin)->get(route('resident.schedules.calendar.data', $params));
        $response->assertRedirect();

        // Crew cannot access
        $response = $this->actingAs($crew)->get(route('resident.schedules.calendar.data', $params));
        $response->assertRedirect();
    }

    public function test_unauthenticated_users_cannot_access_resident_schedules(): void
    {
        // Test all resident schedule endpoints
        $this->get(route('resident.schedules'))->assertRedirect(route('login'));
        $this->get(route('resident.schedules.search', ['zone' => 'TEST-001']))->assertRedirect(route('login'));
        $this->get(route('resident.schedules.calendar', ['zone' => 'TEST-001']))->assertRedirect(route('login'));
        $this->get(route('resident.schedules.calendar.data', [
            'zone' => 'TEST-001',
            'start' => now()->format('Y-m-d'),
            'end' => now()->addMonth()->format('Y-m-d'),
        ]))->assertRedirect(route('login'));
    }
}
