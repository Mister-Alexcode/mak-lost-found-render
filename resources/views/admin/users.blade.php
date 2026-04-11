<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Admin — Users</h2>
    </x-slot>

    <div class="py-8 max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <table class="w-full text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Name</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Email</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Student ID</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Phone</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Points</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Joined</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($users as $user)
                    <tr>
                        <td class="px-4 py-3 font-medium">{{ $user->name }}</td>
                        <td class="px-4 py-3 text-gray-600">{{ $user->email }}</td>
                        <td class="px-4 py-3 text-gray-600">{{ $user->student_id ?? '—' }}</td>
                        <td class="px-4 py-3 text-gray-600">{{ $user->phone_number ?? '—' }}</td>
                        <td class="px-4 py-3 text-right font-bold text-purple-600">{{ $user->reward_points }}</td>
                        <td class="px-4 py-3 text-gray-500 text-xs">{{ $user->created_at->format('d M Y') }}</td>
                        <td class="px-4 py-3">
                            <a href="{{ route('messages.direct', $user) }}"
                               class="text-xs bg-blue-50 text-blue-600 px-2 py-1 rounded hover:bg-blue-100">
                                Message
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-4 py-8 text-center text-gray-500">No users yet.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-4">{{ $users->links() }}</div>
    </div>
</x-app-layout>
