<?php

namespace Database\Factories;

use App\Models\Event;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Event>
 */
class EventFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            // 'organizer_id' should be filled on seeder or upon creation,
            'title' => fake()->sentence(),
            'description' => fake()->paragraph(),
            'start_date' => fake()->dateTimeBetween('+1 day', '+1 year'),
            'end_date' => fake()->dateTimeBetween('+2 days', '+1 year'),
            'location' => fake()->city(),
            'max_participants' => fake()->numberBetween(10, 1000),
            'registration_fee' => fake()->numberBetween(2000, 100000),
            'registration_open' => fake()->dateTimeBetween('-1 year', 'now'),
            'registration_deadline' => fake()->dateTimeBetween('now', '+1 year'),
        ];
    }
}
