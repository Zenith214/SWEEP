<?php

namespace Tests\Feature;

use App\Models\RoleChangeLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoleManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Seed roles
        $this->artisan('db:seed', ['--class' => 'RolePermissionSeeder']);
    }

    public function test_role_assignment(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('administrator');

        $user = User::factory()->create();
        $user->assignRole('resident');

        $response = $this->actingAs($admin)->patch(route('admin.users.update-role', $user), [
            'role' => 'collection_crew',
        ]);

        $response->assertRedirect(route('admin.users.index'));
        $response->assertSessionHas('success');

        $user->refresh();
        $this->assertTrue($user->hasRole('collection_crew'));
        $this->assertFalse($user->hasRole('resident'));
    }

    public function test_role_changes_with_audit_logging(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('administrator');

        $user = User::factory()->create();
        $user->assignRole('resident');

        $this->actingAs($admin)->patch(route('admin.users.update-role', $user), [
            'role' => 'collection_crew',
        ]);

        // Verify audit log was created
        $this->assertDatabaseHas('role_change_logs', [
            'user_id' => $user->id,
            'changed_by' => $admin->id,
            'old_role' => 'resident',
            'new_role' => 'collection_crew',
        ]);

        $log = RoleChangeLog::where('user_id', $user->id)->first();
        $this->assertNotNull($log);
        $this->assertEquals($admin->id, $log->changed_by);
    }

    public function test_prevention_of_self_role_change(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('administrator');

        $response = $this->actingAs($admin)->patch(route('admin.users.update-role', $admin), [
            'role' => 'resident',
        ]);

        $response->assertSessionHasErrors('role');
        $this->assertStringContainsString('cannot change your own role', session('errors')->first('role'));

        // Verify role was not changed
        $admin->refresh();
        $this->assertTrue($admin->hasRole('administrator'));
    }

    public function test_prevention_of_last_admin_removal(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('administrator');

        // This is the only admin
        $response = $this->actingAs($admin)->delete(route('admin.users.destroy', $admin));

        $response->assertSessionHasErrors('role');
        $this->assertStringContainsString('At least one administrator must remain', session('errors')->first('role'));

        // Verify admin was not deleted
        $this->assertDatabaseHas('users', ['id' => $admin->id, 'deleted_at' => null]);
    }

    public function test_prevention_of_last_admin_role_change(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('administrator');

        $anotherAdmin = User::factory()->create();
        $anotherAdmin->assignRole('administrator');

        // Try to change the role of one admin (should succeed since there's another)
        $response = $this->actingAs($admin)->patch(route('admin.users.update-role', $anotherAdmin), [
            'role' => 'resident',
        ]);

        $response->assertRedirect(route('admin.users.index'));
        $response->assertSessionHas('success');

        // Now try to change the last admin's role (should fail)
        $response = $this->actingAs($admin)->patch(route('admin.users.update-role', $admin), [
            'role' => 'resident',
        ]);

        $response->assertSessionHasErrors('role');
    }
}
