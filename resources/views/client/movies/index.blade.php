@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gray-900  py-10 px-6">
    <div class="max-w-7xl mx-auto">
        <div class="flex items-center" style="padding-left: 500px;justify-content: space-between;">
        <h1 class="text-3xl font-bold mb-8 text-center text-yellow-400">Phim đang chiếu</h1>
        <form action="{{ route('movies.search') }}" method="GET" class=" w-64 gray-900">
            <input type="text" name="q" id="search-box"
                placeholder="Tìm phim..."
                autocomplete="off"
                class="w-full px-3 py-2 rounded-md text-gray-900 focus:outline-none">
            <div id="suggestions"
                class="absolute bg-white text-black  mt-1 rounded-md shadow-lg z-50 hidden" style="White-space: nowrap;overflow: hidden;text-overflow: ellipsis; width: 257px;">
            </div>
        </form>
        </div>
        <section class="mb-12 relative" style="height: 80%">
            <div class="swiper mySwiper">
                <div class="swiper-wrapper">
                    @foreach($banners as $banner)
                        <div class="swiper-slide relative group overflow-hidden rounded-lg shadow-lg bg-black">

                            <a href="{{ $banner->link ?? route('movies.show', $banner->movie_id ?? 0) }}" class="block w-full h-[500px]">
                                <div class="w-full h-full flex justify-center items-center bg-black">
                                    <img src="{{ asset('storage/' . $banner->image) }}"
                                        alt="{{ $banner->title }}"
                                        class="transition-transform duration-500 group-hover:scale-110 opacity-100" style="width: 1200px;height: 570px; position: relative;top:35px">
                                </div>
                            </a>

                            <div class="absolute bottom-0 left-0 right-0 bg-gradient-to-t from-black via-transparent opacity-0 group-hover:opacity-100 transition p-6">
                                {{-- Tên phim --}}
                                <h2 class="text-2xl font-bold text-yellow-400 mb-2">
                                    {{ $banner->title }}
                                </h2>

                                {{-- Nút xem chi tiết --}}
                                <a href="{{ $banner->link ?? route('movies.show', $banner->movie_id ?? 0) }}"
                                class=" text-white font-semibold  py-2 rounded-md transition-all duration-200">
                                    Xem chi tiết
                                </a>
                            </div>
                        </div>
                    @endforeach
                </div>

                {{-- Nút điều hướng Swiper --}}
                <div class="swiper-button-next text-yellow-400"></div>
                <div class="swiper-button-prev text-yellow-400"></div>
                <div class="swiper-pagination"></div>
            </div>
        </section>

        <section class="mb-12">
            <h2 class="text-3xl font-bold text-yellow-400 mb-6">
                🎬 Phim Hay Hằng Tuần
            </h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                @foreach($weeklyMovies as $movie)
                    <div class="bg-gray-900 rounded-lg shadow-lg hover:scale-105 transition duration-300 overflow-hidden">
                        <a href="{{ route('movies.show', $movie->id) }}">
                            @php
                                $photoPath = Str::startsWith($movie->photo, 'uploads/')
                                    ? asset('storage/' . $movie->photo)
                                    : asset($movie->photo);
                            @endphp
                            @if($movie->photo)
                                <a href="{{ route('movies.show', $movie->id) }}">
                                    <img src="{{ $photoPath }}"
                                        alt="{{ $movie->title }}"
                                        class="w-full object-cover" style="height: 400px">
                                </a>
                            @else
                                <span class="text-gray-400 italic">Không có poster</span>
                            @endif
                            <div class="p-3 text-center">
                                <h3 class="font-semibold text-yellow-400 truncate">{{ $movie->title }}</h3>
                                <p class="text-gray-400 text-sm mt-1">
                                    {{ $movie->genres->pluck('name')->implode(', ') }}
                                </p>
                                <p class="text-sm text-gray-400 mt-1">{{ Str::limit($movie->description, 40) }}</p>
                            </div>
                        </a>
                    </div>
                @endforeach
            </div>
        </section>
        {{-- 🔥 PHIM NỔI BẬT TRONG THÁNG --}}
        <section>
    <h2 class="text-3xl font-bold text-orange-400 mb-6">
        🔥 Phim Nổi Bật Trong Tháng
    </h2>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
        @foreach($monthlyMovies as $movie)

            @php
                $photoPath = Str::startsWith($movie->photo, 'uploads/')
                    ? asset('storage/' . $movie->photo)
                    : asset($movie->photo);
            @endphp

            <a href="{{ route('movies.show', $movie->id) }}"
               class="block group relative bg-gray-900 rounded-xl shadow-lg overflow-hidden transform transition duration-300 hover:scale-[1.05]">

                {{-- Ảnh --}}
                <img src="{{ $photoPath }}"
                     alt="{{ $movie->title }}"
                     class="transition duration-500 group-hover:scale-110">

                {{-- Overlay --}}
                <div class="absolute inset-0 bg-gradient-to-t from-black via-black/40 to-transparent
                            opacity-0 group-hover:opacity-100 transition duration-300"></div>

                {{-- Text --}}
                <div class="absolute bottom-0 w-full p-4 translate-y-6 opacity-0
                            group-hover:translate-y-0 group-hover:opacity-100
                            transition-all duration-300">

                    <h3 class="text-yellow-400 font-bold text-lg drop-shadow">
                        {{ $movie->title }}
                    </h3>

                    <p class="text-gray-300 text-sm">
                        {{ $movie->genres->pluck('name')->implode(', ') }}
                    </p>

                    <div class="mt-2 text-sm text-gray-200 font-semibold flex items-center gap-1">
                        Chi tiết
                    </div>
                </div>
            </a>

        @endforeach
    </div>
</section>
    </div>
    <footer class="mt-16 text-gray-500 bg-black px-10 py-8 rounded-lg border-t border-gray-700 w-780 ">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 mx-auto mb-6 max-w-7xl gap-8">
                <ul>
                    <li><a class="font-bold">Hotline <br><br><span class="text-yellow-500">0338216050</span></a></li>
                    <li><br><a class="font-bold">Email <br><br><span class="text-yellow-500">phamduc13425@gmail.com</span></a></li>
                </ul>
                <ul class="mx-10 px-5" >
                    <li class="font-bold">Dành cho khách hàng</li>
                    <li class="text-yellow-500 font-bold"><br>Điều khoản sử dụng cho khách hàng</li>
                    <li class="font-bold"><br>Dành cho Ban Tổ Chức</li>
                    <li class="text-yellow-500 font-bold"><br>Điều khoản sử dụng cho Ban Tổ Chức</li>
                </ul>
                <ul class="" style="padding-left: 120px">
                    <li class="font-bold">Về Công Ty Chúng Tôi</li>
                    <li>Điều Khoản Chung </li>
                    <li>Điều Khoản Giao Dịch</li>
                    <li>Chính Sách</li>
                    <li>Thanh Toán</li>
                    <li>Chính Sách Bảo Mật</li>
                    <li>Câu Hỏi Thường Gặp</li>
                </ul>
            </div>
            <p class="text-center">&copy;{{ date('Y') }} Cinema Ticket. All rights reserved.</p>
        </footer>
    </div>
@endsection
<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchBox = document.getElementById('search-box');
    const suggestions = document.getElementById('suggestions');

    searchBox.addEventListener('keyup', async function() {
        const query = this.value.trim();

        if (query.length < 1) {
            suggestions.innerHTML = '';
            suggestions.classList.add('hidden');
            return;
        }

        const response = await fetch(`/search?q=${encodeURIComponent(query)}`, {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        });

        const movies = await response.json();
        if (movies.length === 0) {
            suggestions.innerHTML = '<div class="p-2 text-gray-500 text-sm">Không có kết quả</div>';
        } else {
            suggestions.innerHTML = movies.map(m => `
                <a href="/movies/${m.id}" class="block px-3 py-2 hover:bg-gray-100">
                    🎬 ${m.title}
                </a>
            `).join('');
        }
        suggestions.classList.remove('hidden');
    });

    document.addEventListener('click', (e) => {
        if (!searchBox.contains(e.target)) {
            suggestions.classList.add('hidden');
        }
    });
    const swiper = new Swiper(".mySwiper", {
        loop: true,
        autoplay: {
            delay: 5000,
            disableOnInteraction: false,
        },
        pagination: {
            el: ".swiper-pagination",
            clickable: true,
        },
        navigation: {
            nextEl: ".swiper-button-next",
            prevEl: ".swiper-button-prev",
        },
        effect: "fade",
        fadeEffect: { crossFade: true },
    });
});
</script>

