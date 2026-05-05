<?php

namespace Database\Seeders;

use App\Models\Event;
use Illuminate\Database\Seeder;

class EventLinkSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $events = Event::all();

        foreach ($events as $event) {
            $event->eventLinks()->createMany([
                [
                    'title' => 'Google Form',
                    'url'   => 'https://forms.gle/' . fake()->regexify('[A-Za-z0-9]{15}'),
                ],
                [
                    'title' => 'Instagram Official',
                    'url'   => 'https://instagram.com/' . fake()->userName(),
                ],
                [
                    'title' => 'Linktree',
                    'url'   => 'https://linktr.ee/' . fake()->userName(),
                ],
                [
                    'title' => 'Tokopedia (Merchandise)',
                    'url'   => 'https://tokopedia.com/toko-' . fake()->domainWord(),
                ],
                [
                    'title' => 'Shopee (Tiket & Merch)',
                    'url'   => 'https://shopee.co.id/' . fake()->userName(),
                ],
                [
                    'title' => 'WhatsApp Admin',
                    'url'   => 'https://wa.me/' . fake()->numerify('6281#########'),
                ],
                [
                    'title' => 'Website Resmi',
                    'url'   => 'https://www.' . fake()->domainName(),
                ],
                [
                    'title' => 'TikTok',
                    'url'   => 'https://tiktok.com/@' . fake()->userName(),
                ],
                [
                    'title' => 'X (Twitter)',
                    'url'   => 'https://x.com/' . fake()->userName(),
                ],
            ]);
        }
    }
}
