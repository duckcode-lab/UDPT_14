@extends('admin.users.layout')

@section('users-content')
<div class="min-h-screen bg-gray-900 px-4 py-8 text-white sm:px-6 lg:p-10">
  <div class="mx-auto max-w-3xl">
    <div class="mb-6 flex items-center justify-between gap-4">
        <div>
            <h1 class="text-3xl font-bold text-yellow-400">Thêm người dùng</h1>
            <p class="mt-2 text-sm text-gray-400">Tạo tài khoản mới và thiết lập quyền truy cập.</p>
        </div>
        <a href="{{ route('admin.users.index') }}" class="shrink-0 rounded border border-gray-600 px-4 py-2 text-sm text-white transition hover:bg-gray-800">Quay lại</a>
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

    <form method="POST" action="{{ route('admin.users.store') }}" class="space-y-5 rounded-xl bg-gray-800 p-5 shadow-lg sm:p-7">
        @csrf

        <div>
            <label for="name" class="mb-2 block font-medium text-yellow-400">Họ tên:</label>
            <input id="name" name="name" type="text" value="{{ old('name') }}" required maxlength="255"
                class="block w-full rounded border border-gray-600 bg-gray-900 p-3 text-white focus:border-yellow-500 focus:outline-none focus:ring-1 focus:ring-yellow-500">
            @error('name') <p class="mt-1 text-sm text-red-400">{{ $message }}</p> @enderror
        </div>

        <div>
            <label for="email" class="mb-2 block font-medium text-yellow-400">Email:</label>
            <input id="email" name="email" type="email" value="{{ old('email') }}" required maxlength="255"
                class="block w-full rounded border border-gray-600 bg-gray-900 p-3 text-white focus:border-yellow-500 focus:outline-none focus:ring-1 focus:ring-yellow-500">
            @error('email') <p class="mt-1 text-sm text-red-400">{{ $message }}</p> @enderror
        </div>

        <div>
            <label for="role" class="mb-2 block font-medium text-yellow-400">Quyền:</label>
            <select id="role" name="role" required class="block w-full rounded border border-gray-600 bg-gray-900 p-3 text-white focus:border-yellow-500 focus:outline-none focus:ring-1 focus:ring-yellow-500">
                <option value="user" @selected(old('role', 'user') === 'user')>User</option>
                <option value="admin" @selected(old('role') === 'admin')>Admin</option>
            </select>
            @error('role') <p class="mt-1 text-sm text-red-400">{{ $message }}</p> @enderror
        </div>

        <div>
            <label for="password" class="mb-2 block font-medium text-yellow-400">Mật khẩu:</label>
            <input id="password" name="password" type="password" required autocomplete="new-password"
                class="block w-full rounded border border-gray-600 bg-gray-900 p-3 text-white focus:border-yellow-500 focus:outline-none focus:ring-1 focus:ring-yellow-500">
            @error('password') <p class="mt-1 text-sm text-red-400">{{ $message }}</p> @enderror
        </div>

        <div>
            <label for="password_confirmation" class="mb-2 block font-medium text-yellow-400">Xác nhận mật khẩu:</label>
            <input id="password_confirmation" name="password_confirmation" type="password" required autocomplete="new-password"
                class="block w-full rounded border border-gray-600 bg-gray-900 p-3 text-white focus:border-yellow-500 focus:outline-none focus:ring-1 focus:ring-yellow-500">
        </div>

        <div class="flex flex-wrap gap-3 border-t border-gray-700 pt-5">
            <button type="submit" class="rounded bg-green-600 px-5 py-2.5 font-semibold text-white transition hover:bg-green-700">Lưu người dùng</button>
            <a href="{{ route('admin.users.index') }}" class="rounded border border-gray-600 px-5 py-2.5 text-white transition hover:bg-gray-700">Hủy</a>
        </div>
    </form>
  </div>
</div>
@endsection
