@extends('layouts.app')

@section('content')
<div class="p-10 bg-gray-900  min-h-screen">
    <h1 class="text-3xl text-white font-bold mb-6">Thêm phim mới</h1>

    <form method="POST" action="{{ route('admin.movies.store') }}" enctype="multipart/form-data" class="space-y-4">
        @csrf

        <label class="block text-white">Tên phim:</label>
        <input type="text" name="title" class="block w-full p-2 text-black rounded" required>

        <label class="block text-white">Mô tả:</label>
        <textarea name="description" class="block w-full p-2 text-black rounded" required></textarea>

       <div class="mb-4">
            <label class="block text-white">Thể loại:</label>
            <input type="text" name="genres"
                class="block w-full p-2 text-black rounded">
        </div>

        <label class="block text-white">Thời lượng (phút):</label>
        <input type="number" name="duration" class="block w-full p-2 text-black rounded" required>

        <label class="block text-white">Trailer:</label>
        <input type="text" name="trailer" class="block w-full p-2 text-black rounded">

        <label for="photo_url" class="block text-white">Link poster phim:</label>
        <input type="url" name="photo_url" id="photo_url" value="{{ old('photo_url') }}"
            placeholder="https://example.com/poster.jpg" class="block w-full p-2 text-black rounded">
        @error('photo_url') <p class="text-red-400">{{ $message }}</p> @enderror

        <label class="block text-white">Hoặc tải poster từ máy:</label>
        <input type="file" name="photo" class="block w-full text-white" accept="image/*">
        @error('photo') <p class="text-red-400">{{ $message }}</p> @enderror

        <button class="bg-green-500 text-white hover:bg-green-600 px-4 py-2 rounded">Lưu phim</button>
    </form>
</div>
@endsection
