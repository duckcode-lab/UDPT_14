<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\TicketPrice;

class TicketPriceSeeder extends Seeder
{
    public function run(): void
    {
        TicketPrice::insert([
            ['theater_id' => 1, 'type' => '2D', 'price' => 75000],
            ['theater_id' => 1, 'type' => '3D', 'price' => 90000],
            ['theater_id' => 2, 'type' => '2D', 'price' => 80000],
            ['theater_id' => 2, 'type' => '3D', 'price' => 95000],
            ['theater_id' => 3, 'type' => 'VIP', 'price' => 120000],
            ['theater_id' => 3, 'type' => 'IMAX', 'price' => 150000],
            ['theater_id' => 4, 'type' => '2D', 'price' => 70000],
            ['theater_id' => 4, 'type' => '3D', 'price' => 85000],
            ['theater_id' => 5, 'type' => '4DX', 'price' => 160000],
        ]);
    }
}
