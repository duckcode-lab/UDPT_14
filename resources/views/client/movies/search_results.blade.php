@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gray-900 text-white py-10 px-6">
    <div class="max-w-7xl mx-auto">
        <a href="{{ url()->previous() }}"
            class="bg-gray-700 hover:bg-gray-800 text-white px-2 py-2 rounded-lg shadow-md flex items-center gap-2 z-50 transition" style="width:37px ; margin-bottom: 20px; border-radius: 17px">
                <span>⬅️</span>
        </a>
        <div class="flex" style="justify-content: space-between;">
            <h1 class="text-2xl font-bold text-yellow-400 mb-6">
                Kết quả tìm kiếm cho: <span class="text-white">"{{ $query }}"</span>
            </h1>
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
        @if($movies->isEmpty())
            <p class="text-gray-400 text-center">Không tìm thấy bộ phim nào.</p>
        @else
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
                                class="block text-lg font-semibold text-yellow-300 hover:text-yellow-500 transition duration-200" style="white-space: nowrap;overflow: hidden;text-overflow: ellipsis;">
                                {{ $movie->title }}
                            </a>

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
        @endif
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
});
</script>
