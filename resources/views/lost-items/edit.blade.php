<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Edit Lost Item</h2>
    </x-slot>

    <div class="py-8 max-w-3xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white rounded-lg shadow p-6">

            <form method="POST" action="{{ route('lost-items.update', $lostItem) }}" enctype="multipart/form-data">
                @csrf @method('PUT')

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Item Name *</label>
                        <input type="text" name="item_name" value="{{ old('item_name', $lostItem->item_name) }}"
                               class="w-full border rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Category *</label>
                        <select name="category" class="w-full border rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            @foreach(['Electronics','Documents','Stationery','Clothing','Keys','Other'] as $cat)
                                <option value="{{ $cat }}" {{ old('category', $lostItem->category) == $cat ? 'selected' : '' }}>
                                    {{ $cat }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Color *</label>
                        <input type="text" name="color" value="{{ old('color', $lostItem->color) }}"
                               class="w-full border rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Brand</label>
                        <input type="text" name="brand" value="{{ old('brand', $lostItem->brand) }}"
                               class="w-full border rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Location Lost *</label>
                        <input type="text" name="location_lost" value="{{ old('location_lost', $lostItem->location_lost) }}"
                               class="w-full border rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Date Lost *</label>
                        <input type="date" name="date_lost" value="{{ old('date_lost', $lostItem->date_lost) }}"
                               max="{{ date('Y-m-d') }}"
                               class="w-full border rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                </div>

                <div class="mt-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Description *</label>
                    <textarea name="description" rows="3"
                              class="w-full border rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">{{ old('description', $lostItem->description) }}</textarea>
                </div>

                <x-map-picker :latitude="$lostItem->latitude" :longitude="$lostItem->longitude" />

                <div class="mt-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Reward Offer <span class="text-gray-400 font-normal">(optional)</span></label>
                    <input type="text" name="reward_offer" value="{{ old('reward_offer', $lostItem->reward_offer) }}"
                           class="w-full border rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                           placeholder="e.g. UGX 20,000 airtime, treat to lunch, etc.">
                </div>

                <div class="mt-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Photo</label>
                    @if($lostItem->photo)
                        <img src="{{ asset('storage/' . $lostItem->photo) }}"
                             class="h-24 rounded mb-2" alt="Current photo">
                    @endif
                    <input type="file" name="photo" accept="image/*"
                           class="w-full border rounded px-3 py-2">
                    <p class="text-xs text-gray-400 mt-1">Leave empty to keep current photo.</p>
                </div>

                <div class="mt-6 flex gap-3">
                    <button type="submit"
                            class="bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-700">
                        Update Report
                    </button>
                    <a href="{{ route('lost-items.index') }}"
                       class="bg-gray-200 text-gray-700 px-6 py-2 rounded hover:bg-gray-300">
                        Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>