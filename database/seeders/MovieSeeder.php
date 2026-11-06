<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Movie;

class MovieSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Movie::insert([
        [
            'title' => 'Avatar: Dòng Chảy Của Nước',
            'description' => 'là phần tiếp theo của Avatar (2009), lấy bối cảnh 15 năm sau, tập trung vào gia đình Jake Sully và Neytiri khi họ phải chạy trốn và tìm nơi ẩn náu ở những vùng biển của Pandora. ',
            'duration' => 190,
            'photo' => 'uploads/movies/avatar-poster-9275.jpg',
            'trailer' => 'https://www.youtube.com/embed/d9MyW72ELq0',
            'created_at' => now(),
            'updated_at' => now(),
        ],
        [
            'title' => 'Mưa Đỏ',
            'description' => 'Tác phẩm tái hiện cuộc chiến khốc liệt 81 ngày đêm tại Thành cổ Quảng Trị vào mùa hè năm 1972, tập trung khắc họa sự hy sinh của những người lính trẻ và ý chí kiên cường của họ trong chiến tranh. ',
            'duration' => 120,
            'photo' => 'uploads/movies/87791512525791828-luudanh.webp',
            'trailer' => 'https://www.youtube.com/embed/BD6PoZJdt_M',
            'created_at' => now(),
            'updated_at' => now(),
        ],
        [
            'title' => 'Tử Chiến Trên Không',
            'description' => 'là một bộ phim hành động, giật gân của Việt Nam, được lấy cảm hứng từ vụ cướp máy bay có thật vào những năm cuối thập niên 1970.',
            'duration' => 140,
            'photo' => 'uploads/movies/t13-4 (td).jpg',
            'trailer' => 'https://www.youtube.com/embed/Q-Zf8KhyS6E',
            'created_at' => now(),
            'updated_at' => now(),
        ],
        [
            'title' => 'Quỷ Ăn Tạng 3',
            'description' => 'Yak và gia đình phải đối mặt với nỗi kinh hoàng mới khi “Yee” – cô em út – đột ngột mất tích bí ẩn. Yak buộc phải cùng Yos, Yod và Papan lên đường đến “Bong Sa Noh Bian” – khu rừng ma ám – để cứu Yee trước khi những linh hồn tà ác một lần nữa bị đánh thức.',
            'duration' => 104,
            'photo' => 'uploads/movies/544559206_122181488576483671_1576156352025365396_n.jpg',
            'trailer' => 'https://www.youtube.com/embed/DMOGnGokm4c',
            'created_at' => now(),
            'updated_at' => now(),
        ],
        [
            'title' => 'Chiến Binh Báo Đen: Wakanda Mãi Mãi',
            'description' => 'là phần tiếp theo của Chiến Binh Báo Đen (2018), tiếp tục khám phá vương quốc Wakanda và di sản của Challa sau cái chết của Chadwick Boseman.',
            'duration' => 130,
            'photo' => 'uploads/movies/Black_Panther_Wakanda_Forever_poster.jpg',
            'trailer' => 'https://www.youtube.com/embed/_q8uQMGRIVA',
            'created_at' => now(),
            'updated_at' => now(),
        ],
        [
            'title' => 'spider-man: no way home',
            'description' => 'là bộ phim tiếp nối Spider-Man: Far From Home, trong đó danh tính Người Nhện của Peter Parker bị lộ.',
            'duration' => 115,
            'photo' => 'uploads/movies/s-l1200.jpg',
            'trailer' => 'https://www.youtube.com/embed/JfVOs4VSpmA',
            'created_at' => now(),
            'updated_at' => now(),
        ],
        [
            'title' => 'John Wick 4',
            'description' => 'theo chân sát thủ John Wick trên hành trình tìm cách đánh bại Hội đồng Tối cao để giành lại tự do.',
            'duration' => 169,
            'photo' => 'uploads/movies/dfthfthj.jpg',
            'trailer' => 'https://www.youtube.com/embed/qEVUtrk8_B4',
            'created_at' => now(),
            'updated_at' => now(),
        ],
        [
            'title' => 'Batman đại chiến Superman: Ánh Dương Công Lý',
            'description' => 'bộ phim sẽ xoay quanh cuộc đối đầu có 1-0-2 của vị hiệp sĩ mạnh mẽ, đáng gờm nhất của thành phố Gotham với biểu tượng được tôn sùng nhất của thành phố Metropolis.',
            'duration' => 152,
            'photo' => 'uploads/movies/MV5BZTJkYjdmYjYtOGMyNC00ZGU1LThkY2ItYTc1OTVlMmE2YWY1XkEyXkFqcGc@._V1_.jpg',
            'trailer' => 'https://www.youtube.com/embed/0WWzgGyAH6Y',
            'created_at' => now(),
            'updated_at' => now(),
        ],
    ]);
    }
}
