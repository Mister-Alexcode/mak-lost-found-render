<x-guest-layout>
    <form method="POST" action="{{ route('register') }}">
        @csrf

        <!-- Name -->
        <div>
            <x-input-label for="name">Full Name <span class="text-red-500">*</span></x-input-label>
            <x-text-input id="name" class="block mt-1 w-full" type="text" name="name" :value="old('name')" required autofocus autocomplete="name" />
            <x-input-error :messages="$errors->get('name')" class="mt-2" />
        </div>

        <!-- Email Address -->
        <div class="mt-4">
            <x-input-label for="email">Email Address <span class="text-red-500">*</span></x-input-label>
            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <!-- Phone Number (required) -->
        <div class="mt-4">
            <x-input-label for="phone_number">Phone Number (WhatsApp) <span class="text-red-500">*</span></x-input-label>
            <x-text-input id="phone_number" class="block mt-1 w-full" type="tel" name="phone_number"
                          :value="old('phone_number')" required
                          placeholder="e.g. 0701234567 or +256701234567" />
            <p class="text-xs text-gray-500 mt-1">Must be a valid Ugandan number that can receive WhatsApp messages.</p>
            <x-input-error :messages="$errors->get('phone_number')" class="mt-2" />
        </div>

        <!-- Student ID -->
        <div class="mt-4">
            <x-input-label for="student_id" :value="__('Student/Staff ID (optional)')" />
            <x-text-input id="student_id" class="block mt-1 w-full" type="text" name="student_id"
                          :value="old('student_id')" autocomplete="off"
                          placeholder="e.g. 22/U/1234" />
            <x-input-error :messages="$errors->get('student_id')" class="mt-2" />
        </div>

        <!-- Password -->
        <div class="mt-4">
            <x-input-label for="password">Password <span class="text-red-500">*</span></x-input-label>
            <x-text-input id="password" class="block mt-1 w-full"
                            type="password" name="password"
                            required autocomplete="new-password" />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Confirm Password -->
        <div class="mt-4">
            <x-input-label for="password_confirmation">Confirm Password <span class="text-red-500">*</span></x-input-label>
            <x-text-input id="password_confirmation" class="block mt-1 w-full"
                            type="password" name="password_confirmation" required autocomplete="new-password" />
            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
        </div>

        <!-- Verification Method -->
        <div class="mt-4">
            <x-input-label>Verify your account via <span class="text-red-500">*</span></x-input-label>
            <div class="mt-2 flex gap-4">
                <label class="flex items-center gap-2 cursor-pointer px-4 py-2 border rounded-lg hover:bg-gray-50 transition">
                    <input type="radio" name="verify_via" value="email" {{ old('verify_via', 'email') === 'email' ? 'checked' : '' }}
                           class="text-green-600 focus:ring-green-500">
                    <div>
                        <p class="text-sm font-medium">Email</p>
                        <p class="text-xs text-gray-500">Code sent to your email</p>
                    </div>
                </label>
                <label class="flex items-center gap-2 cursor-pointer px-4 py-2 border rounded-lg hover:bg-gray-50 transition">
                    <input type="radio" name="verify_via" value="whatsapp" {{ old('verify_via') === 'whatsapp' ? 'checked' : '' }}
                           class="text-green-600 focus:ring-green-500">
                    <div>
                        <p class="text-sm font-medium">WhatsApp</p>
                        <p class="text-xs text-gray-500">Code sent to your phone</p>
                    </div>
                </label>
            </div>
            <p class="mt-2 text-xs text-gray-500">
                WhatsApp delivers instantly. Emails may occasionally land in the spam folder.
            </p>
            <div class="mt-2 p-3 bg-green-50 border border-green-200 rounded-lg text-xs text-green-800">
                <p class="font-semibold mb-1">To receive WhatsApp OTP codes, first send:</p>
                <p class="font-mono bg-white px-2 py-1 rounded border border-green-200 inline-block">join nothing-gun</p>
                <p class="mt-1">to <strong>+1 415 523 8886</strong> on WhatsApp (one-time setup).</p>
            </div>
            <x-input-error :messages="$errors->get('verify_via')" class="mt-2" />
        </div>

        <div class="flex items-center justify-end mt-4">
            <a class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500" href="{{ route('login') }}">
                {{ __('Already registered?') }}
            </a>

            <x-primary-button class="ms-4">
                {{ __('Register') }}
            </x-primary-button>
        </div>
    </form>
</x-guest-layout>
