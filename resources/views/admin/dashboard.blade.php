<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Admin Dashboard</h2>
    </x-slot>

    <div class="py-8 max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

        {{-- Stats --}}
        <div class="grid grid-cols-2 md:grid-cols-5 gap-4">
            <div class="bg-white rounded-lg shadow p-4 text-center">
                <p class="text-3xl font-bold text-blue-600">{{ $stats['lost_items'] }}</p>
                <p class="text-sm text-gray-500 mt-1">Lost Reports</p>
            </div>
            <div class="bg-white rounded-lg shadow p-4 text-center">
                <p class="text-3xl font-bold text-green-600">{{ $stats['found_items'] }}</p>
                <p class="text-sm text-gray-500 mt-1">Found Reports</p>
            </div>
            <div class="bg-white rounded-lg shadow p-4 text-center">
                <p class="text-3xl font-bold text-yellow-600">{{ $stats['pending_claims'] }}</p>
                <p class="text-sm text-gray-500 mt-1">Pending Claims</p>
            </div>
            <div class="bg-white rounded-lg shadow p-4 text-center">
                <p class="text-3xl font-bold text-purple-600">{{ $stats['users'] }}</p>
                <p class="text-sm text-gray-500 mt-1">Users</p>
            </div>
            <div class="bg-white rounded-lg shadow p-4 text-center">
                <p class="text-3xl font-bold text-teal-600">{{ $stats['returned'] }}</p>
                <p class="text-sm text-gray-500 mt-1">Items Returned</p>
            </div>
            <div class="bg-white rounded-lg shadow p-4 text-center">
                <p class="text-3xl font-bold text-orange-600">{{ $stats['pending_redemptions'] }}</p>
                <p class="text-sm text-gray-500 mt-1">Pending Redemptions</p>
            </div>
        </div>

        {{-- Quick Links --}}
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <a href="{{ route('admin.claims') }}"
               class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 text-center hover:bg-yellow-100">
                <p class="font-bold text-yellow-700">Review Claims</p>
                <p class="text-xs text-yellow-600 mt-1">{{ $stats['pending_claims'] }} pending</p>
            </a>
            <a href="{{ route('admin.lost-items') }}"
               class="bg-blue-50 border border-blue-200 rounded-lg p-4 text-center hover:bg-blue-100">
                <p class="font-bold text-blue-700">Lost Items</p>
                <p class="text-xs text-blue-600 mt-1">Manage all reports</p>
            </a>
            <a href="{{ route('admin.found-items') }}"
               class="bg-green-50 border border-green-200 rounded-lg p-4 text-center hover:bg-green-100">
                <p class="font-bold text-green-700">Found Items</p>
                <p class="text-xs text-green-600 mt-1">Manage all reports</p>
            </a>
            <a href="{{ route('admin.users') }}"
               class="bg-purple-50 border border-purple-200 rounded-lg p-4 text-center hover:bg-purple-100">
                <p class="font-bold text-purple-700">Users</p>
                <p class="text-xs text-purple-600 mt-1">View all users</p>
            </a>
            <a href="{{ route('admin.redemptions') }}"
               class="bg-orange-50 border border-orange-200 rounded-lg p-4 text-center hover:bg-orange-100">
                <p class="font-bold text-orange-700">Redemptions</p>
                <p class="text-xs text-orange-600 mt-1">{{ $stats['pending_redemptions'] }} pending</p>
            </a>
        </div>

        {{-- Recent Claims --}}
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-bold mb-4">Recent Claims</h3>
            @if($recentClaims->isEmpty())
                <p class="text-gray-500 text-sm">No claims yet.</p>
            @else
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b text-left text-gray-500">
                            <th class="pb-2">Claimant</th>
                            <th class="pb-2">Item</th>
                            <th class="pb-2">Status</th>
                            <th class="pb-2">Date</th>
                            <th class="pb-2">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y">
                        @foreach($recentClaims as $claim)
                        <tr>
                            <td class="py-2">{{ $claim->claimant->name }}</td>
                            <td class="py-2">{{ $claim->match->lostItem->item_name }}</td>
                            <td class="py-2">
                                <span class="px-2 py-0.5 rounded text-xs
                                    @if($claim->claim_status === 'approved') bg-green-100 text-green-700
                                    @elseif($claim->claim_status === 'rejected') bg-red-100 text-red-700
                                    @else bg-yellow-100 text-yellow-700 @endif">
                                    {{ ucfirst($claim->claim_status) }}
                                </span>
                            </td>
                            <td class="py-2 text-gray-500">{{ $claim->created_at->diffForHumans() }}</td>
                            <td class="py-2">
                                @if($claim->claim_status === 'pending')
                                <form method="POST" action="{{ route('admin.claims.approve', $claim) }}" class="inline">
                                    @csrf
                                    <button class="text-xs bg-green-600 text-white px-2 py-1 rounded hover:bg-green-700">
                                        Approve
                                    </button>
                                </form>
                                <form method="POST" action="{{ route('admin.claims.reject', $claim) }}" class="inline ml-1">
                                    @csrf
                                    <button class="text-xs bg-red-600 text-white px-2 py-1 rounded hover:bg-red-700">
                                        Reject
                                    </button>
                                </form>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                <div class="mt-3">
                    <a href="{{ route('admin.claims') }}" class="text-sm text-blue-600 hover:underline">
                        View all claims →
                    </a>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
