<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Edit User</h2>
    </x-slot>

    <div class="py-8 max-w-3xl mx-auto sm:px-6 lg:px-8">
        <x-section-header
            icon="users"
            title="Edit {{ $user->name }}"
            subtitle="Update details, adjust points, or manage account status" />

        @if(session('error'))
            <div class="mb-4 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg text-sm">
                {{ session('error') }}
            </div>
        @endif

        <div class="bg-white rounded-xl shadow p-6">
            <form method="POST" action="{{ route('admin.users.update', $user) }}" class="space-y-5">
                @csrf
                @method('PUT')

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Full Name</label>
                    <input type="text" name="name" value="{{ old('name', $user->name) }}" required
                           class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    @error('name')<p class="text-red-600 text-xs mt-1">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                    <input type="email" name="email" value="{{ old('email', $user->email) }}" required
                           class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    @error('email')<p class="text-red-600 text-xs mt-1">{{ $message }}</p>@enderror
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Phone Number</label>
                        <input type="text" name="phone_number" value="{{ old('phone_number', $user->phone_number) }}"
                               class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Student ID</label>
                        <input type="text" name="student_id" value="{{ old('student_id', $user->student_id) }}"
                               class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Reward Points</label>
                    <input type="number" name="reward_points" min="0" value="{{ old('reward_points', $user->reward_points) }}" required
                           class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    @error('reward_points')<p class="text-red-600 text-xs mt-1">{{ $message }}</p>@enderror
                </div>

                <div class="flex items-center justify-between pt-4 border-t">
                    <a href="{{ route('admin.users') }}" class="text-sm text-gray-600 hover:text-gray-900">← Cancel</a>
                    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-5 py-2 rounded-lg font-medium shadow-sm">
                        Save Changes
                    </button>
                </div>
            </form>
        </div>

        <div class="mt-6 bg-white rounded-xl shadow p-6">
            <h3 class="font-bold text-gray-800 mb-3">Account Status</h3>
            <div class="flex items-center justify-between">
                <div>
                    @if($user->is_blocked)
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 bg-red-100 text-red-700 rounded-full text-sm font-medium">
                            <span class="w-1.5 h-1.5 bg-red-500 rounded-full"></span>
                            Blocked
                            @if($user->blocked_at)
                                <span class="text-xs text-red-500 ml-1">since {{ $user->blocked_at->diffForHumans() }}</span>
                            @endif
                        </span>
                    @else
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 bg-green-100 text-green-700 rounded-full text-sm font-medium">
                            <span class="w-1.5 h-1.5 bg-green-500 rounded-full"></span>
                            Active
                        </span>
                    @endif
                </div>
                <div class="flex gap-2">
                    @if($user->is_blocked)
                        <form method="POST" action="{{ route('admin.users.unblock', $user) }}">
                            @csrf
                            <button class="text-sm bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg">Unblock</button>
                        </form>
                    @else
                        <form method="POST" action="{{ route('admin.users.block', $user) }}">
                            @csrf
                            <button class="text-sm bg-yellow-500 hover:bg-yellow-600 text-white px-4 py-2 rounded-lg">Block User</button>
                        </form>
                    @endif
                    <form method="POST" action="{{ route('admin.users.destroy', $user) }}"
                          onsubmit="return confirm('Permanently delete {{ $user->name }}? This cannot be undone.');">
                        @csrf
                        @method('DELETE')
                        <button class="text-sm bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg">Delete User</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
