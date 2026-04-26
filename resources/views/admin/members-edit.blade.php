@extends('layouts.admin')
@section('title', 'Edit Member')

@section('content')
<h1 class="text-2xl font-bold text-gray-900 mb-6">Edit Member: {{ $user->name }}</h1>

<div class="max-w-2xl">
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <form method="POST" action="{{ route('admin.members.update', $user) }}">
            @csrf @method('PUT')
            <div class="space-y-6">
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Nama</label>
                    <input type="text" name="name" id="name" value="{{ old('name', $user->name) }}" class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500" required>
                    @error('name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                    <input type="email" name="email" id="email" value="{{ old('email', $user->email) }}" class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500" required>
                    @error('email') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="whatsapp_number" class="block text-sm font-medium text-gray-700 mb-1">Nomor WhatsApp</label>
                    <input type="tel" name="whatsapp_number" id="whatsapp_number" value="{{ old('whatsapp_number', $user->whatsapp_number) }}" class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500" placeholder="Contoh: 08123456789">
                    @error('whatsapp_number') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="referral_code" class="block text-sm font-medium text-gray-700 mb-1">Kode Referral</label>
                    <input type="text" name="referral_code" id="referral_code" value="{{ old('referral_code', $user->referral_code) }}" class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500 uppercase" required>
                    @error('referral_code') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700 mb-1">Password Baru (kosongkan jika tidak ingin mengubah)</label>
                    <input type="password" name="password" id="password" class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500" placeholder="Masukkan password baru">
                    @error('password') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="upline_id" class="block text-sm font-medium text-gray-700 mb-1">Upline</label>
                    <select name="upline_id" id="upline_id" class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="">-- Tidak ada upline --</option>
                        @foreach($members as $member)
                            <option value="{{ $member->id }}" {{ old('upline_id', $user->upline_id) == $member->id ? 'selected' : '' }}>{{ $member->name }} ({{ $member->email }})</option>
                        @endforeach
                    </select>
                    @error('upline_id') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div class="bg-gray-50 rounded-lg p-4 space-y-2">
                    <p class="text-sm text-gray-600">Saldo: <strong>Rp {{ number_format($user->balance, 0, ',', '.') }}</strong></p>
                    <p class="text-sm text-gray-600">Bergabung: <strong>{{ $user->created_at->format('d M Y H:i') }}</strong></p>
                </div>
            </div>

            <div class="mt-6 flex gap-4">
                <button type="submit" class="bg-indigo-600 text-white px-6 py-2.5 rounded-lg hover:bg-indigo-700 font-medium">Update Member</button>
                <a href="{{ route('admin.members') }}" class="px-6 py-2.5 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 font-medium">Batal</a>
            </div>
        </form>
    </div>
</div>
@endsection
