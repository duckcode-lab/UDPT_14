@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-black text-white py-10 px-6">
    <div class="max-w-5xl mx-auto bg-gray-900 rounded-2xl shadow-2xl p-8 border border-gray-700">

        <div class="flex flex-col md:flex-row gap-6 items-center">
            <img src="{{ asset($movie->photo) }}" alt="{{ $movie->title }}" class="w-64 h-96 object-cover rounded-lg shadow-md border border-gray-700">
            <div class="flex-1">
                <h1 class="text-3xl font-bold text-yellow-400 mb-4">{{ $movie->title }}</h1>
                <p class="text-gray-300 mb-6">{{ $movie->description }}</p>
                <p class="text-gray-400"><b>Thời lượng:</b> {{ $movie->duration }} phút</p>
            </div>
        </div>

        <hr class="my-8 border-gray-700">

        <h2 class="text-2xl font-semibold mb-4 text-yellow-400 text-center">🎥 Chọn suất chiếu</h2>

        @if($showtimes->count() > 0)
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5 mb-8">
                @foreach($showtimes as $showtime)
                    <div class="showtime-btn bg-gray-800 p-4 rounded-lg border border-gray-700 hover:border-yellow-400 hover:bg-gray-700 cursor-pointer transition duration-200"
                         data-id="{{ $showtime->id }}">
                        <p class="text-lg font-semibold text-white">{{ $showtime->theater }}</p>
                        <p class="text-gray-400 text-sm mb-1">Phòng: {{ $showtime->room }}</p>
                        <p class="text-gray-300 text-sm mb-1">Giờ chiếu: {{ date('H:i d/m/Y', strtotime($showtime->time)) }}</p>
                        <p class="text-yellow-400 font-semibold">{{ number_format($showtime->price) }} VNĐ</p>
                    </div>
                @endforeach
            </div>
        @else
            <p class="text-gray-400 text-center mt-6">🎬 Hiện chưa có suất chiếu cho phim này.</p>
        @endif

        <div id="seat-selection" class="hidden">
            <h2 class="text-2xl font-semibold mb-4 text-center text-yellow-400">🪑 Chọn ghế ngồi</h2>

            <div class="flex flex-col items-center space-y-3">
                @php $rows = ['A','B','C','D','E']; @endphp
                @foreach($rows as $row)
                    <div class="flex gap-2">
                        @for($i = 1; $i <= 8; $i++)
                            <div onclick="toggleSeat('{{ $row.$i }}')"
                                 id="seat-{{ $row.$i }}"
                                 class="seat bg-gray-700 w-10 h-10 flex items-center justify-center rounded-md cursor-pointer hover:bg-gray-500 transition">
                                {{ $row.$i }}
                            </div>
                        @endfor
                    </div>
                @endforeach
            </div>
        </div>
        <div class="text-center mt-8">
            <form id="booking-form" method="POST" action="{{ route('movies.book.store', $movie->id) }}">
            @csrf
            <input type="hidden" name="showtime_id" id="showtime-id">
            <input type="hidden" name="seats" id="seats-input">
            @auth
                <button id="confirm-btn"
                    class="bg-gray-600 text-black font-semibold px-6 py-3 rounded-lg shadow-md transition duration-300 cursor-not-allowed"
                    disabled>
                    Xác nhận đặt vé
                </button>
            @else
                <a href="{{ route('login') }}"
                    class="bg-yellow-500 hover:bg-red-600 text-black font-semibold px-6 py-3 rounded-lg shadow-md transition duration-300 inline-block">
                    Đăng nhập để đặt vé
                </a>
            @endauth
            </form>
        </div>
    </div>
</div>

<script>
    let selectedShowtime = null;
    let selectedSeats = [];

    document.querySelectorAll('.showtime-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            document.querySelectorAll('.showtime-btn').forEach(b => b.classList.remove('border-yellow-400', 'bg-gray-700'));
            this.classList.add('border-yellow-400', 'bg-gray-700');
            selectedShowtime = this.dataset.id;
            document.getElementById('seat-selection').classList.remove('hidden');
        });
    });
    function toggleSeat(seatId) {
        const seat = document.getElementById('seat-' + seatId);
        if (selectedSeats.includes(seatId)) {
            selectedSeats = selectedSeats.filter(s => s !== seatId);
            seat.classList.remove('bg-red-600');
            seat.classList.add('bg-gray-700');
        } else {
            selectedSeats.push(seatId);
            seat.classList.remove('bg-gray-700');
            seat.classList.add('bg-red-600');
        }

        updateConfirmButton();
    }

    function updateConfirmButton() {
        const btn = document.getElementById('confirm-btn');
        document.getElementById('showtime-id').value = selectedShowtime;
        document.getElementById('seats-input').value = selectedSeats.join(',');

        if (selectedShowtime && selectedSeats.length > 0) {
            btn.classList.remove('bg-gray-600', 'cursor-not-allowed');
            btn.classList.add('bg-yellow-500', 'hover:bg-red-600', 'text-black', 'hover:text-white', 'cursor-pointer');
            btn.disabled = false;
        } else {
            btn.classList.remove('bg-yellow-500', 'hover:bg-red-600', 'hover:text-white', 'cursor-pointer');
            btn.classList.add('bg-gray-600', 'cursor-not-allowed');
            btn.disabled = true;
        }
    }


    document.getElementById('confirm-btn').addEventListener('click', function() {
        if (!selectedShowtime) {
            alert('⚠️ Vui lòng chọn suất chiếu!');
        } else if (selectedSeats.length === 0) {
            alert('⚠️ Vui lòng chọn ít nhất 1 ghế!');
        } else {
            alert(`🎟️ Đặt vé thành công!\nSuất chiếu ID: ${selectedShowtime}\nGhế: ${selectedSeats.join(', ')}`);
        }
    });
</script>
@endsection
