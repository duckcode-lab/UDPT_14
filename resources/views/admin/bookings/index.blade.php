@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gray-900 text-white py-10 px-6">
    <div class="max-w-6xl mx-auto">
        <h1 class="text-3xl font-bold text-yellow-400 mb-6 text-center">🎟️ Quản lý vé đặt</h1>

        @if(session('success'))
            <div class="bg-green-600 p-3 rounded mb-4 text-center">{{ session('success') }}</div>
        @endif

        <table class="w-full border border-gray-700 rounded-lg text-center">
            <thead class="bg-gray-800 text-yellow-400">
                <tr>
                    <th class="p-3">Khách hàng</th>
                    <th class="p-3">Phim</th>
                    <th class="p-3">Ghế</th>
                    <th class="p-3">Suất chiếu</th>
                    <th class="p-3">Tổng tiền</th>
                    <th class="p-3">Hành động</th>
                </tr>
            </thead>
            <tbody>
                @foreach($bookings as $booking)
                    <tr class="border-t border-gray-700">
                        <td class="p-3">{{ $booking->user->name ?? 'Ẩn danh' }}</td>
                        <td class="p-3">{{ $booking->movie_title }}</td>
                        <td class="p-3">{{ $booking->seats }}</td>
                        <td class="p-3">{{ \Carbon\Carbon::parse($booking->show_time)->format('H:i d/m/Y') }}</td>
                        <td class="p-3">{{ number_format($booking->total_price) }} VNĐ</td>
                        <td class="p-3">
                            <form action="{{ route('admin.bookings.destroy', $booking->id) }}" method="POST" onsubmit="return confirm('Xác nhận huỷ vé của người dùng này?');">
                                @csrf
                                @method('DELETE')
                                <button class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded">
                                    Huỷ vé
                                </button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection

