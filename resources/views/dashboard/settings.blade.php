@extends('layouts.dashboard')
@section('title', 'Pengaturan')

@section('content')
<h1 class="text-2xl font-bold dk-heading mb-6">Pengaturan Profil</h1>

<div class="max-w-2xl">
    <div class="dk-card" style="padding:24px;">
        <form method="POST" action="{{ route('dashboard.settings.update') }}">
            @csrf
            @method('PUT')

            <div class="space-y-6">
                <div>
                    <label for="name" class="dk-label">Nama</label>
                    <input type="text" name="name" id="name" value="{{ old('name', $user->name) }}" class="w-full dk-input" required>
                    @error('name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="dk-label">Email</label>
                    <input type="email" value="{{ $user->email }}" class="w-full dk-input " style="background:#151e2d" disabled>
                </div>

                <div>
                    <label class="dk-label">Kode Referral</label>
                    <input type="text" value="{{ $user->referral_code }}" class="w-full dk-input " style="background:#151e2d font-mono" disabled>
                </div>

                <div class="dk-divider pt-6">
                    <h3 class="text-lg font-medium dk-heading mb-4">Informasi Bank</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="bank_name" class="dk-label">Nama Bank</label>
                            <select name="bank_name" id="bank_name" class="w-full dk-input">
                                <option value="">Pilih Bank</option>
                                @foreach(['BCA', 'BNI', 'BRI', 'Mandiri', 'CIMB Niaga', 'Permata', 'Danamon', 'BSI', 'BTPN', 'Jago'] as $bank)
                                    <option value="{{ $bank }}" {{ old('bank_name', $user->bank_name) === $bank ? 'selected' : '' }}>{{ $bank }}</option>
                                @endforeach
                            </select>
                            @error('bank_name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label for="bank_account" class="dk-label">Nomor Rekening</label>
                            <input type="text" name="bank_account" id="bank_account" value="{{ old('bank_account', $user->bank_account) }}" class="w-full dk-input" placeholder="1234567890">
                            @error('bank_account') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>
                </div>
            </div>

            <div class="mt-6">
                <button type="submit" class="dk-btn dk-btn-primary">Simpan Perubahan</button>
            </div>
        </form>
    </div>
</div>
@endsection
