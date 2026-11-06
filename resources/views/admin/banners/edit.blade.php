@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gray-900 text-white py-10 px-6">
    <div class="max-w-3xl mx-auto">
        <h1 class="text-3xl font-bold text-yellow-400 mb-8 text-center">
            ✏️ Chỉnh sửa banner quảng bá
        </h1>

        @if (session('success'))
            <div class="bg-green-600 text-center p-3 rounded mb-4">
                {{ session('success') }}
            </div>
        @endif

        <form action="{{ route('admin.banners.update', $banner->id) }}" method="POST" enctype="multipart/form-data"
              class="bg-gray-800 p-6 rounded-lg shadow-lg border border-gray-700">
            @csrf
            @method('PUT')

            {{-- Tiêu đề banner --}}
            <div class="mb-5">
                <label for="title" class="block text-yellow-300 font-semibold mb-2">🎬 Tiêu đề banner</label>
                <input type="text" name="title" id="title"
                       value="{{ old('title', $banner->title) }}"
                       class="w-full p-3 rounded-md bg-gray-700 border border-gray-600 focus:outline-none focus:ring-2 focus:ring-yellow-400">
                @error('title')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            {{-- Đường dẫn liên kết --}}
            <div class="mb-5">
                <label for="link" class="block text-yellow-300 font-semibold mb-2">🔗 Liên kết (nếu có)</label>
                <input type="text" name="link" id="link"
                       value="{{ old('link', $banner->link) }}"
                       class="w-full p-3 rounded-md bg-gray-700 border border-gray-600 focus:outline-none focus:ring-2 focus:ring-yellow-400">
                @error('link')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            {{-- Hình ảnh banner --}}
            <div class="mb-5">
                <label for="image" class="block text-yellow-300 font-semibold mb-2">🖼️ Hình ảnh banner</label>
                @if ($banner->image)
                    <div class="mb-3 text-center">
                        <img src="{{ asset('storage/' . $banner->image) }}"
                             alt="Banner hiện tại"
                             class="rounded-lg shadow-md w-full max-h-72 object-cover mx-auto">
                        <p class="text-gray-400 text-sm mt-2 italic">Ảnh hiện tại</p>
                    </div>
                @endif
                <input type="file" name="image" id="image"
                       class="w-full p-2 rounded-md bg-gray-700 border border-gray-600 focus:outline-none focus:ring-2 focus:ring-yellow-400">
                @error('image')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            {{-- Nút lưu --}}
            <div class="flex justify-between mt-6">
                <a href="{{ route('admin.banners.index') }}"
                   class="px-5 py-2 bg-gray-600 hover:bg-gray-700 rounded-md text-white">
                   ← Quay lại
                </a>

                <button type="submit"
                        class="px-6 py-2 bg-yellow-500 hover:bg-yellow-600 rounded-md text-black font-semibold transition">
                    💾 Lưu thay đổi
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
