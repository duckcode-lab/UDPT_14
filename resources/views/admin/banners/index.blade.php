@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gray-900 text-white py-10 px-6">
    <div class="max-w-6xl mx-auto">
        <h1 class="text-3xl font-bold text-yellow-400 mb-6 text-center">🎬 Quản lý quảng bá phim</h1>

        @if (session('success'))
            <div class="bg-green-600 p-3 rounded mb-4 text-center">
                {{ session('success') }}
            </div>
        @endif

        <div class="flex justify-end mb-4">
            <a href="{{ route('admin.banners.create') }}"
               class="bg-yellow-500 hover:bg-yellow-600 text-black font-semibold px-4 py-2 rounded">
                ➕ Thêm banner mới
            </a>
        </div>

        <table class="w-full border border-gray-700 rounded-lg text-center">
            <thead class="bg-gray-800 text-yellow-400">
                <tr>
                    <th class="p-3">Hình ảnh</th>
                    <th class="p-3">Tiêu đề</th>
                    <th class="p-3">Liên kết</th>
                    <th class="p-3">Ngày tạo</th>
                    <th class="p-3">Hành động</th>
                </tr>
            </thead>
            <tbody>
                @foreach($banners as $banner)
                    <tr class="border-t border-gray-700">
                        <td class="p-3">
                            @if($banner->image)
                                <img src="{{ asset('storage/' . $banner->image) }}" alt="Banner" class="h-24 mx-auto rounded">
                            @else
                                <span class="text-gray-400 italic">Không có ảnh</span>
                            @endif
                        </td>
                        <td class="p-3 font-semibold">{{ $banner->title ?? '—' }}</td>
                        <td class="p-3 text-blue-400">
                            @if($banner->link)
                                <a href="{{ $banner->link }}" target="_blank" class="hover:underline">{{ $banner->link }}</a>
                            @else
                                <span class="text-gray-400 italic">Không có</span>
                            @endif
                        </td>
                        <td class="p-3">{{ $banner->created_at->format('d/m/Y') }}</td>
                        <td class="p-3">
                            <div class="flex justify-center gap-2">
                                <a href="{{ route('admin.banners.edit', $banner->id) }}"
                                   class="bg-blue-600 hover:bg-blue-700 text-white px-3 py-1 rounded">Sửa</a>
                                <form action="{{ route('admin.banners.destroy', $banner->id) }}" method="POST"
                                      onsubmit="return confirm('Xóa banner này?');">
                                    @csrf
                                    @method('DELETE')
                                    <button class="bg-red-600 hover:bg-red-700 text-white px-3 py-1 rounded">Xóa</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div class="mt-6 text-center">
            {{ $banners->links() }}
        </div>
    </div>
</div>
@endsection
