<x-guest-layout>
    <div class="mb-4 text-sm text-gray-600 dark:text-gray-400">
        {{ __('Vui lòng nhập mã OTP gồm 6 chữ số đã được gửi đến email của bạn để hoàn tất đăng ký.') }}
    </div>

    {{-- thông báo lỗi --}}
    <x-auth-session-status class="mb-4" :status="session('error')" />

    {{-- FORM XÁC NHẬN OTP --}}
    <form method="POST" action="{{ route('verify.otp.submit') }}">
        @csrf

        <div>
            <x-input-label for="otp" :value="__('Mã xác thực (OTP)')" />

            <x-text-input id="otp"
                class="block mt-1 w-full"
                type="text"
                name="otp"
                required
                autofocus
                maxlength="6"
                pattern="\d{6}"
                placeholder="Otp code" />

            <x-input-error :messages="$errors->get('otp')" class="mt-2" />
        </div>

        <div class="flex items-center justify-between mt-4">
            <form method="POST" action="{{ route('resend.otp') }}">
                @csrf
                <button type="submit"
                    class="underline text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:focus:ring-offset-gray-800">
                    Gửi lại OTP
                </button>
            </form>
            <x-primary-button>
                Xác thực
            </x-primary-button>
        </div>
    </form>
</x-guest-layout>
