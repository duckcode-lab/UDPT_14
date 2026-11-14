<nav class="bg-gray-900">
    <div class="w-full mx-auto px-4 sm:px-6 lg:px-10">
        <div class="flex justify-between h-16">
            {{-- Logo + Menu chính --}}
            <div class="flex items-center min-w-0">
                <a href="{{ route('home') }}" class="flex items-center">
                    <img src="{{ asset('uploads/movies/logo-b3a9e0cf-61b1-4be7-a2a5-e5017775b2b6-removebg-preview.png') }}"
                         alt="Cinema Ticket Logo"
                         class="h-8 w-8 bg-transparent">
                    <span class="text-white font-semibold ml-2 whitespace-nowrap">Vịt Mê Film</span>
                </a>

                {{-- Menu chính --}}
                <div class="hidden sm:flex items-center space-x-6 ml-10">
                    <x-nav-link :href="route('home')" :active="request()->routeIs('home')">
                        Trang chủ
                    </x-nav-link>

                    <x-nav-link :href="route('movies.list')" :active="request()->routeIs('movies.list')">
                        Phim đang chiếu
                    </x-nav-link>

                    @auth
                        @if(Auth::user()->role === 'admin' && request()->routeIs('admin.*'))
                            <x-nav-link :href="route('admin.dashboard')" :active="request()->routeIs('admin.dashboard')">
                                Trang quản trị
                            </x-nav-link>
                            <x-nav-link :href="route('admin.movies.index')" :active="request()->routeIs('admin.movies.index')">
                                Quản lý phim
                            </x-nav-link>
                            <x-nav-link :href="route('admin.showtimes.index')" :active="request()->routeIs('admin.showtimes.index')">
                                Quản lý suất chiếu
                            </x-nav-link>
                            <x-nav-link :href="route('admin.bookings.index')" :active="request()->routeIs('admin.bookings.index')">
                                Quản lý vé đặt
                            </x-nav-link>
                            <x-nav-link :href="route('admin.banners.index')" :active="request()->routeIs('admin.banners.*')">
                                Quản lý quảng bá
                            </x-nav-link>
                            <x-nav-link :href="route('admin.users.index')" :active="request()->routeIs('admin.users.*')">
                                Quản lý user
                            </x-nav-link>
                        @elseif(Auth::user()->role !== 'admin')
                            <x-nav-link :href="route('bookings.index')" :active="request()->routeIs('bookings.index')">
                                Vé của tôi
                            </x-nav-link>
                        @endif
                    @endauth
                </div>
            </div>

            {{-- Khu vực tài khoản --}}
            <div class="hidden sm:flex sm:items-center sm:space-x-6 text-yellow-500">
                @auth
                    {{-- Dropdown tài khoản --}}
                    <x-dropdown align="right" width="48">
                        <x-slot name="trigger">
                            <button class="inline-flex items-center px-3 py-2 text-sm font-medium rounded-md text-white bg-gray-900 hover:text-red-500">
                                <div>{{ Auth::user()->name }}</div>
                                <svg class="ms-1 h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                </svg>
                            </button>
                        </x-slot>

                        <x-slot name="content">
                            <x-dropdown-link :href="route('profile.edit')">
                                Hồ sơ cá nhân
                            </x-dropdown-link>

                            @if(Auth::user()->role === 'admin')
                                <x-dropdown-link :href="route('admin.dashboard')">Trang quản trị</x-dropdown-link>
                            @endif

                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <x-dropdown-link href="{{ route('logout') }}"
                                    onclick="event.preventDefault(); this.closest('form').submit();">
                                    Đăng xuất
                                </x-dropdown-link>
                            </form>
                        </x-slot>
                    </x-dropdown>
                @else
                    {{-- Khách chưa đăng nhập --}}
                    <a href="{{ route('login') }}" class="text-white hover:text-red-500 font-medium">
                        Đăng nhập
                    </a>
                    <a href="{{ route('register') }}" class="text-white hover:text-red-500 font-medium">
                        Đăng ký
                    </a>
                @endauth
            </div>

            {{-- Mobile toggle --}}
            <div class="-me-2 flex items-center sm:hidden">
                <button @click="open = !open" class="p-2 text-gray-400 hover:text-gray-500 focus:outline-none">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{ 'hidden': open, 'inline-flex': !open }" class="inline-flex"
                              stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{ 'hidden': !open, 'inline-flex': open }" class="hidden"
                              stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    {{-- Mobile menu --}}
    <div :class="{ 'block': open, 'hidden': !open }" class="hidden sm:hidden bg-gray-900 text-white">
        <div class="pt-2 pb-3 space-y-1">
            <x-responsive-nav-link :href="route('home')">Trang chủ</x-responsive-nav-link>
            <x-responsive-nav-link :href="route('movies.list')">Phim đang chiếu</x-responsive-nav-link>

            @auth
                @if(Auth::user()->role === 'admin' && request()->routeIs('admin.*'))
                    <x-responsive-nav-link :href="route('admin.dashboard')">Trang quản trị</x-responsive-nav-link>
                    <x-responsive-nav-link :href="route('admin.movies.index')">Quản lý phim</x-responsive-nav-link>
                    <x-responsive-nav-link :href="route('admin.showtimes.index')">Quản lý suất chiếu</x-responsive-nav-link>
                    <x-responsive-nav-link :href="route('admin.bookings.index')">Quản lý vé đặt</x-responsive-nav-link>
                    <x-responsive-nav-link :href="route('admin.banners.index')">Quản lý quảng bá</x-responsive-nav-link>
                    <x-responsive-nav-link :href="route('admin.users.index')">Quản lý user</x-responsive-nav-link>
                @endif
                <x-responsive-nav-link :href="route('profile.edit')">Hồ sơ cá nhân</x-responsive-nav-link>
                @if(Auth::user()->role !== 'admin')
                    <x-responsive-nav-link :href="route('bookings.index')">Vé của tôi</x-responsive-nav-link>
                @endif
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <x-responsive-nav-link href="{{ route('logout') }}"
                        onclick="event.preventDefault(); this.closest('form').submit();">
                        Đăng xuất
                    </x-responsive-nav-link>
                </form>
            @else
                <x-responsive-nav-link :href="route('login')">Đăng nhập</x-responsive-nav-link>
                <x-responsive-nav-link :href="route('register')">Đăng ký</x-responsive-nav-link>
            @endauth
        </div>
    </div>
</nav>
