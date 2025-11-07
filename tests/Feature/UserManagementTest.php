<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Seed roles
        $this->artisan('db:seed', ['--class' => 'RolePermissionSeeder']);
    }

    public function test_administrator_can_create_user(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('administrator');

        $response = $this->actingAs($admin)->post(route('admin.users.store'), [
            'name' => 'New User',
            'email' => 'newuser@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'resident',
        ]);

        $response->assertRedirect(route('admin.users.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('users', [
            'name' => 'New User',
            'email' => 'newuser@example.com',
        ]);

        $user = User::where('email', 'newuser@example.com')->first();
        $this->assertTrue($user->hasRole('resident'));
    }

    public function test_duplicate_email_validation(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('administrator');

        $existingUser = User::factory()->create(['email' => 'existing@example.com']);

        $response = $this->actingAs($admin)->post(route('admin.users.store'), [
            'name' => 'Duplicate User',
            'email' => 'existing@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'resident',
        ]);

        $response->assertSessionHasErrors('email');
    }

    public function test_user_listing_and_filtering(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('administrator');

        $resident1 = User::factory()->create(['name' => 'John Resident']);
        $resident1->assignRole('resident');

        $crew = User::factory()->create(['name' => 'Jane Crew']);
        $crew->assignRole('collection_crew');

        // Test listing all users
        $response = $this->actingAs($admin)->get(route('admin.users.index'));
        $response->assertOk();
        $response->assertSee('John Resident');
        $response->assertSee('Jane Crew');

        // Test filtering by role
        $response = $this->actingAs($admin)->get(route('admin.users.index', ['role' => 'resident']));
        $response->assertOk();
        $response->assertSee('John Resident');
        $response->assertDontSee('Jane Crew');
    }

    public function test_user_editing(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('administrator');

        $user = User::factory()->create(['name' => 'Old Name']);
        $user->assignRole('resident');

        $response = $this->actingAs($admin)->patch(route('admin.users.update', $user), [
            'name' => 'New Name',
        ]);

        $response->assertRedirect(route('admin.users.index'));
        $response->assertSessionHas('success');

        $user->refresh();
        $this->assertEquals('New Name', $user->name);
    }

    public function test_user_deletion_with_soft_delete(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('administrator');

        $user = User::factory()->create();
        $user->assignRole('resident');

        $response = $this->actingAs($admin)->delete(route('admin.users.destroy', $user));

        $response->assertRedirect(route('admin.users.index'));
        $response->assertSessionHas('success');

        // User should be soft deleted
        $this->assertSoftDeleted('users', ['id' => $user->id]);
    }

    public function test_search_functionality(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('administrator');

        $user1 = User::factory()->create(['name' => 'Alice Smith', 'email' => 'alice@example.com']);
        $user1->assignRole('resident');

        $user2 = User::factory()->create(['name' => 'Bob Jones', 'email' => 'bob@example.com']);
        $user2->assignRole('resident');

        // Search by name
        $response = $this->actingAs($admin)->get(route('admin.users.index', ['search' => 'Alice']));
        $response->assertOk();
        $response->assertSee('Alice Smith');
        $response->assertDontSee('Bob Jones');

        // Search by email
        $response = $this->actingAs($admin)->get(route('admin.users.index', ['search' => 'bob@']));
        $response->assertOk();
        $response->assertSee('Bob Jones');
        $response->assertDontSee('Alice Smith');
    }
}
