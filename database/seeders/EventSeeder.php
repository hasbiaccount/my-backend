<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Event;
use App\Models\User;
use App\Models\Image;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;

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

        // If users with role 'admin' are seeded, assign them as organizer_id
        $users = User::role('admin')->get();

        // Check if there is ANY user with role 'admin'
        if ($users->isEmpty()) {
            $this->command->warn('No admin users found. Please run UserSeeder first.');
            return;
        }

        $categories = Category::all();

        if ($categories->isEmpty()) {
            $this->command->warn('No categories found. Please run CategorySeeder first.');
            return;
        }

        // Create 50 events with random users with role 'admin'
        for ($i = 1; $i <= 50; $i++) {
            $event = Event::factory()->raw([
                'title' => "Campus Hub Event {$i}",
                'organizer_id' => $users->random()->id,
                'category_id' => $categories->random()->id,
            ]);

            $eventModel = Event::updateOrCreate(
                ['title' => $event['title']],
                $event,
            );

            // Seed image assets
            $imageNum = (($i - 1) % 50) + 1; // event_1.jpg to event_50.jpg
            $sourcePath = database_path("seeders/images/events/event_{$imageNum}.jpg");
            $destDir = storage_path('app/public/images');
            
            if (!File::exists($destDir)) {
                File::makeDirectory($destDir, 0755, true);
            }
            
            $destPath = "{$destDir}/event_{$imageNum}.jpg";
            if (File::exists($sourcePath)) {
                File::copy($sourcePath, $destPath);
            }

            // Link image to the event
            Image::updateOrCreate(
                ['event_id' => $eventModel->id, 'path' => "images/event_{$imageNum}.jpg"],
                ['event_id' => $eventModel->id, 'path' => "images/event_{$imageNum}.jpg"]
            );
        }
    }
}
