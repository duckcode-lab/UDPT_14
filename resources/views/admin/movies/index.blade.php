@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gray-900 text-white p-10">
    <h1 class="text-3xl font-bold mb-6 text-yellow-400">Quản lý phim</h1>
    @auth
        @if(Auth::user()->role === 'admin')
            <div class="mb-4 text-right">
                <a href="{{ route('admin.movies.create') }}"
                    class="bg-green-600 hover:bg-green-700 text-red-500 px-4 py-2 rounded-lg shadow-md">
                    Thêm phim mới
                </a>
            </div>
        @endif
    @endauth

    <table class="w-full border border-gray-700 rounded-lg overflow-hidden">
        <thead class="bg-gray-800 text-yellow-400">
            <tr>
                <th class="p-3 text-left">Poster</th>
                <th class="p-3 text-left">Tên phim</th>
                <th class="p-3 text-left">Thời lượng</th>
                <th class="p-3 text-left">Trailer</th>
                <th class="p-3 text-center">Hành động</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($movies as $movie)
                <tr class="border-t border-gray-700 hover:bg-gray-800 transition">
                    <td class="p-3">
                        @if($movie->photo)
                            <img src="{{ $movie->photo_url }}" alt="{{ $movie->title }}" class="w-20 h-auto rounded">
                        @else
                            <span class="text-gray-400 italic">Không có poster</span>
                        @endif
                    </td>

                    <td class="p-3 font-semibold">{{ $movie->title }}</td>
                    <td class="p-3">{{ $movie->duration }} phút</td>
                    <td class="p-3">
                        @if($movie->trailer)
                            <a href="{{ $movie->trailer }}" target="_blank" class="text-blue-400 hover:text-blue-300">
                                Xem trailer
                            </a>
                        @else
                            <span class="text-gray-500">Không có</span>
                        @endif
                    </td>

                    {{-- Hành động: thay đổi tùy theo quyền --}}
                    <td class="p-3 text-center space-x-2">
                        @auth
                            @if(Auth::user()->role === 'admin')
                                <a href="{{ route('admin.movies.edit', $movie->id) }}"
                                   class="bg-yellow-500 hover:bg-yellow-600 text-black px-3 py-1 rounded">Sửa</a>

                                <form action="{{ route('admin.movies.destroy', $movie->id) }}" method="POST" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                            onclick="return confirm('Bạn có chắc muốn xóa phim này không?')"
                                            class="bg-red-600 hover:bg-red-700 text-white px-3 py-1 rounded">
                                            Xóa
                                    </button>
                                </form>
                            @else
                                <a href="{{ route('movies.book', $movie->id) }}"
                                   class="bg-blue-600 hover:bg-blue-700 text-white px-3 py-1 rounded">Đặt vé</a>
                            @endif
                        @else
                            <a href="{{ route('movies.show', $movie->id) }}"
                               class="bg-gray-700 hover:bg-gray-600 text-white px-3 py-1 rounded">Xem</a>
                        @endauth
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="text-center p-4 text-gray-400">Chưa có phim nào.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
