<x-app-layout>
    <div class="py-8 max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

        <x-section-header
            icon="home"
            title="Welcome, {{ Auth::user()->name }}"
            subtitle="{{ Auth::user()->isAdmin() ? 'Admin control center' : 'Report items, track claims, earn rewards' }}" />

        {{-- Admin: Pending Claims Alert --}}
        @if(Auth::user()->isAdmin() && $pendingClaims > 0)
        <div class="bg-yellow-50 border border-yellow-400 rounded-lg p-4 flex justify-between items-center">
            <div>
                <p class="font-bold text-yellow-800">
                    {{ $pendingClaims }} claim{{ $pendingClaims > 1 ? 's' : '' }} waiting for your approval
                </p>
                <p class="text-sm text-yellow-700">Review and approve or reject submitted claims.</p>
            </div>
            <a href="{{ route('admin.claims') }}"
               class="bg-yellow-600 text-white px-4 py-2 rounded hover:bg-yellow-700 text-sm font-medium whitespace-nowrap">
                Review Claims
            </a>
        </div>
        @endif

        {{-- Stats Row --}}
        <div class="grid grid-cols-2 md:grid-cols-5 gap-4">
            <div class="bg-white rounded-lg shadow p-4 text-center">
                <p class="text-3xl font-bold text-green-600">{{ $lostCount }}</p>
                <p class="text-xs text-gray-500 mt-1">{{ Auth::user()->isAdmin() ? 'Total' : 'My' }} Lost Reports</p>
            </div>
            <div class="bg-white rounded-lg shadow p-4 text-center">
                <p class="text-3xl font-bold text-green-600">{{ $foundCount }}</p>
                <p class="text-xs text-gray-500 mt-1">{{ Auth::user()->isAdmin() ? 'Total' : 'My' }} Found Reports</p>
            </div>
            <div class="bg-white rounded-lg shadow p-4 text-center">
                <p class="text-3xl font-bold text-purple-600">{{ $matchesCount }}</p>
                <p class="text-xs text-gray-500 mt-1">Matches</p>
            </div>
            <div class="bg-white rounded-lg shadow p-4 text-center">
                <p class="text-3xl font-bold text-yellow-600">
                    {{ Auth::user()->isAdmin() ? $pendingClaims : $claimsCount }}
                </p>
                <p class="text-xs text-gray-500 mt-1">
                    {{ Auth::user()->isAdmin() ? 'Pending Claims' : 'My Claims' }}
                </p>
            </div>
            <div class="bg-white rounded-lg shadow p-4 text-center">
                @if(Auth::user()->isAdmin())
                    <p class="text-3xl font-bold text-orange-600">{{ $unreadNotifs }}</p>
                    <p class="text-xs text-gray-500 mt-1">Unread Notifs</p>
                @else
                    <p class="text-3xl font-bold text-orange-600">{{ Auth::user()->reward_points }}</p>
                    <p class="text-xs text-gray-500 mt-1">Reward Points</p>
                @endif
            </div>
        </div>

        {{-- Quick Actions --}}
        @if(Auth::user()->isAdmin())
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <a href="{{ route('admin.claims') }}"
               class="bg-yellow-500 text-white rounded-lg p-4 text-center hover:bg-yellow-600 transition">
                <p class="font-bold">Review Claims</p>
                <p class="text-xs text-yellow-100 mt-1">{{ $pendingClaims }} pending</p>
            </a>
            <a href="{{ route('lost-items.index') }}"
               class="bg-green-600 text-white rounded-lg p-4 text-center hover:bg-green-700 transition">
                <p class="font-bold">All Lost Items</p>
                <p class="text-xs text-green-200 mt-1">{{ $lostCount }} reports</p>
            </a>
            <a href="{{ route('found-items.index') }}"
               class="bg-green-600 text-white rounded-lg p-4 text-center hover:bg-green-700 transition">
                <p class="font-bold">All Found Items</p>
                <p class="text-xs text-green-200 mt-1">{{ $foundCount }} reports</p>
            </a>
            <a href="{{ route('admin.users') }}"
               class="bg-purple-600 text-white rounded-lg p-4 text-center hover:bg-purple-700 transition">
                <p class="font-bold">Manage Users</p>
                <p class="text-xs text-purple-200 mt-1">View all users</p>
            </a>
        </div>
        @else
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <a href="{{ route('lost-items.create') }}"
               class="bg-green-600 text-white rounded-lg p-4 text-center hover:bg-green-700 transition">
                <p class="text-lg font-bold">+ Report Lost</p>
                <p class="text-xs text-green-200 mt-1">I lost something</p>
            </a>
            <a href="{{ route('found-items.create') }}"
               class="bg-green-600 text-white rounded-lg p-4 text-center hover:bg-green-700 transition">
                <p class="text-lg font-bold">+ Report Found</p>
                <p class="text-xs text-green-200 mt-1">I found something (+10 pts)</p>
            </a>
            <a href="{{ route('search.index') }}"
               class="bg-gray-700 text-white rounded-lg p-4 text-center hover:bg-gray-800 transition">
                <p class="text-lg font-bold">Search</p>
                <p class="text-xs text-gray-300 mt-1">Browse all items</p>
            </a>
            <a href="{{ route('notifications.index') }}"
               class="bg-orange-500 text-white rounded-lg p-4 text-center hover:bg-orange-600 transition relative">
                <p class="text-lg font-bold">Notifications</p>
                <p class="text-xs text-orange-200 mt-1">
                    {{ $unreadNotifs > 0 ? $unreadNotifs . ' unread' : 'All caught up' }}
                </p>
                @if($unreadNotifs > 0)
                <span class="absolute top-2 right-2 bg-red-500 text-white text-xs rounded-full h-5 w-5 flex items-center justify-center">
                    {{ $unreadNotifs }}
                </span>
                @endif
            </a>
        </div>

        {{-- My Lost Items --}}
        @if($myLostItems->count() > 0)
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex justify-between items-center mb-3">
                <h3 class="font-bold text-gray-800">My Lost Items</h3>
                <a href="{{ route('lost-items.index') }}" class="text-sm text-green-600 hover:underline">View All Lost Items</a>
            </div>
            <div class="space-y-2">
                @foreach($myLostItems as $item)
                <a href="{{ route('lost-items.show', $item) }}"
                   class="flex items-center justify-between p-3 rounded-lg border hover:bg-green-50 transition">
                    <div class="flex items-center gap-3">
                        @if($item->photo)
                            <img src="{{ asset('storage/' . $item->photo) }}" class="w-10 h-10 rounded object-cover" alt="">
                        @else
                            <div class="w-10 h-10 rounded bg-gray-100 flex items-center justify-center text-gray-400 text-xs">N/A</div>
                        @endif
                        <div>
                            <p class="font-medium text-sm">{{ $item->item_name }}</p>
                            <p class="text-xs text-gray-500">{{ $item->location_lost }} · {{ $item->date_lost }}</p>
                        </div>
                    </div>
                    <span class="px-2 py-0.5 text-xs rounded {{ $item->status === 'active' ? 'bg-yellow-100 text-yellow-700' : 'bg-green-100 text-green-700' }}">
                        {{ ucfirst($item->status) }}
                    </span>
                </a>
                @endforeach
            </div>
        </div>
        @endif

        {{-- My Found Items --}}
        @if($myFoundItems->count() > 0)
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex justify-between items-center mb-3">
                <h3 class="font-bold text-gray-800">My Found Items</h3>
                <a href="{{ route('found-items.index') }}" class="text-sm text-green-600 hover:underline">View All Found Items</a>
            </div>
            <div class="space-y-2">
                @foreach($myFoundItems as $item)
                <a href="{{ route('found-items.show', $item) }}"
                   class="flex items-center justify-between p-3 rounded-lg border hover:bg-green-50 transition">
                    <div class="flex items-center gap-3">
                        @if($item->photo)
                            <img src="{{ asset('storage/' . $item->photo) }}" class="w-10 h-10 rounded object-cover" alt="">
                        @else
                            <div class="w-10 h-10 rounded bg-gray-100 flex items-center justify-center text-gray-400 text-xs">N/A</div>
                        @endif
                        <div>
                            <p class="font-medium text-sm">{{ $item->item_name }}</p>
                            <p class="text-xs text-gray-500">{{ $item->location_found }} · {{ $item->date_found }}</p>
                        </div>
                    </div>
                    <span class="px-2 py-0.5 text-xs rounded {{ $item->status === 'active' ? 'bg-yellow-100 text-yellow-700' : 'bg-green-100 text-green-700' }}">
                        {{ ucfirst($item->status) }}
                    </span>
                </a>
                @endforeach
            </div>
        </div>
        @endif

        {{-- Items Returned to Me --}}
        @if($myReturnedItems->count() > 0)
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex justify-between items-center mb-3">
                <h3 class="font-bold text-gray-800">Returned to Me</h3>
            </div>
            <div class="space-y-2">
                @foreach($myReturnedItems as $item)
                <a href="{{ route('lost-items.show', $item) }}"
                   class="flex items-center justify-between p-3 rounded-lg border hover:bg-purple-50 transition">
                    <div class="flex items-center gap-3">
                        @if($item->photo)
                            <img src="{{ asset('storage/' . $item->photo) }}" class="w-10 h-10 rounded object-cover" alt="">
                        @else
                            <div class="w-10 h-10 rounded bg-gray-100 flex items-center justify-center text-gray-400 text-xs">N/A</div>
                        @endif
                        <div>
                            <p class="font-medium text-sm">{{ $item->item_name }}</p>
                            <p class="text-xs text-gray-500">{{ $item->location_lost }} · {{ $item->date_lost }}</p>
                        </div>
                    </div>
                    <span class="px-2 py-0.5 text-xs rounded bg-green-100 text-green-700">
                        Returned
                    </span>
                </a>
                @endforeach
            </div>
        </div>
        @endif

        {{-- Reward Points Progress (users only) --}}
        @php
            $pts = Auth::user()->reward_points;
            $tiers = [
                ['points' => 50,  'label' => 'Certificate'],
                ['points' => 100, 'label' => 'Voucher'],
                ['points' => 200, 'label' => 'Trophy'],
            ];
            $nextTier = collect($tiers)->first(fn($t) => $t['points'] > $pts);
        @endphp
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex justify-between items-center mb-3">
                <h3 class="font-bold text-gray-800">Reward Points Progress</h3>
                <a href="{{ route('leaderboard.index') }}" class="text-sm text-green-600 hover:underline">Leaderboard</a>
            </div>
            <div class="flex items-center gap-4">
                <div class="text-3xl font-bold text-orange-600">{{ $pts }}</div>
                <div class="flex-1">
                    @if($nextTier)
                    <div class="flex justify-between text-xs text-gray-500 mb-1">
                        <span>{{ $pts }} pts</span>
                        <span>Next: {{ $nextTier['label'] }} at {{ $nextTier['points'] }} pts</span>
                    </div>
                    <div class="w-full bg-gray-200 rounded-full h-3">
                        <div class="bg-orange-500 h-3 rounded-full transition-all"
                             style="width: {{ min(100, ($pts / $nextTier['points']) * 100) }}%"></div>
                    </div>
                    @else
                    <p class="text-sm text-green-700 font-medium">You've reached the highest tier!</p>
                    @endif
                </div>
                @if($pts >= 50)
                <a href="{{ route('redemptions.index') }}"
                   class="bg-orange-500 text-white px-3 py-1 rounded text-sm hover:bg-orange-600 whitespace-nowrap">
                    Redeem Points
                </a>
                @endif
            </div>
        </div>
        @endif

        {{-- Recent Matches --}}
        @if($recentMatches->count() > 0)
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-bold mb-4 text-gray-800">Recent Matches</h3>
            <div class="space-y-3">
                @foreach($recentMatches as $match)
                <div class="flex items-center justify-between p-3 bg-green-50 rounded-lg border border-green-100">
                    <div>
                        <p class="font-medium text-sm">
                            {{ $match->lostItem->item_name }}
                            <span class="text-gray-400">matched with</span>
                            {{ $match->foundItem->item_name }}
                        </p>
                        <p class="text-xs text-gray-500">{{ $match->created_at->diffForHumans() }}</p>
                    </div>
                    <div class="text-right mx-4">
                        <span class="text-xl font-bold text-green-600">{{ $match->confidence_score }}%</span>
                        <p class="text-xs text-gray-400">confidence</p>
                    </div>
                    <a href="{{ route('lost-items.show', $match->lostItem) }}"
                       class="text-xs bg-green-600 text-white px-3 py-1 rounded hover:bg-green-700 whitespace-nowrap">
                        View
                    </a>
                </div>
                @endforeach
            </div>
        </div>
        @elseif(!Auth::user()->isAdmin())
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-bold mb-2 text-gray-800">Getting Started</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm text-gray-600">
                <div class="flex items-start gap-2">
                    <span class="text-green-500 font-bold">1.</span>
                    <p>Report your lost item — describe it in detail for better matching.</p>
                </div>
                <div class="flex items-start gap-2">
                    <span class="text-green-500 font-bold">2.</span>
                    <p>Report a found item and earn <strong>10 reward points</strong>.</p>
                </div>
                <div class="flex items-start gap-2">
                    <span class="text-purple-500 font-bold">3.</span>
                    <p>The system automatically matches items by category, color, location, and date.</p>
                </div>
                <div class="flex items-start gap-2">
                    <span class="text-yellow-500 font-bold">4.</span>
                    <p>Submit a claim when a match is found. An admin verifies and approves returns.</p>
                </div>
            </div>
        </div>
        @endif

    </div>
</x-app-layout>
