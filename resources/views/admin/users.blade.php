<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Admin — Users</h2>
    </x-slot>

    <div class="py-8 max-w-7xl mx-auto sm:px-6 lg:px-8">
        <x-section-header
            icon="users"
            title="Manage Users"
            subtitle="Edit details, block, unblock, or remove user accounts" />

        @if(session('success'))
            <div class="mb-4 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg text-sm">
                {{ session('success') }}
            </div>
        @endif
        @if(session('error'))
            <div class="mb-4 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg text-sm">
                {{ session('error') }}
            </div>
        @endif

        <div class="bg-white rounded-xl shadow overflow-hidden">
            <table class="w-full text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Name</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Email</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Student ID</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Phone</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Points</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Joined</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($users as $user)
                    <tr class="{{ $user->is_blocked ? 'bg-red-50/40' : '' }}">
                        <td class="px-4 py-3 font-medium">{{ $user->name }}</td>
                        <td class="px-4 py-3 text-gray-600">{{ $user->email }}</td>
                        <td class="px-4 py-3 text-gray-600">{{ $user->student_id ?? '—' }}</td>
                        <td class="px-4 py-3 text-gray-600">{{ $user->phone_number ?? '—' }}</td>
                        <td class="px-4 py-3 text-right font-bold text-purple-600">{{ $user->reward_points }}</td>
                        <td class="px-4 py-3">
                            @if($user->is_blocked)
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 bg-red-100 text-red-700 rounded-full text-xs font-medium">
                                    <span class="w-1.5 h-1.5 bg-red-500 rounded-full"></span> Blocked
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 bg-green-100 text-green-700 rounded-full text-xs font-medium">
                                    <span class="w-1.5 h-1.5 bg-green-500 rounded-full"></span> Active
                                </span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-gray-500 text-xs">{{ $user->created_at->format('d M Y') }}</td>
                        <td class="px-4 py-3">
                            <div class="flex flex-wrap gap-1">
                                <a href="{{ route('admin.users.edit', $user) }}"
                                   class="text-xs bg-blue-50 text-blue-700 px-2 py-1 rounded hover:bg-blue-100 font-medium">
                                    Edit
                                </a>
                                <a href="{{ route('messages.direct', $user) }}"
                                   class="text-xs bg-indigo-50 text-indigo-700 px-2 py-1 rounded hover:bg-indigo-100 font-medium">
                                    Message
                                </a>
                                @if($user->is_blocked)
                                    <form method="POST" action="{{ route('admin.users.unblock', $user) }}" class="inline">
                                        @csrf
                                        <button class="text-xs bg-green-50 text-green-700 px-2 py-1 rounded hover:bg-green-100 font-medium">
                                            Unblock
                                        </button>
                                    </form>
                                @else
                                    <form method="POST" action="{{ route('admin.users.block', $user) }}" class="inline">
                                        @csrf
                                        <button class="text-xs bg-yellow-50 text-yellow-700 px-2 py-1 rounded hover:bg-yellow-100 font-medium">
                                            Block
                                        </button>
                                    </form>
                                @endif
                                <form method="POST" action="{{ route('admin.users.destroy', $user) }}" class="inline"
                                      onsubmit="return confirm('Delete {{ $user->name }}? This cannot be undone.');">
                                    @csrf
                                    @method('DELETE')
                                    <button class="text-xs bg-red-50 text-red-700 px-2 py-1 rounded hover:bg-red-100 font-medium">
                                        Delete
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="px-4 py-8 text-center text-gray-500">No users yet.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-4">{{ $users->links() }}</div>
    </div>
</x-app-layout>
