<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Report a Found Item</h2>
    </x-slot>

    <div class="py-8 max-w-3xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white rounded-lg shadow p-6">

            <div class="bg-green-50 border border-green-200 rounded p-3 mb-5 text-sm text-green-800">
                Thank you for helping! Reporting a found item earns you <strong>10 reward points</strong>.
            </div>

            @if(!empty($prefill))
            <div class="bg-blue-50 border border-blue-200 rounded p-3 mb-5 text-sm text-blue-800">
                Some fields have been pre-filled from the lost item report. Please update the location and date to where and when <strong>you</strong> found it.
            </div>
            @endif

            @if($errors->any())
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                    <ul class="list-disc list-inside">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('found-items.store') }}" enctype="multipart/form-data">
                @csrf
                @if(request('from_lost'))
                <input type="hidden" name="from_lost_id" value="{{ request('from_lost') }}">
                @endif

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Item Name *</label>
                        <input type="text" name="item_name" value="{{ old('item_name', $prefill['item_name'] ?? '') }}"
                               class="w-full border rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-green-500"
                               placeholder="e.g. iPhone 13, Student ID Card">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Category *</label>
                        <select name="category" class="w-full border rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-green-500">
                            @php $cat = old('category', $prefill['category'] ?? ''); @endphp
                            <option value="">-- Select Category --</option>
                            <option value="Electronics" {{ $cat == 'Electronics' ? 'selected' : '' }}>Electronics</option>
                            <option value="Documents" {{ $cat == 'Documents' ? 'selected' : '' }}>Documents (ID, Cards)</option>
                            <option value="Stationery" {{ $cat == 'Stationery' ? 'selected' : '' }}>Stationery</option>
                            <option value="Clothing" {{ $cat == 'Clothing' ? 'selected' : '' }}>Clothing & Accessories</option>
                            <option value="Keys" {{ $cat == 'Keys' ? 'selected' : '' }}>Keys & Wallets</option>
                            <option value="Other" {{ $cat == 'Other' ? 'selected' : '' }}>Other</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Color *</label>
                        <input type="text" name="color" value="{{ old('color', $prefill['color'] ?? '') }}"
                               class="w-full border rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-green-500"
                               placeholder="e.g. Black, Blue, Red">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Brand</label>
                        <input type="text" name="brand" value="{{ old('brand', $prefill['brand'] ?? '') }}"
                               class="w-full border rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-green-500"
                               placeholder="e.g. Samsung, Lenovo (optional)">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Location Found *</label>
                        <input type="text" name="location_found" value="{{ old('location_found') }}"
                               class="w-full border rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-green-500"
                               placeholder="e.g. CoCIS Block A, Main Library">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Date Found *</label>
                        <input type="date" name="date_found" value="{{ old('date_found') }}"
                               max="{{ date('Y-m-d') }}"
                               class="w-full border rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-green-500">
                    </div>

                </div>

                <x-map-picker />

                <div class="mt-4" x-data="{ highValue: {{ old('is_high_value') ? 'true' : 'false' }} }">
                    <label class="flex items-start gap-3 p-4 bg-amber-50 border border-amber-200 rounded-lg cursor-pointer hover:bg-amber-100 transition">
                        <input type="checkbox" name="is_high_value" value="1"
                               x-model="highValue"
                               {{ old('is_high_value') ? 'checked' : '' }}
                               class="mt-0.5 rounded border-gray-300 text-amber-600 focus:ring-amber-500">
                        <div>
                            <p class="font-medium text-gray-800">High-Value Item</p>
                            <p class="text-sm text-gray-500">Mark this if the item appears valuable (laptop, phone, wallet, etc.). High-value items must be brought to the admin office for verified handover.</p>
                        </div>
                    </label>
                </div>

                <div class="mt-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Description *</label>
                    <textarea name="description" rows="3"
                              class="w-full border rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-green-500"
                              placeholder="Describe the item in detail — any unique features, serial number, stickers, damage, etc.">{{ old('description') }}</textarea>
                </div>

                <div class="mt-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Photo *</label>
                    <input type="file" name="photo" accept="image/*"
                           class="w-full border rounded px-3 py-2">
                    <p class="text-xs text-gray-400 mt-1">Required for found items. JPG or PNG, max 2MB.</p>
                </div>

                <div class="mt-6 flex gap-3">
                    <button type="submit"
                            class="bg-green-600 text-white px-6 py-2 rounded hover:bg-green-700">
                        Submit Report
                    </button>
                    <a href="{{ route('found-items.index') }}"
                       class="bg-gray-200 text-gray-700 px-6 py-2 rounded hover:bg-gray-300">
                        Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
