<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Lost Item — {{ $lostItem->item_name }}
        </h2>
    </x-slot>

    <div class="py-8 max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-6">

        <div class="bg-white rounded-lg shadow p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    @if($lostItem->photo)
                        <img src="{{ asset('storage/' . $lostItem->photo) }}"
                             class="w-full h-56 object-cover rounded" alt="Item photo">
                    @else
                        <div class="w-full h-56 bg-gray-100 rounded flex items-center justify-center text-gray-400">
                            No Photo
                        </div>
                    @endif
                </div>
                <div class="space-y-2">
                    <h3 class="text-2xl font-bold">{{ $lostItem->item_name }}</h3>
                    <p><span class="font-medium">Tracking ID:</span>
                        <span class="font-mono text-blue-600">{{ $lostItem->tracking_id }}</span></p>
                    <p><span class="font-medium">Category:</span> {{ $lostItem->category }}</p>
                    <p><span class="font-medium">Color:</span> {{ $lostItem->color }}</p>
                    <p><span class="font-medium">Brand:</span> {{ $lostItem->brand ?? 'N/A' }}</p>
                    <p><span class="font-medium">Location Lost:</span> {{ $lostItem->location_lost }}</p>
                    <p><span class="font-medium">Date Lost:</span> {{ $lostItem->date_lost }}</p>
                    <p><span class="font-medium">Status:</span>
                        <span class="px-2 py-1 text-xs rounded
                            {{ $lostItem->status === 'active' ? 'bg-yellow-100 text-yellow-700' : 'bg-green-100 text-green-700' }}">
                            {{ ucfirst($lostItem->status) }}
                        </span>
                    </p>
                    <p><span class="font-medium">Description:</span> {{ $lostItem->description }}</p>
                </div>
            </div>
        </div>

        @if($matches->count() > 0)
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-bold mb-4 text-green-700">
                Potential Matches Found ({{ $matches->count() }})
            </h3>
            @foreach($matches as $match)
            <div class="border rounded p-4 mb-3 flex items-center justify-between">
                <div>
                    <p class="font-medium">{{ $match->foundItem->item_name }}</p>
                    <p class="text-sm text-gray-500">
                        Found at: {{ $match->foundItem->location_found }}
                        on {{ $match->foundItem->date_found }}
                    </p>
                    <p class="text-sm text-gray-500">
                        Reported by: {{ $match->foundItem->user->name }}
                    </p>
                </div>
                <div class="text-center">
                    <span class="text-2xl font-bold text-green-600">
                        {{ $match->confidence_score }}%
                    </span>
                    <p class="text-xs text-gray-400">match</p>
                    @if($match->claim_status !== 'approved')
                    <a href="{{ route('claims.create', $match->id) }}"
                       class="mt-2 inline-block bg-green-600 text-white px-3 py-1 rounded text-sm hover:bg-green-700">
                        Claim Item
                    </a>
                    @endif
                </div>
            </div>
            @endforeach
        </div>
        @else
        <div class="bg-white rounded-lg shadow p-6 text-center text-gray-500">
            <p>No matches found yet. We will notify you when a match is found.</p>
        </div>
        @endif

    </div>
</x-app-layout>