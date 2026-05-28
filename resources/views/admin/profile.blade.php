@extends('layouts.admin')
@section('title', 'Profil Saya')

@section('content')
<h1 class="text-2xl font-bold dk-heading mb-6">Profil Saya</h1>

@if(session('success'))
    <div class="dk-alert-success">{{ session('success') }}</div>
@endif

@if(session('error'))
    <div class="dk-alert-error">{{ session('error') }}</div>
@endif

<div class="max-w-2xl space-y-6">
    <div class="dk-card" style="padding:24px">
        <h2 class="text-lg font-semibold dk-heading mb-1">Info Akun</h2>
        <p class="text-xs dk-text-muted mb-4">Email yang dipakai untuk login ke dashboard admin.</p>

        <form method="POST" action="{{ route('admin.profile.update') }}" class="space-y-4">
            @csrf @method('PUT')

            <div>
                <label for="name" class="dk-label">Nama</label>
                <input type="text" name="name" id="name" value="{{ old('name', $user->name) }}" class="w-full dk-input" required>
                @error('name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="email" class="dk-label">Email</label>
                <input type="email" name="email" id="email" value="{{ old('email', $user->email) }}" class="w-full dk-input" required>
                <p class="text-xs mt-1 dk-text-muted">Email ini juga dipakai untuk login.</p>
                @error('email') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <button type="submit" class="dk-btn dk-btn-primary">Simpan Profil</button>
            </div>
        </form>
    </div>

    <div class="dk-card" style="padding:24px">
        <h2 class="text-lg font-semibold dk-heading mb-1">Ubah Password</h2>
        <p class="text-xs dk-text-muted mb-4">Gunakan password yang kuat — minimal 8 karakter, kombinasi huruf, angka, &amp; simbol.</p>

        <form method="POST" action="{{ route('admin.profile.password.update') }}" class="space-y-4">
            @csrf @method('PUT')

            <div>
                <label for="current_password" class="dk-label">Password Lama</label>
                <input type="password" name="current_password" id="current_password" class="w-full dk-input" autocomplete="current-password" required>
                @error('current_password') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="password" class="dk-label">Password Baru</label>
                <input type="password" name="password" id="password" class="w-full dk-input" autocomplete="new-password" required>
                @error('password') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="password_confirmation" class="dk-label">Konfirmasi Password Baru</label>
                <input type="password" name="password_confirmation" id="password_confirmation" class="w-full dk-input" autocomplete="new-password" required>
            </div>

            <div>
                <button type="submit" class="dk-btn dk-btn-primary">Ubah Password</button>
            </div>
        </form>
    </div>

    <form method="POST" action="{{ route('logout') }}" class="flex">
        @csrf
        <button type="submit" class="dk-btn dk-btn-outline">Logout</button>
    </form>
</div>
@endsection
