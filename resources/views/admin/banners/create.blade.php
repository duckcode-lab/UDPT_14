@extends('layouts.app')

@section('content')
<div class="container mx-auto p-6 text-white">
    <h1 class="text-2xl font-bold text-yellow-400 mb-4">➕ Thêm Banner</h1>

    <form action="{{ route('admin.banners.store') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
        @csrf
        <div>
            <label class="block mb-1">Tiêu đề</label>
            <input type="text" name="title" class="w-full p-2 rounded text-black" required>
        </div>

        <div>
            <label class="block mb-1">Liên kết (tùy chọn)</label>
            <input type="text" name="link" class="w-full p-2 rounded text-black">
        </div>

        <div>
            <label for="image_url" class="block mb-1">Link ảnh banner</label>
            <input type="url" name="image_url" id="image_url" value="{{ old('image_url') }}"
                placeholder="https://example.com/banner.jpg" class="w-full p-2 rounded text-black">
            @error('image_url') <p class="text-red-400">{{ $message }}</p> @enderror
        </div>

        <div>
            <label class="block mb-1">Hoặc tải ảnh banner từ máy</label>
            <input type="file" name="image" accept="image/*" class="w-full text-white">
            @error('image') <p class="text-red-400">{{ $message }}</p> @enderror
        </div>

        <button type="submit" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded">Lưu</button>
    </form>
</div>
@endsection
