@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-[#121518] text-slate-100">
    <header class="flex h-[62px] items-center border-b border-[#262b32] bg-[#111316] px-4 sm:px-6 lg:px-8">
        <a href="{{ route('admin.dashboard') }}" class="flex w-56 shrink-0 items-center gap-2.5" aria-label="Trang quản trị">
            <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-red-600 shadow-lg shadow-red-950/40">
                <svg class="ml-0.5 h-4 w-4" viewBox="0 0 16 16" fill="currentColor" aria-hidden="true">
                    <path d="M5 3.8a.7.7 0 0 1 1.05-.61l6.1 3.5a1.5 1.5 0 0 1 0 2.62l-6.1 3.5A.7.7 0 0 1 5 12.2V3.8Z"/>
                </svg>
            </span>
            <span class="text-sm font-bold tracking-[0.12em] text-slate-100">VỊT MÊ <span class="text-red-500">FILM</span></span>
        </a>
        <div class="ml-auto flex items-center gap-5 text-xs text-slate-300">
            <span class="hidden sm:inline">Xin chào, {{ auth()->user()->name }}</span>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="transition hover:text-red-400">Đăng xuất</button>
            </form>
        </div>
    </header>

    <div class="flex min-h-[calc(100vh-62px)]">
        <aside class="hidden w-56 shrink-0 border-r border-[#262b32] bg-[#171a1f] px-3 py-5 md:block">
            <p class="mb-3 px-3 text-[11px] font-semibold tracking-[0.12em] text-slate-500">QUẢN TRỊ</p>
            <nav class="space-y-1" aria-label="Điều hướng quản trị">
                <a href="{{ route('admin.dashboard') }}" class="flex items-center rounded-lg px-3 py-2.5 text-sm text-slate-300 transition hover:bg-[#242a32] hover:text-white"><span class="mr-3 text-slate-400" aria-hidden="true">▦</span>Báo cáo</a>
                <a href="{{ route('admin.movies.index') }}" class="flex items-center rounded-lg px-3 py-2.5 text-sm text-slate-300 transition hover:bg-[#242a32] hover:text-white"><span class="mr-3 text-slate-400" aria-hidden="true">▤</span>Quản lý phim</a>
                <a href="{{ route('admin.showtimes.index') }}" class="flex items-center rounded-lg px-3 py-2.5 text-sm text-slate-300 transition hover:bg-[#242a32] hover:text-white"><span class="mr-3 text-slate-400" aria-hidden="true">◷</span>Quản lý suất chiếu</a>
                <a href="{{ route('admin.bookings.index') }}" class="flex items-center rounded-lg px-3 py-2.5 text-sm text-slate-300 transition hover:bg-[#242a32] hover:text-white"><span class="mr-3 text-slate-400" aria-hidden="true">▣</span>Quản lý vé đặt</a>
                <a href="{{ route('admin.users.index') }}" @class([
                    'flex items-center rounded-lg px-3 py-2.5 text-sm transition',
                    'border border-red-500 bg-[#242a32] font-medium text-white shadow-[0_0_0_2px_rgba(239,68,68,0.12)]' => request()->routeIs('admin.users.*'),
                    'text-slate-300 hover:bg-[#242a32] hover:text-white' => ! request()->routeIs('admin.users.*'),
                ]) aria-current="{{ request()->routeIs('admin.users.*') ? 'page' : 'false' }}"><span class="mr-3 text-slate-300" aria-hidden="true">♙</span>Quản lý user</a>
            </nav>
        </aside>

        <main class="min-w-0 flex-1 px-4 py-6 sm:px-6 sm:py-8 lg:px-8">
            <nav class="mb-6 flex gap-2 overflow-x-auto md:hidden" aria-label="Điều hướng quản trị">
                <a href="{{ route('admin.dashboard') }}" class="whitespace-nowrap rounded-lg bg-[#1d242c] px-3 py-2 text-xs text-slate-300">Báo cáo</a>
                <a href="{{ route('admin.movies.index') }}" class="whitespace-nowrap rounded-lg bg-[#1d242c] px-3 py-2 text-xs text-slate-300">Phim</a>
                <a href="{{ route('admin.showtimes.index') }}" class="whitespace-nowrap rounded-lg bg-[#1d242c] px-3 py-2 text-xs text-slate-300">Suất chiếu</a>
                <a href="{{ route('admin.bookings.index') }}" class="whitespace-nowrap rounded-lg bg-[#1d242c] px-3 py-2 text-xs text-slate-300">Vé đặt</a>
                <a href="{{ route('admin.users.index') }}" class="whitespace-nowrap rounded-lg border border-red-500 bg-[#242a32] px-3 py-2 text-xs text-white">Quản lý user</a>
            </nav>
            <div class="mx-auto max-w-7xl">
                @yield('users-content')
            </div>
        </main>
    </div>
</div>
@endsection
