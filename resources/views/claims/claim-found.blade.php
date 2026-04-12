<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Claim Found Item</h2>
    </x-slot>

    <div class="py-8 max-w-3xl mx-auto sm:px-6 lg:px-8">

        @if($existingClaim)
        <div class="bg-yellow-50 border border-yellow-300 text-yellow-800 px-4 py-3 rounded mb-4">
            You already have a {{ $existingClaim->claim_status }} claim for this item.
            <a href="{{ route('claims.show', $existingClaim) }}" class="underline font-medium">View it here.</a>
        </div>
        @endif

        <div class="bg-white rounded-lg shadow p-6">

            {{-- Found item summary --}}
            <div class="p-4 bg-green-50 border border-green-200 rounded-lg mb-6">
                <p class="text-xs text-green-600 uppercase font-medium mb-2">Found Item You're Claiming</p>
                <div class="flex gap-4">
                    @if($foundItem->photo)
                        <img src="{{ asset('storage/' . $foundItem->photo) }}"
                             class="w-20 h-20 object-cover rounded" alt="">
                    @else
                        <div class="w-20 h-20 bg-gray-100 rounded flex items-center justify-center text-gray-400 text-xs">No Photo</div>
                    @endif
                    <div>
                        <p class="font-bold text-lg">{{ $foundItem->item_name }}</p>
                        <p class="text-sm text-gray-600">{{ $foundItem->category }} · {{ $foundItem->color }}</p>
                        <p class="text-sm text-gray-600">Found at: {{ $foundItem->location_found }}</p>
                        <p class="text-sm text-gray-600">Date: {{ $foundItem->date_found }}</p>
                        <p class="text-xs font-mono text-green-600">{{ $foundItem->tracking_id }}</p>
                    </div>
                </div>
                @if($foundItem->is_high_value)
                <div class="mt-3 flex items-center gap-1.5 text-amber-700 bg-amber-50 border border-amber-200 px-3 py-2 rounded text-xs font-medium">
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                    High-Value Item — Admin verification required before handover
                </div>
                @endif
            </div>

            @if($errors->any())
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                    <ul class="list-disc list-inside">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('claims.claim-found.store', $foundItem) }}">
                @csrf

                {{-- Link to existing lost item report --}}
                @if($myLostItems->count() > 0)
                <div class="mb-5">
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Link to Your Lost Item Report <span class="text-gray-400 font-normal">(optional)</span>
                    </label>
                    <p class="text-xs text-gray-500 mb-2">
                        If you already reported this item as lost, select it below. This strengthens your claim.
                    </p>
                    <select name="lost_item_id" class="w-full border rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">-- No existing report (one will be created for you) --</option>
                        @foreach($myLostItems as $lostItem)
                            <option value="{{ $lostItem->id }}" {{ old('lost_item_id') == $lostItem->id ? 'selected' : '' }}>
                                {{ $lostItem->item_name }} — {{ $lostItem->category }} · {{ $lostItem->location_lost }} ({{ $lostItem->tracking_id }})
                            </option>
                        @endforeach
                    </select>
                </div>
                @endif

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Verification Details *
                    </label>
                    <p class="text-xs text-gray-500 mb-2">
                        Prove this item is yours. Include details only the true owner would know:
                        serial number, unique markings, contents, purchase date, etc. (minimum 20 characters)
                    </p>
                    <textarea name="verification_details" rows="5"
                              class="w-full border rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                              placeholder="e.g. My laptop has a MAK sticker on the back, a scratch on the bottom left corner, serial number ABC123. I lost it in the library on Monday...">{{ old('verification_details') }}</textarea>
                </div>

                <div class="mt-6 flex gap-3">
                    <button type="submit"
                            class="bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-700">
                        Submit Claim
                    </button>
                    <a href="{{ route('found-items.show', $foundItem) }}"
                       class="bg-gray-200 text-gray-700 px-6 py-2 rounded hover:bg-gray-300">
                        Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
