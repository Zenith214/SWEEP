<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Define all permissions based on design document
        $permissions = [
            // User permissions
            'users.create',
            'users.read',
            'users.update',
            'users.delete',
            'users.manage_roles',

            // Route permissions
            'routes.create',
            'routes.read',
            'routes.update',
            'routes.delete',

            // Schedule permissions
            'schedules.create',
            'schedules.read',
            'schedules.update',
            'schedules.delete',

            // Collection permissions
            'collections.create',
            'collections.read',
            'collections.update',

            // Report permissions
            'reports.create',
            'reports.read',
            'reports.update',
            'reports.delete',

            // Recycling permissions
            'recycling.create',
            'recycling.read',
            'recycling.update',

            // Dashboard permissions
            'dashboard.admin',
            'dashboard.crew',
            'dashboard.resident',
        ];

        // Create all permissions
        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        // Create roles and assign permissions
        
        // Administrator role - full system access
        $adminRole = Role::firstOrCreate(['name' => 'administrator', 'guard_name' => 'web']);
        $adminRole->syncPermissions([
            'users.create',
            'users.read',
            'users.update',
            'users.delete',
            'users.manage_roles',
            'routes.create',
            'routes.read',
            'routes.update',
            'routes.delete',
            'schedules.create',
            'schedules.read',
            'schedules.update',
            'schedules.delete',
            'collections.create',
            'collections.read',
            'collections.update',
            'reports.create',
            'reports.read',
            'reports.update',
            'reports.delete',
            'recycling.create',
            'recycling.read',
            'recycling.update',
            'dashboard.admin',
        ]);

        // Collection Crew role - access to route and collection features
        $crewRole = Role::firstOrCreate(['name' => 'collection_crew', 'guard_name' => 'web']);
        $crewRole->syncPermissions([
            'routes.read',
            'schedules.read',
            'collections.create',
            'collections.read',
            'collections.update',
            'dashboard.crew',
        ]);

        // Resident role - access to schedule viewing and reporting features
        $residentRole = Role::firstOrCreate(['name' => 'resident', 'guard_name' => 'web']);
        $residentRole->syncPermissions([
            'schedules.read',
            'reports.create',
            'reports.read',
            'dashboard.resident',
        ]);
    }
}
