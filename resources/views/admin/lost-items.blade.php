<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Admin — Lost Items</h2>
    </x-slot>

    <div class="py-8 max-w-7xl mx-auto sm:px-6 lg:px-8">

        <div class="bg-white rounded-lg shadow overflow-hidden">
            <table class="w-full text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tracking ID</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Item</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Reporter</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Category</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Location</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Update Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($items as $item)
                    <tr>
                        <td class="px-4 py-3 font-mono text-xs text-blue-600">{{ $item->tracking_id }}</td>
                        <td class="px-4 py-3 font-medium">{{ $item->item_name }}</td>
                        <td class="px-4 py-3">{{ $item->user->name }}</td>
                        <td class="px-4 py-3">{{ $item->category }}</td>
                        <td class="px-4 py-3 text-xs">{{ $item->location_lost }}</td>
                        <td class="px-4 py-3">
                            <span class="px-2 py-0.5 rounded text-xs
                                {{ $item->status === 'active' ? 'bg-yellow-100 text-yellow-700' : 'bg-green-100 text-green-700' }}">
                                {{ ucfirst($item->status) }}
                            </span>
                        </td>
                        <td class="px-4 py-3">
                            <form method="POST" action="{{ route('admin.items.status', ['type' => 'lost', 'id' => $item->id]) }}"
                                  class="flex gap-1">
                                @csrf
                                <select name="status" class="text-xs border rounded px-1 py-1">
                                    @foreach(['active','returned','expired','donated'] as $s)
                                    <option value="{{ $s }}" {{ $item->status === $s ? 'selected' : '' }}>
                                        {{ ucfirst($s) }}
                                    </option>
                                    @endforeach
                                </select>
                                <button class="text-xs bg-gray-600 text-white px-2 py-1 rounded hover:bg-gray-700">
                                    Set
                                </button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-4 py-8 text-center text-gray-500">No lost items yet.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-4">{{ $items->links() }}</div>
    </div>
</x-app-layout>
