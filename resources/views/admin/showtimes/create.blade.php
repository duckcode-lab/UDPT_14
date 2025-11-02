@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gray-900 text-white py-10 px-6">
    <div class="max-w-3xl mx-auto bg-gray-800 rounded-xl shadow-lg p-8">
        <h1 class="text-3xl font-bold text-yellow-400 mb-6 text-center">Thêm Suất Chiếu Mới</h1>

        <form action="{{ route('admin.showtimes.store') }}" method="POST" class="space-y-4">
            @csrf

            <div>
                <label class="block mb-2 font-bold text-yellow-500">Chọn phim:</label>
                <select name="movie_id" class="w-full p-2 text-black rounded" required>
                    <option value="">-- Chọn phim --</option>
                    @foreach($movies as $movie)
                        <option value="{{ $movie->id }}">{{ $movie->title }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block mb-2 font-bold text-yellow-500">Chọn rạp:</label>
                <select name="theater_id" class="w-full p-2 text-black rounded" required>
                    <option value="">-- Chọn rạp --</option>
                    @foreach($theaters as $theater)
                        <option value="{{ $theater->id }}">{{ $theater->name }} ({{ $theater->address }})</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block mb-2 font-bold text-yellow-500">Chọn giá vé:</label>
                <select name="ticket_price_id" class="w-full p-2 text-black rounded" required>
                    <option value="">-- Chọn giá vé --</option>
                    @foreach($prices as $price)
                        <option value="{{ $price->id }}">{{ number_format($price->price) }} VNĐ ({{ $price->type }})</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block mb-2 font-bold text-yellow-500">Ngày & Giờ chiếu:</label>
                <input type="datetime-local" name="time" class="w-full p-2 text-black rounded" required>
            </div>

            <div>
                <label class="block mb-2 font-bold text-yellow-500">Phòng chiếu:</label>
                <select name="room" class="w-full p-2 text-black rounded" required>
                    <option value="">-- Chọn phòng --</option>
                    <option value="Phòng 1">Phòng 1</option>
                    <option value="Phòng 2">Phòng 2</option>
                    <option value="Phòng 3">Phòng 3</option>
                    <option value="Phòng 4">Phòng 4</option>
                    <option value="Phòng 5">Phòng 5</option>
                </select>
            </div>

            <div class="text-center mt-6">
                <button type="submit" class="bg-yellow-500 hover:bg-yellow-600 text-black font-semibold px-6 py-3 rounded-lg">
                    Thêm suất chiếu
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
