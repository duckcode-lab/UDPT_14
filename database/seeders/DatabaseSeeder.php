<?php

namespace Database\Seeders;
use App\Models\Movie;
use App\Models\Showtime;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
{
    $this->call([
        MovieSeeder::class,
        ShowtimeSeeder::class,
    ]);
}
}
