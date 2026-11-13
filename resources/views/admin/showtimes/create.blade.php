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
                <label class="block mb-2 font-bold text-yellow-500">Rạp và giá vé:</label>
                <select name="ticket_price_id" class="w-full p-2 text-black rounded" required>
                    <option value="">-- Chọn rạp và giá vé --</option>
                    @foreach($prices as $price)
                        <option value="{{ $price->id }}" @selected(old('ticket_price_id') == $price->id)>
                            {{ $price->theater->name }} - {{ $price->type }}:
                            {{ number_format($price->price) }} VNĐ
                        </option>
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
                    @for ($i = 1; $i <= 5; $i++)
                        @php $roomName = "Phòng " . $i; @endphp
                        <option value="{{ $roomName }}" @selected(old('room') == $roomName)>
                            {{ $roomName }}
                        </option>
                    @endfor
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
