<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Notifications</h2>
            @if($notifications->where('is_read', false)->count() > 0)
            <form method="POST" action="{{ route('notifications.read-all') }}">
                @csrf
                <button class="text-sm text-blue-600 hover:underline">Mark all as read</button>
            </form>
            @endif
        </div>
    </x-slot>

    <div class="py-8 max-w-3xl mx-auto sm:px-6 lg:px-8">

        @if($notifications->isEmpty())
            <div class="bg-white p-8 rounded shadow text-center text-gray-500">
                <p>No notifications yet.</p>
            </div>
        @else
            <div class="space-y-2">
                @foreach($notifications as $n)
                <div class="bg-white rounded-lg shadow p-4 flex items-start gap-3
                    {{ $n->is_read ? 'opacity-70' : 'border-l-4 border-blue-500' }}">
                    <div class="flex-1">
                        @if($n->link)
                        <a href="{{ route('notifications.visit', $n) }}" class="block group">
                            <p class="text-sm {{ $n->is_read ? 'text-gray-600' : 'text-gray-800 font-medium' }} group-hover:text-blue-600 transition">
                                {{ $n->message }}
                            </p>
                            <span class="text-xs text-blue-500 group-hover:underline">View details &rarr;</span>
                        </a>
                        @else
                        <p class="text-sm {{ $n->is_read ? 'text-gray-600' : 'text-gray-800 font-medium' }}">
                            {{ $n->message }}
                        </p>
                        @endif
                        <p class="text-xs text-gray-400 mt-1">{{ $n->created_at->diffForHumans() }}</p>
                    </div>
                    @if(!$n->is_read)
                    <form method="POST" action="{{ route('notifications.read', $n) }}">
                        @csrf
                        <button class="text-xs text-gray-400 hover:text-gray-600 whitespace-nowrap">Mark read</button>
                    </form>
                    @endif
                </div>
                @endforeach
            </div>
            <div class="mt-6">{{ $notifications->links() }}</div>
        @endif
    </div>
</x-app-layout>
