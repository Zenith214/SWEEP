<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Route;
use App\Models\Schedule;
use App\Models\ScheduleDay;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ErrorHandlingTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create administrator role
        \Spatie\Permission\Models\Role::create(['name' => 'administrator']);
        
        // Create admin user
        $this->admin = User::factory()->create();
        $this->admin->assignRole('administrator');
    }

    /** @test */
    public function it_prevents_deleting_route_with_active_schedules()
    {
        // Create a route with an active schedule
        $route = Route::factory()->create(['is_active' => true]);
        
        $schedule = Schedule::factory()->create([
            'route_id' => $route->id,
            'is_active' => true,
            'start_date' => now(),
            'end_date' => null,
        ]);
        
        ScheduleDay::create([
            'schedule_id' => $schedule->id,
            'day_of_week' => 1, // Monday
        ]);

        // Attempt to delete the route
        $response = $this->actingAs($this->admin)
            ->delete(route('admin.routes.destroy', $route));

        // Should redirect back with error message
        $response->assertRedirect();
        $response->assertSessionHas('error', 'Cannot delete route with active schedules. Please deactivate or delete schedules first.');
        
        // Route should still exist
        $this->assertDatabaseHas('routes', ['id' => $route->id]);
    }

    /** @test */
    public function it_shows_error_for_duplicate_route_name()
    {
        // Create a route
        $existingRoute = Route::factory()->create(['name' => 'Downtown Route']);

        // Attempt to create another route with the same name
        $response = $this->actingAs($this->admin)
            ->post(route('admin.routes.store'), [
                'name' => 'Downtown Route',
                'zone' => 'Zone A',
                'is_active' => true,
            ]);

        // Should redirect back with validation error
        $response->assertSessionHasErrors('name');
    }

    /** @test */
    public function it_shows_error_for_schedule_without_days()
    {
        $route = Route::factory()->create(['is_active' => true]);

        // Attempt to create schedule without selecting days
        $response = $this->actingAs($this->admin)
            ->post(route('admin.schedules.store'), [
                'route_id' => $route->id,
                'collection_time' => '08:00',
                'start_date' => now()->format('Y-m-d'),
                'is_active' => true,
                // Missing days_of_week
            ]);

        // Should redirect back with validation error
        $response->assertSessionHasErrors('days_of_week');
    }

    /** @test */
    public function it_shows_error_for_past_start_date()
    {
        $route = Route::factory()->create(['is_active' => true]);

        // Attempt to create schedule with past start date
        $response = $this->actingAs($this->admin)
            ->post(route('admin.schedules.store'), [
                'route_id' => $route->id,
                'collection_time' => '08:00',
                'start_date' => now()->subDays(5)->format('Y-m-d'),
                'days_of_week' => [1, 3, 5],
                'is_active' => true,
            ]);

        // Should redirect back with validation error
        $response->assertSessionHasErrors('start_date');
    }

    /** @test */
    public function it_shows_error_for_end_date_before_start_date()
    {
        $route = Route::factory()->create(['is_active' => true]);

        // Attempt to create schedule with end date before start date
        $response = $this->actingAs($this->admin)
            ->post(route('admin.schedules.store'), [
                'route_id' => $route->id,
                'collection_time' => '08:00',
                'start_date' => now()->addDays(10)->format('Y-m-d'),
                'end_date' => now()->addDays(5)->format('Y-m-d'),
                'days_of_week' => [1, 3, 5],
                'is_active' => true,
            ]);

        // Should redirect back with validation error
        $response->assertSessionHasErrors('end_date');
    }

    /** @test */
    public function it_shows_success_message_after_creating_route()
    {
        $response = $this->actingAs($this->admin)
            ->post(route('admin.routes.store'), [
                'name' => 'New Route',
                'zone' => 'Zone B',
                'is_active' => true,
            ]);

        $response->assertRedirect(route('admin.routes.index'));
        $response->assertSessionHas('success', 'Route created successfully.');
    }

    /** @test */
    public function it_shows_success_message_after_updating_route()
    {
        $route = Route::factory()->create();

        $response = $this->actingAs($this->admin)
            ->put(route('admin.routes.update', $route), [
                'name' => 'Updated Route Name',
                'zone' => 'Zone C',
                'is_active' => true,
            ]);

        $response->assertRedirect(route('admin.routes.index'));
        $response->assertSessionHas('success', 'Route updated successfully.');
    }

    /** @test */
    public function it_shows_success_message_after_deleting_route()
    {
        $route = Route::factory()->create();

        $response = $this->actingAs($this->admin)
            ->delete(route('admin.routes.destroy', $route));

        $response->assertRedirect(route('admin.routes.index'));
        $response->assertSessionHas('success', 'Route deleted successfully.');
    }
}
