<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthorizationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Seed roles
        $this->artisan('db:seed', ['--class' => 'RolePermissionSeeder']);
    }

    public function test_administrator_access_to_all_features(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('administrator');

        // Test access to user management
        $response = $this->actingAs($admin)->get(route('admin.users.index'));
        $response->assertOk();

        // Test access to admin dashboard
        $response = $this->actingAs($admin)->get(route('admin.dashboard'));
        $response->assertOk();
    }

    public function test_collection_crew_access_restrictions(): void
    {
        $crew = User::factory()->create();
        $crew->assignRole('collection_crew');

        // Should NOT have access to user management
        $response = $this->actingAs($crew)->get(route('admin.users.index'));
        $response->assertRedirect(route('dashboard'));
        $response->assertSessionHas('error');

        // Should have access to crew dashboard
        $response = $this->actingAs($crew)->get(route('crew.dashboard'));
        $response->assertOk();
    }

    public function test_resident_access_restrictions(): void
    {
        $resident = User::factory()->create();
        $resident->assignRole('resident');

        // Should NOT have access to user management
        $response = $this->actingAs($resident)->get(route('admin.users.index'));
        $response->assertRedirect(route('dashboard'));
        $response->assertSessionHas('error');

        // Should have access to resident dashboard
        $response = $this->actingAs($resident)->get(route('resident.dashboard'));
        $response->assertOk();
    }

    public function test_unauthorized_access_handling(): void
    {
        $resident = User::factory()->create();
        $resident->assignRole('resident');

        // Try to access admin-only feature
        $response = $this->actingAs($resident)->get(route('admin.users.create'));
        $response->assertRedirect(route('dashboard'));
        $response->assertSessionHas('error', 'You do not have permission to access this resource.');
    }

    public function test_unauthenticated_user_redirected_to_login(): void
    {
        // Try to access protected route without authentication
        $response = $this->get(route('dashboard'));
        $response->assertRedirect(route('login'));

        $response = $this->get(route('admin.users.index'));
        $response->assertRedirect(route('login'));
    }

    public function test_role_based_dashboard_access(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('administrator');

        $crew = User::factory()->create();
        $crew->assignRole('collection_crew');

        $resident = User::factory()->create();
        $resident->assignRole('resident');

        // Admin can access admin dashboard
        $response = $this->actingAs($admin)->get(route('admin.dashboard'));
        $response->assertOk();

        // Crew cannot access admin dashboard
        $response = $this->actingAs($crew)->get(route('admin.dashboard'));
        $response->assertRedirect(route('dashboard'));

        // Resident cannot access crew dashboard
        $response = $this->actingAs($resident)->get(route('crew.dashboard'));
        $response->assertRedirect(route('dashboard'));
    }
}
