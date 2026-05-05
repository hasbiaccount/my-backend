<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Run this seeder in order
        $this->call([
            PermissionSeeder::class,
            UserSeeder::class,
            EventSeeder::class,
            ParticipantSeeder::class,
            EventLinkSeeder::class,
            CartSeeder::class,
        ]);
    }
}
