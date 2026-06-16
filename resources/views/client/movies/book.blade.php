@extends('layouts.app')
@section('content')
<div class="min-h-screen bg-black text-white py-10 px-6">

    <div class="max-w-5xl mx-auto bg-gray-900 rounded-2xl shadow-2xl p-8 border border-gray-700">
        <a href="{{ route('movies.show', $movie->id) }}"
            class="bg-gray-700 hover:bg-gray-800 text-white px-2 py-2 rounded-lg shadow-md flex items-center gap-2 z-50 transition" style="width:37px ; margin-bottom: 20px; border-radius: 17px">
                <span>⬅️</span>
        </a>
        <div class="flex flex-col md:flex-row gap-6 items-center">

            @php
                $photoPath = Str::startsWith($movie->photo, 'uploads/')
                    ? asset('storage/' . $movie->photo)
                    : asset($movie->photo);
            @endphp
            @if($movie->photo)
                <a href="{{ route('movies.show', $movie->id) }}">
                    <img src="{{ $photoPath }}"
                        alt="{{ $movie->title }}"
                        class="w-full object-cover" style="height: 400px;">
                </a>
            @else
                <span class="text-gray-400 italic">Không có poster</span>
            @endif
            <div class="flex-1">
                <h1 class="text-3xl font-bold text-yellow-400 mb-4">{{ $movie->title }}</h1>
                <p class="text-gray-300 mb-6">{{ $movie->description }}</p>
                <p class="text-gray-400 mb-2">
                    {{ $movie->genres->pluck('name')->implode(', ') }}
                </p>
                <p class="text-gray-400"><b>Thời lượng:</b> {{ $movie->duration }} phút</p>
            </div>
        </div>

        <hr class="my-8 border-gray-700">

        <label for="showtime_id">Chọn suất chiếu:</label>
        @if($showtimes->count() > 0)
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5 mb-8">
                @foreach($showtimes as $showtime)
                    <div class="showtime-btn bg-gray-800 p-4 rounded-lg border border-gray-700 hover:border-yellow-400 hover:bg-gray-700 cursor-pointer transition duration-200"
                         data-id="{{ $showtime->id }}">
                        <h3 class="font-bold text-lg">{{ $showtime->theater_name ?? ($showtime->theater->name ?? 'Không rõ rạp') }}</h3>
                        <p>Phòng: <span class="font-semibold">{{ $showtime->room }}</span></p>
                        <p>Giờ chiếu: <span class="text-yellow-400">{{ \Carbon\Carbon::parse($showtime->time)->format('H:i d/m/Y') }}</span></p>
                        <p>Giá vé: <span class="text-green-400">{{ isset($showtime->ticket_price) ? number_format($showtime->ticket_price) : ($showtime->ticketPrice ? number_format($showtime->ticketPrice->price) : '0') }} VNĐ</span> </p>
                    </div>
                @endforeach
            </div>
        @else
            <p class="text-gray-400 text-center mt-6"> Hiện chưa có suất chiếu cho phim này.</p>
        @endif
        <div id="seat-selection" class="hidden" data-booked='@json($bookedSeatsByShowtime)'>
            <h2 class="text-2xl font-bold mb-4 text-center">Chọn ghế ngồi</h2>
            <div class="bg-gray-700 mb-6 rounded-md flex items-center justify-center w-full h-12">
                Màn hình
            </div>
            @php $rows = ['A','B','C','D','E']; @endphp
            @foreach($rows as $row)
                <div class="flex gap-2 justify-center mb-2">
                    @for($i = 1; $i <= 8; $i++)
                        @php $seat = $row.$i; @endphp
                        <div id="seat-{{ $seat }}"
                            onclick="toggleSeat('{{ $seat }}')"
                            class="seat w-10 h-10 flex items-center justify-center rounded-md text-sm bg-gray-700 cursor-pointer transition">
                            {{ $seat }}
                        </div>
                    @endfor
                </div>
            @endforeach
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
                <a href="{{ route('login_modal') }}"
                    class="bg-yellow-500 hover:bg-red-600 text-black font-semibold px-6 py-3 rounded-lg shadow-md transition duration-300 inline-block">
                    Đăng nhập để đặt vé
                </a>
            @endauth
            </form>
        </div>
    </div>
    <footer class="mt-16 text-gray-500 bg-black px-10 py-8 rounded-lg border-t border-gray-700 w-780 ">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 mx-auto mb-6 max-w-7xl gap-8">
                <ul>
                    <li><a class="font-bold">Hotline <br><br><span class="text-yellow-500">0338216050</span></a></li>
                    <li><br><a class="font-bold">Email <br><br><span class="text-yellow-500">phamduc13425@gmail.com</span></a></li>
                </ul>
                <ul class="mx-10 px-5" >
                    <li class="font-bold">Dành cho khách hàng</li>
                    <li class="text-yellow-500 font-bold"><br>Điều khoản sử dụng cho khách hàng</li>
                    <li class="font-bold"><br>Dành cho Ban Tổ Chức</li>
                    <li class="text-yellow-500 font-bold"><br>Điều khoản sử dụng cho Ban Tổ Chức</li>
                </ul>
                <ul class="" style="padding-left: 120px">
                    <li class="font-bold">Về Công Ty Chúng Tôi</li>
                    <li>Điều Khoản Chung </li>
                    <li>Điều Khoản Giao Dịch</li>
                    <li>Chính Sách</li>
                    <li>Thanh Toán</li>
                    <li>Chính Sách Bảo Mật</li>
                    <li>Câu Hỏi Thường Gặp</li>
                </ul>
            </div>
            <p class="text-center">&copy;{{ date('Y') }} Cinema Ticket. All rights reserved.</p>
        </footer>
</div>
<script>
    let selectedShowtime = null;
    let selectedSeats = [];
    const bookedSeatsByShowtime = @json($bookedSeatsByShowtime);

    const seatContainer = document.getElementById('seat-selection');
    seatContainer.classList.add('hidden');

    document.querySelectorAll('.showtime-btn').forEach(btn => {
        btn.addEventListener('click', function() {

            document.querySelectorAll('.showtime-btn').forEach(b => b.classList.remove('border-yellow-400', 'bg-gray-700'));
            this.classList.add('border-yellow-400', 'bg-gray-700');
            selectedShowtime = this.dataset.id;
            selectedSeats = [];

            seatContainer.classList.remove('hidden');

            document.querySelectorAll('.seat').forEach(seat => {
                seat.classList.remove('bg-red-600', 'bg-gray-500', 'cursor-not-allowed', 'hover:bg-gray-500');
                seat.classList.add('bg-gray-700', 'cursor-pointer', 'hover:bg-yellow-500');
                seat.dataset.disabled = "false";
            });
            const bookedSeats = bookedSeatsByShowtime[selectedShowtime] || [];
            bookedSeats.forEach(id => {
                const seat = document.getElementById('seat-' + id);
                if (seat) {
                    seat.classList.remove('bg-gray-700', 'hover:bg-yellow-500');
                    seat.classList.add('bg-red-600', 'cursor-not-allowed');
                    seat.dataset.disabled = "true";
                }
            });

            updateConfirmButton();
        });
    });

    function toggleSeat(seatId) {
        const seat = document.getElementById('seat-' + seatId);
        if (!seat || seat.dataset.disabled === "true") return;

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
</script>
@endsection
