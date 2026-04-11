<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Leaderboard — Top Helpers</h2>
    </x-slot>

    <div class="py-8 max-w-3xl mx-auto sm:px-6 lg:px-8">

        @auth
        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6 flex justify-between items-center">
            <div>
                <p class="text-sm text-blue-600 font-medium">Your Rank</p>
                <p class="text-2xl font-bold text-blue-800">#{{ $myRank }}</p>
            </div>
            <div class="text-right">
                <p class="text-sm text-blue-600 font-medium">Your Points</p>
                <p class="text-2xl font-bold text-blue-800">{{ auth()->user()->reward_points }}</p>
            </div>
            <div class="text-right text-xs text-blue-500 max-w-40">
                <p>+10 pts: Report found item</p>
                <p>+20 pts: Successful return</p>
                <p>+5 pts: Referral</p>
            </div>
        </div>
        @endauth

        <div class="bg-white rounded-lg shadow overflow-hidden">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Rank</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Name</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Points</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($topUsers as $index => $user)
                    <tr class="{{ auth()->check() && auth()->id() === $user->id ? 'bg-blue-50' : '' }}">
                        <td class="px-4 py-3">
                            @if($index === 0)
                                <span class="text-yellow-500 font-bold text-lg">🥇</span>
                            @elseif($index === 1)
                                <span class="text-gray-400 font-bold text-lg">🥈</span>
                            @elseif($index === 2)
                                <span class="text-amber-600 font-bold text-lg">🥉</span>
                            @else
                                <span class="text-gray-500 font-medium">#{{ $index + 1 }}</span>
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            <span class="font-medium">{{ $user->name }}</span>
                            @if(auth()->check() && auth()->id() === $user->id)
                                <span class="text-xs text-blue-600 ml-1">(you)</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-right font-bold text-gray-800">
                            {{ number_format($user->reward_points) }}
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>

            @if($topUsers->isEmpty())
            <div class="p-8 text-center text-gray-500">
                No users on the leaderboard yet. Be the first to earn points!
            </div>
            @endif
        </div>
    </div>
</x-app-layout>
