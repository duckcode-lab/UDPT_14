@extends('admin.users.layout')

@section('users-content')
<div class="mx-auto max-w-7xl">
                <header class="mb-8 flex flex-wrap items-end justify-between gap-4">
                    <div>
                        <h1 class="text-2xl font-bold tracking-tight sm:text-3xl">Quản lý người dùng</h1>
                        <p class="mt-2 text-sm text-slate-400">Danh sách tài khoản và quyền truy cập hệ thống.</p>
                    </div>
                    <a href="{{ route('admin.users.create') }}" class="rounded-lg bg-green-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-green-500">
                        Thêm người dùng mới
                    </a>
                </header>

                @if (session('success'))
                    <div class="mb-5 rounded-lg border border-emerald-800 bg-emerald-950/60 px-4 py-3 text-sm text-emerald-300" role="status">
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
                    <p class="text-sm text-slate-400">
                        {{ $users->total() }} tài khoản
                        @if ($users->total() > 0)
                            <span class="text-slate-500">· Trang {{ $users->currentPage() }}/{{ $users->lastPage() }}</span>
                        @endif
                    </p>
                </div>

                <div class="overflow-x-auto rounded-xl border border-slate-800">
                    <table class="w-full min-w-[850px] border-collapse text-left text-sm">
                        <thead class="bg-[#1d242c] text-xs uppercase tracking-wide text-slate-400">
                            <tr>
                                <th scope="col" class="px-4 py-3">STT</th>
                                <th scope="col" class="px-4 py-3">Email</th>
                                <th scope="col" class="px-4 py-3">Họ tên</th>
                                <th scope="col" class="px-4 py-3">Role</th>
                                <th scope="col" class="px-4 py-3">Created at</th>
                                <th scope="col" class="px-4 py-3 text-right">Thao tác</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-800 bg-[#171c22]">
                            @forelse ($users as $user)
                                <tr class="transition hover:bg-[#1d242c]">
                                    <td class="px-4 py-4 text-slate-400">{{ $users->firstItem() + $loop->index }}</td>
                                    <td class="px-4 py-4 font-medium text-slate-100">{{ $user->email }}</td>
                                    <td class="px-4 py-4 text-slate-300">{{ $user->name }}</td>
                                    <td class="px-4 py-4">
                                        <span @class([
                                            'inline-flex rounded-full px-2.5 py-1 text-xs font-medium',
                                            'bg-purple-950 text-purple-300 ring-1 ring-inset ring-purple-800' => $user->role === 'admin',
                                            'bg-emerald-950 text-emerald-300 ring-1 ring-inset ring-emerald-800' => $user->role !== 'admin',
                                        ])>
                                            {{ strtoupper($user->role) }}
                                        </span>
                                    </td>
                                    <td class="whitespace-nowrap px-4 py-4 text-slate-400">{{ $user->created_at?->format('H:i d/m/Y') }}</td>
                                    <td class="px-4 py-4">
                                        <div class="flex justify-end gap-2">
                                            @if (auth()->id() !== $user->id)
                                                <a href="{{ route('admin.users.edit', $user) }}" class="rounded-lg border border-slate-700 px-3 py-2 text-xs font-medium text-slate-200 transition hover:border-yellow-500 hover:text-white">
                                                    Sửa
                                                </a>
                                                <form method="POST" action="{{ route('admin.users.destroy', $user) }}"
                                                    onsubmit="return confirm('Xóa tài khoản này? Các vé đặt và bình luận liên quan cũng sẽ bị xóa.')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="rounded-lg border border-red-900 px-3 py-2 text-xs font-medium text-red-300 transition hover:bg-red-950 hover:text-red-200">
                                                        Xóa
                                                    </button>
                                                </form>
                                            @else
                                                <a href="{{ route('admin.users.edit', $user) }}" class="rounded-lg border border-slate-700 px-3 py-2 text-xs font-medium text-slate-200 transition hover:border-yellow-500 hover:text-white">
                                                    Sửa thông tin
                                                </a>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-4 py-10 text-center text-slate-400">Chưa có tài khoản nào.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if ($users->hasPages())
                    <div class="mt-5">
                        {{ $users->links() }}
                    </div>
                @endif
            </div>
@endsection
