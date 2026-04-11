<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Admin — Redemption Requests</h2>
    </x-slot>

    <div class="py-8 max-w-5xl mx-auto sm:px-6 lg:px-8">

        <div class="bg-white rounded-lg shadow overflow-hidden">
            <table class="w-full text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">User</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Reward</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Points Used</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($redemptions as $r)
                    <tr>
                        <td class="px-4 py-3">
                            {{ $r->user->name }}<br>
                            <span class="text-xs text-gray-400">{{ $r->user->email }}</span>
                        </td>
                        <td class="px-4 py-3 capitalize font-medium">{{ str_replace('_', ' ', $r->reward_tier) }}</td>
                        <td class="px-4 py-3 font-bold text-orange-600">{{ $r->points_used }} pts</td>
                        <td class="px-4 py-3">
                            <span class="px-2 py-0.5 rounded text-xs
                                {{ $r->status === 'claimed' ? 'bg-green-100 text-green-700' : 'bg-yellow-100 text-yellow-700' }}">
                                {{ ucfirst($r->status) }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-gray-500">{{ $r->created_at->format('d M Y') }}</td>
                        <td class="px-4 py-3">
                            @if($r->status === 'pending')
                            <form method="POST" action="{{ route('admin.redemptions.approve', $r) }}">
                                @csrf
                                <button class="text-xs bg-green-600 text-white px-2 py-1 rounded hover:bg-green-700">
                                    Mark Claimed
                                </button>
                            </form>
                            @else
                            <span class="text-xs text-gray-400">Done</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-4 py-8 text-center text-gray-500">No redemption requests yet.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-4">{{ $redemptions->links() }}</div>
    </div>
</x-app-layout>
