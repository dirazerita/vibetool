<x-guest-layout :logo-height="288" :logo-max-width="720">
    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}">
        @csrf

        <!-- Email Address -->
        <div>
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autofocus autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <!-- Password -->
        <div style="margin-top: 16px;">
            <x-input-label for="password" :value="__('Password')" />

            <x-text-input id="password" class="block mt-1 w-full"
                            type="password"
                            name="password"
                            required autocomplete="current-password" />

            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Remember Me -->
        <div style="margin-top: 16px;">
            <label for="remember_me" style="display: inline-flex; align-items: center;">
                <input id="remember_me" type="checkbox" style="border-radius: 4px; border: 1px solid #2d3a4a; background-color: #151e2d; color: #818cf8;" name="remember">
                <span style="margin-left: 8px; font-size: 0.875rem; color: #94a3b8;">{{ __('Remember me') }}</span>
            </label>
        </div>

        <div style="display: flex; align-items: center; justify-content: flex-end; margin-top: 16px;">
            @if (Route::has('password.request'))
                <a style="text-decoration: underline; font-size: 0.875rem; color: #94a3b8;" href="{{ route('password.request') }}">
                    {{ __('Forgot your password?') }}
                </a>
            @endif

            <x-primary-button class="ms-3">
                {{ __('Log in') }}
            </x-primary-button>
        </div>
    </form>
</x-guest-layout>
