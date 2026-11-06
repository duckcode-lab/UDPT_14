<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Theater;

class TheaterSeeder extends Seeder
{

    public function run(): void
    {
        Theater::insert([
            ['name' => 'CGV Aeon Mall Hà Đông', 'address' => 'Tầng 3 & 4 – TTTM AEON MALL HÀ ĐÔNG, P. Dương Nội, Q. Hà Đông, Hà Nội'],
            ['name' => 'CGV Aeon Mall Long Biên', 'address' => 'Tầng 4 - TTTM AEON Long Biên, Số 27 Cổ Linh, Quận Long Biên, Hà Nội'],
            ['name' => 'CGV Vincom Bà Triệu', 'address' => 'Tầng 5, Vincom Center Bà Triệu, 191 Bà Triệu, Quận Hai Bà Trưng, Hà Nội'],
            ['name' => 'Lotte Mart Hà Đông', 'address' => 'Tầng 3, Lotte Mart Hà Đông, Số 6 đường Tô Hiệu, Quận Hà Đông, Hà Nội'],
            ['name' => 'Galaxy Nguyễn Du', 'address' => '116 Nguyễn Du, Phường Bến Thành, Quận 1, TP. Hồ Chí Minh'],
            ['name' => 'BHD Star Cineplex Phạm Hùng', 'address' => 'Tầng 3, TTTM Crescent Mall, Số 101 Tôn Dật Tiên, Phường Tân Phú, Quận 7, TP. Hồ Chí Minh'],
            ['name' => 'Mega GS Cao Thắng', 'address' => 'Tầng 3, TTTM NowZone, Số 235 Cao Thắng, Phường 12, Quận 10, TP. Hồ Chí Minh'],
            ['name' => 'CGV Vincom Đồng Khởi', 'address' => 'Tầng B2, Vincom Center Đồng Khởi, 72 Lê Thánh Tôn, Quận 1, TP. Hồ Chí Minh'],
        ]);
    }
}
