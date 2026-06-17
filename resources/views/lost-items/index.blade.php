<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">All Lost Item Reports</h2>
            <a href="{{ route('lost-items.create') }}"
               class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700 text-sm">
                + Report Lost Item
            </a>
        </div>
    </x-slot>

    <div class="py-8 max-w-7xl mx-auto sm:px-6 lg:px-8">

        @if($lostItems->isEmpty())
            <div class="bg-white p-8 rounded shadow text-center text-gray-500">
                <p class="text-lg">No lost items reported yet.</p>
                <a href="{{ route('lost-items.create') }}"
                   class="mt-4 inline-block bg-green-600 text-white px-6 py-2 rounded hover:bg-green-700">
                    Report a Lost Item
                </a>
            </div>
        @else
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($lostItems as $item)
                <div class="bg-white rounded-lg shadow p-5 hover:shadow-md transition-shadow duration-200">
                    @if($item->photo)
                        <img src="{{ $item->photo_url }}"
                             class="w-full h-40 object-cover rounded mb-3" alt="Item photo">
                    @else
                        <div class="w-full h-40 bg-gray-100 rounded mb-3 flex items-center justify-center text-gray-400">
                            No Photo
                        </div>
                    @endif
                    <h3 class="font-bold text-lg">{{ $item->item_name }}</h3>
                    <p class="text-xs text-purple-600 font-medium">Reported by: {{ $item->user->name }}</p>
                    <p class="text-sm text-gray-500">Category: {{ $item->category }}</p>
                    <p class="text-sm text-gray-500">Location: {{ $item->location_lost }}</p>
                    <p class="text-sm text-gray-500">Date: {{ $item->date_lost }}</p>
                    @if($item->reward_offer)
                        <p class="text-sm text-green-700 font-medium mt-1">Reward: {{ $item->reward_offer }}</p>
                    @endif
                    <p class="text-xs text-green-600 font-mono mt-1">{{ $item->tracking_id }}</p>
                    <span class="inline-block mt-2 px-2 py-1 text-xs rounded
                        {{ $item->status === 'active' ? 'bg-yellow-100 text-yellow-700' : 'bg-green-100 text-green-700' }}">
                        {{ ucfirst($item->status) }}
                    </span>
                    <div class="mt-3 flex gap-2">
                        <a href="{{ route('lost-items.show', $item) }}"
                           class="text-sm bg-green-50 text-green-600 px-3 py-1 rounded hover:bg-green-100">
                            View
                        </a>
                        @if($isAdmin || $item->user_id === Auth::id())
                        <a href="{{ route('lost-items.edit', $item) }}"
                           class="text-sm bg-gray-50 text-gray-600 px-3 py-1 rounded hover:bg-gray-100">
                            Edit
                        </a>
                        <form method="POST" action="{{ route('lost-items.destroy', $item) }}"
                              onsubmit="return confirm('Delete this report?')">
                            @csrf @method('DELETE')
                            <button class="text-sm bg-red-50 text-red-600 px-3 py-1 rounded hover:bg-red-100">
                                Delete
                            </button>
                        </form>
                        @endif
                    </div>
                </div>
                @endforeach
            </div>
            <div class="mt-6">{{ $lostItems->links() }}</div>
        @endif
    </div>
</x-app-layout>
