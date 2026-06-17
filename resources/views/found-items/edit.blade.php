<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Edit Found Item</h2>
    </x-slot>

    <div class="py-8 max-w-3xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white rounded-lg shadow p-6">

            @if($errors->any())
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                    <ul class="list-disc list-inside">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('found-items.update', $foundItem) }}" enctype="multipart/form-data">
                @csrf @method('PATCH')

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Item Name *</label>
                        <input type="text" name="item_name" value="{{ old('item_name', $foundItem->item_name) }}"
                               class="w-full border rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-green-500">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Category *</label>
                        <select name="category" class="w-full border rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-green-500">
                            <option value="">-- Select Category --</option>
                            @foreach(['Electronics','Documents','Stationery','Clothing','Keys','Other'] as $cat)
                            <option value="{{ $cat }}" {{ old('category', $foundItem->category) == $cat ? 'selected' : '' }}>
                                {{ $cat }}
                            </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Color *</label>
                        <input type="text" name="color" value="{{ old('color', $foundItem->color) }}"
                               class="w-full border rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-green-500">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Brand</label>
                        <input type="text" name="brand" value="{{ old('brand', $foundItem->brand) }}"
                               class="w-full border rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-green-500">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Location Found *</label>
                        <input type="text" name="location_found" value="{{ old('location_found', $foundItem->location_found) }}"
                               class="w-full border rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-green-500">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Date Found *</label>
                        <input type="date" name="date_found" value="{{ old('date_found', $foundItem->date_found) }}"
                               max="{{ date('Y-m-d') }}"
                               class="w-full border rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-green-500">
                    </div>

                </div>

                <x-map-picker :latitude="$foundItem->latitude" :longitude="$foundItem->longitude" />

                <div class="mt-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Description *</label>
                    <textarea name="description" rows="3"
                              class="w-full border rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-green-500">{{ old('description', $foundItem->description) }}</textarea>
                </div>

                <div class="mt-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Photo</label>
                    @if($foundItem->photo)
                        <img src="{{ $foundItem->photo_url }}"
                             class="h-24 rounded mb-2" alt="Current photo">
                        <p class="text-xs text-gray-400 mb-1">Current photo shown above. Upload new to replace.</p>
                    @endif
                    <input type="file" name="photo" accept="image/*"
                           class="w-full border rounded px-3 py-2">
                </div>

                <div class="mt-6 flex gap-3">
                    <button type="submit"
                            class="bg-green-600 text-white px-6 py-2 rounded hover:bg-green-700">
                        Save Changes
                    </button>
                    <a href="{{ route('found-items.show', $foundItem) }}"
                       class="bg-gray-200 text-gray-700 px-6 py-2 rounded hover:bg-gray-300">
                        Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
