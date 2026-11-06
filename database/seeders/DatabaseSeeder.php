<?php

namespace Database\Seeders;
use App\Models\Movie;
use App\Models\Showtime;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;


class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            TheaterSeeder::class,
            TicketPriceSeeder::class,
            MovieSeeder::class,
            ShowtimeSeeder::class,
        ]);
        User::updateOrCreate(
            ['email' => 'admin@cinema.com'],
            [
            'name' => 'Admin',
            'password' => Hash::make('admin123'),
            'role' => 'admin'
            ]
        );
    }
}
