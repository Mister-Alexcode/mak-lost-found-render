<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Chat with {{ $user->name }}
            @if($user->isAdmin())
                <span class="ml-2 text-sm bg-purple-100 text-purple-700 px-2 py-0.5 rounded">Admin</span>
            @endif
        </h2>
    </x-slot>

    <div class="py-8 max-w-3xl mx-auto sm:px-6 lg:px-8">

        @isset($contextItem)
        @if($contextItem)
        <div class="bg-blue-50 border border-blue-200 rounded-lg p-3 mb-4 text-sm text-blue-800">
            <p class="font-medium">Re: {{ $contextItem->item_name }} ({{ $contextItem->tracking_id }})</p>
            <p class="text-xs mt-1">Peer chat for a non-high-value item. Coordinate the return directly. For valuable items, file a claim so an admin can mediate.</p>
        </div>
        @endif
        @endisset

        <div class="bg-gray-50 border rounded-lg p-3 mb-4 text-sm text-gray-600 flex items-center gap-3">
            <div class="w-8 h-8 rounded-full bg-purple-100 text-purple-700 flex items-center justify-center text-sm font-bold">
                {{ strtoupper(substr($user->name, 0, 1)) }}
            </div>
            <div>
                <span class="font-medium text-gray-800">{{ $user->name }}</span>
                @if(!$user->isAdmin())
                <span class="text-gray-400 ml-2">{{ $user->email }}</span>
                @else
                <span class="text-xs text-purple-500 ml-2">System Administrator</span>
                @endif
            </div>
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
            <p class="text-center text-gray-400 text-sm py-8">No messages yet. Start the conversation.</p>
            @endforelse
        </div>

        {{-- Send Message --}}
        <form method="POST" action="{{ route('messages.direct.store', $user) }}">
            @csrf
            @isset($contextItem)
            @if($contextItem)
            <input type="hidden" name="about" value="{{ $contextItem->id }}">
            @endif
            @endisset
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
