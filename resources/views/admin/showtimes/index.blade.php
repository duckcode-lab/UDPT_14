@extends('layouts.app')

@section('content')
<div class="p-10 bg-gray-900 text-white min-h-screen">
    <h1 class="text-3xl font-bold mb-6 text-yellow-400">Quản lý suất chiếu</h1>

    <div class="mb-4 text-right">
        <a href="{{ route('admin.showtimes.create') }}" class="bg-green-500 hover:bg-green-600 px-4 py-2 rounded">
            Thêm suất chiếu
        </a>
    </div>

    <table class="w-full border border-gray-700 rounded-lg">
        <tbody>
            @php
                $groupedShowtimes = $showtimes->groupBy('movie_id');
            @endphp

            @foreach($groupedShowtimes as $movieId => $movieShowtimes)
                @php $movie = $movieShowtimes->first()->movie; @endphp

                <div class="mb-8 p-6 bg-gray-900 rounded-xl shadow-lg">
                    <h2 class="text-2xl font-bold text-yellow-400 mb-4">
                        Film: {{ $movie->title }}
                    </h2>

                    @foreach($movieShowtimes as $showtime)
                        <div class="p-3 bg-gray-800 rounded-lg mb-2 flex flex-col md:flex-row md:justify-between md:items-center">
                            <div class="info">
                                {{ $showtime->theater->name }}
                                <div class="flex flex-col md:flex-row md:gap-12">
                                    <p>Phòng: {{ $showtime->room }}</p>
                                    <p>Giờ chiếu: {{ \Carbon\Carbon::parse($showtime->time)->format('H:i d/m/Y') }}</p>
                                    <p>Giá vé:
                                        <span class="text-green-400 font-semibold">
                                            {{ number_format($showtime->ticketPrice->price) . ' VNĐ' }}
                                        </span>
                                    </p>
                                </div>
                            </div>
                            <div class="mt-2 flex gap-2">
                                <a href="{{ route('admin.showtimes.edit', $showtime->id) }}"
                                   class="bg-yellow-500 hover:bg-yellow-600 px-3 py-1 rounded text-white">
                                   Sửa
                                </a>
                                <form action="{{ route('admin.showtimes.destroy', $showtime->id) }}"
                                        method="POST"
                                        onsubmit="return confirm('Bạn có chắc chắn muốn xoá suất chiếu này không?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                                class="bg-red-600 hover:bg-red-700 px-3 py-1 rounded text-white">
                                            Xoá
                                        </button>
                                </form>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endforeach
        </tbody>
    </table>
</div>
@endsection

