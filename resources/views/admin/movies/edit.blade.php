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

        <div class="mb-4">
            <label for="genres" class="block text-sm font-medium text-white">Thể loại (tối đa 2)</label>
            <select name="genres[]" id="genres" multiple
                class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-yellow-400 focus:border-yellow-400">
                @foreach($genres as $genre)
                    <option value="{{ $genre->id }}"
                        {{ isset($movie) && $movie->genres->contains($genre->id) ? 'selected' : '' }}>
                        {{ $genre->name }}
                    </option>
                @endforeach
            </select>
            <small class="text-white">Giữ Ctrl để chọn nhiều thể loại (tối đa 2).</small>
        </div>

        <label class="block text-white">Thời lượng (phút):</label>
        <input type="number" name="duration" value="{{ $movie->duration }}" class="block w-full p-2 text-black rounded" required>

        <label class="block text-white">Trailer (URL YouTube):</label>
        <input type="text" name="trailer" value="{{ $movie->trailer }}" class="block w-full p-2 text-black rounded">

        <label class="block text-white">Ảnh hiện tại:</label>
        @if($movie->photo)
            <img src="{{ asset('storage/'.$movie->photo) }}" class="w-48 mb-2 rounded border border-gray-700">
        @else
            <p class="text-gray-400 italic mb-2">Chưa có post</p>
        @endif

        <label class="block text-white">Thay ảnh mới:</label>
        <input type="file" name="photo" class="block w-full text-white" accept="image/*">

        <button class="bg-yellow-500 hover:bg-yellow-600 px-4 py-2 rounded">Cập nhật phim</button>
    </form>
</div>
@endsection
