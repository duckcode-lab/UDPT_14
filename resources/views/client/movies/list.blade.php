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
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
            @foreach($movies as $movie)

                <div class="bg-gray-900 border border-gray-700 rounded-lg overflow-hidden shadow-md hover:shadow-xl transform hover:-translate-y-1 transition-all duration-300">
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
                    <div class="p-4 text-center">
                        <a href="{{ route('movies.show', $movie->id) }}"
                            class="block text-lg font-semibold text-yellow-300 hover:text-yellow-500 transition duration-200">
                            {{ $movie->title }}
                        </a>
                        <p class="text-gray-400 text-sm mt-1">
                            {{ $movie->genres->pluck('name')->implode(', ') }}
                        </p>
                        <p class="text-gray-400 text-sm mt-2 line-clamp-2">
                            {{ Str::limit($movie->description, 80) }}
                        </p>

                        <a href="{{ route('movies.book', $movie->id) }}"
                            class="mt-4 inline-block px-4 py-2 border-2 border-gray-600 text-gray-200 rounded-md hover:bg-red-600 hover:border-red-600 transition duration-200">
                            Đặt vé
                        </a>
                    </div>
                </div>
            @endforeach
        </div>
        <div class="mt-10 text-center">
            {{ $movies->links() }}
        </div>
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

    // Ẩn dropdown khi click ra ngoài
    document.addEventListener('click', (e) => {
        if (!searchBox.contains(e.target)) {
            suggestions.classList.add('hidden');
        }
    });
});
</script>

