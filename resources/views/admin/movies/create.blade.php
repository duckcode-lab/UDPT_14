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
            <small class="text-white">Giữ Ctrl (hoặc Cmd trên Mac) để chọn nhiều thể loại (tối đa 2).</small>
        </div>

        <label class="block text-white">Thời lượng (phút):</label>
        <input type="number" name="duration" class="block w-full p-2 text-black rounded" required>

        <label class="block text-white">Trailer:</label>
        <input type="text" name="trailer" class="block w-full p-2 text-black rounded">

        <label class="block text-white">Poster phim:</label>
        <input type="file" name="photo" class="block w-full text-white" accept="image/*">

        <button class="bg-green-500 text-white hover:bg-green-600 px-4 py-2 rounded">Lưu phim</button>
    </form>
</div>
@endsection
