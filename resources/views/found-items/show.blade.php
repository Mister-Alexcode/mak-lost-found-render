<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Found Item — {{ $foundItem->item_name }}
        </h2>
    </x-slot>

    <div class="py-8 max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-6">

        <div class="bg-white rounded-lg shadow p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    @if($foundItem->photo)
                        <img src="{{ asset('storage/' . $foundItem->photo) }}"
                             class="w-full h-56 object-cover rounded" alt="Item photo">
                    @else
                        <div class="w-full h-56 bg-gray-100 rounded flex items-center justify-center text-gray-400">
                            No Photo
                        </div>
                    @endif
                </div>
                <div class="space-y-2">
                    <h3 class="text-2xl font-bold">{{ $foundItem->item_name }}</h3>
                    <p><span class="font-medium">Tracking ID:</span>
                        <span class="font-mono text-green-600">{{ $foundItem->tracking_id }}</span></p>
                    <p><span class="font-medium">Category:</span> {{ $foundItem->category }}</p>
                    <p><span class="font-medium">Color:</span> {{ $foundItem->color }}</p>
                    <p><span class="font-medium">Brand:</span> {{ $foundItem->brand ?? 'N/A' }}</p>
                    <p><span class="font-medium">Location Found:</span> {{ $foundItem->location_found }}</p>
                    <p><span class="font-medium">Date Found:</span> {{ $foundItem->date_found }}</p>
                    <p><span class="font-medium">Status:</span>
                        <span class="px-2 py-1 text-xs rounded
                            {{ $foundItem->status === 'active' ? 'bg-yellow-100 text-yellow-700' : 'bg-green-100 text-green-700' }}">
                            {{ ucfirst($foundItem->status) }}
                        </span>
                    </p>
                    <p><span class="font-medium">Description:</span> {{ $foundItem->description }}</p>
                </div>
            </div>
        </div>

        @if($foundItem->latitude && $foundItem->longitude)
        <div class="bg-white rounded-lg shadow p-6">
            <x-map-picker :latitude="$foundItem->latitude" :longitude="$foundItem->longitude" :readonly="true" />
        </div>
        @endif

        @if($matches->count() > 0)
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-bold mb-4 text-blue-700">
                Potential Lost Item Matches ({{ $matches->count() }})
            </h3>
            @foreach($matches as $match)
            <div class="border rounded p-4 mb-3 flex items-center justify-between">
                <div>
                    <p class="font-medium">{{ $match->lostItem->item_name }}</p>
                    <p class="text-sm text-gray-500">
                        Lost at: {{ $match->lostItem->location_lost }}
                        on {{ $match->lostItem->date_lost }}
                    </p>
                    <p class="text-sm text-gray-500">
                        Owner: {{ $match->lostItem->user->name }}
                    </p>
                    <p class="text-xs font-mono text-blue-600 mt-1">{{ $match->lostItem->tracking_id }}</p>
                </div>
                <div class="text-center">
                    <span class="text-2xl font-bold text-green-600">
                        {{ $match->confidence_score }}%
                    </span>
                    <p class="text-xs text-gray-400">match</p>
                    <span class="inline-block mt-1 px-2 py-1 text-xs rounded bg-gray-100 text-gray-600">
                        {{ ucfirst($match->match_status) }}
                    </span>
                </div>
            </div>
            @endforeach
        </div>
        @else
        <div class="bg-white rounded-lg shadow p-6 text-center text-gray-500">
            <p>No lost item matches found yet.</p>
            <p class="text-sm mt-1">The system will automatically match when a lost report is filed for this item.</p>
        </div>
        @endif

    </div>
</x-app-layout>
