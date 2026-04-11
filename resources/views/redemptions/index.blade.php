<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Redeem Reward Points</h2>
    </x-slot>

    <div class="py-8 max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-6">

        {{-- Points Balance --}}
        <div class="bg-white rounded-lg shadow p-6 flex items-center gap-6">
            <div>
                <p class="text-5xl font-bold text-orange-500">{{ $myPoints }}</p>
                <p class="text-gray-500 text-sm mt-1">Your current points</p>
            </div>
            <div class="text-sm text-gray-600 border-l pl-6">
                <p class="font-medium mb-2">How to earn points:</p>
                <p>+10 pts — Report a found item</p>
                <p>+20 pts — Successful item return (claim approved)</p>
                <p>+5 pts — Referral bonus</p>
            </div>
        </div>

        {{-- Reward Tiers --}}
        <h3 class="text-lg font-bold text-gray-800">Available Rewards</h3>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
            @foreach($tiers as $tier)
            @php $canRedeem = $myPoints >= $tier['points']; @endphp
            <div class="bg-white rounded-lg shadow p-5 border-2 {{ $canRedeem ? 'border-orange-400' : 'border-gray-100' }}">
                <div class="text-3xl mb-2">
                    @if($tier['tier'] === 'certificate') 📜
                    @elseif($tier['tier'] === 'voucher') 🎟️
                    @else 🏆
                    @endif
                </div>
                <h4 class="font-bold text-gray-800">{{ $tier['label'] }}</h4>
                <p class="text-sm text-gray-500 mt-1 mb-3">{{ $tier['description'] }}</p>
                <p class="text-lg font-bold {{ $canRedeem ? 'text-orange-600' : 'text-gray-400' }}">
                    {{ $tier['points'] }} points
                </p>
                @if($canRedeem)
                <form method="POST" action="{{ route('redemptions.store') }}" class="mt-3"
                      onsubmit="return confirm('Redeem ' + this.dataset.points + ' points for ' + this.dataset.label + '?')"
                      data-points="{{ $tier['points'] }}" data-label="{{ $tier['label'] }}">
                    @csrf
                    <input type="hidden" name="tier" value="{{ $tier['tier'] }}">
                    <button type="submit"
                            class="w-full bg-orange-500 text-white px-4 py-2 rounded hover:bg-orange-600 text-sm font-medium">
                        Redeem Now
                    </button>
                </form>
                @else
                <p class="mt-3 text-xs text-gray-400">
                    Need {{ $tier['points'] - $myPoints }} more points
                </p>
                <div class="w-full bg-gray-200 rounded-full h-2 mt-2">
                    <div class="bg-orange-300 h-2 rounded-full"
                         style="width: {{ min(100, ($myPoints / $tier['points']) * 100) }}%"></div>
                </div>
                @endif
            </div>
            @endforeach
        </div>

        {{-- My Redemption History --}}
        @if($myRedemptions->count() > 0)
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="font-bold text-gray-800 mb-4">My Redemption History</h3>
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b text-left text-gray-500">
                        <th class="pb-2">Reward</th>
                        <th class="pb-2">Points Used</th>
                        <th class="pb-2">Status</th>
                        <th class="pb-2">Date</th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    @foreach($myRedemptions as $r)
                    <tr>
                        <td class="py-2 capitalize">{{ str_replace('_', ' ', $r->reward_tier) }}</td>
                        <td class="py-2">{{ $r->points_used }} pts</td>
                        <td class="py-2">
                            <span class="px-2 py-0.5 rounded text-xs
                                {{ $r->status === 'claimed' ? 'bg-green-100 text-green-700' : 'bg-yellow-100 text-yellow-700' }}">
                                {{ ucfirst($r->status) }}
                            </span>
                        </td>
                        <td class="py-2 text-gray-500">{{ $r->created_at->format('d M Y') }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif
    </div>
</x-app-layout>
