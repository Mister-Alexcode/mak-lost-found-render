<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Manage Claims</h2>
    </x-slot>

    <div class="py-8 max-w-7xl mx-auto sm:px-6 lg:px-8">

        <div class="bg-white rounded-lg shadow overflow-hidden">
            <table class="w-full text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Claimant</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Lost Item</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Found Item</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Match %</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Verification</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($claims as $claim)
                    <tr>
                        <td class="px-4 py-3">
                            {{ $claim->claimant->name }}<br>
                            <span class="text-xs text-gray-400">{{ $claim->claimant->email }}</span>
                        </td>
                        <td class="px-4 py-3">{{ $claim->match->lostItem->item_name }}</td>
                        <td class="px-4 py-3">{{ $claim->match->foundItem->item_name }}</td>
                        <td class="px-4 py-3 font-bold text-green-600">{{ $claim->match->confidence_score }}%</td>
                        <td class="px-4 py-3 max-w-xs">
                            <p class="text-xs text-gray-600 truncate" title="{{ $claim->verification_details }}">
                                {{ $claim->verification_details }}
                            </p>
                        </td>
                        <td class="px-4 py-3">
                            <span class="px-2 py-0.5 rounded text-xs
                                @if($claim->claim_status === 'approved') bg-green-100 text-green-700
                                @elseif($claim->claim_status === 'rejected') bg-red-100 text-red-700
                                @else bg-yellow-100 text-yellow-700 @endif">
                                {{ ucfirst($claim->claim_status) }}
                            </span>
                        </td>
                        <td class="px-4 py-3">
                            @if($claim->claim_status === 'pending')
                            <form method="POST" action="{{ route('admin.claims.approve', $claim) }}" class="inline">
                                @csrf
                                <button class="text-xs bg-green-600 text-white px-2 py-1 rounded hover:bg-green-700 mb-1 block">
                                    Approve
                                </button>
                            </form>
                            <form method="POST" action="{{ route('admin.claims.reject', $claim) }}" class="inline">
                                @csrf
                                <button class="text-xs bg-red-600 text-white px-2 py-1 rounded hover:bg-red-700 block">
                                    Reject
                                </button>
                            </form>
                            @else
                            <span class="text-xs text-gray-400">
                                {{ $claim->resolved_at ? \Carbon\Carbon::parse($claim->resolved_at)->diffForHumans() : '—' }}
                            </span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-4 py-8 text-center text-gray-500">No claims yet.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-4">{{ $claims->links() }}</div>
    </div>
</x-app-layout>
