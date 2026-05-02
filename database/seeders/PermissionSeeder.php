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

        // Roles
        $adminRole = Role::create(['name' => 'admin']);
        $organizerRole = Role::create(['name' => 'organizer']);
        $userRole = Role::create(['name' => 'user']);

        // Events Table Permissions
        Permission::create(['name' => 'create events']);
        Permission::create(['name' => 'update events']);
        Permission::create(['name' => 'delete events']);

        // Category Table Permissions
        Permission::create(['name' => 'create categories']);
        Permission::create(['name' => 'update categories']);
        Permission::create(['name' => 'delete categories']);

        // Other Table Permission
        // [ HERE ]

        // Reset Cached Permission AFTER Seeding (due to WithoutModelEvents Trait)
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // Assign Admin Permissions
        $adminRole->givePermissionTo(Permission::all());
        
        // Assign Organizer Permissions
        $organizerRole->givePermissionTo([
            // Events
            'create events',
            'update events',
            'delete events',
        ]);

        // Assign User Permissions
        $userRole->givePermissionTo([
            // None yet
        ]);
    }
}
