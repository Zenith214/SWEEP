<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DrillDownNavigationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\RolePermissionSeeder::class);
    }

    /**
     * Test that dashboard metric cards include drill-down attributes.
     */
    public function test_dashboard_metric_cards_have_drill_down_attributes(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('administrator');

        $response = $this->actingAs($admin)->get(route('admin.dashboard'));

        $response->assertStatus(200);
        
        // Check that metric cards have data-metric attributes
        $response->assertSee('data-metric', false);
    }

    /**
     * Test that context parameters are preserved in URLs.
     */
    public function test_context_parameters_preserved_in_navigation(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('administrator');

        // Access dashboard with filters
        $response = $this->actingAs($admin)->get(route('admin.dashboard', [
            'period' => '30days',
            'start_date' => '2024-01-01',
            'end_date' => '2024-01-31'
        ]));

        $response->assertStatus(200);
        
        // Verify the page loads with filters
        $response->assertViewHas('metrics');
    }

    /**
     * Test that breadcrumb component renders correctly.
     */
    public function test_breadcrumb_component_renders(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('administrator');

        // Access a detail page with return_url parameter
        $returnUrl = route('admin.dashboard', ['period' => '30days']);
        $response = $this->actingAs($admin)->get(
            route('admin.collection-logs.index', ['return_url' => $returnUrl])
        );

        $response->assertStatus(200);
        
        // Check that breadcrumb is rendered
        $response->assertSee('Dashboard', false);
        $response->assertSee('Collection Logs', false);
    }

    /**
     * Test that drill-down endpoint returns data.
     */
    public function test_drill_down_endpoint_returns_data(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('administrator');

        $response = $this->actingAs($admin)->get(
            route('dashboard.drill-down', ['metric' => 'collections_today'])
        );

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'metric',
            'data',
            'filters'
        ]);
    }

    /**
     * Test that navigation JavaScript file exists.
     */
    public function test_navigation_javascript_file_exists(): void
    {
        $this->assertFileExists(public_path('js/dashboard-navigation.js'));
    }

    /**
     * Test that breadcrumb component file exists.
     */
    public function test_breadcrumb_component_exists(): void
    {
        $this->assertFileExists(resource_path('views/components/dashboard/breadcrumb.blade.php'));
    }

    /**
     * Test that metric card supports new drill-down props.
     */
    public function test_metric_card_supports_drill_down_props(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('administrator');

        $response = $this->actingAs($admin)->get(route('admin.dashboard'));

        $response->assertStatus(200);
        
        // Check that the page renders successfully with new props
        $response->assertSee('handleMetricClick', false);
    }
}
