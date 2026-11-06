@extends('layouts.app')

@section('content')
<div class="p-10 bg-gray-900 text-white min-h-screen">
    <h1 class="text-3xl font-bold mb-6">Vé của tôi</h1>

    @if($bookings->isEmpty())
        <p>Bạn chưa đặt vé nào.</p>
    @else
        <table class="w-full text-white border border-gray-700">
            <thead>
                <tr class="bg-gray-800">
                    <th class="p-2 border">Phim</th>
                    <th class="p-2 border">Suất chiếu</th>
                    <th class="p-2 border">Ghế</th>
                    <th class="p-2 border">Tổng tiền</th>
                    <th class="p-2 border">Ngày đặt</th>
                    <th class="p-2 border">chức năng</th>
                </tr>
            </thead>
            <tbody>
                @foreach($bookings as $booking)
                    <tr class="border-b border-gray-700">
                        <td class="p-2 border">{{ $booking->showtime->movie->title }}</td>
                        <td class="p-2 border">{{ $booking->showtime->time }}</td>
                        <td class="p-2 border">{{ $booking->seats }}</td>
                        <td class="p-2 border">{{ $booking->total_price }} VNĐ</td>
                        <td class="p-2 border">{{ $booking->created_at->format('d/m/Y H:i') }}</td>

                        <td class="p-2 border items-center flex justify-center">
                            <form action="{{ route('bookings.destroy', $booking->id) }}" method="POST" onsubmit="return confirm('Bạn có chắc chắn muốn hủy vé này không?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="bg-red-600 hover:bg-red-700 text-white font-bold py-1 px-3 rounded">
                                    Hủy vé
                                </button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
</div>
@endsection
