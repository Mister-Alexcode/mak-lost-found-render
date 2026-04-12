<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Report a Lost Item</h2>
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

            <form method="POST" action="{{ route('lost-items.store') }}" enctype="multipart/form-data">
                @csrf

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Item Name *</label>
                        <input type="text" name="item_name" value="{{ old('item_name') }}"
                               class="w-full border rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                               placeholder="e.g. iPhone 13, Student ID Card">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Category *</label>
                        <select name="category" class="w-full border rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="">-- Select Category --</option>
                            <option value="Electronics" {{ old('category') == 'Electronics' ? 'selected' : '' }}>Electronics</option>
                            <option value="Documents" {{ old('category') == 'Documents' ? 'selected' : '' }}>Documents (ID, Cards)</option>
                            <option value="Stationery" {{ old('category') == 'Stationery' ? 'selected' : '' }}>Stationery</option>
                            <option value="Clothing" {{ old('category') == 'Clothing' ? 'selected' : '' }}>Clothing & Accessories</option>
                            <option value="Keys" {{ old('category') == 'Keys' ? 'selected' : '' }}>Keys & Wallets</option>
                            <option value="Other" {{ old('category') == 'Other' ? 'selected' : '' }}>Other</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Color *</label>
                        <input type="text" name="color" value="{{ old('color') }}"
                               class="w-full border rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                               placeholder="e.g. Black, Blue, Red">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Brand</label>
                        <input type="text" name="brand" value="{{ old('brand') }}"
                               class="w-full border rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                               placeholder="e.g. Samsung, Lenovo (optional)">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Location Lost *</label>
                        <input type="text" name="location_lost" value="{{ old('location_lost') }}"
                               class="w-full border rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                               placeholder="e.g. CoCIS Block A, Main Library">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Date Lost *</label>
                        <input type="date" name="date_lost" value="{{ old('date_lost') }}"
                               max="{{ date('Y-m-d') }}"
                               class="w-full border rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>

                </div>

                <div class="mt-4" x-data="{ highValue: {{ old('is_high_value') ? 'true' : 'false' }} }">
                    <label class="flex items-start gap-3 p-4 bg-amber-50 border border-amber-200 rounded-lg cursor-pointer hover:bg-amber-100 transition">
                        <input type="checkbox" name="is_high_value" value="1"
                               x-model="highValue"
                               {{ old('is_high_value') ? 'checked' : '' }}
                               class="mt-0.5 rounded border-gray-300 text-amber-600 focus:ring-amber-500">
                        <div>
                            <p class="font-medium text-gray-800">High-Value Item</p>
                            <p class="text-sm text-gray-500">Mark this if your item is valuable (laptop, phone, wallet with money, etc.). High-value items require admin verification before return — the finder must bring it to the admin office.</p>
                        </div>
                    </label>
                </div>

                <div class="mt-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Description *</label>
                    <textarea name="description" rows="3"
                              class="w-full border rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                              placeholder="Describe the item in detail — any unique features, serial number, stickers, etc.">{{ old('description') }}</textarea>
                </div>

                <x-map-picker />

                <div class="mt-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Reward Offer <span class="text-gray-400 font-normal">(optional)</span></label>
                    <input type="text" name="reward_offer" value="{{ old('reward_offer') }}"
                           class="w-full border rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                           placeholder="e.g. UGX 20,000 airtime, treat to lunch, etc.">
                    <p class="text-xs text-gray-400 mt-1">A thank-you gesture to whoever finds and returns your item. Separate from reward points.</p>
                </div>

                <div class="mt-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Photo</label>
                    <input type="file" name="photo" accept="image/*"
                           class="w-full border rounded px-3 py-2">
                    <p class="text-xs text-gray-400 mt-1">Optional. JPG or PNG, max 2MB.</p>
                </div>

                <div class="mt-6 flex gap-3">
                    <button type="submit"
                            class="bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-700">
                        Submit Report
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