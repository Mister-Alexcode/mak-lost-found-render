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
               class="text-xs text-green-600 hover:underline">View Item</a>
        </div>

        @if(session('error'))
            <div class="bg-red-50 border border-red-200 text-red-700 text-sm rounded-lg p-3 mb-4">
                {{ session('error') }}
            </div>
        @endif

        @if($isHighValue && !$isAdmin)
        <div class="bg-amber-50 border border-amber-300 rounded-lg p-4 mb-4 flex items-start gap-3">
            <svg class="w-6 h-6 text-amber-600 shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
            <div>
                <p class="font-semibold text-amber-800 text-sm">High-Value Item — Visit the Admin Office</p>
                @if($isOwner)
                <p class="text-sm text-amber-700 mt-1">Please go to the admin office with proof of ownership (receipts, photos, ID matching the item, etc.). The admin will verify and return your item. There is no direct chat with the finder for high-value items.</p>
                @else
                <p class="text-sm text-amber-700 mt-1">Please deliver the item you found to the admin office at your earliest convenience. The admin will verify ownership with the claimant and handle the handover. There is no direct chat with the claimant for high-value items.</p>
                @endif
            </div>
        </div>
        @elseif($isAdmin)
        <div class="bg-blue-50 border border-blue-200 rounded-lg p-3 mb-4 flex items-start gap-2">
            <svg class="w-5 h-5 text-blue-600 shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm-.25-10.5a.75.75 0 011.5 0v4.75a.75.75 0 01-1.5 0V7.5zM10 14.5a1 1 0 100-2 1 1 0 000 2z" clip-rule="evenodd"/></svg>
            <div>
                <p class="font-medium text-blue-800 text-sm">Observer Mode</p>
                <p class="text-xs text-blue-700 mt-0.5">You are observing this conversation. To speak with either party, open a direct message from the messages page.</p>
            </div>
        </div>
        @endif

        @if(!($isHighValue && !$isAdmin))
        {{-- Messages --}}
        <div class="bg-white rounded-lg shadow p-4 mb-4 space-y-3 min-h-60 max-h-96 overflow-y-auto" id="messages-box">
            @forelse($messages as $msg)
            <div class="flex {{ $msg->sender_id === auth()->id() ? 'justify-end' : 'justify-start' }}">
                <div class="max-w-xs lg:max-w-md px-4 py-2 rounded-lg text-sm
                    {{ $msg->sender_id === auth()->id()
                        ? 'bg-green-600 text-white'
                        : 'bg-gray-100 text-gray-800' }}">
                    @if($isAdmin && $msg->sender_id !== auth()->id())
                        <p class="text-xs font-semibold text-gray-500 mb-0.5">{{ $msg->sender->name }}</p>
                    @endif
                    <p>{{ $msg->content }}</p>
                    <p class="text-xs mt-1 {{ $msg->sender_id === auth()->id() ? 'text-green-200' : 'text-gray-400' }}">
                        {{ $msg->created_at->format('H:i') }}
                    </p>
                </div>
            </div>
            @empty
            <p class="text-center text-gray-400 text-sm py-8">No messages yet.</p>
            @endforelse
        </div>
        @endif

        @if(!$isAdmin && !$isHighValue)
        {{-- Send Message --}}
        <form method="POST" action="{{ route('messages.store', $match) }}">
            @csrf
            @if($errors->any())
                <p class="text-red-600 text-sm mb-2">{{ $errors->first() }}</p>
            @endif
            <div class="flex gap-2">
                <input type="text" name="content"
                       class="flex-1 border rounded-lg px-3 py-2 focus:ring-2 focus:ring-green-500 text-sm"
                       placeholder="Type a message..." autocomplete="off" required maxlength="1000">
                <button type="submit"
                        class="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700 text-sm">
                    Send
                </button>
            </div>
        </form>
        @endif

        <script>
            const box = document.getElementById('messages-box');
            if (box) box.scrollTop = box.scrollHeight;
        </script>
    </div>
</x-app-layout>
