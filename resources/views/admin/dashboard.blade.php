@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-black text-white p-10">
    <h1 class="text-3xl font-bold mb-8 text-yellow-400">Bảng điều khiển quản trị</h1>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 text-center">
        <div class="bg-gray-800 p-6 rounded-lg shadow-md">
            <h2 class="text-xl font-semibold mb-2">Phim</h2>
            <p class="text-4xl text-yellow-400 font-bold">{{ $movies }}</p>
        </div>
        <div class="bg-gray-800 p-6 rounded-lg shadow-md">
            <h2 class="text-xl font-semibold mb-2">Suất chiếu</h2>
            <p class="text-4xl text-yellow-400 font-bold">{{ $showtimes }}</p>
        </div>
        <div class="bg-gray-800 p-6 rounded-lg shadow-md">
            <h2 class="text-xl font-semibold mb-2">Vé đã đặt</h2>
            <p class="text-4xl text-yellow-400 font-bold">{{ $bookings }}</p>
        </div>
    </div>
</div>
@endsection
