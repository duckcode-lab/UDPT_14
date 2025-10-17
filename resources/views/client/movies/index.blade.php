@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-black text-white py-10 px-6">
    <div class="max-w-7xl mx-auto">
        <h1 class="text-3xl font-bold mb-8 text-center text-yellow-400">🎬 Phim đang chiếu</h1>
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
            @foreach($movies as $movie)
                <div class="bg-gray-900 border border-gray-700 rounded-lg overflow-hidden shadow-md hover:shadow-xl transform hover:-translate-y-1 transition-all duration-300">
                    <a href="{{ route('movies.show', $movie->id) }}">
                        <img src="{{ asset($movie->photo) }}" alt="{{ $movie->title }}" class="w-full h-72 object-cover">
                    </a>
                    <div class="p-4 text-center">
                        <a href="{{ route('movies.show', $movie->id) }}"
                           class="block text-lg font-semibold text-yellow-300 hover:text-yellow-500 transition duration-200">
                           {{ $movie->title }}
                        </a>

                        <p class="text-gray-400 text-sm mt-2 line-clamp-2">
                            {{ Str::limit($movie->description, 80) }}
                        </p>

                        <a href="{{ route('movies.book', $movie->id) }}"
                           class="mt-4 inline-block px-4 py-2 border-2 border-gray-600 text-gray-200 rounded-md hover:bg-red-600 hover:border-red-600 transition duration-200">
                           🎟️ Đặt vé
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
