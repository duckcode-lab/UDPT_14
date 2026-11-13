@extends('admin.users.layout')

@section('users-content')
<div class="min-h-screen bg-gray-900 px-4 py-8 text-white sm:px-6 lg:p-10">
    <div class="mx-auto max-w-7xl">
        <header class="mb-8 flex flex-wrap items-center justify-between gap-4">
            <div>
                <h1 class="text-3xl font-bold text-yellow-400">Quản lý người dùng</h1>
                <p class="mt-2 text-sm text-gray-400">Danh sách tài khoản và quyền truy cập hệ thống.</p>
            </div>
            <a href="{{ route('admin.users.create') }}" class="rounded bg-green-600 px-4 py-2.5 font-semibold text-white transition hover:bg-green-700">
                Thêm người dùng mới
            </a>
        </header>

        @if (session('success'))
            <div class="mb-5 rounded-lg bg-green-700 p-3 text-center" role="status">
                {{ session('success') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="mb-5 rounded-lg border border-red-800 bg-red-950/60 px-4 py-3 text-sm text-red-300" role="alert">
                <ul class="list-inside list-disc space-y-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="mb-3 flex flex-wrap items-center justify-between gap-3">
            <p class="text-sm text-gray-400">
                {{ $users->total() }} tài khoản
                @if ($users->total() > 0)
                    <span class="text-gray-500">· Trang {{ $users->currentPage() }}/{{ $users->lastPage() }}</span>
                @endif
            </p>
        </div>

        <div class="overflow-x-auto rounded-lg border border-gray-700">
            <table class="w-full min-w-[850px] border-collapse text-left text-sm">
                <thead class="bg-gray-800 text-yellow-400">
                    <tr>
                        <th scope="col" class="p-3">STT</th>
                        <th scope="col" class="p-3">Email</th>
                        <th scope="col" class="p-3">Họ tên</th>
                        <th scope="col" class="p-3">Quyền</th>
                        <th scope="col" class="p-3">Ngày tạo</th>
                        <th scope="col" class="p-3 text-center">Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($users as $user)
                        <tr class="border-t border-gray-700 transition hover:bg-gray-800">
                            <td class="p-3 text-gray-400">{{ $users->firstItem() + $loop->index }}</td>
                            <td class="p-3 font-medium">{{ $user->email }}</td>
                            <td class="p-3">{{ $user->name }}</td>
                            <td class="p-3">
                                <span @class([
                                    'inline-flex rounded-full border px-2.5 py-1 text-xs font-medium',
                                    'border-blue-700 text-blue-300' => $user->role === 'admin',
                                    'border-green-700 text-green-300' => $user->role !== 'admin',
                                ])>{{ strtoupper($user->role) }}</span>
                            </td>
                            <td class="whitespace-nowrap p-3 text-gray-400">{{ $user->created_at?->format('H:i d/m/Y') }}</td>
                            <td class="p-3">
                                <div class="flex justify-center gap-2">
                                    <a href="{{ route('admin.users.edit', $user) }}" class="rounded border border-gray-500 px-3 py-1.5 text-sm transition hover:border-yellow-400 hover:text-yellow-300">
                                        {{ auth()->id() === $user->id ? 'Sửa thông tin' : 'Sửa' }}
                                    </a>
                                    @if (auth()->id() !== $user->id)
                                        <form method="POST" action="{{ route('admin.users.destroy', $user) }}"
                                            onsubmit="return confirm('Xóa tài khoản này? Các vé đặt và bình luận liên quan cũng sẽ bị xóa.')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="rounded bg-red-600 px-3 py-1.5 text-sm text-white transition hover:bg-red-700">Xóa</button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="p-10 text-center text-gray-400">Chưa có tài khoản nào.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($users->hasPages())
            <div class="mt-6">{{ $users->links() }}</div>
        @endif
    </div>
</div>
@endsection
