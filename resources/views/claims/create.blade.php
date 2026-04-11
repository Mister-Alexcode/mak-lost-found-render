<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Submit a Claim</h2>
    </x-slot>

    <div class="py-8 max-w-3xl mx-auto sm:px-6 lg:px-8">

        @if($existing)
        <div class="bg-yellow-50 border border-yellow-300 text-yellow-800 px-4 py-3 rounded mb-4">
            You already have a {{ $existing->claim_status }} claim for this match.
            <a href="{{ route('claims.show', $existing) }}" class="underline">View it here.</a>
        </div>
        @endif

        <div class="bg-white rounded-lg shadow p-6">

            <div class="grid grid-cols-2 gap-4 mb-6 p-4 bg-gray-50 rounded">
                <div>
                    <p class="text-xs text-gray-500 uppercase font-medium">Your Lost Item</p>
                    <p class="font-bold">{{ $match->lostItem->item_name }}</p>
                    <p class="text-sm text-gray-600">{{ $match->lostItem->location_lost }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-500 uppercase font-medium">Found Item</p>
                    <p class="font-bold">{{ $match->foundItem->item_name }}</p>
                    <p class="text-sm text-gray-600">{{ $match->foundItem->location_found }}</p>
                </div>
                <div class="col-span-2 text-center">
                    <span class="text-2xl font-bold text-green-600">{{ $match->confidence_score }}%</span>
                    <span class="text-sm text-gray-500 ml-1">match confidence</span>
                </div>
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

            <form method="POST" action="{{ route('claims.store') }}">
                @csrf
                <input type="hidden" name="match_id" value="{{ $match->id }}">

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Verification Details *
                    </label>
                    <p class="text-xs text-gray-500 mb-2">
                        Provide proof that this is your item. Include specific details that only the true owner would know:
                        serial number, unique markings, what was stored inside, purchase date, etc. (minimum 20 characters)
                    </p>
                    <textarea name="verification_details" rows="5"
                              class="w-full border rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                              placeholder="e.g. My laptop has a MAK sticker on the back, a scratch on the bottom left corner, and the serial number is ABC123. I last used it in the library on Monday...">{{ old('verification_details') }}</textarea>
                </div>

                <div class="mt-6 flex gap-3">
                    <button type="submit"
                            class="bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-700">
                        Submit Claim
                    </button>
                    <a href="{{ route('lost-items.show', $match->lostItem) }}"
                       class="bg-gray-200 text-gray-700 px-6 py-2 rounded hover:bg-gray-300">
                        Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
