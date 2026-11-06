@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gray-900 text-white py-10 px-6">
    <div class="max-w-3xl mx-auto bg-gray-800 rounded-xl shadow-lg p-8">
        <h1 class="text-3xl font-bold text-yellow-400 mb-6 text-center">Chỉnh Sửa Suất Chiếu</h1>

        <h2 class="text-xl mb-4 text-center">
             <span class="font-semibold text-yellow-400">Phim: {{ $showtime->movie->title }}</span>
        </h2>

        @if ($errors->any())
            <div class="bg-red-500 text-white p-4 rounded-lg mb-6">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>⚠️ {{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('admin.showtimes.update', $showtime->id) }}" method="POST" class="space-y-4">
            @csrf
            @method('PUT')

            <input type="hidden" name="movie_id" value="{{ $showtime->movie_id }}">
            <div>
                <label class="block mb-2 font-bold text-yellow-500">Rạp và giá vé:</label>
                <select name="ticket_price_id" class="w-full p-2 text-black rounded" required>
                    @foreach($prices as $price)
                        <option value="{{ $price->id }}"
                            @selected(old('ticket_price_id', $showtime->ticket_price_id) == $price->id)>
                            {{ $price->theater->name }} - {{ $price->type }}:
                            {{ number_format($price->price) }} VNĐ
                        </option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block mb-2 font-bold text-yellow-500">Ngày & Giờ chiếu:</label>
                <input type="datetime-local"
                       name="time"
                       value="{{ old('time', \Carbon\Carbon::parse($showtime->time)->format('Y-m-d\TH:i')) }}"
                       class="w-full p-2 text-black rounded"
                       required>
            </div>
            <div>
                <label class="block mb-2 font-bold text-yellow-500">Phòng chiếu:</label>
                <select name="room" class="w-full p-2 text-black rounded" required>
                    <option value="">-- Chọn phòng --</option>
                    @for ($i = 1; $i <= 5; $i++)
                        @php $roomName = "Phòng " . $i; @endphp
                        <option value="{{ $roomName }}" {{ old('room', $showtime->room) == $roomName ? 'selected' : '' }}>
                            {{ $roomName }}
                        </option>
                    @endfor
                </select>
            </div>
            <div class="text-center mt-6">
                <button type="submit" class="bg-yellow-500 hover:bg-yellow-600 text-black font-semibold px-6 py-3 rounded-lg transition duration-200">
                    Cập nhật suất chiếu
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
