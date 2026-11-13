@extends('layouts.app')

@section('content')
<div class="p-10 bg-gray-900 min-h-screen">
    <h1 class="text-3xl font-bold mb-6 text-white">Sửa phim</h1>

    <form method="POST" action="{{ route('admin.movies.update', $movie->id) }}" enctype="multipart/form-data" class="space-y-4">
        @csrf
        @method('PUT')

        <label class="block text-white">Tên phim:</label>
        <input type="text" name="title" value="{{ $movie->title }}" class="block w-full p-2 text-black rounded" required>

        <label class="block text-white">Mô tả:</label>
        <textarea name="description" class="block w-full p-2 text-black rounded" required>{{ $movie->description }}</textarea>

        <div>
            <label class="block mb-2 font-bold text-white">Thể loại :</label>
            <input type="text"
                name="genres"
                value="{{ old('genres', $movie->genre_string ?? '') }}"
                class="w-full p-2 text-black rounded"
                required>
        </div>

        <label class="block text-white">Thời lượng (phút):</label>
        <input type="number" name="duration" value="{{ $movie->duration }}" class="block w-full p-2 text-black rounded" required>

        <label class="block text-white">Trailer (URL YouTube):</label>
        <input type="text" name="trailer" value="{{ $movie->trailer }}" class="block w-full p-2 text-black rounded">

        <label class="block text-white">Ảnh hiện tại:</label>
        @if($movie->photo)
            <img src="{{ $movie->photo_url }}" alt="{{ $movie->title }}" class="w-48 mb-2 rounded border border-gray-700">
        @else
            <p class="text-gray-400 italic mb-2">Chưa có post</p>
        @endif

        <label for="photo_url" class="block text-white">Link poster mới (để trống để giữ ảnh hiện tại):</label>
        <input type="url" name="photo_url" id="photo_url" value="{{ old('photo_url', $movie->remote_photo_url) }}"
            placeholder="https://example.com/poster.jpg" class="block w-full p-2 text-black rounded">
        @error('photo_url') <p class="text-red-400">{{ $message }}</p> @enderror

        <label class="block text-white">Hoặc tải ảnh mới từ máy:</label>
        <input type="file" name="photo" class="block w-full text-white" accept="image/*">
        @error('photo') <p class="text-red-400">{{ $message }}</p> @enderror

        <button class="bg-yellow-500 hover:bg-yellow-600 px-4 py-2 rounded">Cập nhật phim</button>
    </form>
</div>
@endsection
