@extends('layouts.admin')
@section('title', 'Pengaturan')

@section('content')
<h1 class="text-2xl font-bold text-gray-900 mb-6">Pengaturan</h1>

@if(session('success'))
    <div class="mb-4 p-3 bg-green-50 border border-green-200 rounded-lg text-sm text-green-800">
        {{ session('success') }}
    </div>
@endif

@if(session('error'))
    <div class="mb-4 p-3 bg-red-50 border border-red-200 rounded-lg text-sm text-red-800">
        {{ session('error') }}
    </div>
@endif

<div class="max-w-2xl">
    <form method="POST" action="{{ route('admin.settings.update') }}" x-data="{ manualOn: {{ old('manual_payment_enabled', $manualPaymentEnabled) ? 'true' : 'false' }}, telegramOn: {{ old('telegram_enabled', $telegramEnabled) ? 'true' : 'false' }} }">
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

        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-1">Integrasi Telegram</h2>
            <p class="text-xs text-gray-500 mb-4">Kirim notifikasi pesanan & member baru ke Telegram admin, plus aktivasi/tandai lunas langsung dari tombol di chat.</p>

            <label class="flex items-start gap-3 cursor-pointer">
                <input type="checkbox" name="telegram_enabled" value="1" x-model="telegramOn" class="mt-1 h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                <span>
                    <span class="block text-sm font-medium text-gray-900">Aktifkan Notifikasi Telegram</span>
                    <span class="block text-xs text-gray-500 mt-0.5">Notifikasi otomatis terkirim saat: member baru daftar, order manual baru dibuat, bukti transfer diupload.</span>
                </span>
            </label>

            <div x-show="telegramOn" x-transition class="mt-6 space-y-4 border-t border-gray-100 pt-6">
                <div class="p-3 bg-blue-50 border border-blue-200 rounded-lg text-xs text-blue-900 space-y-1">
                    <p class="font-semibold">Cara setup:</p>
                    <ol class="list-decimal list-inside space-y-0.5">
                        <li>Chat <a href="https://t.me/BotFather" target="_blank" class="underline font-medium">@BotFather</a> di Telegram → <code>/newbot</code> → simpan <b>Bot Token</b>.</li>
                        <li>Buka bot baru kamu di Telegram → kirim <code>/start</code> sekali (supaya bot tahu chat ID kamu).</li>
                        <li>Untuk dapat <b>Chat ID</b>: buka <code>https://api.telegram.org/bot&lt;TOKEN&gt;/getUpdates</code> di browser → cari <code>"chat":{"id": ...}</code>.</li>
                        <li>Isi token + chat ID di bawah, klik <b>Simpan</b>, lalu klik <b>Tes Koneksi</b>.</li>
                        <li>Klik <b>Pasang Webhook</b> supaya tombol aksi di chat (Aktifkan, Tandai Lunas, dll.) berfungsi. <b>Wajib HTTPS</b>.</li>
                    </ol>
                </div>

                <div>
                    <label for="telegram_bot_token" class="block text-sm font-medium text-gray-700 mb-1">Bot Token <span class="text-red-500">*</span></label>
                    <input type="text" name="telegram_bot_token" id="telegram_bot_token" value="{{ old('telegram_bot_token', $telegramBotToken) }}" placeholder="contoh: 1234567890:AAH..." class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500 font-mono text-sm" autocomplete="off">
                    @error('telegram_bot_token') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="telegram_chat_id" class="block text-sm font-medium text-gray-700 mb-1">Chat ID <span class="text-red-500">*</span></label>
                    <input type="text" name="telegram_chat_id" id="telegram_chat_id" value="{{ old('telegram_chat_id', $telegramChatId) }}" placeholder="contoh: 123456789" class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500 font-mono text-sm" autocomplete="off">
                    <p class="text-xs text-gray-500 mt-1">ID chat tujuan notifikasi (biasanya chat pribadi kamu dengan bot — angka bulat).</p>
                    @error('telegram_chat_id') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                @if($telegramWebhookUrl)
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">URL Webhook</label>
                        <input type="text" value="{{ $telegramWebhookUrl }}" readonly class="w-full border-gray-200 bg-gray-50 rounded-lg shadow-sm font-mono text-xs text-gray-700">
                        <p class="text-xs text-gray-500 mt-1">URL ini dipakai bot Telegram untuk kirim callback saat tombol di chat ditekan. Pasang dengan tombol di bawah setelah simpan.</p>
                    </div>
                @endif
            </div>
        </div>

        <div class="flex flex-wrap items-center gap-3">
            <button type="submit"
                    class="px-6 py-2.5 rounded-lg font-medium"
                    style="background-color:#4f46e5; color:#ffffff;"
                    onmouseover="this.style.backgroundColor='#4338ca'"
                    onmouseout="this.style.backgroundColor='#4f46e5'">Simpan</button>
        </div>
    </form>

    <div class="mt-6 flex flex-wrap items-center gap-3" x-data="{ telegramOn: {{ $telegramEnabled ? 'true' : 'false' }} }" x-show="telegramOn">
        <form method="POST" action="{{ route('admin.settings.telegram.test') }}">
            @csrf
            <button type="submit"
                    class="px-5 py-2 rounded-lg font-medium text-sm"
                    style="background-color:#0ea5e9; color:#ffffff;"
                    onmouseover="this.style.backgroundColor='#0284c7'"
                    onmouseout="this.style.backgroundColor='#0ea5e9'">Tes Koneksi Telegram</button>
        </form>
        <form method="POST" action="{{ route('admin.settings.telegram.setup-webhook') }}">
            @csrf
            <button type="submit"
                    class="px-5 py-2 rounded-lg font-medium text-sm"
                    style="background-color:#16a34a; color:#ffffff;"
                    onmouseover="this.style.backgroundColor='#15803d'"
                    onmouseout="this.style.backgroundColor='#16a34a'">Pasang Webhook</button>
        </form>
    </div>
</div>
@endsection
