@extends('layouts.dashboard')
@section('title', 'Pengaturan')

@section('content')
<h1 class="text-2xl font-bold text-gray-900 mb-6">Pengaturan Profil</h1>

<div class="max-w-2xl">
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <form method="POST" action="{{ route('dashboard.settings.update') }}">
            @csrf
            @method('PUT')

            <div class="space-y-6">
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Nama</label>
                    <input type="text" name="name" id="name" value="{{ old('name', $user->name) }}" class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500" required>
                    @error('name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                    <input type="email" value="{{ $user->email }}" class="w-full border-gray-300 rounded-lg shadow-sm bg-gray-50" disabled>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Kode Referral</label>
                    <input type="text" value="{{ $user->referral_code }}" class="w-full border-gray-300 rounded-lg shadow-sm bg-gray-50 font-mono" disabled>
                </div>

                <div class="border-t border-gray-200 pt-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Informasi Bank</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="bank_name" class="block text-sm font-medium text-gray-700 mb-1">Nama Bank</label>
                            <select name="bank_name" id="bank_name" class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                                <option value="">Pilih Bank</option>
                                @foreach(['BCA', 'BNI', 'BRI', 'Mandiri', 'CIMB Niaga', 'Permata', 'Danamon', 'BSI', 'BTPN', 'Jago'] as $bank)
                                    <option value="{{ $bank }}" {{ old('bank_name', $user->bank_name) === $bank ? 'selected' : '' }}>{{ $bank }}</option>
                                @endforeach
                            </select>
                            @error('bank_name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label for="bank_account" class="block text-sm font-medium text-gray-700 mb-1">Nomor Rekening</label>
                            <input type="text" name="bank_account" id="bank_account" value="{{ old('bank_account', $user->bank_account) }}" class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500" placeholder="1234567890">
                            @error('bank_account') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>
                </div>
            </div>

            <div class="mt-6">
                <button type="submit" class="bg-indigo-600 text-white px-6 py-2.5 rounded-lg hover:bg-indigo-700 font-medium">Simpan Perubahan</button>
            </div>
        </form>
    </div>
</div>
@endsection
