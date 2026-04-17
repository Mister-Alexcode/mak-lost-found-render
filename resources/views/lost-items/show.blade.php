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
                        <span class="font-mono text-green-600">{{ $lostItem->tracking_id }}</span></p>
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
                    @if($lostItem->is_high_value)
                    <p class="mt-2">
                        <span class="inline-flex items-center gap-1 px-2.5 py-1 bg-amber-100 text-amber-800 text-xs font-medium rounded-full">
                            <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                            High-Value Item — Admin verification required
                        </span>
                    </p>
                    @endif
                    @if($lostItem->reward_offer)
                    <p class="mt-2 text-green-700 font-medium bg-green-50 px-3 py-2 rounded">
                        Reward offered: {{ $lostItem->reward_offer }}
                    </p>
                    @endif
                </div>
            </div>
        </div>

        @if($lostItem->latitude && $lostItem->longitude)
        <div class="bg-white rounded-lg shadow p-6">
            <x-map-picker :latitude="$lostItem->latitude" :longitude="$lostItem->longitude" :readonly="true" />
        </div>
        @endif

        {{-- Report Found Button (only for other users, only if item is still active, and no pending claim) --}}
        @php
            $hasPendingClaim = $matches->flatMap->claims
                ->where('claimant_id', Auth::id())
                ->whereIn('claim_status', ['pending', 'under_review'])
                ->isNotEmpty();
        @endphp
        @if(Auth::id() !== $lostItem->user_id && $lostItem->status === 'active' && !$hasPendingClaim)
        <div class="bg-green-50 border border-green-200 rounded-lg shadow p-6 flex items-center justify-between">
            <div>
                <h3 class="font-bold text-green-800">Did you find this item?</h3>
                <p class="text-sm text-green-600 mt-1">Report it as found and help return it to its owner. You'll earn <strong>10 reward points</strong>!</p>
            </div>
            <a href="{{ route('found-items.create', ['from_lost' => $lostItem->id]) }}"
               class="shrink-0 bg-green-600 text-white px-5 py-2.5 rounded-lg hover:bg-green-700 transition font-medium text-sm">
                I Found This Item
            </a>
        </div>
        @elseif(Auth::id() !== $lostItem->user_id && $hasPendingClaim)
        <div class="bg-yellow-50 border border-yellow-200 rounded-lg shadow p-4">
            <p class="text-sm text-yellow-800 font-medium">You have a pending claim for this item. An admin will review it shortly.</p>
        </div>
        @endif

        @if($matches->count() > 0)
        <div class="space-y-4">
            <h3 class="text-lg font-bold text-green-700">
                Potential Matches Found ({{ $matches->count() }})
            </h3>

            @foreach($matches as $match)
            <div class="bg-white rounded-lg shadow overflow-hidden border-2 {{ $match->confidence_score >= 90 ? 'border-green-400' : 'border-gray-200' }}">
                <div class="flex items-center justify-between px-5 py-3 {{ $match->confidence_score >= 90 ? 'bg-green-50' : 'bg-gray-50' }}">
                    <div class="flex items-center gap-3">
                        <span class="text-2xl font-bold {{ $match->confidence_score >= 90 ? 'text-green-600' : 'text-yellow-600' }}">
                            {{ $match->confidence_score }}%
                        </span>
                        <span class="text-sm text-gray-500">confidence match</span>
                    </div>
                    <span class="text-xs px-2 py-1 rounded-full
                        @if($match->match_status === 'confirmed') bg-green-100 text-green-700
                        @elseif($match->claims->where('claim_status', 'approved')->count()) bg-green-100 text-green-700
                        @else bg-yellow-100 text-yellow-700
                        @endif">
                        {{ $match->match_status === 'confirmed' || $match->claims->where('claim_status', 'approved')->count() ? 'Confirmed' : 'Pending Verification' }}
                    </span>
                </div>

                <div class="p-5">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        {{-- Finder's photo --}}
                        <div>
                            @if($match->foundItem->photo)
                                <img src="{{ asset('storage/' . $match->foundItem->photo) }}"
                                     class="w-full h-48 object-cover rounded-lg" alt="Found item photo">
                            @else
                                <div class="w-full h-48 bg-gray-100 rounded-lg flex items-center justify-center text-gray-400">
                                    No Photo
                                </div>
                            @endif
                        </div>

                        {{-- Finder's details --}}
                        <div class="space-y-2">
                            <h4 class="font-bold text-gray-800">{{ $match->foundItem->item_name }}</h4>

                            <div class="flex items-center gap-2 text-sm">
                                <span class="font-medium text-gray-600">Found by:</span>
                                <div class="flex items-center gap-1.5">
                                    <div class="w-5 h-5 rounded-full bg-green-100 text-green-700 flex items-center justify-center text-[10px] font-bold">
                                        {{ strtoupper(substr($match->foundItem->user->name, 0, 1)) }}
                                    </div>
                                    <span class="text-gray-800">{{ $match->foundItem->user->name }}</span>
                                </div>
                            </div>

                            <p class="text-sm">
                                <span class="font-medium text-gray-600">Location Found:</span>
                                <span class="text-gray-800">{{ $match->foundItem->location_found }}</span>
                            </p>
                            <p class="text-sm">
                                <span class="font-medium text-gray-600">Date Found:</span>
                                <span class="text-gray-800">{{ $match->foundItem->date_found }}</span>
                            </p>
                            @if($match->foundItem->color)
                            <p class="text-sm">
                                <span class="font-medium text-gray-600">Color:</span>
                                <span class="text-gray-800">{{ $match->foundItem->color }}</span>
                            </p>
                            @endif
                            @if($match->foundItem->brand)
                            <p class="text-sm">
                                <span class="font-medium text-gray-600">Brand:</span>
                                <span class="text-gray-800">{{ $match->foundItem->brand }}</span>
                            </p>
                            @endif
                        </div>
                    </div>

                    {{-- Finder's description --}}
                    @if($match->foundItem->description)
                    <div class="mt-4 bg-gray-50 rounded-lg p-3">
                        <p class="text-xs font-medium text-gray-500 uppercase mb-1">Finder's Description</p>
                        <p class="text-sm text-gray-700">{{ $match->foundItem->description }}</p>
                    </div>
                    @endif

                    {{-- Action buttons --}}
                    @php
                        $isHighValue = $lostItem->is_high_value || $match->foundItem->is_high_value;
                        $isResolved = $match->match_status === 'confirmed' || $match->claims->where('claim_status', 'approved')->count();
                    @endphp

                    <div class="mt-4 flex flex-wrap gap-2">
                        <a href="{{ route('messages.show', $match->id) }}"
                           class="inline-flex items-center gap-1.5 bg-green-600 text-white px-4 py-2 rounded-lg text-sm hover:bg-green-700 transition">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                            </svg>
                            Chat with Finder
                        </a>

                        @if(!$isResolved)
                            @if($isHighValue)
                            {{-- High-value: must go through admin claim process --}}
                            <a href="{{ route('claims.create', $match->id) }}"
                               class="inline-flex items-center gap-1.5 bg-amber-600 text-white px-4 py-2 rounded-lg text-sm hover:bg-amber-700 transition">
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                                Claim via Admin
                            </a>
                            <p class="w-full text-xs text-amber-600 mt-1">
                                This is a high-value item. The finder must bring it to the admin office for verified handover.
                            </p>
                            @else
                            {{-- Normal item: peer-to-peer confirm return --}}
                            @if(Auth::id() === $lostItem->user_id)
                            <form method="POST" action="{{ route('claims.confirm-return', $match->id) }}"
                                  onsubmit="return confirm('Confirm that this item has been returned to you?')">
                                @csrf
                                <button type="submit"
                                        class="inline-flex items-center gap-1.5 bg-green-600 text-white px-4 py-2 rounded-lg text-sm hover:bg-green-700 transition">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    Confirm Returned to Me
                                </button>
                            </form>
                            @endif
                            @endif
                        @endif

                        <a href="{{ route('found-items.show', $match->foundItem->id) }}"
                           class="inline-flex items-center gap-1.5 bg-gray-100 text-gray-700 px-4 py-2 rounded-lg text-sm hover:bg-gray-200 transition">
                            View Full Details
                        </a>
                    </div>
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