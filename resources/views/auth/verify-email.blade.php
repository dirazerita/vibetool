<x-guest-layout>
    <div style="margin-bottom: 16px; font-size: 0.875rem; color: #94a3b8;">
        {{ __('Thanks for signing up! Before getting started, could you verify your email address by clicking on the link we just emailed to you? If you didn\'t receive the email, we will gladly send you another.') }}
    </div>

    @if (session('status') == 'verification-link-sent')
        <div style="margin-bottom: 16px; font-weight: 500; font-size: 0.875rem; color: #86efac;">
            {{ __('A new verification link has been sent to the email address you provided during registration.') }}
        </div>
    @endif

    <div style="margin-top: 16px; display: flex; align-items: center; justify-content: space-between;">
        <form method="POST" action="{{ route('verification.send') }}">
            @csrf

            <div>
                <x-primary-button>
                    {{ __('Resend Verification Email') }}
                </x-primary-button>
            </div>
        </form>

        <form method="POST" action="{{ route('logout') }}">
            @csrf

            <button type="submit" style="text-decoration: underline; font-size: 0.875rem; color: #94a3b8; background: none; border: none; cursor: pointer;">
                {{ __('Log Out') }}
            </button>
        </form>
    </div>
</x-guest-layout>
