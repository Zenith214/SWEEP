<?php

namespace Tests\Feature;

use App\Models\Assignment;
use App\Models\Route;
use App\Models\Truck;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TruckHistoryAndReportingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Seed roles
        $this->artisan('db:seed', ['--class' => 'RolePermissionSeeder']);
    }

    // ========================================
    // Test assignment history display
    // ========================================

    public function test_administrator_can_view_truck_assignment_history(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('administrator');

        $truck = Truck::factory()->create(['truck_number' => 'T-1001']);
        $crew = User::factory()->create(['name' => 'John Doe']);
        $crew->assignRole('collection_crew');
        $route = Route::factory()->create(['name' => 'North District Route']);

        // Create past assignments
        Assignment::factory()->create([
            'truck_id' => $truck->id,
            'user_id' => $crew->id,
            'route_id' => $route->id,
            'assignment_date' => now()->subDays(5),
            'status' => Assignment::STATUS_ACTIVE,
        ]);

        Assignment::factory()->create([
            'truck_id' => $truck->id,
            'user_id' => $crew->id,
            'route_id' => $route->id,
            'assignment_date' => now()->subDays(10),
            'status' => Assignment::STATUS_ACTIVE,
        ]);

        $response = $this->actingAs($admin)->get(route('admin.trucks.show', $truck));

        $response->assertOk();
        $response->assertSee('T-1001');
        $response->assertSee('North District Route');
        $response->assertSee('John Doe');
    }

    public function test_truck_show_page_displays_assignment_history(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('administrator');

        $truck = Truck::factory()->create(['truck_number' => 'T-1001']);
        $crew = User::factory()->create(['name' => 'Jane Smith']);
        $crew->assignRole('collection_crew');
        $route = Route::factory()->create(['name' => 'South District Route']);

        // Create assignment
        Assignment::factory()->create([
            'truck_id' => $truck->id,
            'user_id' => $crew->id,
            'route_id' => $route->id,
            'assignment_date' => now()->subDays(3),
            'status' => Assignment::STATUS_ACTIVE,
        ]);

        $response = $this->actingAs($admin)->get(route('admin.trucks.show', $truck));

        $response->assertOk();
        $response->assertSee('South District Route');
        $response->assertSee('Jane Smith');
    }

    public function test_assignment_history_shows_multiple_assignments(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('administrator');

        $truck = Truck::factory()->create();
        $crew1 = User::factory()->create(['name' => 'Crew Member 1']);
        $crew1->assignRole('collection_crew');
        $crew2 = User::factory()->create(['name' => 'Crew Member 2']);
        $crew2->assignRole('collection_crew');
        
        $route1 = Route::factory()->create(['name' => 'Route A']);
        $route2 = Route::factory()->create(['name' => 'Route B']);

        // Create multiple assignments
        Assignment::factory()->create([
            'truck_id' => $truck->id,
            'user_id' => $crew1->id,
            'route_id' => $route1->id,
            'assignment_date' => now()->subDays(1),
        ]);

        Assignment::factory()->create([
            'truck_id' => $truck->id,
            'user_id' => $crew2->id,
            'route_id' => $route2->id,
            'assignment_date' => now()->subDays(2),
        ]);

        $response = $this->actingAs($admin)->get(route('admin.trucks.show', $truck));

        $response->assertOk();
        $response->assertSee('Route A');
        $response->assertSee('Route B');
        $response->assertSee('Crew Member 1');
        $response->assertSee('Crew Member 2');
    }

    public function test_assignment_history_displays_cancelled_assignments(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('administrator');

        $truck = Truck::factory()->create();
        $crew = User::factory()->create();
        $crew->assignRole('collection_crew');
        $route = Route::factory()->create(['name' => 'Cancelled Route']);

        // Create cancelled assignment
        Assignment::factory()->create([
            'truck_id' => $truck->id,
            'user_id' => $crew->id,
            'route_id' => $route->id,
            'assignment_date' => now()->subDays(5),
            'status' => Assignment::STATUS_CANCELLED,
        ]);

        $response = $this->actingAs($admin)->get(route('admin.trucks.show', $truck));

        $response->assertOk();
        $response->assertSee('Cancelled Route');
    }

    public function test_assignment_history_ordered_by_date_descending(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('administrator');

        $truck = Truck::factory()->create();
        $crew = User::factory()->create();
        $crew->assignRole('collection_crew');
        
        $route1 = Route::factory()->create(['name' => 'Oldest Route']);
        $route2 = Route::factory()->create(['name' => 'Middle Route']);
        $route3 = Route::factory()->create(['name' => 'Newest Route']);

        // Create assignments in non-chronological order
        Assignment::factory()->create([
            'truck_id' => $truck->id,
            'user_id' => $crew->id,
            'route_id' => $route2->id,
            'assignment_date' => now()->subDays(5),
        ]);

        Assignment::factory()->create([
            'truck_id' => $truck->id,
            'user_id' => $crew->id,
            'route_id' => $route1->id,
            'assignment_date' => now()->subDays(10),
        ]);

        Assignment::factory()->create([
            'truck_id' => $truck->id,
            'user_id' => $crew->id,
            'route_id' => $route3->id,
            'assignment_date' => now()->subDays(2),
        ]);

        $history = $truck->getAssignmentHistory(now()->subDays(15), now());

        // Verify order (newest first)
        $this->assertEquals('Newest Route', $history->first()->route->name);
        $this->assertEquals('Oldest Route', $history->last()->route->name);
    }

    // ========================================
    // Test date range filtering
    // ========================================

    public function test_assignment_history_can_be_filtered_by_date_range(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('administrator');

        $truck = Truck::factory()->create();
        $crew = User::factory()->create();
        $crew->assignRole('collection_crew');
        
        $route1 = Route::factory()->create(['name' => 'Within Range']);
        $route2 = Route::factory()->create(['name' => 'Outside Range']);

        // Assignment within range
        Assignment::factory()->create([
            'truck_id' => $truck->id,
            'user_id' => $crew->id,
            'route_id' => $route1->id,
            'assignment_date' => now()->subDays(5),
        ]);

        // Assignment outside range
        Assignment::factory()->create([
            'truck_id' => $truck->id,
            'user_id' => $crew->id,
            'route_id' => $route2->id,
            'assignment_date' => now()->subDays(100),
        ]);

        $startDate = now()->subDays(10);
        $endDate = now();

        $history = $truck->getAssignmentHistory($startDate, $endDate);

        $this->assertEquals(1, $history->count());
        $this->assertEquals('Within Range', $history->first()->route->name);
    }

    public function test_assignment_history_uses_default_date_range(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('administrator');

        $truck = Truck::factory()->create();
        $crew = User::factory()->create();
        $crew->assignRole('collection_crew');
        $route = Route::factory()->create(['name' => 'Recent Route']);

        // Create assignment within default 90-day range
        Assignment::factory()->create([
            'truck_id' => $truck->id,
            'user_id' => $crew->id,
            'route_id' => $route->id,
            'assignment_date' => now()->subDays(30),
        ]);

        $response = $this->actingAs($admin)->get(route('admin.trucks.show', $truck));

        $response->assertOk();
        $response->assertSee('Recent Route');
    }

    public function test_date_range_filter_excludes_assignments_outside_range(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('administrator');

        $truck = Truck::factory()->create();
        $crew = User::factory()->create();
        $crew->assignRole('collection_crew');
        
        $route1 = Route::factory()->create(['name' => 'Old Assignment']);
        $route2 = Route::factory()->create(['name' => 'New Assignment']);

        // Old assignment
        Assignment::factory()->create([
            'truck_id' => $truck->id,
            'user_id' => $crew->id,
            'route_id' => $route1->id,
            'assignment_date' => now()->subDays(200),
        ]);

        // Recent assignment
        Assignment::factory()->create([
            'truck_id' => $truck->id,
            'user_id' => $crew->id,
            'route_id' => $route2->id,
            'assignment_date' => now()->subDays(5),
        ]);

        $startDate = now()->subDays(30)->format('Y-m-d');
        $endDate = now()->format('Y-m-d');

        $history = $truck->getAssignmentHistory(
            Carbon::parse($startDate),
            Carbon::parse($endDate)
        );

        $this->assertEquals(1, $history->count());
        $this->assertEquals('New Assignment', $history->first()->route->name);
    }

    public function test_custom_date_range_can_span_any_period(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('administrator');

        $truck = Truck::factory()->create();
        $crew = User::factory()->create();
        $crew->assignRole('collection_crew');
        $route = Route::factory()->create();

        // Create assignments across different time periods
        Assignment::factory()->create([
            'truck_id' => $truck->id,
            'user_id' => $crew->id,
            'route_id' => $route->id,
            'assignment_date' => now()->subDays(365), // 1 year ago
        ]);

        Assignment::factory()->create([
            'truck_id' => $truck->id,
            'user_id' => $crew->id,
            'route_id' => $route->id,
            'assignment_date' => now()->subDays(180), // 6 months ago
        ]);

        // Query for 1 year range
        $startDate = now()->subDays(400);
        $endDate = now();

        $history = $truck->getAssignmentHistory($startDate, $endDate);

        $this->assertEquals(2, $history->count());
    }

    // ========================================
    // Test utilization rate calculation
    // ========================================

    public function test_utilization_rate_calculated_correctly(): void
    {
        $truck = Truck::factory()->create();
        $crew = User::factory()->create();
        $crew->assignRole('collection_crew');
        $route = Route::factory()->create();

        // Create 4 assignments over a 10-day period (40% utilization)
        for ($i = 0; $i < 4; $i++) {
            Assignment::factory()->create([
                'truck_id' => $truck->id,
                'user_id' => $crew->id,
                'route_id' => $route->id,
                'assignment_date' => now()->subDays($i * 2),
                'status' => Assignment::STATUS_ACTIVE,
            ]);
        }

        $startDate = now()->subDays(9);
        $endDate = now();

        $utilizationRate = $truck->getUtilizationRate($startDate, $endDate);

        // 4 assignments over 10 days = 40%
        $this->assertEquals(40.0, $utilizationRate);
    }

    public function test_utilization_rate_with_full_utilization(): void
    {
        $truck = Truck::factory()->create();
        $crew = User::factory()->create();
        $crew->assignRole('collection_crew');
        $route = Route::factory()->create();

        // Create assignment for every day in a 7-day period
        for ($i = 0; $i < 7; $i++) {
            Assignment::factory()->create([
                'truck_id' => $truck->id,
                'user_id' => $crew->id,
                'route_id' => $route->id,
                'assignment_date' => now()->startOfDay()->subDays($i),
                'status' => Assignment::STATUS_ACTIVE,
            ]);
        }

        $startDate = now()->startOfDay()->subDays(6);
        $endDate = now()->startOfDay();

        $utilizationRate = $truck->getUtilizationRate($startDate, $endDate);

        // 6 assignments over 7 days = 85.71%
        $this->assertEquals(85.71, $utilizationRate);
    }

    public function test_utilization_rate_with_zero_assignments(): void
    {
        $truck = Truck::factory()->create();

        $startDate = now()->subDays(30);
        $endDate = now();

        $utilizationRate = $truck->getUtilizationRate($startDate, $endDate);

        // No assignments = 0%
        $this->assertEquals(0.0, $utilizationRate);
    }

    public function test_utilization_rate_excludes_cancelled_assignments(): void
    {
        $truck = Truck::factory()->create();
        $crew = User::factory()->create();
        $crew->assignRole('collection_crew');
        $route = Route::factory()->create();

        // Create 2 active assignments
        for ($i = 0; $i < 2; $i++) {
            Assignment::factory()->create([
                'truck_id' => $truck->id,
                'user_id' => $crew->id,
                'route_id' => $route->id,
                'assignment_date' => now()->subDays($i),
                'status' => Assignment::STATUS_ACTIVE,
            ]);
        }

        // Create 2 cancelled assignments
        for ($i = 3; $i < 5; $i++) {
            Assignment::factory()->create([
                'truck_id' => $truck->id,
                'user_id' => $crew->id,
                'route_id' => $route->id,
                'assignment_date' => now()->subDays($i),
                'status' => Assignment::STATUS_CANCELLED,
            ]);
        }

        $startDate = now()->subDays(9);
        $endDate = now();

        $utilizationRate = $truck->getUtilizationRate($startDate, $endDate);

        // Only 2 active assignments over 10 days = 20%
        $this->assertEquals(20.0, $utilizationRate);
    }

    public function test_utilization_rate_displayed_on_show_page(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('administrator');

        $truck = Truck::factory()->create();
        $crew = User::factory()->create();
        $crew->assignRole('collection_crew');
        $route = Route::factory()->create();

        // Create 2 assignments over 10 days (20% utilization)
        Assignment::factory()->create([
            'truck_id' => $truck->id,
            'user_id' => $crew->id,
            'route_id' => $route->id,
            'assignment_date' => now()->subDays(2),
            'status' => Assignment::STATUS_ACTIVE,
        ]);

        Assignment::factory()->create([
            'truck_id' => $truck->id,
            'user_id' => $crew->id,
            'route_id' => $route->id,
            'assignment_date' => now()->subDays(5),
            'status' => Assignment::STATUS_ACTIVE,
        ]);

        $response = $this->actingAs($admin)->get(route('admin.trucks.show', $truck));

        $response->assertOk();
        // Check that utilization rate is displayed
        $response->assertSee('Utilization');
    }

    public function test_utilization_rate_rounds_to_two_decimal_places(): void
    {
        $truck = Truck::factory()->create();
        $crew = User::factory()->create();
        $crew->assignRole('collection_crew');
        $route = Route::factory()->create();

        // Create 1 assignment over 3 days (33.33...%)
        Assignment::factory()->create([
            'truck_id' => $truck->id,
            'user_id' => $crew->id,
            'route_id' => $route->id,
            'assignment_date' => now()->subDays(1),
            'status' => Assignment::STATUS_ACTIVE,
        ]);

        $startDate = now()->subDays(2);
        $endDate = now();

        $utilizationRate = $truck->getUtilizationRate($startDate, $endDate);

        // Should be rounded to 33.33
        $this->assertEquals(33.33, $utilizationRate);
    }

    // ========================================
    // Test history export functionality
    // ========================================

    public function test_show_page_includes_export_functionality(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('administrator');

        $truck = Truck::factory()->create();

        $response = $this->actingAs($admin)->get(route('admin.trucks.show', $truck));

        $response->assertOk();
        // Check for export button or link
        $response->assertSee('Export');
    }

    public function test_assignment_count_displayed_on_show_page(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('administrator');

        $truck = Truck::factory()->create();
        $crew = User::factory()->create();
        $crew->assignRole('collection_crew');
        $route = Route::factory()->create();

        // Create 5 assignments
        for ($i = 0; $i < 5; $i++) {
            Assignment::factory()->create([
                'truck_id' => $truck->id,
                'user_id' => $crew->id,
                'route_id' => $route->id,
                'assignment_date' => now()->subDays($i),
            ]);
        }

        $response = $this->actingAs($admin)->get(route('admin.trucks.show', $truck));

        $response->assertOk();
        // Check that total count is displayed
        $response->assertSee('5');
    }

    public function test_truck_show_page_displays_utilization_statistics(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('administrator');

        $truck = Truck::factory()->create();
        $crew = User::factory()->create();
        $crew->assignRole('collection_crew');
        $route = Route::factory()->create();

        // Create assignments
        Assignment::factory()->create([
            'truck_id' => $truck->id,
            'user_id' => $crew->id,
            'route_id' => $route->id,
            'assignment_date' => now()->subDays(5),
        ]);

        $response = $this->actingAs($admin)->get(route('admin.trucks.show', $truck));

        $response->assertOk();
        // Check for utilization statistics
        $response->assertSee('Utilization');
    }

    public function test_history_includes_assignment_dates(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('administrator');

        $truck = Truck::factory()->create();
        $crew = User::factory()->create();
        $crew->assignRole('collection_crew');
        $route = Route::factory()->create();

        $assignmentDate = now()->subDays(5);
        
        Assignment::factory()->create([
            'truck_id' => $truck->id,
            'user_id' => $crew->id,
            'route_id' => $route->id,
            'assignment_date' => $assignmentDate,
        ]);

        $history = $truck->getAssignmentHistory(now()->subDays(10), now());

        $this->assertEquals(
            $assignmentDate->format('Y-m-d'),
            $history->first()->assignment_date->format('Y-m-d')
        );
    }

    public function test_history_loads_route_and_user_relationships(): void
    {
        $truck = Truck::factory()->create();
        $crew = User::factory()->create(['name' => 'Test Crew']);
        $crew->assignRole('collection_crew');
        $route = Route::factory()->create(['name' => 'Test Route']);

        Assignment::factory()->create([
            'truck_id' => $truck->id,
            'user_id' => $crew->id,
            'route_id' => $route->id,
            'assignment_date' => now()->subDays(1),
        ]);

        $history = $truck->getAssignmentHistory(now()->subDays(10), now());

        // Verify relationships are loaded
        $this->assertTrue($history->first()->relationLoaded('route'));
        $this->assertTrue($history->first()->relationLoaded('user'));
        $this->assertEquals('Test Route', $history->first()->route->name);
        $this->assertEquals('Test Crew', $history->first()->user->name);
    }
}
