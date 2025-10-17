@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-black text-white py-10 px-6" style="padding-top: 30px;">
    <div class="max-w-7xl mx-auto">
        <div class="flex flex-row items-start justify-center gap-3 flex-wrap md:flex-nowrap">

            <div class="w-full md:w-1/3 flex justify-center px-2">
                <img src="{{ asset($movie->photo) }}"
                     alt="{{ $movie->title }}"
                     class="rounded-lg shadow-lg object-cover w-[350px] h-auto">
            </div>

            <div class="w-full md:w-2/3 flex flex-col justify-start px-2">
                <h1 class="text-3xl font-bold mb-4">{{ $movie->title }}</h1>

                <p class="text-gray-400 mb-2">⏱️ Thời lượng: {{ $movie->duration }} phút</p>
                <p class="text-gray-300 text-justify mb-6">{{ $movie->description }}</p>

                <div class="aspect-video w-full mb-6">
                    <iframe
                        class="w-full h-full rounded-lg shadow-md"
                        src="{{ $movie->trailer }}"
                        title="Trailer {{ $movie->title }}"
                        frameborder="0"
                        allowfullscreen>
                    </iframe>
                </div>
                @auth
                    <a href="{{ route('movies.book', $movie->id) }}"
                        class="inline-block mt-4 px-4 py-2 bg-green-600 hover:bg-red-600 text-white rounded-md transition">
                        Đặt vé ngay
                    </a>
                @else
                    <a href="{{ route('login') }}"
                        class="inline-block mt-4 px-4 py-2 bg-gray-700 text-white rounded-md hover:bg-gray-600 transition">
                        Đăng nhập để đặt vé
                    </a>
                @endauth
                <a href="{{ url('/') }}"
                   class="inline-block px-4 py-2 border border-gray-600 text-gray-300 rounded-md hover:bg-gray-800 hover:text-white transition-all duration-300 w-fit">
                    Quay lại danh sách
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
