<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ProfileManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Seed roles
        $this->artisan('db:seed', ['--class' => 'RolePermissionSeeder']);
    }

    public function test_profile_information_update(): void
    {
        $user = User::factory()->create(['name' => 'Old Name']);
        $user->assignRole('resident');

        $response = $this->actingAs($user)->patch(route('profile.update'), [
            'name' => 'New Name',
            'email' => $user->email,
        ]);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect(route('profile.edit'));

        $user->refresh();
        $this->assertEquals('New Name', $user->name);
    }

    public function test_password_change_with_verification(): void
    {
        $user = User::factory()->create([
            'password' => bcrypt('oldpassword123'),
        ]);
        $user->assignRole('resident');

        $response = $this->actingAs($user)->patch(route('profile.password.update'), [
            'current_password' => 'oldpassword123',
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123',
        ]);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect(route('profile.edit'));

        $user->refresh();
        $this->assertTrue(Hash::check('newpassword123', $user->password));
    }

    public function test_password_change_requires_current_password(): void
    {
        $user = User::factory()->create([
            'password' => bcrypt('oldpassword123'),
        ]);
        $user->assignRole('resident');

        $response = $this->actingAs($user)->patch(route('profile.password.update'), [
            'current_password' => 'wrongpassword',
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123',
        ]);

        $response->assertSessionHasErrors('current_password');

        // Verify password was not changed
        $user->refresh();
        $this->assertTrue(Hash::check('oldpassword123', $user->password));
    }

    public function test_password_strength_validation(): void
    {
        $user = User::factory()->create();
        $user->assignRole('resident');

        // Test password less than 8 characters
        $response = $this->actingAs($user)->patch(route('profile.password.update'), [
            'current_password' => 'password',
            'password' => 'short',
            'password_confirmation' => 'short',
        ]);

        $response->assertSessionHasErrors('password');
    }

    public function test_profile_update_confirmation_message(): void
    {
        $user = User::factory()->create();
        $user->assignRole('resident');

        $response = $this->actingAs($user)->patch(route('profile.update'), [
            'name' => 'Updated Name',
            'email' => $user->email,
        ]);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect(route('profile.edit'));
    }
}
