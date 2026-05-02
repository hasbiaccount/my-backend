<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create a dummy user that also allow bypassing registration step
        $newUserA = User::create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => bcrypt('admin'),
        ]);

        $newUserB = User::create([
            'name' => 'Organizer User',
            'email' => 'organizer@example.com',
            'password' => bcrypt('organizer'),
        ]);

        $newUserC = User::create([
            'name' => 'Regular User',
            'email' => 'user@example.com',
            'password' => bcrypt('user'),
        ]);

        // Assign roles
        $newUserA->assignRole('admin');
        $newUserB->assignRole('organizer');
        $newUserC->assignRole('user');
    }
}
