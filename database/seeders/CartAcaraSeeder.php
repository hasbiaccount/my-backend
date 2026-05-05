<?php

namespace Database\Seeders;

use App\Models\CartAcara;
use App\Models\Event;
use App\Models\User;
use Illuminate\Database\Seeder;

class CartAcaraSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::all();
        $events = Event::all();

        if ($users->isEmpty() || $events->isEmpty()) {
            return;
        }

        // Ambil maksimal 5 user untuk di-seed cart-nya
        foreach ($users->take(5) as $user) {
            // Berikan 1-3 acara acak ke dalam cart mereka
            $randomEvents = $events->random(min(rand(1, 3), $events->count()));
            
            foreach ($randomEvents as $event) {
                CartAcara::create([
                    'user_id' => $user->id,
                    'event_id' => $event->id,
                    'quantity' => rand(1, 3),
                ]);
            }
        }
    }
}
