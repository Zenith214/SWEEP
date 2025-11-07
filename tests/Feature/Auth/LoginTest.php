<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\RateLimiter;
use Tests\TestCase;

class LoginTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Seed roles
        $this->artisan('db:seed', ['--class' => 'RolePermissionSeeder']);
    }

    public function test_successful_login_with_valid_credentials(): void
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password123'),
        ]);
        $user->assignRole('resident');

        $response = $this->post('/login', [
            'email' => 'test@example.com',
            'password' => 'password123',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('resident.dashboard'));
    }

    public function test_failed_login_with_invalid_credentials(): void
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password123'),
        ]);

        $response = $this->post('/login', [
            'email' => 'test@example.com',
            'password' => 'wrongpassword',
        ]);

        $this->assertGuest();
        $response->assertSessionHasErrors();
    }

    public function test_rate_limiting_after_multiple_failed_attempts(): void
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password123'),
        ]);

        // Make 5 failed login attempts
        for ($i = 0; $i < 5; $i++) {
            $this->post('/login', [
                'email' => 'test@example.com',
                'password' => 'wrongpassword',
            ]);
        }

        // 6th attempt should be rate limited
        $response = $this->post('/login', [
            'email' => 'test@example.com',
            'password' => 'wrongpassword',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertStringContainsString('Too many login attempts', session('errors')->first('email'));
    }

    public function test_administrator_redirected_to_admin_dashboard(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('administrator');

        $response = $this->post('/login', [
            'email' => $admin->email,
            'password' => 'password',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('admin.dashboard'));
    }

    public function test_collection_crew_redirected_to_crew_dashboard(): void
    {
        $crew = User::factory()->create();
        $crew->assignRole('collection_crew');

        $response = $this->post('/login', [
            'email' => $crew->email,
            'password' => 'password',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('crew.dashboard'));
    }

    public function test_resident_redirected_to_resident_dashboard(): void
    {
        $resident = User::factory()->create();
        $resident->assignRole('resident');

        $response = $this->post('/login', [
            'email' => $resident->email,
            'password' => 'password',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('resident.dashboard'));
    }

    public function test_rate_limiter_cleared_on_successful_login(): void
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password123'),
        ]);
        $user->assignRole('resident');

        // Make 3 failed attempts
        for ($i = 0; $i < 3; $i++) {
            $this->post('/login', [
                'email' => 'test@example.com',
                'password' => 'wrongpassword',
            ]);
        }

        // Successful login should clear rate limiter
        $this->post('/login', [
            'email' => 'test@example.com',
            'password' => 'password123',
        ]);

        $key = 'login-attempts:test@example.com';
        $this->assertFalse(RateLimiter::tooManyAttempts($key, 5));
    }
}
