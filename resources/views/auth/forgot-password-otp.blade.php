<x-guest-layout>
    <div class="mb-4 text-sm text-gray-600">
        {{ __('Forgot your password? No problem. Enter your email or phone number and we\'ll send you a verification code to reset it.') }}
    </div>

    <!-- Session Status -->
    @if(session('status'))
    <div class="mb-4 text-sm text-green-600 bg-green-50 border border-green-200 rounded p-3">
        {{ session('status') }}
    </div>
    @endif

    <form method="POST" action="{{ route('password.otp.send') }}">
        @csrf

        <!-- Channel Selection -->
        <div class="mb-4">
            <x-input-label :value="__('Reset via')" />
            <div class="mt-2 flex gap-3" x-data="{ channel: '{{ old('channel', 'email') }}' }">
                <label class="flex-1 cursor-pointer">
                    <input type="radio" name="channel" value="email" x-model="channel" class="hidden peer">
                    <div class="peer-checked:bg-green-50 peer-checked:border-green-500 peer-checked:text-green-700 border-2 rounded-lg p-3 text-center transition hover:bg-gray-50">
                        <p class="font-medium text-sm">Email</p>
                        <p class="text-xs text-gray-500">Code to your email</p>
                    </div>
                </label>
                <label class="flex-1 cursor-pointer">
                    <input type="radio" name="channel" value="whatsapp" x-model="channel" class="hidden peer">
                    <div class="peer-checked:bg-green-50 peer-checked:border-green-500 peer-checked:text-green-700 border-2 rounded-lg p-3 text-center transition hover:bg-gray-50">
                        <p class="font-medium text-sm">WhatsApp</p>
                        <p class="text-xs text-gray-500">Code to your phone</p>
                    </div>
                </label>
            </div>
            <x-input-error :messages="$errors->get('channel')" class="mt-2" />
        </div>

        <!-- Identifier -->
        <div>
            <x-input-label for="identifier" :value="__('Email or Phone Number')" />
            <x-text-input id="identifier" class="block mt-1 w-full" type="text" name="identifier"
                          :value="old('identifier')" required autofocus
                          placeholder="your@email.com or 0701234567" />
            <x-input-error :messages="$errors->get('identifier')" class="mt-2" />
        </div>

        <div class="flex items-center justify-between mt-4">
            <a href="{{ route('login') }}" class="text-sm text-gray-600 hover:underline">
                Back to Login
            </a>
            <x-primary-button>
                {{ __('Send Verification Code') }}
            </x-primary-button>
        </div>
    </form>

    <div class="mt-4 pt-4 border-t text-center">
        <a href="{{ route('password.request') }}" class="text-sm text-gray-500 hover:underline">
            Use email reset link instead
        </a>
    </div>
</x-guest-layout>
