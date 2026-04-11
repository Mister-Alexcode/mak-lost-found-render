<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Chat with {{ $otherUser->name }}
        </h2>
    </x-slot>

    <div class="py-8 max-w-3xl mx-auto sm:px-6 lg:px-8">

        <div class="bg-gray-50 border rounded-lg p-3 mb-4 text-sm text-gray-600 flex justify-between items-center">
            <div>
                Re: <strong>{{ $match->lostItem->item_name }}</strong>
                ({{ $match->confidence_score }}% match)
            </div>
            <a href="{{ route('lost-items.show', $match->lostItem) }}"
               class="text-xs text-blue-600 hover:underline">View Item</a>
        </div>

        {{-- Messages --}}
        <div class="bg-white rounded-lg shadow p-4 mb-4 space-y-3 min-h-60 max-h-96 overflow-y-auto" id="messages-box">
            @forelse($messages as $msg)
            <div class="flex {{ $msg->sender_id === auth()->id() ? 'justify-end' : 'justify-start' }}">
                <div class="max-w-xs lg:max-w-md px-4 py-2 rounded-lg text-sm
                    {{ $msg->sender_id === auth()->id()
                        ? 'bg-blue-600 text-white'
                        : 'bg-gray-100 text-gray-800' }}">
                    <p>{{ $msg->content }}</p>
                    <p class="text-xs mt-1 {{ $msg->sender_id === auth()->id() ? 'text-blue-200' : 'text-gray-400' }}">
                        {{ $msg->created_at->format('H:i') }}
                    </p>
                </div>
            </div>
            @empty
            <p class="text-center text-gray-400 text-sm py-8">No messages yet. Say hello!</p>
            @endforelse
        </div>

        {{-- Send Message --}}
        <form method="POST" action="{{ route('messages.store', $match) }}">
            @csrf
            @if($errors->any())
                <p class="text-red-600 text-sm mb-2">{{ $errors->first() }}</p>
            @endif
            <div class="flex gap-2">
                <input type="text" name="content"
                       class="flex-1 border rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 text-sm"
                       placeholder="Type a message..." autocomplete="off" required maxlength="1000">
                <button type="submit"
                        class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 text-sm">
                    Send
                </button>
            </div>
        </form>

        <script>
            const box = document.getElementById('messages-box');
            if (box) box.scrollTop = box.scrollHeight;
        </script>
    </div>
</x-app-layout>
