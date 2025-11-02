<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Showtime;

class ShowtimeSeeder extends Seeder
{
    public function run(): void
    {
        Showtime::insert([
            [
                'movie_id' => 1,
                'theater_id' => '1',
                'room' => 'Phòng 1',
                'time' => '2025-10-18 18:30:00',
                'ticket_price_id' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'movie_id' => 1,
                'theater_id' => '2',
                'room' => 'Phòng 3',
                'time' => '2025-10-18 21:00:00',
                'ticket_price_id' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'movie_id' => 2,
                'theater_id' => '3',
                'room' => 'Phòng 2',
                'time' => '2025-10-19 19:00:00',
                'ticket_price_id' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'movie_id' => 3,
                'theater_id' => '4',
                'room' => 'Phòng 4',
                'time' => '2025-10-20 20:30:00',
                'ticket_price_id' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'movie_id' => 4,
                'theater_id' => '5',
                'room' => 'Phòng 5',
                'time' => '2025-10-21 17:00:00',
                'ticket_price_id' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'movie_id' => 5,
                'theater_id' => '1',
                'room' => 'Phòng 1',
                'time' => '2025-10-22 18:45:00',
                'ticket_price_id' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'movie_id' => 6,
                'theater_id' => '8',
                'room' => 'Phòng 2',
                'time' => '2025-10-22 21:15:00',
                'ticket_price_id' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'movie_id' => 7,
                'theater_id' => '6',
                'room' => 'Phòng 3',
                'time' => '2025-10-23 20:00:00',
                'ticket_price_id' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'movie_id' => 8,
                'theater_id' => '7',
                'room' => 'Phòng 4',
                'time' => '2025-10-24 19:30:00',
                'ticket_price_id' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
