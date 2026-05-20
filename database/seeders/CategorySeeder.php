<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Seminar',
                'description' => 'Academic seminar events and guest lectures.',
            ],
            [
                'name' => 'Workshop',
                'description' => 'Hands-on learning sessions and practical training.',
            ],
            [
                'name' => 'Competition',
                'description' => 'Campus contests, hackathons, and tournaments.',
            ],
            [
                'name' => 'Webinar',
                'description' => 'Online talks, classes, and virtual events.',
            ],
            [
                'name' => 'Career',
                'description' => 'Career fairs, recruitment events, and professional development.',
            ],
            [
                'name' => 'Sport',
                'description' => 'Sports matches, fitness activities, and athletic events.',
            ],
            [
                'name' => 'Art',
                'description' => 'Creative performances, exhibitions, and art activities.',
            ],
            [
                'name' => 'Volunteer',
                'description' => 'Community service and volunteer opportunities.',
            ],
        ];

        foreach ($categories as $category) {
            Category::updateOrCreate(
                ['name' => $category['name']],
                [
                    'slug' => Str::slug($category['name']),
                    'description' => $category['description'],
                ],
            );
        }
    }
}
