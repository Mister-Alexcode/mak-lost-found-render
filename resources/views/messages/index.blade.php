<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Messages</h2>
            @if($isAdmin)
            <div x-data="{ open: false, search: '', users: [] }" class="relative">
                <button @click="open = !open" class="bg-blue-600 text-white px-4 py-2 rounded-lg text-sm hover:bg-blue-700 transition">
                    + New Message
                </button>
                <div x-show="open" @click.away="open = false" x-cloak
                     class="absolute right-0 mt-2 w-72 bg-white rounded-lg shadow-lg border p-3 z-50">
                    <form method="GET" action="" x-ref="userSearch">
                        <input type="text" placeholder="Search user by name or email..."
                               class="w-full border rounded px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500"
                               x-model="search"
                               @input.debounce.300ms="
                                   if(search.length >= 2) {
                                       fetch('/api/users/search?q=' + encodeURIComponent(search))
                                           .then(r => r.json()).then(d => users = d);
                                   } else { users = []; }
                               ">
                    </form>
                    <div class="mt-2 max-h-40 overflow-y-auto">
                        <template x-for="user in users" :key="user.id">
                            <a :href="'/messages/direct/' + user.id"
                               class="block px-3 py-2 text-sm hover:bg-gray-50 rounded">
                                <span x-text="user.name" class="font-medium"></span>
                                <span x-text="user.email" class="text-gray-400 text-xs block"></span>
                            </a>
                        </template>
                        <p x-show="search.length >= 2 && users.length === 0" class="text-xs text-gray-400 px-3 py-2">
                            No users found.
                        </p>
                    </div>
                </div>
            </div>
            @endif
        </div>
    </x-slot>

    <div class="py-8 max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-6">

        <x-section-header
            icon="messages"
            title="Messages"
            subtitle="Direct conversations and match-based chats" />

        {{-- Direct Conversations --}}
        @if($directConversations->isNotEmpty())
        <div>
            <h3 class="text-sm font-semibold text-gray-500 uppercase tracking-wider mb-3">Direct Messages</h3>
            <div class="space-y-2">
                @foreach($directConversations as $otherUser)
                <a href="{{ route('messages.direct', $otherUser) }}"
                   class="block bg-white rounded-lg shadow p-4 hover:bg-gray-50 transition">
                    <div class="flex justify-between items-center">
                        <div class="flex items-center gap-3">
                            <div class="w-9 h-9 rounded-full bg-purple-100 text-purple-700 flex items-center justify-center text-sm font-bold">
                                {{ strtoupper(substr($otherUser->name, 0, 1)) }}
                            </div>
                            <div>
                                <p class="font-medium">{{ $otherUser->name }}</p>
                                @if(!$otherUser->isAdmin())
                                <p class="text-xs text-gray-500">{{ $otherUser->email }}</p>
                                @endif
                            </div>
                        </div>
                        @if($otherUser->isAdmin())
                        <span class="text-xs bg-purple-100 text-purple-700 px-2 py-0.5 rounded">Admin</span>
                        @endif
                    </div>
                </a>
                @endforeach
            </div>
        </div>
        @endif

        {{-- Match-based Conversations --}}
        @if($matches->isNotEmpty())
        <div>
            <h3 class="text-sm font-semibold text-gray-500 uppercase tracking-wider mb-3">Item Conversations</h3>
            <div class="space-y-2">
                @foreach($matches as $match)
                @php
                    $userId = auth()->id();
                    $isOwner = $match->lostItem->user_id === $userId;
                    $otherUser = $isOwner ? $match->foundItem->user : $match->lostItem->user;
                @endphp
                <a href="{{ route('messages.show', $match) }}"
                   class="block bg-white rounded-lg shadow p-4 hover:bg-gray-50 transition">
                    <div class="flex justify-between items-center">
                        <div class="flex items-center gap-3">
                            <div class="w-9 h-9 rounded-full bg-blue-100 text-blue-700 flex items-center justify-center text-sm font-bold">
                                {{ strtoupper(substr($otherUser->name, 0, 1)) }}
                            </div>
                            <div>
                                <p class="font-medium">{{ $otherUser->name }}</p>
                                <p class="text-sm text-gray-500">
                                    Re: {{ $isOwner ? $match->lostItem->item_name : $match->foundItem->item_name }}
                                    &middot; {{ $match->confidence_score }}% match
                                </p>
                            </div>
                        </div>
                        <span class="text-xs text-gray-400">
                            {{ $match->updated_at->diffForHumans() }}
                        </span>
                    </div>
                </a>
                @endforeach
            </div>
        </div>
        @endif

        @if($matches->isEmpty() && $directConversations->isEmpty())
            <div class="bg-white p-8 rounded-lg shadow text-center text-gray-500">
                <p>No conversations yet.</p>
                <p class="text-sm mt-1">Messages become available when items are matched or an admin reaches out.</p>
            </div>
        @endif
    </div>
</x-app-layout>
