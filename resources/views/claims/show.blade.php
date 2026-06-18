<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Claim Details</h2>
    </x-slot>

    <div class="py-8 max-w-3xl mx-auto sm:px-6 lg:px-8 space-y-6">

        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-bold">Claim Status</h3>
                <span class="px-3 py-1 rounded-full text-sm font-medium
                    @if($claim->claim_status === 'approved') bg-green-100 text-green-700
                    @elseif($claim->claim_status === 'rejected') bg-red-100 text-red-700
                    @elseif($claim->claim_status === 'under_review') bg-green-100 text-green-700
                    @else bg-yellow-100 text-yellow-700 @endif">
                    {{ ucfirst(str_replace('_', ' ', $claim->claim_status)) }}
                </span>
            </div>

            <div class="grid grid-cols-2 gap-4 p-4 bg-gray-50 rounded mb-4">
                <div>
                    <p class="text-xs text-gray-500 uppercase font-medium">Lost Item</p>
                    <p class="font-bold">{{ $claim->match->lostItem->item_name }}</p>
                    <p class="text-sm text-gray-600">{{ $claim->match->lostItem->location_lost }}</p>
                    <p class="text-xs font-mono text-green-600">{{ $claim->match->lostItem->tracking_id }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-500 uppercase font-medium">Found Item</p>
                    <p class="font-bold">{{ $claim->match->foundItem->item_name }}</p>
                    <p class="text-sm text-gray-600">{{ $claim->match->foundItem->location_found }}</p>
                    <p class="text-xs font-mono text-green-600">{{ $claim->match->foundItem->tracking_id }}</p>
                </div>
            </div>

            <div class="mb-4">
                <p class="text-sm font-medium text-gray-700 mb-1">Your Verification Details:</p>
                <p class="text-gray-600 bg-gray-50 p-3 rounded">{{ $claim->verification_details }}</p>
            </div>

            <div class="text-sm text-gray-500">
                <p>Submitted: {{ $claim->created_at->format('d M Y, H:i') }}</p>
                @if($claim->resolved_at)
                    <p>Resolved: {{ \Carbon\Carbon::parse($claim->resolved_at)->format('d M Y, H:i') }}</p>
                @endif
            </div>
        </div>

        @if($claim->claim_status === 'approved')
        <div class="bg-green-50 border border-green-200 rounded-lg p-6 text-center">
            <p class="text-green-800 font-bold text-lg">Claim Approved!</p>
            <p class="text-green-700 text-sm mt-1">
                Your item has been marked as returned. You earned 10 reward points!
            </p>
        </div>
        @endif

        <div class="flex gap-3">
            <a href="{{ route('claims.index') }}"
               class="bg-gray-200 text-gray-700 px-4 py-2 rounded hover:bg-gray-300 text-sm">
                Back to My Claims
            </a>
            @if($claim->claim_status === 'approved')
            <a href="{{ route('messages.show', $claim->match_id) }}"
               class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700 text-sm">
                Message Finder
            </a>
            @endif
        </div>
    </div>
</x-app-layout>
