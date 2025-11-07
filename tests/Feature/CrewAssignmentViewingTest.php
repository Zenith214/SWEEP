<?php

namespace Tests\Feature;

use App\Models\Assignment;
use App\Models\Route;
use App\Models\Schedule;
use App\Models\ScheduleDay;
use App\Models\Truck;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CrewAssignmentViewingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Seed roles
        $this->artisan('db:seed', ['--class' => 'RolePermissionSeeder']);
    }

    // ========================================
    // 23.1 Test crew assignment viewing
    // ========================================

    public function test_crew_can_view_todays_assignment(): void
    {
        $crew = User::factory()->create();
        $crew->assignRole('collection_crew');

        $truck = Truck::factory()->create(['truck_number' => 'T-1001']);
        $route = Route::create([
            'name' => 'North District Route',
            'zone' => 'NORTH-001',
            'is_active' => true,
        ]);

        $today = Carbon::today();

        // Create schedule for the route
        $schedule = Schedule::create([
            'route_id' => $route->id,
            'collection_time' => '08:30:00',
            'start_date' => $today,
            'end_date' => $today->copy()->addMonths(3),
            'is_active' => true,
        ]);

        ScheduleDay::create([
            'schedule_id' => $schedule->id,
            'day_of_week' => $today->dayOfWeek,
        ]);

        // Create today's assignment
        Assignment::factory()->create([
            'truck_id' => $truck->id,
            'user_id' => $crew->id,
            'route_id' => $route->id,
            'assignment_date' => $today,
            'status' => Assignment::STATUS_ACTIVE,
        ]);

        $response = $this->actingAs($crew)->get(route('crew.assignments'));

        $response->assertOk();
        $response->assertSee('T-1001');
        $response->assertSee('North District Route');
        $response->assertSee('NORTH-001');
        $response->assertSee('8:30');
    }

    public function test_crew_todays_assignment_displays_truck_details(): void
    {
        $crew = User::factory()->create();
        $crew->assignRole('collection_crew');

        $truck = Truck::factory()->create([
            'truck_number' => 'T-2002',
            'license_plate' => 'ABC-1234',
            'capacity' => 15.5,
        ]);
        $route = Route::create([
            'name' => 'Test Route',
            'zone' => 'TEST-001',
            'is_active' => true,
        ]);

        $today = Carbon::today();

        // Create assignment
        Assignment::factory()->create([
            'truck_id' => $truck->id,
            'user_id' => $crew->id,
            'route_id' => $route->id,
            'assignment_date' => $today,
            'status' => Assignment::STATUS_ACTIVE,
        ]);

        $response = $this->actingAs($crew)->get(route('crew.assignments'));

        $response->assertOk();
        $response->assertSee('T-2002');
        $response->assertSee('ABC-1234');
    }

    public function test_crew_todays_assignment_displays_collection_time(): void
    {
        $crew = User::factory()->create();
        $crew->assignRole('collection_crew');

        $truck = Truck::factory()->create();
        $route = Route::create([
            'name' => 'Morning Route',
            'zone' => 'MORNING-001',
            'is_active' => true,
        ]);

        $today = Carbon::today();

        // Create schedule with specific collection time
        $schedule = Schedule::create([
            'route_id' => $route->id,
            'collection_time' => '06:00:00',
            'start_date' => $today,
            'end_date' => $today->copy()->addMonths(3),
            'is_active' => true,
        ]);

        ScheduleDay::create([
            'schedule_id' => $schedule->id,
            'day_of_week' => $today->dayOfWeek,
        ]);

        // Create assignment
        Assignment::factory()->create([
            'truck_id' => $truck->id,
            'user_id' => $crew->id,
            'route_id' => $route->id,
            'assignment_date' => $today,
            'status' => Assignment::STATUS_ACTIVE,
        ]);

        $response = $this->actingAs($crew)->get(route('crew.assignments'));

        $response->assertOk();
        $response->assertSee('6:00');
    }

    public function test_crew_todays_assignment_displays_special_instructions_from_assignment_notes(): void
    {
        $crew = User::factory()->create();
        $crew->assignRole('collection_crew');

        $truck = Truck::factory()->create();
        $route = Route::create([
            'name' => 'Test Route',
            'zone' => 'TEST-001',
            'is_active' => true,
        ]);

        $today = Carbon::today();

        // Create assignment with notes
        Assignment::factory()->create([
            'truck_id' => $truck->id,
            'user_id' => $crew->id,
            'route_id' => $route->id,
            'assignment_date' => $today,
            'status' => Assignment::STATUS_ACTIVE,
            'notes' => 'Watch for road construction on Main Street',
        ]);

        $response = $this->actingAs($crew)->get(route('crew.assignments'));

        $response->assertOk();
        $response->assertSee('Watch for road construction on Main Street');
    }

    public function test_crew_todays_assignment_displays_special_instructions_from_route_notes(): void
    {
        $crew = User::factory()->create();
        $crew->assignRole('collection_crew');

        $truck = Truck::factory()->create();
        $route = Route::create([
            'name' => 'Test Route',
            'zone' => 'TEST-001',
            'notes' => 'Narrow streets, use caution',
            'is_active' => true,
        ]);

        $today = Carbon::today();

        // Create assignment without notes (should fall back to route notes)
        Assignment::factory()->create([
            'truck_id' => $truck->id,
            'user_id' => $crew->id,
            'route_id' => $route->id,
            'assignment_date' => $today,
            'status' => Assignment::STATUS_ACTIVE,
            'notes' => null, // Explicitly set to null to test fallback to route notes
        ]);

        $response = $this->actingAs($crew)->get(route('crew.assignments'));

        $response->assertOk();
        $response->assertSee('Narrow streets, use caution');
    }

    public function test_crew_sees_no_assignment_message_when_no_assignment_today(): void
    {
        $crew = User::factory()->create();
        $crew->assignRole('collection_crew');

        $response = $this->actingAs($crew)->get(route('crew.assignments'));

        $response->assertOk();
        $response->assertSee('No Assignment Today');
    }

    public function test_crew_only_sees_their_own_todays_assignment(): void
    {
        $crew1 = User::factory()->create();
        $crew1->assignRole('collection_crew');
        $crew2 = User::factory()->create();
        $crew2->assignRole('collection_crew');

        $truck1 = Truck::factory()->create(['truck_number' => 'T-1001']);
        $truck2 = Truck::factory()->create(['truck_number' => 'T-2002']);
        $route1 = Route::create(['name' => 'Route 1', 'zone' => 'R1-001', 'is_active' => true]);
        $route2 = Route::create(['name' => 'Route 2', 'zone' => 'R2-001', 'is_active' => true]);

        $today = Carbon::today();

        // Create assignment for crew1
        Assignment::factory()->create([
            'truck_id' => $truck1->id,
            'user_id' => $crew1->id,
            'route_id' => $route1->id,
            'assignment_date' => $today,
            'status' => Assignment::STATUS_ACTIVE,
        ]);

        // Create assignment for crew2
        Assignment::factory()->create([
            'truck_id' => $truck2->id,
            'user_id' => $crew2->id,
            'route_id' => $route2->id,
            'assignment_date' => $today,
            'status' => Assignment::STATUS_ACTIVE,
        ]);

        // Crew1 should only see their assignment
        $response = $this->actingAs($crew1)->get(route('crew.assignments'));
        $response->assertOk();
        $response->assertSee('T-1001');
        $response->assertSee('Route 1');
        $response->assertDontSee('T-2002');
        $response->assertDontSee('Route 2');
    }

    public function test_crew_can_view_upcoming_assignments(): void
    {
        $crew = User::factory()->create();
        $crew->assignRole('collection_crew');

        $truck = Truck::factory()->create(['truck_number' => 'T-1001']);
        $route = Route::create([
            'name' => 'Future Route',
            'zone' => 'FUTURE-001',
            'is_active' => true,
        ]);

        $tomorrow = Carbon::today()->addDay();

        // Create schedule for the route
        $schedule = Schedule::create([
            'route_id' => $route->id,
            'collection_time' => '09:00:00',
            'start_date' => Carbon::today(),
            'end_date' => Carbon::today()->addMonths(3),
            'is_active' => true,
        ]);

        ScheduleDay::create([
            'schedule_id' => $schedule->id,
            'day_of_week' => $tomorrow->dayOfWeek,
        ]);

        // Create future assignment
        Assignment::factory()->create([
            'truck_id' => $truck->id,
            'user_id' => $crew->id,
            'route_id' => $route->id,
            'assignment_date' => $tomorrow,
            'status' => Assignment::STATUS_ACTIVE,
        ]);

        $response = $this->actingAs($crew)->get(route('crew.assignments.upcoming'));

        $response->assertOk();
        $response->assertSee('T-1001');
        $response->assertSee('Future Route');
        $response->assertSee('FUTURE-001');
    }

    public function test_crew_upcoming_assignments_shows_next_14_days(): void
    {
        $crew = User::factory()->create();
        $crew->assignRole('collection_crew');

        $truck = Truck::factory()->create();
        $route1 = Route::create(['name' => 'Day 10 Route', 'zone' => 'D10-001', 'is_active' => true]);
        $route2 = Route::create(['name' => 'Day 15 Route', 'zone' => 'D15-001', 'is_active' => true]);

        $day10 = Carbon::today()->addDays(10);
        $day15 = Carbon::today()->addDays(15);

        // Create assignment for day 10 (should appear)
        Assignment::factory()->create([
            'truck_id' => $truck->id,
            'user_id' => $crew->id,
            'route_id' => $route1->id,
            'assignment_date' => $day10,
            'status' => Assignment::STATUS_ACTIVE,
        ]);

        // Create assignment for day 15 (should not appear - beyond 14 days)
        Assignment::factory()->create([
            'truck_id' => $truck->id,
            'user_id' => $crew->id,
            'route_id' => $route2->id,
            'assignment_date' => $day15,
            'status' => Assignment::STATUS_ACTIVE,
        ]);

        $response = $this->actingAs($crew)->get(route('crew.assignments.upcoming'));

        $response->assertOk();
        $response->assertSee('Day 10 Route');
        $response->assertDontSee('Day 15 Route');
    }

    public function test_crew_upcoming_assignments_grouped_by_date(): void
    {
        $crew = User::factory()->create();
        $crew->assignRole('collection_crew');

        $truck1 = Truck::factory()->create();
        $truck2 = Truck::factory()->create();
        $route1 = Route::create(['name' => 'Route 1', 'zone' => 'R1-001', 'is_active' => true]);
        $route2 = Route::create(['name' => 'Route 2', 'zone' => 'R2-001', 'is_active' => true]);

        $tomorrow = Carbon::today()->addDay();

        // Create two assignments for the same date (shouldn't happen in practice, but test grouping)
        Assignment::factory()->create([
            'truck_id' => $truck1->id,
            'user_id' => $crew->id,
            'route_id' => $route1->id,
            'assignment_date' => $tomorrow,
            'status' => Assignment::STATUS_ACTIVE,
        ]);

        $response = $this->actingAs($crew)->get(route('crew.assignments.upcoming'));

        $response->assertOk();
        $response->assertSee('Route 1');
        // Should see the date heading
        $response->assertSee($tomorrow->format('l, F j, Y'));
    }

    public function test_crew_upcoming_assignments_displays_collection_time(): void
    {
        $crew = User::factory()->create();
        $crew->assignRole('collection_crew');

        $truck = Truck::factory()->create();
        $route = Route::create([
            'name' => 'Timed Route',
            'zone' => 'TIME-001',
            'is_active' => true,
        ]);

        $tomorrow = Carbon::today()->addDay();

        // Create schedule with collection time
        $schedule = Schedule::create([
            'route_id' => $route->id,
            'collection_time' => '10:30:00',
            'start_date' => Carbon::today(),
            'end_date' => Carbon::today()->addMonths(3),
            'is_active' => true,
        ]);

        ScheduleDay::create([
            'schedule_id' => $schedule->id,
            'day_of_week' => $tomorrow->dayOfWeek,
        ]);

        // Create assignment
        Assignment::factory()->create([
            'truck_id' => $truck->id,
            'user_id' => $crew->id,
            'route_id' => $route->id,
            'assignment_date' => $tomorrow,
            'status' => Assignment::STATUS_ACTIVE,
        ]);

        $response = $this->actingAs($crew)->get(route('crew.assignments.upcoming'));

        $response->assertOk();
        $response->assertSee('10:30');
    }

    public function test_crew_sees_empty_state_when_no_upcoming_assignments(): void
    {
        $crew = User::factory()->create();
        $crew->assignRole('collection_crew');

        $response = $this->actingAs($crew)->get(route('crew.assignments.upcoming'));

        $response->assertOk();
        $response->assertSee('No Upcoming Assignments');
    }

    public function test_crew_only_sees_active_assignments(): void
    {
        $crew = User::factory()->create();
        $crew->assignRole('collection_crew');

        $truck = Truck::factory()->create(['truck_number' => 'T-1001']);
        $route1 = Route::create(['name' => 'Active Route', 'zone' => 'ACT-001', 'is_active' => true]);
        $route2 = Route::create(['name' => 'Cancelled Route', 'zone' => 'CAN-001', 'is_active' => true]);

        $today = Carbon::today();

        // Create active assignment
        Assignment::factory()->create([
            'truck_id' => $truck->id,
            'user_id' => $crew->id,
            'route_id' => $route1->id,
            'assignment_date' => $today,
            'status' => Assignment::STATUS_ACTIVE,
        ]);

        // Create cancelled assignment
        Assignment::factory()->create([
            'truck_id' => $truck->id,
            'user_id' => $crew->id,
            'route_id' => $route2->id,
            'assignment_date' => $today,
            'status' => Assignment::STATUS_CANCELLED,
        ]);

        $response = $this->actingAs($crew)->get(route('crew.assignments'));

        $response->assertOk();
        $response->assertSee('Active Route');
        $response->assertDontSee('Cancelled Route');
    }

    // ========================================
    // 23.2 Test crew authorization
    // ========================================

    public function test_crew_can_only_view_their_own_assignments(): void
    {
        $crew1 = User::factory()->create();
        $crew1->assignRole('collection_crew');
        $crew2 = User::factory()->create();
        $crew2->assignRole('collection_crew');

        $truck = Truck::factory()->create();
        $route = Route::create(['name' => 'Test Route', 'zone' => 'TEST-001', 'is_active' => true]);

        $tomorrow = Carbon::today()->addDay();

        // Create assignment for crew2
        Assignment::factory()->create([
            'truck_id' => $truck->id,
            'user_id' => $crew2->id,
            'route_id' => $route->id,
            'assignment_date' => $tomorrow,
            'status' => Assignment::STATUS_ACTIVE,
        ]);

        // Crew1 should not see crew2's assignment
        $response = $this->actingAs($crew1)->get(route('crew.assignments.upcoming'));

        $response->assertOk();
        $response->assertDontSee('Test Route');
    }

    public function test_crew_cannot_view_other_crew_assignments_on_todays_view(): void
    {
        $crew1 = User::factory()->create();
        $crew1->assignRole('collection_crew');
        $crew2 = User::factory()->create();
        $crew2->assignRole('collection_crew');

        $truck1 = Truck::factory()->create(['truck_number' => 'T-1001']);
        $truck2 = Truck::factory()->create(['truck_number' => 'T-2002']);
        $route1 = Route::create(['name' => 'Crew1 Route', 'zone' => 'C1-001', 'is_active' => true]);
        $route2 = Route::create(['name' => 'Crew2 Route', 'zone' => 'C2-001', 'is_active' => true]);

        $today = Carbon::today();

        // Create assignment for crew1
        Assignment::factory()->create([
            'truck_id' => $truck1->id,
            'user_id' => $crew1->id,
            'route_id' => $route1->id,
            'assignment_date' => $today,
            'status' => Assignment::STATUS_ACTIVE,
        ]);

        // Create assignment for crew2
        Assignment::factory()->create([
            'truck_id' => $truck2->id,
            'user_id' => $crew2->id,
            'route_id' => $route2->id,
            'assignment_date' => $today,
            'status' => Assignment::STATUS_ACTIVE,
        ]);

        // Crew1 should only see their own assignment
        $response = $this->actingAs($crew1)->get(route('crew.assignments'));
        $response->assertOk();
        $response->assertSee('Crew1 Route');
        $response->assertDontSee('Crew2 Route');

        // Crew2 should only see their own assignment
        $response = $this->actingAs($crew2)->get(route('crew.assignments'));
        $response->assertOk();
        $response->assertSee('Crew2 Route');
        $response->assertDontSee('Crew1 Route');
    }

    public function test_only_crew_can_access_crew_assignments(): void
    {
        $resident = User::factory()->create();
        $resident->assignRole('resident');

        // Resident cannot access
        $response = $this->actingAs($resident)->get(route('crew.assignments'));
        $response->assertRedirect();
    }

    public function test_only_crew_can_access_upcoming_assignments(): void
    {
        $resident = User::factory()->create();
        $resident->assignRole('resident');

        // Resident cannot access
        $response = $this->actingAs($resident)->get(route('crew.assignments.upcoming'));
        $response->assertRedirect();
    }

    public function test_administrators_can_access_crew_assignments(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('administrator');

        // Admin can access
        $response = $this->actingAs($admin)->get(route('crew.assignments'));
        $response->assertOk();
    }

    public function test_administrators_can_access_upcoming_assignments(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('administrator');

        // Admin can access
        $response = $this->actingAs($admin)->get(route('crew.assignments.upcoming'));
        $response->assertOk();
    }

    public function test_unauthenticated_users_cannot_access_crew_assignments(): void
    {
        // Test all crew assignment endpoints
        $this->get(route('crew.assignments'))->assertRedirect(route('login'));
        $this->get(route('crew.assignments.upcoming'))->assertRedirect(route('login'));
    }
}

