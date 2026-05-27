<x-guest-layout>
    <form method="POST" action="{{ route('register') }}">
        @csrf

        @if(isset($ref) && $ref)
            <input type="hidden" name="ref" value="{{ $ref }}">
            <div style="margin-bottom: 16px; background-color: #1a3b2a; border: 1px solid #166534; color: #86efac; padding: 12px 16px; border-radius: 8px; font-size: 0.875rem;">
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
        <div style="margin-top: 16px;">
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <!-- Nomor WhatsApp -->
        <div style="margin-top: 16px;">
            <x-input-label for="whatsapp_number" value="Nomor WhatsApp" />
            <x-text-input id="whatsapp_number" class="block mt-1 w-full" type="tel" name="whatsapp_number" :value="old('whatsapp_number')" placeholder="Contoh: 08123456789" autocomplete="tel" />
            <x-input-error :messages="$errors->get('whatsapp_number')" class="mt-2" />
        </div>

        <!-- Password -->
        <div style="margin-top: 16px;">
            <x-input-label for="password" :value="__('Password')" />
            <x-text-input id="password" class="block mt-1 w-full"
                            type="password"
                            name="password"
                            required autocomplete="new-password" />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Confirm Password -->
        <div style="margin-top: 16px;">
            <x-input-label for="password_confirmation" :value="__('Confirm Password')" />
            <x-text-input id="password_confirmation" class="block mt-1 w-full"
                            type="password"
                            name="password_confirmation" required autocomplete="new-password" />
            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
        </div>

        <div style="display: flex; align-items: center; justify-content: flex-end; margin-top: 16px;">
            <a style="text-decoration: underline; font-size: 0.875rem; color: #94a3b8;" href="{{ route('login') }}">
                {{ __('Already registered?') }}
            </a>

            <x-primary-button class="ms-4">
                {{ __('Register') }}
            </x-primary-button>
        </div>
    </form>
</x-guest-layout>
