<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>MAK Lost & Found — Makerere University</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
          integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
            integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
</head>
<body class="antialiased bg-gray-50">

    {{-- Navigation --}}
    <nav class="bg-white/90 backdrop-blur-sm shadow-sm sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex justify-between items-center h-16">
            <a href="{{ url('/') }}" class="flex items-center gap-2">
                <img src="{{ asset('logo.svg') }}" alt="MAK Lost & Found" class="w-9 h-9">
                <span class="font-bold text-gray-800">MAK Lost & Found</span>
            </a>
            <div class="flex gap-4 items-center">
                <a href="{{ route('search.index') }}" class="text-sm text-gray-600 hover:text-gray-900 hidden sm:inline">Browse Items</a>
                <a href="{{ route('leaderboard.index') }}" class="text-sm text-gray-600 hover:text-gray-900 hidden sm:inline">Leaderboard</a>
                @auth
                    <a href="{{ route('dashboard') }}"
                       class="bg-green-600 text-white text-sm px-4 py-2 rounded-lg hover:bg-green-700 transition">
                        Dashboard
                    </a>
                @else
                    <a href="{{ route('login') }}" class="text-sm text-gray-600 hover:text-gray-900">Log in</a>
                    <a href="{{ route('register') }}"
                       class="bg-green-600 text-white text-sm px-4 py-2 rounded-lg hover:bg-green-700 transition">
                        Register
                    </a>
                @endauth
            </div>
        </div>
    </nav>

    {{-- Hero --}}
    <section class="relative bg-gradient-to-br from-green-700 via-green-600 to-green-800 text-white py-24 px-4 text-center overflow-hidden">
        <div class="absolute inset-0 opacity-10">
            <svg class="w-full h-full" viewBox="0 0 100 100" preserveAspectRatio="none">
                <circle cx="20" cy="30" r="30" fill="white"/>
                <circle cx="80" cy="70" r="40" fill="white"/>
            </svg>
        </div>
        <div class="relative z-10">
            <div class="inline-flex items-center gap-2 bg-white/10 backdrop-blur-sm rounded-full px-4 py-1.5 text-sm mb-6">
                <span class="w-2 h-2 bg-green-400 rounded-full animate-pulse"></span>
                Serving Makerere University students & staff
            </div>
            <h1 class="text-4xl md:text-5xl lg:text-6xl font-bold mb-4 tracking-tight">
                Lost something on campus?
            </h1>
            <p class="text-green-100 text-lg md:text-xl max-w-2xl mx-auto mb-10">
                Report lost or found items and let our smart matching system reunite you with your belongings.
            </p>
            <div class="flex flex-col sm:flex-row gap-4 justify-center">
                <a href="{{ route('register') }}"
                   class="bg-white text-green-700 font-bold px-8 py-3.5 rounded-lg hover:bg-green-50 transition shadow-lg">
                    Get Started Now
                </a>
                <a href="{{ route('search.index') }}"
                   class="border-2 border-white/40 text-white font-bold px-8 py-3.5 rounded-lg hover:bg-white/10 transition">
                    Browse Items
                </a>
            </div>

            {{-- Quick Stats --}}
            @php
                $totalLost = \App\Models\LostItem::count();
                $totalFound = \App\Models\FoundItem::count();
                $totalReturned = \App\Models\LostItem::where('status', 'returned')->count();
            @endphp
            <div class="flex justify-center gap-8 sm:gap-16 mt-14">
                <div>
                    <p class="text-3xl font-bold">{{ $totalLost }}</p>
                    <p class="text-green-200 text-sm">Lost Reports</p>
                </div>
                <div>
                    <p class="text-3xl font-bold">{{ $totalFound }}</p>
                    <p class="text-green-200 text-sm">Found Reports</p>
                </div>
                <div>
                    <p class="text-3xl font-bold">{{ $totalReturned }}</p>
                    <p class="text-green-200 text-sm">Items Returned</p>
                </div>
            </div>
        </div>
    </section>

    {{-- How it works --}}
    <section class="py-20 px-4 max-w-6xl mx-auto">
        <p class="text-sm font-semibold text-green-600 text-center uppercase tracking-wider mb-2">Simple Process</p>
        <h2 class="text-3xl font-bold text-center text-gray-800 mb-12">How it works</h2>
        <div class="grid grid-cols-1 md:grid-cols-4 gap-8 text-center">
            <div class="relative">
                <div class="w-14 h-14 bg-green-100 rounded-2xl flex items-center justify-center mx-auto mb-4">
                    <svg class="w-7 h-7 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                </div>
                <h3 class="font-bold text-gray-800 mb-2">1. Report</h3>
                <p class="text-sm text-gray-600">
                    Report your lost or found item with details, photo, and pin the location on the map.
                </p>
            </div>
            <div>
                <div class="w-14 h-14 bg-green-100 rounded-2xl flex items-center justify-center mx-auto mb-4">
                    <svg class="w-7 h-7 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/>
                    </svg>
                </div>
                <h3 class="font-bold text-gray-800 mb-2">2. Auto-Match</h3>
                <p class="text-sm text-gray-600">
                    Our algorithm scores items by category, color, location, brand, and date to find matches.
                </p>
            </div>
            <div>
                <div class="w-14 h-14 bg-yellow-100 rounded-2xl flex items-center justify-center mx-auto mb-4">
                    <svg class="w-7 h-7 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                    </svg>
                </div>
                <h3 class="font-bold text-gray-800 mb-2">3. Verify</h3>
                <p class="text-sm text-gray-600">
                    Submit a claim with proof of ownership. An admin verifies before approving returns.
                </p>
            </div>
            <div>
                <div class="w-14 h-14 bg-purple-100 rounded-2xl flex items-center justify-center mx-auto mb-4">
                    <svg class="w-7 h-7 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v13m0-13V6a2 2 0 112 2h-2zm0 0V5.5A2.5 2.5 0 109.5 8H12zm-7 4h14M5 12a2 2 0 110-4h14a2 2 0 110 4M5 12v7a2 2 0 002 2h10a2 2 0 002-2v-7"/>
                    </svg>
                </div>
                <h3 class="font-bold text-gray-800 mb-2">4. Reunite</h3>
                <p class="text-sm text-gray-600">
                    Get your item back and earn reward points. Climb the leaderboard!
                </p>
            </div>
        </div>
    </section>

    {{-- Campus Map Section --}}
    <section class="bg-white py-20 px-4">
        <div class="max-w-6xl mx-auto">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-10 items-center">
                <div>
                    <p class="text-sm font-semibold text-green-600 uppercase tracking-wider mb-2">Campus Map</p>
                    <h2 class="text-3xl font-bold text-gray-800 mb-4">Pin-point where you lost it</h2>
                    <p class="text-gray-600 mb-6">
                        Use our interactive map to mark the exact location where you lost or found an item
                        on Makerere University campus. This helps our matching algorithm find better results
                        and lets others know where to look.
                    </p>
                    <ul class="space-y-3 text-sm text-gray-600">
                        <li class="flex items-center gap-3">
                            <div class="w-2 h-2 bg-red-500 rounded-full"></div>
                            Red markers show lost item locations
                        </li>
                        <li class="flex items-center gap-3">
                            <div class="w-2 h-2 bg-green-500 rounded-full"></div>
                            Green markers show found item locations
                        </li>
                        <li class="flex items-center gap-3">
                            <div class="w-2 h-2 bg-green-500 rounded-full"></div>
                            Click anywhere on the map to pin a location when reporting
                        </li>
                    </ul>
                </div>
                <div>
                    <div id="welcome-map" class="w-full h-80 rounded-xl shadow-lg border border-gray-200"></div>
                </div>
            </div>
        </div>
    </section>

    {{-- Features --}}
    <section class="bg-gray-50 py-20 px-4">
        <div class="max-w-6xl mx-auto">
            <p class="text-sm font-semibold text-green-600 text-center uppercase tracking-wider mb-2">Features</p>
            <h2 class="text-3xl font-bold text-center text-gray-800 mb-12">Why use MAK Lost & Found?</h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="bg-white rounded-xl p-6 shadow-sm hover:shadow-md transition-shadow">
                    <div class="w-10 h-10 bg-green-100 rounded-lg flex items-center justify-center mb-4">
                        <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                    </div>
                    <h3 class="font-bold text-gray-800 mb-2">Smart Matching</h3>
                    <p class="text-sm text-gray-600">
                        Items matched using a confidence score based on category, color, brand, location, and date.
                    </p>
                </div>
                <div class="bg-white rounded-xl p-6 shadow-sm hover:shadow-md transition-shadow">
                    <div class="w-10 h-10 bg-green-100 rounded-lg flex items-center justify-center mb-4">
                        <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                    </div>
                    <h3 class="font-bold text-gray-800 mb-2">Interactive Map</h3>
                    <p class="text-sm text-gray-600">
                        Pin exact locations on the Makerere campus map. Visualise lost and found hotspots.
                    </p>
                </div>
                <div class="bg-white rounded-xl p-6 shadow-sm hover:shadow-md transition-shadow">
                    <div class="w-10 h-10 bg-orange-100 rounded-lg flex items-center justify-center mb-4">
                        <svg class="w-5 h-5 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v13m0-13V6a2 2 0 112 2h-2zm0 0V5.5A2.5 2.5 0 109.5 8H12zm-7 4h14M5 12a2 2 0 110-4h14a2 2 0 110 4M5 12v7a2 2 0 002 2h10a2 2 0 002-2v-7"/>
                        </svg>
                    </div>
                    <h3 class="font-bold text-gray-800 mb-2">Reward Points</h3>
                    <p class="text-sm text-gray-600">
                        Earn points for reporting found items (+10) and successful returns (+20). Redeem for prizes.
                    </p>
                </div>
                <div class="bg-white rounded-xl p-6 shadow-sm hover:shadow-md transition-shadow">
                    <div class="w-10 h-10 bg-purple-100 rounded-lg flex items-center justify-center mb-4">
                        <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                        </svg>
                    </div>
                    <h3 class="font-bold text-gray-800 mb-2">Verified Returns</h3>
                    <p class="text-sm text-gray-600">
                        Claims verified by admin before approval — items go back to rightful owners only.
                    </p>
                </div>
                <div class="bg-white rounded-xl p-6 shadow-sm hover:shadow-md transition-shadow">
                    <div class="w-10 h-10 bg-yellow-100 rounded-lg flex items-center justify-center mb-4">
                        <svg class="w-5 h-5 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                        </svg>
                    </div>
                    <h3 class="font-bold text-gray-800 mb-2">In-App Messaging</h3>
                    <p class="text-sm text-gray-600">
                        Communicate securely with finders/owners without exposing personal contact details.
                    </p>
                </div>
                <div class="bg-white rounded-xl p-6 shadow-sm hover:shadow-md transition-shadow">
                    <div class="w-10 h-10 bg-red-100 rounded-lg flex items-center justify-center mb-4">
                        <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                        </svg>
                    </div>
                    <h3 class="font-bold text-gray-800 mb-2">Instant Notifications</h3>
                    <p class="text-sm text-gray-600">
                        Get notified the moment a potential match is found for your lost item.
                    </p>
                </div>
            </div>
        </div>
    </section>

    {{-- CTA --}}
    <section class="py-20 px-4 text-center bg-white">
        <div class="max-w-2xl mx-auto">
            <h2 class="text-3xl font-bold text-gray-800 mb-4">Ready to find your item?</h2>
            <p class="text-gray-600 mb-8">Join the Makerere University Lost & Found community today.</p>
            <a href="{{ route('register') }}"
               class="inline-block bg-green-600 text-white font-bold px-10 py-3.5 rounded-lg hover:bg-green-700 transition shadow-lg">
                Create an Account
            </a>
        </div>
    </section>

    {{-- Footer --}}
    <footer class="bg-gray-900 text-gray-400 text-sm py-8 px-4">
        <div class="max-w-6xl mx-auto flex flex-col sm:flex-row justify-between items-center gap-4">
            <div class="flex items-center gap-2">
                <div class="w-6 h-6 bg-green-600 rounded flex items-center justify-center">
                    <svg class="w-3.5 h-3.5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                </div>
                <span>MAK Lost & Found</span>
            </div>
            <div class="text-center sm:text-right">
                <p>Makerere University — IST 3201 Final Year Project</p>
                <p class="text-gray-500 mt-0.5">Group 46 — Supervised by Dr. Nasser Kimbugwe</p>
            </div>
        </div>
    </footer>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const map = L.map('welcome-map').setView([0.3348, 32.5699], 15);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; OpenStreetMap contributors',
            maxZoom: 19,
        }).addTo(map);

        // Show recent items with coordinates
        @php
            $mapItems = collect()
                ->merge(\App\Models\LostItem::whereNotNull('latitude')->where('status', 'active')->latest()->take(20)->get()->map(fn($i) => ['type'=>'lost','name'=>$i->item_name,'lat'=>$i->latitude,'lng'=>$i->longitude,'loc'=>$i->location_lost]))
                ->merge(\App\Models\FoundItem::whereNotNull('latitude')->where('status', 'active')->latest()->take(20)->get()->map(fn($i) => ['type'=>'found','name'=>$i->item_name,'lat'=>$i->latitude,'lng'=>$i->longitude,'loc'=>$i->location_found]));
        @endphp

        const items = @json($mapItems->values());

        const lostIcon = L.divIcon({
            className: '',
            html: '<div style="background:#dc2626;width:12px;height:12px;border-radius:50%;border:2px solid #fff;box-shadow:0 1px 4px rgba(0,0,0,.3)"></div>',
            iconSize: [12,12], iconAnchor: [6,6],
        });
        const foundIcon = L.divIcon({
            className: '',
            html: '<div style="background:#16a34a;width:12px;height:12px;border-radius:50%;border:2px solid #fff;box-shadow:0 1px 4px rgba(0,0,0,.3)"></div>',
            iconSize: [12,12], iconAnchor: [6,6],
        });

        items.forEach(function(item) {
            L.marker([item.lat, item.lng], { icon: item.type === 'lost' ? lostIcon : foundIcon })
                .addTo(map)
                .bindPopup('<strong>' + item.name + '</strong><br><span style="color:#888">' + item.loc + '</span>');
        });
    });
    </script>

</body>
</html>
