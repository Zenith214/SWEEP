<?php

namespace Tests\Feature;

use App\Models\DashboardPreference;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardCustomizationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Seed roles and permissions
        $this->artisan('db:seed', ['--class' => 'RolePermissionSeeder']);
    }

    /**
     * Test that admin can view dashboard with default preferences.
     */
    public function test_admin_can_view_dashboard_with_default_preferences(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('administrator');

        $response = $this->actingAs($admin)->get(route('admin.dashboard'));

        $response->assertStatus(200);
        $response->assertViewHas('metrics');
        
        // Check that preferences are included in metrics
        $metrics = $response->viewData('metrics');
        $this->assertArrayHasKey('preferences', $metrics);
        $this->assertArrayHasKey('widget_visibility', $metrics['preferences']);
        $this->assertArrayHasKey('widget_order', $metrics['preferences']);
    }

    /**
     * Test that admin can save dashboard preferences.
     */
    public function test_admin_can_save_dashboard_preferences(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('administrator');

        $preferences = [
            'widget_visibility' => [
                'collection_status' => true,
                'pending_items' => false,
                'collection_trends' => true,
            ],
            'widget_order' => [
                'collection_status',
                'collection_trends',
                'pending_items',
            ],
        ];

        $response = $this->actingAs($admin)
            ->postJson(route('dashboard.preferences.save'), $preferences);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        // Verify preferences were saved to database
        $this->assertDatabaseHas('dashboard_preferences', [
            'user_id' => $admin->id,
        ]);

        $savedPreference = DashboardPreference::where('user_id', $admin->id)->first();
        $this->assertEquals($preferences['widget_visibility'], $savedPreference->widget_visibility);
        $this->assertEquals($preferences['widget_order'], $savedPreference->widget_order);
    }

    /**
     * Test that admin can reset dashboard preferences to defaults.
     */
    public function test_admin_can_reset_dashboard_preferences_to_defaults(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('administrator');

        // Create custom preferences
        $customPreference = DashboardPreference::create([
            'user_id' => $admin->id,
            'widget_visibility' => ['collection_status' => false],
            'widget_order' => ['pending_items', 'collection_status'],
        ]);

        // Reset to defaults
        $defaultPreferences = [
            'widget_visibility' => DashboardPreference::getDefaultWidgetVisibility(),
            'widget_order' => DashboardPreference::getDefaultWidgetOrder(),
        ];

        $response = $this->actingAs($admin)
            ->postJson(route('dashboard.preferences.save'), $defaultPreferences);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        // Verify preferences were reset
        $savedPreference = DashboardPreference::where('user_id', $admin->id)->first();
        $this->assertEquals($defaultPreferences['widget_visibility'], $savedPreference->widget_visibility);
        $this->assertEquals($defaultPreferences['widget_order'], $savedPreference->widget_order);
    }

    /**
     * Test that preferences persist across sessions.
     */
    public function test_preferences_persist_across_sessions(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('administrator');

        // Save preferences
        $preferences = [
            'widget_visibility' => [
                'collection_status' => true,
                'pending_items' => false,
            ],
            'widget_order' => ['collection_status', 'recycling_metrics'],
        ];

        $this->actingAs($admin)
            ->postJson(route('dashboard.preferences.save'), $preferences);

        // Simulate new session by making a new request
        $response = $this->actingAs($admin)->get(route('admin.dashboard'));

        $response->assertStatus(200);
        $metrics = $response->viewData('metrics');
        
        // Verify saved preferences are loaded
        $this->assertEquals($preferences['widget_visibility'], $metrics['preferences']['widget_visibility']);
        $this->assertEquals($preferences['widget_order'], $metrics['preferences']['widget_order']);
    }

    /**
     * Test that widget visibility toggle works correctly.
     */
    public function test_widget_visibility_toggle_works(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('administrator');

        // Create initial preference
        $preference = DashboardPreference::create([
            'user_id' => $admin->id,
            'widget_visibility' => ['collection_status' => true],
            'widget_order' => [],
        ]);

        // Toggle widget visibility
        $preference->toggleWidget('collection_status');

        $this->assertFalse($preference->widget_visibility['collection_status']);

        // Toggle again
        $preference->toggleWidget('collection_status');

        $this->assertTrue($preference->widget_visibility['collection_status']);
    }

    /**
     * Test that non-admin users cannot access admin dashboard.
     */
    public function test_non_admin_cannot_access_admin_dashboard(): void
    {
        $resident = User::factory()->create();
        $resident->assignRole('resident');

        $response = $this->actingAs($resident)->get(route('admin.dashboard'));

        $response->assertStatus(403);
    }

    /**
     * Test that unauthenticated users are redirected to login.
     */
    public function test_unauthenticated_users_redirected_to_login(): void
    {
        $response = $this->get(route('admin.dashboard'));

        $response->assertRedirect(route('login'));
    }
}
