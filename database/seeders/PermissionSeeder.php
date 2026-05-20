<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

// Spatie Permission Models
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset Cached Permission BEFORE Seeding
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $guard = 'api';

        // Roles
        $adminRole = Role::updateOrCreate(['name' => 'admin', 'guard_name' => $guard]);
        $organizerRole = Role::updateOrCreate(['name' => 'organizer', 'guard_name' => $guard]);
        $userRole = Role::updateOrCreate(['name' => 'user', 'guard_name' => $guard]);

        $permissions = [
            'create events',
            'update events',
            'delete events',
            'create categories',
            'update categories',
            'delete categories',
            'enroll events',
            'manage participants',
        ];

        foreach ($permissions as $permission) {
            Permission::updateOrCreate(['name' => $permission, 'guard_name' => $guard]);
        }

        // Reset Cached Permission AFTER Seeding (due to WithoutModelEvents Trait)
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // Assign Admin Permissions
        $adminRole->syncPermissions($permissions);
        
        // Assign Organizer Permissions
        $organizerRole->syncPermissions([
            'create events',
            'update events',
            'delete events',
            'enroll events',
            'manage participants',
        ]);

        // Assign User Permissions
        $userRole->syncPermissions([
            'enroll events',
        ]);
    }
}
