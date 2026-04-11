@if(session('success') || session('error') || $errors->any())
<div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)"
     x-transition:leave="transition ease-in duration-300"
     x-transition:leave-start="opacity-100 translate-y-0"
     x-transition:leave-end="opacity-0 -translate-y-2"
     class="fixed top-4 right-4 z-50 max-w-sm w-full">

    @if(session('success'))
    <div class="bg-white border-l-4 border-green-500 rounded-lg shadow-lg p-4 flex items-start gap-3">
        <div class="flex-shrink-0 w-6 h-6 bg-green-100 rounded-full flex items-center justify-center">
            <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
            </svg>
        </div>
        <div class="flex-1">
            <p class="text-sm font-medium text-gray-800">{{ session('success') }}</p>
        </div>
        <button @click="show = false" class="text-gray-400 hover:text-gray-600">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
        </button>
    </div>
    @endif

    @if(session('error'))
    <div class="bg-white border-l-4 border-red-500 rounded-lg shadow-lg p-4 flex items-start gap-3">
        <div class="flex-shrink-0 w-6 h-6 bg-red-100 rounded-full flex items-center justify-center">
            <svg class="w-4 h-4 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
        </div>
        <div class="flex-1">
            <p class="text-sm font-medium text-gray-800">{{ session('error') }}</p>
        </div>
        <button @click="show = false" class="text-gray-400 hover:text-gray-600">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
        </button>
    </div>
    @endif

    @if($errors->any() && !session('success'))
    <div class="bg-white border-l-4 border-red-500 rounded-lg shadow-lg p-4 flex items-start gap-3">
        <div class="flex-shrink-0 w-6 h-6 bg-red-100 rounded-full flex items-center justify-center">
            <svg class="w-4 h-4 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
        </div>
        <div class="flex-1">
            <p class="text-sm font-medium text-gray-800">{{ $errors->first() }}</p>
        </div>
        <button @click="show = false" class="text-gray-400 hover:text-gray-600">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
        </button>
    </div>
    @endif
</div>
@endif
