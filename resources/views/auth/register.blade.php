<x-guest-layout>
    <form method="POST" action="{{ route('register') }}">
        @csrf

        @if(isset($ref) && $ref)
            <input type="hidden" name="ref" value="{{ $ref }}">
            <div class="mb-4 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg text-sm">
                @if(isset($refMemberName) && $refMemberName)
                    Anda mendaftar melalui referral <strong>{{ $refMemberName }}</strong>
                @else
                    Anda diundang oleh affiliator dengan kode: <strong>{{ $ref }}</strong>
                @endif
            </div>
        @endif

        <!-- Name -->
        <div>
            <x-input-label for="name" :value="__('Name')" />
            <x-text-input id="name" class="block mt-1 w-full" type="text" name="name" :value="old('name')" required autofocus autocomplete="name" />
            <x-input-error :messages="$errors->get('name')" class="mt-2" />
        </div>

        <!-- Email Address -->
        <div class="mt-4">
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <!-- Nomor WhatsApp -->
        <div class="mt-4">
            <x-input-label for="whatsapp_number" value="Nomor WhatsApp" />
            <x-text-input id="whatsapp_number" class="block mt-1 w-full" type="tel" name="whatsapp_number" :value="old('whatsapp_number')" placeholder="Contoh: 08123456789" autocomplete="tel" />
            <x-input-error :messages="$errors->get('whatsapp_number')" class="mt-2" />
        </div>

        <!-- Password -->
        <div class="mt-4">
            <x-input-label for="password" :value="__('Password')" />

            <x-text-input id="password" class="block mt-1 w-full"
                            type="password"
                            name="password"
                            required autocomplete="new-password" />

            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Confirm Password -->
        <div class="mt-4">
            <x-input-label for="password_confirmation" :value="__('Confirm Password')" />

            <x-text-input id="password_confirmation" class="block mt-1 w-full"
                            type="password"
                            name="password_confirmation" required autocomplete="new-password" />

            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
        </div>

        <div class="flex items-center justify-end mt-4">
            <a class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500" href="{{ route('login') }}">
                {{ __('Already registered?') }}
            </a>

            <x-primary-button class="ms-4">
                {{ __('Register') }}
            </x-primary-button>
        </div>
    </form>
</x-guest-layout>
