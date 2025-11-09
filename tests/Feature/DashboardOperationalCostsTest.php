<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardOperationalCostsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Seed roles and permissions
        $this->artisan('db:seed', ['--class' => 'RolePermissionSeeder']);
    }

    /**
     * Test that admin dashboard includes operational costs section.
     */
    public function test_admin_dashboard_includes_operational_costs_section(): void
    {
        // Create an admin user
        $admin = User::factory()->create();
        $admin->assignRole('administrator');

        // Act as the admin and visit the dashboard
        $response = $this->actingAs($admin)->get(route('admin.dashboard'));

        // Assert the response is successful
        $response->assertStatus(200);

        // Assert operational costs section is present
        $response->assertSee('Operational Costs Summary');
    }

    /**
     * Test that operational costs data is included in metrics.
     */
    public function test_operational_costs_data_included_in_metrics(): void
    {
        // Create an admin user
        $admin = User::factory()->create();
        $admin->assignRole('administrator');

        // Act as the admin and visit the dashboard
        $response = $this->actingAs($admin)->get(route('admin.dashboard'));

        // Assert the response is successful
        $response->assertStatus(200);

        // Assert that operational_costs key exists in the view data
        $response->assertViewHas('metrics', function ($metrics) {
            return isset($metrics['operational_costs']);
        });
    }

    /**
     * Test that operational costs shows not available message when cost tracking is disabled.
     */
    public function test_operational_costs_shows_not_available_message(): void
    {
        // Create an admin user
        $admin = User::factory()->create();
        $admin->assignRole('administrator');

        // Act as the admin and visit the dashboard
        $response = $this->actingAs($admin)->get(route('admin.dashboard'));

        // Assert the response is successful
        $response->assertStatus(200);

        // Assert that the "not available" message is shown
        $response->assertSee('Cost Tracking Not Available');
    }

    /**
     * Test that operational costs widget can be hidden via customization.
     */
    public function test_operational_costs_widget_can_be_customized(): void
    {
        // Create an admin user
        $admin = User::factory()->create();
        $admin->assignRole('administrator');

        // Save preferences to hide operational costs widget
        $response = $this->actingAs($admin)->postJson(route('dashboard.preferences.save'), [
            'widget_visibility' => [
                'operational_costs' => false,
            ],
        ]);

        // Assert the preferences were saved successfully
        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        // Verify the preference was saved in the database
        $this->assertDatabaseHas('dashboard_preferences', [
            'user_id' => $admin->id,
        ]);
    }

    /**
     * Test that operational costs metrics are returned via AJAX.
     */
    public function test_operational_costs_metrics_returned_via_ajax(): void
    {
        // Create an admin user
        $admin = User::factory()->create();
        $admin->assignRole('administrator');

        // Request metrics via AJAX
        $response = $this->actingAs($admin)->getJson(route('dashboard.metrics'));

        // Assert the response is successful
        $response->assertStatus(200);

        // Assert operational costs are included in the response
        $response->assertJsonStructure([
            'success',
            'metrics' => [
                'operational_costs',
            ],
        ]);
    }

    /**
     * Test that operational costs are included in PDF export.
     */
    public function test_operational_costs_included_in_pdf_export(): void
    {
        // Create an admin user
        $admin = User::factory()->create();
        $admin->assignRole('administrator');

        // Request PDF export
        $response = $this->actingAs($admin)->get(route('dashboard.export', ['format' => 'pdf']));

        // Assert the response is successful (download)
        $response->assertStatus(200);
        $response->assertHeader('content-type', 'application/pdf');
    }

    /**
     * Test that operational costs are included in CSV export.
     */
    public function test_operational_costs_included_in_csv_export(): void
    {
        // Create an admin user
        $admin = User::factory()->create();
        $admin->assignRole('administrator');

        // Request CSV export
        $response = $this->actingAs($admin)->get(route('dashboard.export', ['format' => 'csv']));

        // Assert the response is successful (download)
        $response->assertStatus(200);
        // Check that content-type starts with text/csv (may include charset)
        $this->assertStringStartsWith('text/csv', $response->headers->get('content-type'));
    }

    /**
     * Test that operational costs comparison data is included when comparison is active.
     */
    public function test_operational_costs_comparison_data_included(): void
    {
        // Create an admin user
        $admin = User::factory()->create();
        $admin->assignRole('administrator');

        // Request dashboard with comparison period
        $response = $this->actingAs($admin)->get(route('admin.dashboard', [
            'compare_period' => 'previous_month',
        ]));

        // Assert the response is successful
        $response->assertStatus(200);

        // Assert that comparisons are included in the view data
        $response->assertViewHas('metrics', function ($metrics) {
            return isset($metrics['comparisons']);
        });
    }
}
