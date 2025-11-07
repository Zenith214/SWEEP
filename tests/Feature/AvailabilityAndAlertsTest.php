<?php

namespace Tests\Feature;

use App\Models\Assignment;
use App\Models\Route;
use App\Models\Schedule;
use App\Models\ScheduleDay;
use App\Models\Truck;
use App\Models\User;
use App\Services\AlertService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AvailabilityAndAlertsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Seed roles
        $this->artisan('db:seed', ['--class' => 'RolePermissionSeeder']);
    }

    // ========================================
    // 24.1 Test truck availability checking
    // ========================================

    public function test_availability_display_for_selected_date(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('administrator');

        $operationalTruck = Truck::factory()->create([
            'truck_number' => 'T-OP-001',
            'operational_status' => Truck::STATUS_OPERATIONAL
        ]);
        $maintenanceTruck = Truck::factory()->create([
            'truck_number' => 'T-MT-001',
            'operational_status' => Truck::STATUS_MAINTENANCE
        ]);
        $outOfServiceTruck = Truck::factory()->create([
            'truck_number' => 'T-OS-001',
            'operational_status' => Truck::STATUS_OUT_OF_SERVICE
        ]);

        $selectedDate = now()->addDays(3)->format('Y-m-d');

        $response = $this->actingAs($admin)->get(route('admin.truck-availability.index', [
            'date' => $selectedDate
        ]));

        $response->assertOk();
        $response->assertSee('T-OP-001');
        $response->assertSee('T-MT-001');
        $response->assertSee('T-OS-001');
    }

    public function test_operational_truck_filtering(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('administrator');

        $operationalTruck1 = Truck::factory()->create([
            'truck_number' => 'T-OP-001',
            'operational_status' => Truck::STATUS_OPERATIONAL
        ]);
        $operationalTruck2 = Truck::factory()->create([
            'truck_number' => 'T-OP-002',
            'operational_status' => Truck::STATUS_OPERATIONAL
        ]);
        $maintenanceTruck = Truck::factory()->create([
            'truck_number' => 'T-MT-001',
            'operational_status' => Truck::STATUS_MAINTENANCE
        ]);

        $selectedDate = now()->addDays(3)->format('Y-m-d');

        $response = $this->actingAs($admin)->get(route('admin.truck-availability.index', [
            'date' => $selectedDate
        ]));

        $response->assertOk();
        
        // Verify operational trucks are shown
        $response->assertViewHas('availability', function ($availability) use ($operationalTruck1, $operationalTruck2, $maintenanceTruck) {
            $operationalIds = collect($availability['operational'])->pluck('truck.id');
            return count($availability['operational']) === 2 &&
                   count($availability['maintenance']) === 1 &&
                   $operationalIds->contains($operationalTruck1->id) &&
                   $operationalIds->contains($operationalTruck2->id);
        });
    }

    public function test_assignment_status_display(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('administrator');

        $assignedTruck = Truck::factory()->create([
            'truck_number' => 'T-ASSIGNED',
            'operational_status' => Truck::STATUS_OPERATIONAL
        ]);
        $availableTruck = Truck::factory()->create([
            'truck_number' => 'T-AVAILABLE',
            'operational_status' => Truck::STATUS_OPERATIONAL
        ]);

        $crew = User::factory()->create();
        $crew->assignRole('collection_crew');
        $route = Route::factory()->create();

        $selectedDate = now()->addDays(3);

        // Create assignment for the assigned truck
        Assignment::factory()->create([
            'truck_id' => $assignedTruck->id,
            'user_id' => $crew->id,
            'route_id' => $route->id,
            'assignment_date' => $selectedDate,
            'status' => Assignment::STATUS_ACTIVE,
        ]);

        $response = $this->actingAs($admin)->get(route('admin.truck-availability.index', [
            'date' => $selectedDate->format('Y-m-d')
        ]));

        $response->assertOk();
        
        // Verify assignment status is displayed correctly
        $response->assertViewHas('availability', function ($availability) use ($assignedTruck, $availableTruck) {
            $assignedItem = collect($availability['operational'])->firstWhere('truck.id', $assignedTruck->id);
            $availableItem = collect($availability['operational'])->firstWhere('truck.id', $availableTruck->id);
            
            return $assignedItem['is_available'] === false &&
                   $assignedItem['assignment'] !== null &&
                   $availableItem['is_available'] === true &&
                   $availableItem['assignment'] === null;
        });
    }

    public function test_availability_ajax_endpoint_returns_correct_data(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('administrator');

        $truck = Truck::factory()->create([
            'truck_number' => 'T-001',
            'operational_status' => Truck::STATUS_OPERATIONAL
        ]);
        $crew = User::factory()->create();
        $crew->assignRole('collection_crew');
        $route = Route::factory()->create(['name' => 'North Route']);

        $selectedDate = now()->addDays(3);

        Assignment::factory()->create([
            'truck_id' => $truck->id,
            'user_id' => $crew->id,
            'route_id' => $route->id,
            'assignment_date' => $selectedDate,
            'status' => Assignment::STATUS_ACTIVE,
        ]);

        $response = $this->actingAs($admin)->get(route('admin.truck-availability.data', [
            'date' => $selectedDate->format('Y-m-d')
        ]));

        $response->assertOk();
        $response->assertJson([
            'date' => $selectedDate->format('Y-m-d'),
        ]);
        
        $data = $response->json();
        $this->assertArrayHasKey('availability', $data);
        $this->assertArrayHasKey('operational', $data['availability']);
        
        $operationalTruck = collect($data['availability']['operational'])->firstWhere('id', $truck->id);
        $this->assertNotNull($operationalTruck);
        $this->assertEquals('T-001', $operationalTruck['truck_number']);
        $this->assertFalse($operationalTruck['is_available']);
        $this->assertNotNull($operationalTruck['assignment']);
        $this->assertEquals('North Route', $operationalTruck['assignment']['route_name']);
    }

    // ========================================
    // 24.2 Test unassigned routes detection
    // ========================================

    public function test_unassigned_routes_listing(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('administrator');

        // Create route with schedule
        $route = Route::factory()->create([
            'name' => 'Unassigned Route',
            'is_active' => true
        ]);
        
        $schedule = Schedule::factory()->create([
            'route_id' => $route->id,
            'is_active' => true,
            'collection_time' => '08:00:00'
        ]);

        // Create schedule day for tomorrow
        $tomorrow = now()->addDay();
        ScheduleDay::factory()->create([
            'schedule_id' => $schedule->id,
            'day_of_week' => $tomorrow->dayOfWeek
        ]);

        $response = $this->actingAs($admin)->get(route('admin.assignments.unassigned-routes'));

        $response->assertOk();
        $response->assertSee('Unassigned Route');
    }

    public function test_date_range_filtering_for_unassigned_routes(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('administrator');

        $route = Route::factory()->create([
            'name' => 'Test Route',
            'is_active' => true
        ]);
        
        $schedule = Schedule::factory()->create([
            'route_id' => $route->id,
            'is_active' => true,
            'collection_time' => '08:00:00'
        ]);

        // Create schedule day for a specific day
        $targetDate = now()->addDays(5);
        ScheduleDay::factory()->create([
            'schedule_id' => $schedule->id,
            'day_of_week' => $targetDate->dayOfWeek
        ]);

        // Test with date range that includes the target date
        $response = $this->actingAs($admin)->get(route('admin.assignments.unassigned-routes', [
            'start_date' => now()->format('Y-m-d'),
            'end_date' => now()->addDays(7)->format('Y-m-d')
        ]));

        $response->assertOk();
        $response->assertViewHas('unassignedRoutes');
        
        // Test with date range that excludes the target date
        $response = $this->actingAs($admin)->get(route('admin.assignments.unassigned-routes', [
            'start_date' => now()->addDays(10)->format('Y-m-d'),
            'end_date' => now()->addDays(15)->format('Y-m-d')
        ]));

        $response->assertOk();
        $response->assertViewHas('unassignedRoutes');
    }

    public function test_route_details_display_for_unassigned_routes(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('administrator');

        $route = Route::factory()->create([
            'name' => 'North District Route',
            'zone' => 'North',
            'is_active' => true
        ]);
        
        $tomorrow = now()->addDay();
        $schedule = Schedule::factory()->create([
            'route_id' => $route->id,
            'is_active' => true,
            'collection_time' => '08:00:00',
            'start_date' => now(),
            'end_date' => now()->addDays(30)
        ]);

        ScheduleDay::factory()->create([
            'schedule_id' => $schedule->id,
            'day_of_week' => $tomorrow->dayOfWeek
        ]);

        $response = $this->actingAs($admin)->get(route('admin.assignments.unassigned-routes'));

        $response->assertOk();
        $response->assertSee('North District Route');
        $response->assertSee('North');
    }

    public function test_assigned_routes_are_not_shown_as_unassigned(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('administrator');

        $route = Route::factory()->create([
            'name' => 'Assigned Route',
            'is_active' => true
        ]);
        
        $schedule = Schedule::factory()->create([
            'route_id' => $route->id,
            'is_active' => true,
            'collection_time' => '08:00:00'
        ]);

        $tomorrow = now()->addDay();
        ScheduleDay::factory()->create([
            'schedule_id' => $schedule->id,
            'day_of_week' => $tomorrow->dayOfWeek
        ]);

        // Create assignment for this route
        $truck = Truck::factory()->create(['operational_status' => Truck::STATUS_OPERATIONAL]);
        $crew = User::factory()->create();
        $crew->assignRole('collection_crew');

        Assignment::factory()->create([
            'truck_id' => $truck->id,
            'user_id' => $crew->id,
            'route_id' => $route->id,
            'assignment_date' => $tomorrow,
            'status' => Assignment::STATUS_ACTIVE,
        ]);

        $response = $this->actingAs($admin)->get(route('admin.assignments.unassigned-routes'));

        $response->assertOk();
        
        // Verify the route is not in the unassigned list
        $response->assertViewHas('unassignedRoutes', function ($unassignedRoutes) use ($route, $tomorrow) {
            foreach ($unassignedRoutes as $item) {
                if ($item['route']->id === $route->id && 
                    $item['date']->format('Y-m-d') === $tomorrow->format('Y-m-d')) {
                    return false;
                }
            }
            return true;
        });
    }

    // ========================================
    // 24.3 Test dashboard alerts
    // ========================================

    public function test_unassigned_routes_alert_generation(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('administrator');

        // Create route with schedule in next 3 days
        $route = Route::factory()->create([
            'name' => 'Alert Route',
            'is_active' => true
        ]);
        
        $tomorrow = now()->addDay();
        $schedule = Schedule::factory()->create([
            'route_id' => $route->id,
            'is_active' => true,
            'collection_time' => '08:00:00',
            'start_date' => now(),
            'end_date' => now()->addDays(30)
        ]);

        ScheduleDay::factory()->create([
            'schedule_id' => $schedule->id,
            'day_of_week' => $tomorrow->dayOfWeek
        ]);

        $alertService = app(AlertService::class);
        $alert = $alertService->getUnassignedRoutesAlert();

        $this->assertNotNull($alert);
        $this->assertEquals('unassigned_routes', $alert['type']);
        $this->assertEquals('Unassigned Routes', $alert['title']);
        $this->assertGreaterThan(0, $alert['count']);
        $this->assertEquals('warning', $alert['severity']);
    }

    public function test_underutilized_trucks_alert_generation(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('administrator');

        // Create operational truck with no assignments
        Truck::factory()->create([
            'truck_number' => 'T-IDLE-001',
            'operational_status' => Truck::STATUS_OPERATIONAL
        ]);

        $alertService = app(AlertService::class);
        $alert = $alertService->getUnderutilizedTrucksAlert();

        $this->assertNotNull($alert);
        $this->assertEquals('underutilized_trucks', $alert['type']);
        $this->assertEquals('Underutilized Trucks', $alert['title']);
        $this->assertGreaterThan(0, $alert['count']);
        $this->assertEquals('info', $alert['severity']);
    }

    public function test_no_alert_when_all_routes_assigned(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('administrator');

        // Create route with schedule
        $route = Route::factory()->create([
            'name' => 'Assigned Route',
            'is_active' => true
        ]);
        
        $schedule = Schedule::factory()->create([
            'route_id' => $route->id,
            'is_active' => true,
            'collection_time' => '08:00:00'
        ]);

        $tomorrow = now()->addDay();
        ScheduleDay::factory()->create([
            'schedule_id' => $schedule->id,
            'day_of_week' => $tomorrow->dayOfWeek
        ]);

        // Create assignment
        $truck = Truck::factory()->create(['operational_status' => Truck::STATUS_OPERATIONAL]);
        $crew = User::factory()->create();
        $crew->assignRole('collection_crew');

        Assignment::factory()->create([
            'truck_id' => $truck->id,
            'user_id' => $crew->id,
            'route_id' => $route->id,
            'assignment_date' => $tomorrow,
            'status' => Assignment::STATUS_ACTIVE,
        ]);

        $alertService = app(AlertService::class);
        $alert = $alertService->getUnassignedRoutesAlert();

        $this->assertNull($alert);
    }

    public function test_no_alert_when_all_trucks_utilized(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('administrator');

        // Create operational truck with assignment
        $truck = Truck::factory()->create([
            'truck_number' => 'T-BUSY-001',
            'operational_status' => Truck::STATUS_OPERATIONAL
        ]);

        $crew = User::factory()->create();
        $crew->assignRole('collection_crew');
        $route = Route::factory()->create();

        Assignment::factory()->create([
            'truck_id' => $truck->id,
            'user_id' => $crew->id,
            'route_id' => $route->id,
            'assignment_date' => now()->addDays(3),
            'status' => Assignment::STATUS_ACTIVE,
        ]);

        $alertService = app(AlertService::class);
        $alert = $alertService->getUnderutilizedTrucksAlert();

        $this->assertNull($alert);
    }

    public function test_alert_dismissal(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('administrator');

        $response = $this->actingAs($admin)->post(route('admin.dashboard.dismiss-alert'), [
            'alert_type' => 'unassigned_routes'
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        // Verify alert is dismissed
        $alertService = app(AlertService::class);
        $isDismissed = $alertService->isAlertDismissed('unassigned_routes', $admin);
        
        $this->assertTrue($isDismissed);
    }

    public function test_alert_links_are_correct(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('administrator');

        // Create unassigned route
        $route = Route::factory()->create([
            'name' => 'Alert Route',
            'is_active' => true
        ]);
        
        $tomorrow = now()->addDay();
        $schedule = Schedule::factory()->create([
            'route_id' => $route->id,
            'is_active' => true,
            'collection_time' => '08:00:00',
            'start_date' => now(),
            'end_date' => now()->addDays(30)
        ]);

        ScheduleDay::factory()->create([
            'schedule_id' => $schedule->id,
            'day_of_week' => $tomorrow->dayOfWeek
        ]);

        // Create underutilized truck
        Truck::factory()->create([
            'truck_number' => 'T-IDLE-001',
            'operational_status' => Truck::STATUS_OPERATIONAL
        ]);

        $alertService = app(AlertService::class);
        
        $unassignedAlert = $alertService->getUnassignedRoutesAlert();
        $this->assertNotNull($unassignedAlert);
        $this->assertEquals(route('admin.assignments.unassigned-routes'), $unassignedAlert['link']);
        $this->assertEquals('View Unassigned Routes', $unassignedAlert['link_text']);

        $underutilizedAlert = $alertService->getUnderutilizedTrucksAlert();
        $this->assertNotNull($underutilizedAlert);
        $this->assertEquals(route('admin.truck-availability.index'), $underutilizedAlert['link']);
        $this->assertEquals('View Truck Availability', $underutilizedAlert['link_text']);
    }

    public function test_alerts_displayed_on_admin_dashboard(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('administrator');

        // Create unassigned route
        $route = Route::factory()->create([
            'name' => 'Alert Route',
            'is_active' => true
        ]);
        
        $tomorrow = now()->addDay();
        $schedule = Schedule::factory()->create([
            'route_id' => $route->id,
            'is_active' => true,
            'collection_time' => '08:00:00',
            'start_date' => now(),
            'end_date' => now()->addDays(30)
        ]);

        ScheduleDay::factory()->create([
            'schedule_id' => $schedule->id,
            'day_of_week' => $tomorrow->dayOfWeek
        ]);

        $response = $this->actingAs($admin)->get(route('admin.dashboard'));

        $response->assertOk();
        $response->assertViewHas('alerts');
        
        $alerts = $response->viewData('alerts');
        $this->assertIsArray($alerts);
        $this->assertGreaterThan(0, count($alerts));
    }

    public function test_dismissed_alerts_not_shown_on_dashboard(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('administrator');

        // Create unassigned route
        $route = Route::factory()->create([
            'name' => 'Alert Route',
            'is_active' => true
        ]);
        
        $schedule = Schedule::factory()->create([
            'route_id' => $route->id,
            'is_active' => true,
            'collection_time' => '08:00:00'
        ]);

        $tomorrow = now()->addDay();
        ScheduleDay::factory()->create([
            'schedule_id' => $schedule->id,
            'day_of_week' => $tomorrow->dayOfWeek
        ]);

        // Dismiss the alert
        $alertService = app(AlertService::class);
        $alertService->dismissAlert('unassigned_routes', $admin);

        $response = $this->actingAs($admin)->get(route('admin.dashboard'));

        $response->assertOk();
        $response->assertViewHas('alerts', function ($alerts) {
            foreach ($alerts as $alert) {
                if ($alert['type'] === 'unassigned_routes') {
                    return false;
                }
            }
            return true;
        });
    }
}
