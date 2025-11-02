@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gray-900 text-white py-10 px-6">
    <div class="max-w-3xl mx-auto bg-gray-800 rounded-xl shadow-lg p-8">
        <h1 class="text-3xl font-bold text-yellow-400 mb-6 text-center">Chỉnh Sửa Suất Chiếu</h1>
        <h2 class="text-xl mb-4 text-center">
             <span class="font-semibold text-yellow-400">Phim: {{ $showtime->movie->title }}</span>
        </h2>
        <form action="{{ route('admin.showtimes.update', $showtime->id) }}" method="POST" class="space-y-4" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            <input type="hidden" name="movie_id" value="{{ $showtime->movie_id }}">
            <div>
                <label class="block mb-2 font-bold text-yellow-500">Chọn rạp:</label>
                <select name="theater_id" class="w-full p-2 text-black rounded" required>
                    @foreach($theaters as $theater)
                        <option value="{{ $theater->id }}" {{ $showtime->theater_id == $theater->id ? 'selected' : '' }}>
                            {{ $theater->name }} ({{ $theater->address }})
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block mb-2 font-bold text-yellow-500">Chọn giá vé:</label>
                <select name="ticket_price_id" class="w-full p-2 text-black rounded" required>
                    @foreach($prices as $price)
                        <option value="{{ $price->id }}" {{ $showtime->ticket_price_id == $price->id ? 'selected' : '' }}>
                            {{ number_format($price->price) }} VNĐ ({{ $price->type }})
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block mb-2 font-bold text-yellow-500">Ngày & Giờ chiếu:</label>
                <input type="datetime-local" name="time" value="{{ \Carbon\Carbon::parse($showtime->time)->format('Y-m-d\TH:i') }}" class="w-full p-2 text-black rounded" required>
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
                    Cập nhật suất chiếu
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
