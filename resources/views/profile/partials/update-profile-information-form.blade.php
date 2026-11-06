<section>
    <header>
        <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
            Thông tin cá nhân
        </h2>
        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
            Cập nhật thông tin hồ sơ. Email tài khoản không thể thay đổi.
        </p>
    </header>

    <form method="post" action="{{ route('profile.update') }}" enctype="multipart/form-data" class="mt-6 space-y-6">
        @csrf
        @method('patch')

        <div>
            <x-input-label for="name" value="Họ tên" />
            <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" :value="old('name', $user->name)" required autocomplete="name" />
            <x-input-error class="mt-2" :messages="$errors->get('name')" />
        </div>

        <div>
            <x-input-label for="email" value="Email" />
            <x-text-input id="email" type="email" class="mt-1 block w-full cursor-not-allowed bg-gray-100 text-gray-500 dark:bg-gray-900 dark:text-gray-400" :value="$user->email" disabled />
            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">Email được dùng để đăng nhập và không thể sửa.</p>
        </div>

        <div>
            <x-input-label for="phone" value="Số điện thoại" />
            <x-text-input id="phone" name="phone" type="tel" class="mt-1 block w-full" :value="old('phone', $user->phone)" autocomplete="tel" />
            <x-input-error class="mt-2" :messages="$errors->get('phone')" />
        </div>

        <div>
            <x-input-label for="birth_date" value="Ngày sinh" />
            <x-text-input id="birth_date" name="birth_date" type="date" class="mt-1 block w-full" :value="old('birth_date', $user->birth_date?->format('Y-m-d'))" max="{{ now()->toDateString() }}" />
            <x-input-error class="mt-2" :messages="$errors->get('birth_date')" />
        </div>

        <div>
            <x-input-label for="address" value="Địa chỉ" />
            <textarea id="address" name="address" rows="3" class="mt-1 block w-full rounded-md border-gray-300 bg-white text-gray-900 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100">{{ old('address', $user->address) }}</textarea>
            <x-input-error class="mt-2" :messages="$errors->get('address')" />
        </div>

        <div>
            <x-input-label for="gender" value="Giới tính" />
            <select id="gender" name="gender" class="mt-1 block w-full rounded-md border-gray-300 bg-white text-gray-900 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100">
                <option value="">Chọn giới tính</option>
                <option value="male" @selected(old('gender', $user->gender) === 'male')>Nam</option>
                <option value="female" @selected(old('gender', $user->gender) === 'female')>Nữ</option>
                <option value="other" @selected(old('gender', $user->gender) === 'other')>Khác</option>
                <option value="prefer_not_to_say" @selected(old('gender', $user->gender) === 'prefer_not_to_say')>Không muốn chia sẻ</option>
            </select>
            <x-input-error class="mt-2" :messages="$errors->get('gender')" />
        </div>

        <div>
            <x-input-label for="avatar" value="Ảnh đại diện" />
            @if ($user->avatar)
                <img src="{{ asset('storage/'.$user->avatar) }}" alt="Ảnh đại diện hiện tại" class="mt-2 mb-3 h-24 w-24 rounded-full object-cover">
            @endif
            <input id="avatar" name="avatar" type="file" accept="image/jpeg,image/png,image/webp"
                class="mt-1 block w-full text-sm text-gray-700 file:mr-4 file:rounded-md file:border-0 file:bg-gray-100 file:px-4 file:py-2 file:font-semibold dark:text-gray-300 dark:file:bg-gray-700 dark:file:text-gray-100">
            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">JPG, PNG hoặc WebP, tối đa 2 MB.</p>
            <x-input-error class="mt-2" :messages="$errors->get('avatar')" />
            @if ($user->avatar)
                <label class="mt-3 inline-flex items-center gap-2 text-sm text-gray-700 dark:text-gray-300">
                    <input type="checkbox" name="remove_avatar" value="1" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
                    Xóa ảnh đại diện hiện tại
                </label>
            @endif
        </div>

        <div class="flex items-center gap-4">
            <x-primary-button>Lưu thay đổi</x-primary-button>

            @if (session('status') === 'profile-updated')
                <p class="text-sm text-green-600 dark:text-green-400" role="status">Đã lưu thông tin hồ sơ.</p>
            @endif
        </div>
    </form>
</section>
