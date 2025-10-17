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
                'theater' => 'CGV Aeon Mall',
                'room' => 'Phòng 1',
                'time' => '2025-10-18 18:30:00',
                'price' => 90000,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'movie_id' => 1,
                'theater' => 'Lotte Mart',
                'room' => 'Phòng 3',
                'time' => '2025-10-18 21:00:00',
                'price' => 100000,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'movie_id' => 2,
                'theater' => 'CGV Vincom',
                'room' => 'Phòng 2',
                'time' => '2025-10-19 19:00:00',
                'price' => 85000,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'movie_id' => 3,
                'theater' => 'Galaxy Nguyễn Du',
                'room' => 'Phòng 4',
                'time' => '2025-10-20 20:30:00',
                'price' => 95000,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'movie_id' => 4,
                'theater' => 'BHD Star',
                'room' => 'Phòng 5',
                'time' => '2025-10-21 17:00:00',
                'price' => 80000,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'movie_id' => 5,
                'theater' => 'Mega GS',
                'room' => 'Phòng 1',
                'time' => '2025-10-22 18:45:00',
                'price' => 110000,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'movie_id' => 6,
                'theater' => 'CGV Aeon Mall',
                'room' => 'Phòng 2',
                'time' => '2025-10-22 21:15:00',
                'price' => 115000,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'movie_id' => 7,
                'theater' => 'Lotte Mart',
                'room' => 'Phòng 3',
                'time' => '2025-10-23 20:00:00',
                'price' => 120000,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'movie_id' => 8,
                'theater' => 'Galaxy Nguyễn Du',
                'room' => 'Phòng 4',
                'time' => '2025-10-24 19:30:00',
                'price' => 130000,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
