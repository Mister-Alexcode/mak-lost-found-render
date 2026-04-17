<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Manage Claims</h2>
    </x-slot>

    <div class="py-8 max-w-7xl mx-auto sm:px-6 lg:px-8">

        <x-section-header
            icon="claims"
            title="Manage Claims"
            subtitle="Review submitted claims and approve or reject them" />

        <div class="bg-white rounded-xl shadow overflow-hidden">
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
                        <td class="px-4 py-3 space-y-1">
                            @if($claim->claim_status === 'pending')
                            <div class="flex gap-1 mb-2">
                                <form method="POST" action="{{ route('admin.claims.approve', $claim) }}" class="inline">
                                    @csrf
                                    <button class="text-xs bg-green-600 text-white px-2 py-1 rounded hover:bg-green-700">
                                        Approve
                                    </button>
                                </form>
                                <form method="POST" action="{{ route('admin.claims.reject', $claim) }}" class="inline"
                                      x-data="{ showReason: false }">
                                    @csrf
                                    <button type="button" @click="showReason = !showReason"
                                            class="text-xs bg-red-600 text-white px-2 py-1 rounded hover:bg-red-700">
                                        Reject
                                    </button>
                                    <div x-show="showReason" x-cloak class="mt-1">
                                        <textarea name="reason" rows="2" placeholder="Reason for rejection (optional)"
                                                  class="w-full text-xs border-gray-300 rounded p-1"></textarea>
                                        <button type="submit"
                                                class="text-xs bg-red-600 text-white px-2 py-0.5 rounded mt-1 hover:bg-red-700">
                                            Confirm Reject
                                        </button>
                                    </div>
                                </form>
                            </div>
                            @else
                            <span class="text-xs text-gray-400 block mb-1">
                                {{ $claim->resolved_at ? \Carbon\Carbon::parse($claim->resolved_at)->diffForHumans() : '—' }}
                            </span>
                            @endif
                            <div class="flex gap-1">
                                <a href="{{ route('messages.direct', $claim->match->lostItem->user) }}"
                                   class="text-xs bg-green-50 text-green-600 px-2 py-1 rounded hover:bg-green-100" title="Message {{ $claim->match->lostItem->user->name }}">
                                    Msg Owner
                                </a>
                                <a href="{{ route('messages.direct', $claim->match->foundItem->user) }}"
                                   class="text-xs bg-green-50 text-green-600 px-2 py-1 rounded hover:bg-green-100" title="Message {{ $claim->match->foundItem->user->name }}">
                                    Msg Finder
                                </a>
                            </div>
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
