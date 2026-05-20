<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Event;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class EventSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Check if user exist in case UserSeeder failed
        if (User::count() == 0) {
            $this->command->warn('No users found. Please run UserSeeder first.');
            return;
        }

        // If users with role 'organizer' are seeded, assign them as organizer_id
        $users = User::role('organizer')->get();

        // Check if there is ANY user with role 'organizer'
        if ($users->isEmpty()) {
            $this->command->warn('No organizers found. Please run UserSeeder first or create an organizer.');
            return;
        }

        $categories = Category::all();

        if ($categories->isEmpty()) {
            $this->command->warn('No categories found. Please run CategorySeeder first.');
            return;
        }

        // Create 50 events with random users with role 'organizer'
        for ($i = 1; $i <= 50; $i++) {
            $event = Event::factory()->raw([
                'title' => "Campus Hub Event {$i}",
                'organizer_id' => $users->random()->id,
                'category_id' => $categories->random()->id,
            ]);

            Event::updateOrCreate(
                ['title' => $event['title']],
                $event,
            );
        }
    }
}
