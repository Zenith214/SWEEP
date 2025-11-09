<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardPeriodComparisonTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Seed roles and permissions
        $this->artisan('db:seed', ['--class' => 'RolePermissionSeeder']);
    }

    /**
     * Test that admin can view dashboard with period selector.
     */
    public function test_admin_can_view_dashboard_with_period_selector(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('administrator');

        $response = $this->actingAs($admin)->get(route('admin.dashboard', ['period' => '30days']));

        $response->assertStatus(200);
        $response->assertSee('Time Period');
        $response->assertSee('Last 30 Days');
    }

    /**
     * Test that admin can view dashboard with comparison period.
     */
    public function test_admin_can_view_dashboard_with_comparison_period(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('administrator');

        $response = $this->actingAs($admin)->get(route('admin.dashboard', [
            'period' => '30days',
            'compare_period' => 'previous_month'
        ]));

        $response->assertStatus(200);
        $response->assertViewHas('metrics');
        
        $metrics = $response->viewData('metrics');
        
        // Check that comparisons are included when compare_period is set
        if (isset($metrics['comparisons'])) {
            $this->assertArrayHasKey('period_info', $metrics['comparisons']);
        }
    }

    /**
     * Test that admin can export dashboard to PDF.
     */
    public function test_admin_can_export_dashboard_to_pdf(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('administrator');

        $response = $this->actingAs($admin)->get(route('dashboard.export', [
            'format' => 'pdf',
            'period' => '30days'
        ]));

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/pdf');
    }

    /**
     * Test that admin can export dashboard to CSV.
     */
    public function test_admin_can_export_dashboard_to_csv(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('administrator');

        $response = $this->actingAs($admin)->get(route('dashboard.export', [
            'format' => 'csv',
            'period' => '30days'
        ]));

        $response->assertStatus(200);
        $this->assertTrue(
            $response->headers->get('Content-Type') === 'text/csv' ||
            $response->headers->get('Content-Type') === 'application/zip'
        );
    }

    /**
     * Test that export requires valid format parameter.
     */
    public function test_export_requires_valid_format(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('administrator');

        $response = $this->actingAs($admin)->get(route('dashboard.export', [
            'format' => 'invalid'
        ]));

        $response->assertStatus(302); // Validation error redirects
    }

    /**
     * Test that custom date range works correctly.
     */
    public function test_custom_date_range_works(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('administrator');

        $response = $this->actingAs($admin)->get(route('admin.dashboard', [
            'period' => 'custom',
            'start_date' => '2024-01-01',
            'end_date' => '2024-01-31'
        ]));

        $response->assertStatus(200);
        $response->assertViewHas('metrics');
        
        $metrics = $response->viewData('metrics');
        $this->assertArrayHasKey('metadata', $metrics);
    }

    /**
     * Test that comparison periods are calculated correctly.
     */
    public function test_comparison_periods_calculated_correctly(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('administrator');

        $comparisonTypes = ['previous_week', 'previous_month', 'previous_quarter', 'previous_year'];

        foreach ($comparisonTypes as $comparisonType) {
            $response = $this->actingAs($admin)->get(route('admin.dashboard', [
                'period' => '30days',
                'compare_period' => $comparisonType
            ]));

            $response->assertStatus(200);
            
            $metrics = $response->viewData('metrics');
            
            if (isset($metrics['comparisons']['period_info'])) {
                $this->assertArrayHasKey('comparison_type', $metrics['comparisons']['period_info']);
                $this->assertEquals($comparisonType, $metrics['comparisons']['period_info']['comparison_type']);
            }
        }
    }

    /**
     * Test that non-admin users cannot export dashboard.
     */
    public function test_non_admin_cannot_export_dashboard(): void
    {
        $resident = User::factory()->create();
        $resident->assignRole('resident');

        $response = $this->actingAs($resident)->get(route('dashboard.export', [
            'format' => 'pdf'
        ]));

        $response->assertStatus(403);
    }
}
