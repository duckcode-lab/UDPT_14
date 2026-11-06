@extends('admin.users.layout')

@section('users-content')
<div class="mx-auto max-w-3xl">
    <div class="mb-6 flex items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-white sm:text-3xl">Thêm người dùng</h1>
            <p class="mt-2 text-sm text-slate-400">Tạo tài khoản mới và thiết lập quyền truy cập.</p>
        </div>
        <a href="{{ route('admin.users.index') }}" class="shrink-0 rounded-lg border border-slate-700 px-4 py-2 text-sm text-slate-200 transition hover:bg-slate-800">Quay lại</a>
    </div>

    @if ($errors->any())
        <div class="mb-5 rounded-lg border border-red-800 bg-red-950/60 px-4 py-3 text-sm text-red-300" role="alert">
            <ul class="list-inside list-disc space-y-1">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('admin.users.store') }}" class="space-y-5 rounded-xl border border-slate-800 bg-[#171c22] p-5 sm:p-7">
        @csrf

        <div>
            <label for="name" class="mb-2 block font-medium text-white">Họ tên:</label>
            <input id="name" name="name" type="text" value="{{ old('name') }}" required maxlength="255"
                class="block w-full rounded-lg border border-slate-700 bg-[#11161b] p-3 text-white focus:border-red-500 focus:outline-none focus:ring-1 focus:ring-red-500">
            @error('name') <p class="mt-1 text-sm text-red-400">{{ $message }}</p> @enderror
        </div>

        <div>
            <label for="email" class="mb-2 block font-medium text-white">Email:</label>
            <input id="email" name="email" type="email" value="{{ old('email') }}" required maxlength="255"
                class="block w-full rounded-lg border border-slate-700 bg-[#11161b] p-3 text-white focus:border-red-500 focus:outline-none focus:ring-1 focus:ring-red-500">
            @error('email') <p class="mt-1 text-sm text-red-400">{{ $message }}</p> @enderror
        </div>

        <div>
            <label for="role" class="mb-2 block font-medium text-white">Quyền:</label>
            <select id="role" name="role" required class="block w-full rounded-lg border border-slate-700 bg-[#11161b] p-3 text-white focus:border-red-500 focus:outline-none focus:ring-1 focus:ring-red-500">
                <option value="user" @selected(old('role', 'user') === 'user')>User</option>
                <option value="admin" @selected(old('role') === 'admin')>Admin</option>
            </select>
            @error('role') <p class="mt-1 text-sm text-red-400">{{ $message }}</p> @enderror
        </div>

        <div>
            <label for="password" class="mb-2 block font-medium text-white">Mật khẩu:</label>
            <input id="password" name="password" type="password" required autocomplete="new-password"
                class="block w-full rounded-lg border border-slate-700 bg-[#11161b] p-3 text-white focus:border-red-500 focus:outline-none focus:ring-1 focus:ring-red-500">
            @error('password') <p class="mt-1 text-sm text-red-400">{{ $message }}</p> @enderror
        </div>

        <div>
            <label for="password_confirmation" class="mb-2 block font-medium text-white">Xác nhận mật khẩu:</label>
            <input id="password_confirmation" name="password_confirmation" type="password" required autocomplete="new-password"
                class="block w-full rounded-lg border border-slate-700 bg-[#11161b] p-3 text-white focus:border-red-500 focus:outline-none focus:ring-1 focus:ring-red-500">
        </div>

        <div class="flex flex-wrap gap-3 border-t border-slate-800 pt-5">
            <button type="submit" class="rounded-lg bg-green-600 px-5 py-2.5 font-semibold text-white transition hover:bg-green-500">Lưu người dùng</button>
            <a href="{{ route('admin.users.index') }}" class="rounded-lg border border-slate-700 px-5 py-2.5 text-slate-200 transition hover:bg-slate-800">Hủy</a>
        </div>
    </form>
</div>
@endsection
