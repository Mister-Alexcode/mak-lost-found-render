<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Notification Preferences</h2>
    </x-slot>

    <div class="py-8 max-w-3xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white rounded-lg shadow p-6">

            <p class="text-sm text-gray-600 mb-6">
                Choose how you'd like to receive notifications. In-app notifications are always enabled.
            </p>

            <form method="POST" action="{{ route('notification-settings.update') }}">
                @csrf @method('PATCH')

                <div class="space-y-4">

                    {{-- In-App (always on) --}}
                    <label class="flex items-start gap-4 p-4 bg-green-50 border border-green-200 rounded-lg">
                        <input type="checkbox" checked disabled
                               class="mt-0.5 rounded border-gray-300 text-green-600">
                        <div>
                            <p class="font-medium text-gray-800">In-App Notifications</p>
                            <p class="text-sm text-gray-500">Always enabled. You'll see notifications in the bell icon and notifications page.</p>
                        </div>
                        <span class="ml-auto text-xs bg-green-100 text-green-700 px-2 py-0.5 rounded-full whitespace-nowrap">Always on</span>
                    </label>

                    {{-- Email --}}
                    <label class="flex items-start gap-4 p-4 bg-white border border-gray-200 rounded-lg hover:bg-gray-50 cursor-pointer transition">
                        <input type="checkbox" name="email" value="1"
                               {{ !empty($prefs['email']) ? 'checked' : '' }}
                               class="mt-0.5 rounded border-gray-300 text-green-600 focus:ring-green-500">
                        <div>
                            <p class="font-medium text-gray-800">Email Notifications</p>
                            <p class="text-sm text-gray-500">Receive notifications at <strong>{{ Auth::user()->email }}</strong></p>
                            <p class="text-xs text-amber-700 bg-amber-50 border border-amber-200 rounded px-2 py-1 mt-2 inline-block">
                                Tip: if emails don't appear in your inbox, check your spam folder — or enable WhatsApp below for faster, more reliable delivery.
                            </p>
                        </div>
                    </label>

                    {{-- SMS --}}
                    <label class="flex items-start gap-4 p-4 bg-white border border-gray-200 rounded-lg hover:bg-gray-50 cursor-pointer transition">
                        <input type="checkbox" name="sms" value="1"
                               {{ !empty($prefs['sms']) ? 'checked' : '' }}
                               {{ Auth::user()->phone_number ? '' : 'disabled' }}
                               class="mt-0.5 rounded border-gray-300 text-green-600 focus:ring-green-500">
                        <div>
                            <p class="font-medium text-gray-800">SMS Notifications</p>
                            @if(Auth::user()->phone_number)
                                <p class="text-sm text-gray-500">Receive SMS at <strong>{{ Auth::user()->phone_number }}</strong></p>
                            @else
                                <p class="text-sm text-red-500">Add a phone number in your <a href="{{ route('profile.edit') }}" class="underline">profile</a> to enable SMS.</p>
                            @endif
                        </div>
                    </label>

                    {{-- WhatsApp --}}
                    <label class="flex items-start gap-4 p-4 bg-white border border-gray-200 rounded-lg hover:bg-gray-50 cursor-pointer transition">
                        <input type="checkbox" name="whatsapp" value="1"
                               {{ !empty($prefs['whatsapp']) ? 'checked' : '' }}
                               {{ Auth::user()->phone_number ? '' : 'disabled' }}
                               class="mt-0.5 rounded border-gray-300 text-green-600 focus:ring-green-500">
                        <div>
                            <p class="font-medium text-gray-800">WhatsApp Notifications</p>
                            @if(Auth::user()->phone_number)
                                <p class="text-sm text-gray-500">Receive WhatsApp messages at <strong>{{ Auth::user()->phone_number }}</strong></p>
                            @else
                                <p class="text-sm text-red-500">Add a phone number in your <a href="{{ route('profile.edit') }}" class="underline">profile</a> to enable WhatsApp.</p>
                            @endif
                        </div>
                    </label>

                </div>

                <div class="mt-6">
                    <button type="submit"
                            class="bg-green-600 text-white px-6 py-2 rounded-lg hover:bg-green-700 transition">
                        Save Preferences
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
