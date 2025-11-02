@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gray-900 text-white py-10 px-6">
    <div class="max-w-7xl mx-auto">
        <h1 class="text-3xl font-bold mb-8 text-center text-yellow-400">Phim đang chiếu</h1>
        <form action="{{ route('movies.search') }}" method="GET" class="absolute w-64 gray-900" style="left:1200px; top:100px">
            <input type="text" name="q" id="search-box"
                placeholder="Tìm phim..."
                autocomplete="off"
                class="w-full px-3 py-2 rounded-md text-gray-900 focus:outline-none">
            <div id="suggestions"
                class="absolute bg-white text-black w-full mt-1 rounded-md shadow-lg z-50 hidden">
            </div>
        </form>

        <section class="mb-12 relative" style="height: 66%">
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

                                {{-- Mô tả phim --}}
                                @if($banner->description)
                                    <p class="text-gray-300 text-sm mb-4 line-clamp-3 max-w-3xl ">
                                        {{ $banner->description }}
                                    </p>
                                @endif

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
            <h2 class="text-3xl font-bold text-orange-400 mb-6 ">
                🔥 Phim Nổi Bật Trong Tháng
            </h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                @foreach($monthlyMovies as $movie)
                    <div class="relative group bg-gray-900 rounded-lg shadow-lg hover:scale-105 transition duration-300 overflow-hidden">
                        <a href="{{ route('movies.show', $movie->id) }}" class="text-sm text-white underline hover:text-yellow-400">
                            @php
                                $photoPath = Str::startsWith($movie->photo, 'uploads/')
                                    ? asset('storage/' . $movie->photo)
                                    : asset($movie->photo);
                            @endphp
                            @if($movie->photo)
                                <img src="{{ $photoPath }}"
                                    alt="{{ $movie->title }}"
                                    class="w-full object-cover" style="height: 400px">
                            @else
                                <span class="text-gray-400 italic">Không có poster</span>
                            @endif
                            <div class="absolute inset-0 flex flex-col justify-end p-3 bg-gradient-to-t from-black via-transparent opacity-0 group-hover:opacity-100 transition">
                                <h3 class="text-yellow-400 font-semibold">{{ $movie->title }}</h3>
                                <p class="text-gray-400 text-sm mt-1">
                                    {{ $movie->genres->pluck('name')->implode(', ') }}
                                </p>
                            </div>
                        </a>
                    </div>
                @endforeach
            </div>
        </section>
    </div>
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

