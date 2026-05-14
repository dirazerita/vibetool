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
    <form method="POST" action="{{ route('admin.settings.update') }}" x-data="{ manualOn: {{ old('manual_payment_enabled', $manualPaymentEnabled) ? 'true' : 'false' }} }">
        @csrf @method('PUT')

        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">Aktivasi Member</h2>
            <div>
                <label for="whatsapp_admin" class="block text-sm font-medium text-gray-700 mb-1">Nomor WhatsApp Admin</label>
                <input type="text" name="whatsapp_admin" id="whatsapp_admin" value="{{ old('whatsapp_admin', $whatsappAdmin) }}" placeholder="contoh 082312181216" class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500" required>
                <p class="text-xs text-gray-500 mt-1">Nomor ini digunakan member yang baru registrasi untuk meminta aktivasi akun. Format yang diterima: 08xxxx, 62xxxx, atau +62xxxx — akan dinormalisasi otomatis.</p>
                @error('whatsapp_admin') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">Pembayaran Manual (Transfer Bank)</h2>

            <label class="flex items-start gap-3 cursor-pointer">
                <input type="checkbox" name="manual_payment_enabled" value="1" x-model="manualOn" class="mt-1 h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                <span>
                    <span class="block text-sm font-medium text-gray-900">Aktifkan Pembayaran Manual</span>
                    <span class="block text-xs text-gray-500 mt-0.5">Kalau diaktifkan, semua checkout akan menggunakan transfer bank manual (Xendit tidak dipakai). Member upload bukti transfer, admin verifikasi & tandai lunas dari menu Pesanan.</span>
                </span>
            </label>

            <div x-show="manualOn" x-transition class="mt-6 space-y-4 border-t border-gray-100 pt-6">
                <div>
                    <label for="manual_bank_name" class="block text-sm font-medium text-gray-700 mb-1">Nama Bank <span class="text-red-500">*</span></label>
                    <input type="text" name="manual_bank_name" id="manual_bank_name" value="{{ old('manual_bank_name', $manualBankName) }}" placeholder="contoh: BCA / Mandiri / BRI" class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                    @error('manual_bank_name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="manual_bank_account" class="block text-sm font-medium text-gray-700 mb-1">Nomor Rekening <span class="text-red-500">*</span></label>
                    <input type="text" name="manual_bank_account" id="manual_bank_account" value="{{ old('manual_bank_account', $manualBankAccount) }}" placeholder="contoh: 1234567890" class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                    @error('manual_bank_account') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="manual_bank_holder" class="block text-sm font-medium text-gray-700 mb-1">Atas Nama <span class="text-red-500">*</span></label>
                    <input type="text" name="manual_bank_holder" id="manual_bank_holder" value="{{ old('manual_bank_holder', $manualBankHolder) }}" placeholder="nama pemilik rekening" class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                    @error('manual_bank_holder') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="manual_payment_note" class="block text-sm font-medium text-gray-700 mb-1">Catatan Tambahan (opsional)</label>
                    <textarea name="manual_payment_note" id="manual_payment_note" rows="3" placeholder="Contoh: Mohon transfer sesuai nominal yang tertera & cantumkan ID pesanan di berita transfer." class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500">{{ old('manual_payment_note', $manualPaymentNote) }}</textarea>
                    @error('manual_payment_note') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
            </div>
        </div>

        <div>
            <button type="submit" class="bg-indigo-600 text-white px-6 py-2.5 rounded-lg hover:bg-indigo-700 font-medium">Simpan</button>
        </div>
    </form>
</div>
@endsection
