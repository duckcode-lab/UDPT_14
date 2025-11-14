<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
</head>
<body class="font-sans antialiased bg-gray-900 text-white">
    <div class="fixed inset-0 bg-black/60 flex items-center justify-center z-50">
        <div class="bg-white p-6 rounded-lg relative w-96 text-black">
            <h2 class="text-xl font-semibold mb-4 text-center">Đăng nhập</h2>
            <form method="POST" action="{{ route('login') }}">
                @csrf
                <input type="email" name="email" placeholder="Email" class="w-full p-2 border rounded mb-3" required>
                <input type="password" name="password" placeholder="Mật khẩu" class="w-full p-2 border rounded mb-4" required>
                <div class="flex justify-between items-center mb-4">
                    <label class="flex items-center text-sm">
                        <input type="checkbox" name="remember" class="mr-1"> Nhớ mật khẩu
                    </label>
                    @if(Route::has('password.request'))
                        <a href="{{ route('password.request') }}" class="text-sm text-blue-600 hover:underline">Quên mật khẩu?</a>
                    @endif
                </div>
                <button class="w-full bg-red-500 text-white py-2 rounded">Đăng nhập</button>
            </form>
        </div>
    </div>
    <div class="fixed inset-0 bg-black/60 flex items-center justify-center z-50">
        <div class="bg-white p-6 rounded-lg relative w-96 text-black">
            <h2 class="text-xl font-semibold mb-4 text-center">Đăng ký</h2>
            <form method="POST" action="{{ route('register') }}">
                @csrf
                <input type="text" name="name" placeholder="Tên" class="w-full p-2 border rounded mb-3" required>
                <input type="email" name="email" placeholder="Email" class="w-full p-2 border rounded mb-3" required>
                <input type="password" name="password" placeholder="Mật khẩu" class="w-full p-2 border rounded mb-3" required>
                <input type="password" name="password_confirmation" placeholder="Xác nhận mật khẩu" class="w-full p-2 border rounded mb-4" required>
                <button class="w-full bg-green-500 text-white py-2 rounded">Tạo tài khoản</button>
            </form>
        </div>
    </div>

    {{-- NAVIGATION --}}
    <div>
        @include('layouts.navigation')
    </div>

    {{-- MAIN CONTENT --}}
    <main>
        @yield('content')
    </main>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const swiper = new Swiper(".mySwiper", {
                loop: true,
                autoplay: { delay: 5000, disableOnInteraction: false },
                pagination: { el: ".swiper-pagination", clickable: true },
                navigation: { nextEl: ".swiper-button-next", prevEl: ".swiper-button-prev" },
                effect: "fade",
                fadeEffect: { crossFade: true },
            });
        });
    </script>
</body>
</html>
