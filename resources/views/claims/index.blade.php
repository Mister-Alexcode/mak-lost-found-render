<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">My Claims</h2>
    </x-slot>

    <div class="py-8 max-w-5xl mx-auto sm:px-6 lg:px-8">

        @if($claims->isEmpty())
            <div class="bg-white p-8 rounded shadow text-center text-gray-500">
                <p class="text-lg">No claims submitted yet.</p>
                <p class="text-sm mt-2">When you find a match for your lost item, you can submit a claim.</p>
                <a href="{{ route('lost-items.index') }}"
                   class="mt-4 inline-block bg-green-600 text-white px-6 py-2 rounded hover:bg-green-700">
                    View My Lost Items
                </a>
            </div>
        @else
            <div class="space-y-4">
                @foreach($claims as $claim)
                <div class="bg-white rounded-lg shadow p-5">
                    <div class="flex justify-between items-start">
                        <div>
                            <h3 class="font-bold text-lg">
                                {{ $claim->match->lostItem->item_name }}
                                <span class="text-sm font-normal text-gray-500">→</span>
                                {{ $claim->match->foundItem->item_name }}
                            </h3>
                            <p class="text-sm text-gray-500 mt-1">
                                Match confidence: <strong>{{ $claim->match->confidence_score }}%</strong>
                            </p>
                            <p class="text-sm text-gray-500">
                                Submitted: {{ $claim->created_at->diffForHumans() }}
                            </p>
                        </div>
                        <div class="text-right">
                            <span class="px-3 py-1 rounded-full text-sm font-medium
                                @if($claim->claim_status === 'approved') bg-green-100 text-green-700
                                @elseif($claim->claim_status === 'rejected') bg-red-100 text-red-700
                                @elseif($claim->claim_status === 'under_review') bg-green-100 text-green-700
                                @else bg-yellow-100 text-yellow-700 @endif">
                                {{ ucfirst(str_replace('_', ' ', $claim->claim_status)) }}
                            </span>
                        </div>
                    </div>
                    <div class="mt-3">
                        <a href="{{ route('claims.show', $claim) }}"
                           class="text-sm bg-green-50 text-green-600 px-3 py-1 rounded hover:bg-green-100">
                            View Details
                        </a>
                    </div>
                </div>
                @endforeach
            </div>
            <div class="mt-6">{{ $claims->links() }}</div>
        @endif
    </div>
</x-app-layout>
