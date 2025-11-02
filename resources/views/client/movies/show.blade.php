@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-black text-white py-10 px-6" style="padding-top: 30px;">
    <div class="max-w-7xl mx-auto">
        <div class="flex flex-row items-start justify-center gap-3 flex-wrap md:flex-nowrap">

            <div class="w-full md:w-1/3 flex justify-center px-2">
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
            </div>
            <div class="w-full md:w-3/4 flex flex-col justify-start px-8 py-8 bg-gray-900" style="border-radius: 30px">
                <h1 class="text-3xl font-bold mb-4">{{ $movie->title }}</h1>

                <p class="text-gray-400 mb-2">Thời lượng: {{ $movie->duration }} phút</p>
                <p class="text-gray-300 text-justify mb-6">{{ $movie->description }}</p>

                <div class="aspect-video w-full mb-6">
                    @if($movie->trailer)
                        @php
                            $embedUrl = str_replace('watch?v=', 'embed/', $movie->trailer);
                        @endphp
                        <iframe width="870" height="470"
                            src="{{ $embedUrl }}"
                            frameborder="0"
                            allowfullscreen
                            class="rounded-lg shadow-lg">
                        </iframe>
                    @else
                        <p class="text-gray-500 italic">Không có trailer</p>
                    @endif
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
                <hr class="my-8 border-gray-700">
                <h2 class="text-2xl font-bold text-yellow-400 mb-4">Bình luận</h2>

                @foreach($movie->comments()->latest()->get() as $comment)
                    <div class="bg-gray-800 p-4 rounded-lg mb-3 border border-gray-700">
                        <p class="text-yellow-300 font-semibold">{{ $comment->user->name ?? 'Ẩn danh' }}</p>
                        <p class="text-gray-300">{{ $comment->content }}</p>
                        <p class="text-gray-500 text-sm mt-1">{{ $comment->created_at->diffForHumans() }}</p>
                    </div>
                @endforeach
                @auth
                    <form action="{{ route('comments.store', $movie->id) }}" method="POST" class="mt-6 space-y-3">
                        @csrf
                        <textarea name="content" rows="3" class="w-full p-3 rounded bg-gray-800 text-white border border-gray-700"
                                placeholder="Bạn muốn viết gì..." required></textarea>
                        <button class="bg-yellow-500 hover:bg-yellow-600 text-black font-semibold px-6 py-2 rounded-lg">
                            Gửi bình luận
                        </button>
                    </form>
                @else
                    <p class="text-gray-400 mt-4 italic">⚠️ Vui lòng <a href="{{ route('login') }}" class="text-yellow-400 underline">đăng nhập</a> để bình luận.</p>
                @endauth
            </div>
        </div>
    </div>
</div>
@endsection
