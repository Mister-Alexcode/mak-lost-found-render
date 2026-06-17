<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Search Lost & Found Items</h2>
    </x-slot>

    <div class="py-8 max-w-7xl mx-auto sm:px-6 lg:px-8">

        {{-- Search Form --}}
        <div class="bg-white rounded-lg shadow p-5 mb-6">
            <form method="GET" action="{{ route('search.index') }}" class="space-y-3">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-3">
                    <div class="md:col-span-2">
                        <input type="text" name="q" value="{{ $query }}"
                               class="w-full border rounded px-3 py-2 focus:ring-2 focus:ring-green-500"
                               placeholder="Search by item name, description, brand...">
                    </div>
                    <div>
                        <select name="category" class="w-full border rounded px-3 py-2 focus:ring-2 focus:ring-green-500">
                            <option value="">All Categories</option>
                            @foreach($categories as $cat)
                            <option value="{{ $cat }}" {{ $category == $cat ? 'selected' : '' }}>{{ $cat }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <input type="text" name="location" value="{{ $location }}"
                               class="w-full border rounded px-3 py-2 focus:ring-2 focus:ring-green-500"
                               placeholder="Location...">
                    </div>
                </div>
                <div class="flex items-center gap-4">
                    <div class="flex gap-1">
                        <label class="cursor-pointer px-3 py-1.5 rounded-lg text-sm font-medium transition
                            {{ $type === 'lost' ? 'bg-green-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
                            <input type="radio" name="type" value="lost" class="hidden" onchange="this.form.submit()"
                                {{ $type === 'lost' ? 'checked' : '' }}> Lost Items
                        </label>
                        <label class="cursor-pointer px-3 py-1.5 rounded-lg text-sm font-medium transition
                            {{ $type === 'found' ? 'bg-green-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
                            <input type="radio" name="type" value="found" class="hidden" onchange="this.form.submit()"
                                {{ $type === 'found' ? 'checked' : '' }}> Found Items
                        </label>
                        <label class="cursor-pointer px-3 py-1.5 rounded-lg text-sm font-medium transition
                            {{ $type === 'both' ? 'bg-purple-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
                            <input type="radio" name="type" value="both" class="hidden" onchange="this.form.submit()"
                                {{ $type === 'both' ? 'checked' : '' }}> Both
                        </label>
                    </div>
                    <button type="submit" class="bg-green-600 text-white px-5 py-2 rounded hover:bg-green-700 text-sm">
                        Search
                    </button>
                    <a href="{{ route('search.index') }}" class="text-sm text-gray-500 hover:underline">Clear</a>
                </div>
            </form>
        </div>

        {{-- View Toggle --}}
        <div class="flex gap-2 mb-4" x-data="{ view: 'list' }">
            <button @click="view = 'list'"
                    :class="view === 'list' ? 'bg-green-600 text-white' : 'bg-white text-gray-700'"
                    class="px-4 py-2 rounded shadow text-sm font-medium transition">
                List View
            </button>
            <button @click="view = 'map'; $nextTick(() => { if(window._searchMap) window._searchMap.invalidateSize(); else window.initSearchMap(); })"
                    :class="view === 'map' ? 'bg-green-600 text-white' : 'bg-white text-gray-700'"
                    class="px-4 py-2 rounded shadow text-sm font-medium transition">
                Map View
            </button>

            {{-- Map View --}}
            <div x-show="view === 'map'" x-cloak class="absolute left-0 right-0 px-4 sm:px-6 lg:px-8" style="margin-top: 2.5rem;">
            </div>
        </div>

        {{-- Map Container --}}
        <div id="search-map-wrapper" class="mb-6 hidden">
            <div id="search-map" class="w-full h-96 rounded-lg shadow border border-gray-200 z-0"></div>
        </div>

        @push('scripts')
        <script>
        window.initSearchMap = function() {
            const wrapper = document.getElementById('search-map-wrapper');
            wrapper.classList.remove('hidden');

            if (window._searchMap) {
                window._searchMap.invalidateSize();
                return;
            }

            const map = L.map('search-map').setView([0.3348, 32.5699], 15);
            window._searchMap = map;

            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; OpenStreetMap contributors',
                maxZoom: 19,
            }).addTo(map);

            const lostIcon = L.divIcon({
                className: 'custom-marker',
                html: '<div style="background:#dc2626;width:14px;height:14px;border-radius:50%;border:2px solid #fff;box-shadow:0 1px 4px rgba(0,0,0,.3)"></div>',
                iconSize: [14, 14],
                iconAnchor: [7, 7],
            });

            const foundIcon = L.divIcon({
                className: 'custom-marker',
                html: '<div style="background:#16a34a;width:14px;height:14px;border-radius:50%;border:2px solid #fff;box-shadow:0 1px 4px rgba(0,0,0,.3)"></div>',
                iconSize: [14, 14],
                iconAnchor: [7, 7],
            });

            @php
                $mapData = collect();
                if ($type !== 'found' && $lostItems instanceof \Illuminate\Pagination\LengthAwarePaginator) {
                    foreach ($lostItems->getCollection() as $i) {
                        if ($i->latitude && $i->longitude) {
                            $mapData->push(['type' => 'lost', 'name' => $i->item_name, 'lat' => $i->latitude, 'lng' => $i->longitude, 'location' => $i->location_lost, 'tracking_id' => $i->tracking_id, 'url' => route('lost-items.show', $i->id)]);
                        }
                    }
                }
                if ($type !== 'lost' && $foundItems instanceof \Illuminate\Pagination\LengthAwarePaginator) {
                    foreach ($foundItems->getCollection() as $i) {
                        if ($i->latitude && $i->longitude) {
                            $mapData->push(['type' => 'found', 'name' => $i->item_name, 'lat' => $i->latitude, 'lng' => $i->longitude, 'location' => $i->location_found, 'tracking_id' => $i->tracking_id, 'url' => route('found-items.show', $i->id)]);
                        }
                    }
                }
            @endphp
            const items = @json($mapData->values());

            const bounds = [];
            items.forEach(function(item) {
                const icon = item.type === 'lost' ? lostIcon : foundIcon;
                const label = item.type === 'lost' ? 'Lost' : 'Found';
                const color = item.type === 'lost' ? '#dc2626' : '#16a34a';
                const marker = L.marker([item.lat, item.lng], { icon: icon }).addTo(map);
                marker.bindPopup(
                    '<div class="text-sm">' +
                    '<span style="color:' + color + ';font-weight:700">' + label + '</span> ' +
                    '<strong>' + item.name + '</strong><br>' +
                    '<span class="text-gray-500">' + item.location + '</span><br>' +
                    '<code class="text-xs">' + item.tracking_id + '</code><br>' +
                    '<a href="' + item.url + '" class="text-green-600 underline text-xs">View Details</a>' +
                    '</div>'
                );
                bounds.push([item.lat, item.lng]);
            });

            if (bounds.length > 0) {
                map.fitBounds(bounds, { padding: [30, 30] });
            }

            // Legend
            const legend = L.control({ position: 'bottomright' });
            legend.onAdd = function() {
                const div = L.DomUtil.create('div', 'bg-white px-3 py-2 rounded shadow text-xs');
                div.innerHTML =
                    '<div class="flex items-center gap-1 mb-1"><div style="background:#dc2626;width:10px;height:10px;border-radius:50%"></div> Lost Items</div>' +
                    '<div class="flex items-center gap-1"><div style="background:#16a34a;width:10px;height:10px;border-radius:50%"></div> Found Items</div>';
                return div;
            };
            legend.addTo(map);
        };

        // Handle Alpine toggle
        document.addEventListener('alpine:init', () => {});
        document.addEventListener('click', function(e) {
            const btn = e.target.closest('button');
            if (!btn) return;
            const wrapper = document.getElementById('search-map-wrapper');
            if (btn.textContent.trim() === 'Map View') {
                wrapper.classList.remove('hidden');
                setTimeout(() => { if (window._searchMap) window._searchMap.invalidateSize(); else window.initSearchMap(); }, 50);
            } else if (btn.textContent.trim() === 'List View') {
                wrapper.classList.add('hidden');
            }
        });
        </script>
        @endpush

        {{-- Lost Items --}}
        @if($type !== 'found' && $lostItems->count() > 0)
        <div class="mb-8">
            <h3 class="text-lg font-bold mb-3 text-green-700">Lost Items ({{ $lostItems->total() }})</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
                @foreach($lostItems as $item)
                <div class="bg-white rounded-lg shadow p-4">
                    @if($item->photo)
                        <img src="{{ $item->photo_url }}"
                             class="w-full h-36 object-cover rounded mb-3" alt="">
                    @else
                        <div class="w-full h-36 bg-gray-100 rounded mb-3 flex items-center justify-center text-gray-300 text-sm">
                            No Photo
                        </div>
                    @endif
                    <h4 class="font-bold">{{ $item->item_name }}</h4>
                    <p class="text-xs text-gray-500">{{ $item->category }} · {{ $item->color }}</p>
                    <p class="text-xs text-gray-500">Lost: {{ $item->location_lost }}</p>
                    <p class="text-xs text-gray-500">Date: {{ $item->date_lost }}</p>
                    <p class="text-xs font-mono text-green-600 mt-1">{{ $item->tracking_id }}</p>
                    @auth
                    <a href="{{ route('lost-items.show', $item) }}"
                       class="mt-2 inline-block text-xs bg-green-50 text-green-600 px-3 py-1 rounded hover:bg-green-100">
                        View Details
                    </a>
                    @endauth
                </div>
                @endforeach
            </div>
            <div class="mt-4">{{ $lostItems->appends(request()->query())->links() }}</div>
        </div>
        @endif

        {{-- Found Items --}}
        @if($type !== 'lost' && $foundItems->count() > 0)
        <div>
            <h3 class="text-lg font-bold mb-3 text-green-700">Found Items ({{ $foundItems->total() }})</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
                @foreach($foundItems as $item)
                <div class="bg-white rounded-lg shadow p-4">
                    @if($item->photo)
                        <img src="{{ $item->photo_url }}"
                             class="w-full h-36 object-cover rounded mb-3" alt="">
                    @else
                        <div class="w-full h-36 bg-gray-100 rounded mb-3 flex items-center justify-center text-gray-300 text-sm">
                            No Photo
                        </div>
                    @endif
                    <h4 class="font-bold">{{ $item->item_name }}</h4>
                    <p class="text-xs text-gray-500">{{ $item->category }} · {{ $item->color }}</p>
                    <p class="text-xs text-gray-500">Found: {{ $item->location_found }}</p>
                    <p class="text-xs text-gray-500">Date: {{ $item->date_found }}</p>
                    <p class="text-xs font-mono text-green-600 mt-1">{{ $item->tracking_id }}</p>
                    @auth
                    <a href="{{ route('found-items.show', $item) }}"
                       class="mt-2 inline-block text-xs bg-green-50 text-green-600 px-3 py-1 rounded hover:bg-green-100">
                        View Details
                    </a>
                    @endauth
                </div>
                @endforeach
            </div>
            <div class="mt-4">{{ $foundItems->appends(request()->query())->links() }}</div>
        </div>
        @endif

        @php
            $hasLost = ($type === 'lost' || $type === 'both') && $lostItems instanceof \Illuminate\Pagination\LengthAwarePaginator && $lostItems->count() > 0;
            $hasFound = ($type === 'found' || $type === 'both') && $foundItems instanceof \Illuminate\Pagination\LengthAwarePaginator && $foundItems->count() > 0;
        @endphp

        @if(!$hasLost && !$hasFound)
        <div class="bg-white rounded-lg shadow p-8 text-center text-gray-500">
            <p class="text-lg">No items found matching your search.</p>
            <p class="text-sm mt-1">Try a different keyword, category, or location.</p>
        </div>
        @endif

    </div>
</x-app-layout>
