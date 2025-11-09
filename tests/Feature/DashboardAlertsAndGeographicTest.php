<?php

namespace Tests\Feature;

use App\Models\Assignment;
use App\Models\DismissedAlert;
use App\Models\Report;
use App\Models\Route;
use App\Models\Truck;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardAlertsAndGeographicTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $resident;

    protected function setUp(): void
    {
        parent::setUp();

        // Seed roles
        $this->artisan('db:seed', ['--class' => 'RolePermissionSeeder']);

        $this->admin = User::factory()->create();
        $this->admin->assignRole('administrator');

        $this->resident = User::factory()->create();
        $this->resident->assignRole('resident');
    }

    /** @test */
    public function admin_can_view_dashboard_with_alerts()
    {
        // Create some data that should trigger alerts
        $route = Route::factory()->create();
        
        // Unassigned route within 3 days
        Assignment::factory()->create([
            'route_id' => $route->id,
            'user_id' => null,
            'assignment_date' => now()->addDays(2),
            'status' => Assignment::STATUS_ACTIVE,
        ]);

        // Overdue report
        Report::factory()->create([
            'resident_id' => $this->resident->id,
            'status' => Report::STATUS_PENDING,
            'created_at' => now()->subHours(50),
        ]);

        // Truck in maintenance
        Truck::factory()->create([
            'operational_status' => Truck::STATUS_MAINTENANCE,
        ]);

        $response = $this->actingAs($this->admin)
            ->get(route('admin.dashboard'));

        $response->assertStatus(200);
        $response->assertViewHas('metrics');
        
        $metrics = $response->viewData('metrics');
        $this->assertArrayHasKey('alerts', $metrics);
        $this->assertNotEmpty($metrics['alerts']);
    }

    /** @test */
    public function admin_can_dismiss_an_alert()
    {
        $route = Route::factory()->create();
        $assignment = Assignment::factory()->create([
            'route_id' => $route->id,
            'user_id' => null,
            'assignment_date' => now()->addDays(2),
            'status' => Assignment::STATUS_ACTIVE,
        ]);

        $alertId = "assignment_{$assignment->id}";

        $response = $this->actingAs($this->admin)
            ->postJson(route('dashboard.alerts.dismiss', ['alertId' => $alertId]));

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        // Verify the alert was dismissed in the database
        $this->assertDatabaseHas('dismissed_alerts', [
            'user_id' => $this->admin->id,
            'alert_category' => 'unassigned_route',
            'alert_identifier' => $alertId,
        ]);
    }

    /** @test */
    public function admin_can_dismiss_all_alerts()
    {
        $route = Route::factory()->create();
        
        // Create multiple alerts
        Assignment::factory()->create([
            'route_id' => $route->id,
            'user_id' => null,
            'assignment_date' => now()->addDays(2),
            'status' => Assignment::STATUS_ACTIVE,
        ]);

        Report::factory()->create([
            'resident_id' => $this->resident->id,
            'status' => Report::STATUS_PENDING,
            'created_at' => now()->subHours(50),
        ]);

        $response = $this->actingAs($this->admin)
            ->postJson(route('dashboard.alerts.dismiss-all'));

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
    }

    /** @test */
    public function dismissed_alerts_are_not_shown_to_user()
    {
        $route = Route::factory()->create();
        $assignment = Assignment::factory()->create([
            'route_id' => $route->id,
            'user_id' => null,
            'assignment_date' => now()->addDays(2),
            'status' => Assignment::STATUS_ACTIVE,
        ]);

        $alertId = "assignment_{$assignment->id}";

        // Dismiss the alert
        DismissedAlert::dismissAlert($this->admin->id, 'unassigned_route', $alertId);

        // Load dashboard
        $response = $this->actingAs($this->admin)
            ->get(route('admin.dashboard'));

        $response->assertStatus(200);
        
        $metrics = $response->viewData('metrics');
        $alerts = $metrics['alerts'] ?? [];
        
        // The dismissed alert should not be in the list
        $alertIds = array_column($alerts, 'id');
        $this->assertNotContains($alertId, $alertIds);
    }

    /** @test */
    public function admin_can_view_geographic_distribution()
    {
        $response = $this->actingAs($this->admin)
            ->getJson(route('dashboard.geographic-distribution'));

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'data' => [
                'collections_by_zone',
                'reports_by_zone',
                'zones_without_collections',
                'total_zones',
            ],
        ]);
    }

    /** @test */
    public function geographic_distribution_can_be_filtered_by_zone()
    {
        $route1 = Route::factory()->create(['zone' => 'Zone A']);
        $route2 = Route::factory()->create(['zone' => 'Zone B']);

        $response = $this->actingAs($this->admin)
            ->getJson(route('dashboard.geographic-distribution', ['zone' => 'Zone A']));

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
        
        $data = $response->json('data');
        
        // If there's data, it should only be for Zone A
        if (!empty($data['collections_by_zone'])) {
            foreach ($data['collections_by_zone'] as $zoneData) {
                $this->assertEquals('Zone A', $zoneData['zone']);
            }
        }
    }

    /** @test */
    public function geographic_distribution_includes_performance_color_coding()
    {
        $route = Route::factory()->create(['zone' => 'Test Zone']);

        $response = $this->actingAs($this->admin)
            ->getJson(route('dashboard.geographic-distribution'));

        $response->assertStatus(200);
        
        $data = $response->json('data');
        
        // Check that color coding is present if there's data
        if (!empty($data['collections_by_zone'])) {
            foreach ($data['collections_by_zone'] as $zoneData) {
                $this->assertArrayHasKey('performance_level', $zoneData);
                $this->assertArrayHasKey('color', $zoneData);
                $this->assertContains($zoneData['performance_level'], ['high', 'medium', 'low']);
            }
        }
    }

    /** @test */
    public function non_admin_cannot_dismiss_alerts()
    {
        $route = Route::factory()->create();
        $assignment = Assignment::factory()->create([
            'route_id' => $route->id,
            'user_id' => null,
            'assignment_date' => now()->addDays(2),
            'status' => Assignment::STATUS_ACTIVE,
        ]);

        $alertId = "assignment_{$assignment->id}";

        $response = $this->actingAs($this->resident)
            ->postJson(route('dashboard.alerts.dismiss', ['alertId' => $alertId]));

        // Should be forbidden or redirected
        $this->assertTrue(in_array($response->status(), [403, 302]));
    }

    /** @test */
    public function alert_dismissal_is_user_specific()
    {
        $admin2 = User::factory()->create();
        $admin2->assignRole('administrator');

        $route = Route::factory()->create();
        $assignment = Assignment::factory()->create([
            'route_id' => $route->id,
            'user_id' => null,
            'assignment_date' => now()->addDays(2),
            'status' => Assignment::STATUS_ACTIVE,
        ]);

        $alertId = "assignment_{$assignment->id}";

        // Admin 1 dismisses the alert
        DismissedAlert::dismissAlert($this->admin->id, 'unassigned_route', $alertId);

        // Admin 1 should not see the alert
        $response1 = $this->actingAs($this->admin)
            ->get(route('admin.dashboard'));
        $metrics1 = $response1->viewData('metrics');
        $alerts1 = $metrics1['alerts'] ?? [];
        $alertIds1 = array_column($alerts1, 'id');
        $this->assertNotContains($alertId, $alertIds1);

        // Admin 2 should still see the alert
        $response2 = $this->actingAs($admin2)
            ->get(route('admin.dashboard'));
        $metrics2 = $response2->viewData('metrics');
        $alerts2 = $metrics2['alerts'] ?? [];
        $alertIds2 = array_column($alerts2, 'id');
        $this->assertContains($alertId, $alertIds2);
    }
}
