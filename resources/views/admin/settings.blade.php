@extends('layouts.admin')
@section('title', 'Pengaturan')

@section('content')
<h1 class="text-2xl font-bold text-gray-900 mb-6">Pengaturan</h1>

@if(session('success'))
    <div class="mb-4 p-3 bg-green-50 border border-green-200 rounded-lg text-sm text-green-800">
        {{ session('success') }}
    </div>
@endif

<div class="max-w-2xl">
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <form method="POST" action="{{ route('admin.settings.update') }}">
            @csrf @method('PUT')
            <div class="space-y-6">
                <div>
                    <label for="whatsapp_admin" class="block text-sm font-medium text-gray-700 mb-1">Nomor WhatsApp Admin</label>
                    <input type="text" name="whatsapp_admin" id="whatsapp_admin" value="{{ old('whatsapp_admin', $whatsappAdmin) }}" placeholder="contoh 082312181216" class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500" required>
                    <p class="text-xs text-gray-500 mt-1">Nomor ini digunakan member yang baru registrasi untuk meminta aktivasi akun. Format yang diterima: 08xxxx, 62xxxx, atau +62xxxx — akan dinormalisasi otomatis.</p>
                    @error('whatsapp_admin') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
            </div>

            <div class="mt-6">
                <button type="submit" class="bg-indigo-600 text-white px-6 py-2.5 rounded-lg hover:bg-indigo-700 font-medium">Simpan</button>
            </div>
        </form>
    </div>
</div>
@endsection
