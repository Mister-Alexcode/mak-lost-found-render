<nav x-data="{ open: false }" class="bg-white border-b border-gray-200 shadow-sm">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex">
                <!-- Logo -->
                <div class="shrink-0 flex items-center gap-2">
                    <a href="{{ Auth::check() ? route('dashboard') : url('/') }}" class="flex items-center gap-2">
                        <div class="w-8 h-8 bg-blue-600 rounded-lg flex items-center justify-center">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                            </svg>
                        </div>
                        <span class="font-bold text-gray-800 hidden sm:inline">MAK Lost & Found</span>
                    </a>
                </div>

                <!-- Navigation Links -->
                <div class="hidden space-x-1 sm:-my-px sm:ms-6 sm:flex">
                    @auth
                    <x-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                        Dashboard
                    </x-nav-link>
                    <x-nav-link :href="route('lost-items.index')" :active="request()->routeIs('lost-items.*')">
                        Lost Items
                    </x-nav-link>
                    <x-nav-link :href="route('found-items.index')" :active="request()->routeIs('found-items.*')">
                        Found Items
                    </x-nav-link>
                    @endauth
                    <x-nav-link :href="route('search.index')" :active="request()->routeIs('search.*')">
                        Search
                    </x-nav-link>
                    @auth
                    <x-nav-link :href="route('claims.index')" :active="request()->routeIs('claims.*')">
                        Claims
                    </x-nav-link>
                    <x-nav-link :href="route('messages.index')" :active="request()->routeIs('messages.*')">
                        Messages
                    </x-nav-link>
                    @if(Auth::user()->isAdmin())
                    <x-nav-link :href="route('admin.dashboard')" :active="request()->routeIs('admin.*')"
                                class="!text-purple-600 !border-purple-500">
                        Admin
                    </x-nav-link>
                    @endif
                    @endauth
                </div>
            </div>

            <!-- Right Side -->
            <div class="hidden sm:flex sm:items-center sm:gap-3">
                @auth
                @php
                    $unreadCount = \App\Models\ItemNotification::where('user_id', Auth::id())
                        ->where('is_read', false)->count();
                @endphp

                {{-- Points Badge --}}
                <a href="{{ route('leaderboard.index') }}"
                   class="flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-medium
                          {{ request()->routeIs('leaderboard.*') ? 'bg-orange-100 text-orange-700' : 'bg-gray-100 text-gray-600 hover:bg-orange-50 hover:text-orange-600' }}
                          transition">
                    <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                    </svg>
                    {{ Auth::user()->reward_points }} pts
                </a>

                {{-- Notification Bell --}}
                <a href="{{ route('notifications.index') }}"
                   class="relative p-2 rounded-full text-gray-400 hover:text-gray-600 hover:bg-gray-100 transition">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                    </svg>
                    @if($unreadCount > 0)
                    <span class="absolute top-0.5 right-0.5 bg-red-500 text-white text-[10px] font-bold rounded-full h-4 w-4 flex items-center justify-center ring-2 ring-white">
                        {{ $unreadCount > 9 ? '9+' : $unreadCount }}
                    </span>
                    @endif
                </a>

                {{-- User Dropdown --}}
                <x-dropdown align="right" width="48">
                    <x-slot name="trigger">
                        <button class="inline-flex items-center gap-2 px-3 py-2 rounded-lg text-sm font-medium text-gray-600 hover:text-gray-800 hover:bg-gray-50 transition">
                            <div class="w-7 h-7 rounded-full bg-blue-100 text-blue-700 flex items-center justify-center text-xs font-bold">
                                {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                            </div>
                            <span class="hidden lg:inline">{{ Auth::user()->name }}</span>
                            <svg class="w-4 h-4 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                            </svg>
                        </button>
                    </x-slot>

                    <x-slot name="content">
                        <div class="px-4 py-2.5 border-b border-gray-100">
                            <p class="text-sm font-medium text-gray-800">{{ Auth::user()->name }}</p>
                            <p class="text-xs text-gray-500">{{ Auth::user()->email }}</p>
                        </div>
                        <x-dropdown-link :href="route('profile.edit')">
                            Profile
                        </x-dropdown-link>
                        <x-dropdown-link :href="route('redemptions.index')">
                            Redeem Points
                        </x-dropdown-link>
                        <x-dropdown-link :href="route('notification-settings.edit')">
                            Notification Settings
                        </x-dropdown-link>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <x-dropdown-link :href="route('logout')"
                                    onclick="event.preventDefault(); this.closest('form').submit();"
                                    class="!text-red-600 hover:!bg-red-50">
                                Log Out
                            </x-dropdown-link>
                        </form>
                    </x-slot>
                </x-dropdown>
                @else
                {{-- Guest links --}}
                <a href="{{ route('login') }}" class="text-sm text-gray-600 hover:text-gray-900">Log in</a>
                <a href="{{ route('register') }}"
                   class="bg-blue-600 text-white text-sm px-4 py-2 rounded-lg hover:bg-blue-700 transition">
                    Register
                </a>
                @endauth
            </div>

            <!-- Hamburger -->
            <div class="-me-2 flex items-center sm:hidden">
                @auth
                @php $unreadCount = $unreadCount ?? 0; @endphp
                @if($unreadCount > 0)
                <a href="{{ route('notifications.index') }}" class="relative p-2 mr-1 text-gray-400">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                    </svg>
                    <span class="absolute top-0.5 right-0.5 bg-red-500 text-white text-[10px] font-bold rounded-full h-4 w-4 flex items-center justify-center">
                        {{ $unreadCount > 9 ? '9+' : $unreadCount }}
                    </span>
                </a>
                @endif
                @endauth
                <button @click="open = ! open" class="p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 transition">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Responsive Navigation Menu -->
    <div :class="{'block': open, 'hidden': ! open}" class="hidden sm:hidden border-t border-gray-100">
        <div class="pt-2 pb-3 space-y-1">
            @auth
            <x-responsive-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">Dashboard</x-responsive-nav-link>
            <x-responsive-nav-link :href="route('lost-items.index')" :active="request()->routeIs('lost-items.*')">Lost Items</x-responsive-nav-link>
            <x-responsive-nav-link :href="route('found-items.index')" :active="request()->routeIs('found-items.*')">Found Items</x-responsive-nav-link>
            @endauth
            <x-responsive-nav-link :href="route('search.index')" :active="request()->routeIs('search.*')">Search</x-responsive-nav-link>
            @auth
            <x-responsive-nav-link :href="route('claims.index')" :active="request()->routeIs('claims.*')">Claims</x-responsive-nav-link>
            <x-responsive-nav-link :href="route('messages.index')" :active="request()->routeIs('messages.*')">Messages</x-responsive-nav-link>
            <x-responsive-nav-link :href="route('leaderboard.index')" :active="request()->routeIs('leaderboard.*')">Leaderboard</x-responsive-nav-link>
            <x-responsive-nav-link :href="route('redemptions.index')" :active="request()->routeIs('redemptions.*')">Redeem Points</x-responsive-nav-link>
            @if(Auth::user()->isAdmin())
            <x-responsive-nav-link :href="route('admin.dashboard')" :active="request()->routeIs('admin.*')">
                Admin Panel
            </x-responsive-nav-link>
            @endif
            @endauth
        </div>

        @auth
        <div class="pt-3 pb-2 border-t border-gray-200">
            <div class="px-4 flex items-center gap-3">
                <div class="w-9 h-9 rounded-full bg-blue-100 text-blue-700 flex items-center justify-center text-sm font-bold">
                    {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                </div>
                <div>
                    <p class="text-sm font-medium text-gray-800">{{ Auth::user()->name }}</p>
                    <p class="text-xs text-gray-500">{{ Auth::user()->reward_points }} pts</p>
                </div>
            </div>
            <div class="mt-3 space-y-1">
                <x-responsive-nav-link :href="route('profile.edit')">Profile</x-responsive-nav-link>
                <x-responsive-nav-link :href="route('notifications.index')">
                    Notifications @if(($unreadCount ?? 0) > 0) <span class="ml-1 bg-red-500 text-white text-xs rounded-full px-1.5 py-0.5">{{ $unreadCount }}</span> @endif
                </x-responsive-nav-link>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <x-responsive-nav-link :href="route('logout')"
                            onclick="event.preventDefault(); this.closest('form').submit();">
                        Log Out
                    </x-responsive-nav-link>
                </form>
            </div>
        </div>
        @else
        <div class="pt-3 pb-2 border-t border-gray-200">
            <div class="space-y-1">
                <x-responsive-nav-link :href="route('login')">Log in</x-responsive-nav-link>
                <x-responsive-nav-link :href="route('register')">Register</x-responsive-nav-link>
            </div>
        </div>
        @endauth
    </div>
</nav>
