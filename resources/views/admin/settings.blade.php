@extends('layouts.admin')
@section('title', 'Pengaturan')

@section('content')
<h1 class="text-2xl font-bold dk-heading mb-6">Pengaturan</h1>

@if(session('success'))
    <div class="dk-alert-success">
        {{ session('success') }}
    </div>
@endif

@if(session('error'))
    <div class="dk-alert-error">
        {{ session('error') }}
    </div>
@endif

<div class="max-w-2xl">
    <form method="POST" action="{{ route('admin.settings.update') }}" x-data="{ manualOn: {{ old('manual_payment_enabled', $manualPaymentEnabled) ? 'true' : 'false' }}, telegramOn: {{ old('telegram_enabled', $telegramEnabled) ? 'true' : 'false' }} }">
        @csrf @method('PUT')

        <div class="dk-card" style="padding:24px mb-6">
            <h2 class="text-lg font-semibold dk-heading mb-4">Aktivasi Member</h2>
            <div>
                <label for="whatsapp_admin" class="dk-label">Nomor WhatsApp Admin</label>
                <input type="text" name="whatsapp_admin" id="whatsapp_admin" value="{{ old('whatsapp_admin', $whatsappAdmin) }}" placeholder="contoh 082312181216" class="w-full dk-input" required>
                <p class="text-xs mt-1 dk-text-muted">Nomor ini digunakan member yang baru registrasi untuk meminta aktivasi akun. Format yang diterima: 08xxxx, 62xxxx, atau +62xxxx — akan dinormalisasi otomatis.</p>
                @error('whatsapp_admin') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>
        </div>

        <div class="dk-card" style="padding:24px mb-6">
            <h2 class="text-lg font-semibold dk-heading mb-4">Pembayaran Manual (Transfer Bank)</h2>

            <label class="flex items-start gap-3 cursor-pointer">
                <input type="checkbox" name="manual_payment_enabled" value="1" x-model="manualOn" class="dk-checkbox">
                <span>
                    <span class="block text-sm font-medium dk-heading">Aktifkan Pembayaran Manual</span>
                    <span class="block text-xs dk-text-muted mt-0.5">Kalau diaktifkan, semua checkout akan menggunakan transfer bank manual (Xendit tidak dipakai). Member upload bukti transfer, admin verifikasi & tandai lunas dari menu Pesanan.</span>
                </span>
            </label>

            <div x-show="manualOn" x-transition class="mt-6 space-y-4 dk-divider pt-6">
                <div>
                    <label for="manual_bank_name" class="dk-label">Nama Bank <span class="text-red-500">*</span></label>
                    <input type="text" name="manual_bank_name" id="manual_bank_name" value="{{ old('manual_bank_name', $manualBankName) }}" placeholder="contoh: BCA / Mandiri / BRI" class="w-full dk-input">
                    @error('manual_bank_name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="manual_bank_account" class="dk-label">Nomor Rekening <span class="text-red-500">*</span></label>
                    <input type="text" name="manual_bank_account" id="manual_bank_account" value="{{ old('manual_bank_account', $manualBankAccount) }}" placeholder="contoh: 1234567890" class="w-full dk-input">
                    @error('manual_bank_account') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="manual_bank_holder" class="dk-label">Atas Nama <span class="text-red-500">*</span></label>
                    <input type="text" name="manual_bank_holder" id="manual_bank_holder" value="{{ old('manual_bank_holder', $manualBankHolder) }}" placeholder="nama pemilik rekening" class="w-full dk-input">
                    @error('manual_bank_holder') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="manual_payment_note" class="dk-label">Catatan Tambahan (opsional)</label>
                    <textarea name="manual_payment_note" id="manual_payment_note" rows="3" placeholder="Contoh: Mohon transfer sesuai nominal yang tertera & cantumkan ID pesanan di berita transfer." class="w-full dk-input">{{ old('manual_payment_note', $manualPaymentNote) }}</textarea>
                    @error('manual_payment_note') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
            </div>
        </div>

        <div class="dk-card" style="padding:24px mb-6">
            <h2 class="text-lg font-semibold dk-heading mb-1">Integrasi Telegram</h2>
            <p class="text-xs dk-text-muted mb-4">Kirim notifikasi pesanan & member baru ke Telegram admin, plus aktivasi/tandai lunas langsung dari tombol di chat.</p>

            <label class="flex items-start gap-3 cursor-pointer">
                <input type="checkbox" name="telegram_enabled" value="1" x-model="telegramOn" class="dk-checkbox">
                <span>
                    <span class="block text-sm font-medium dk-heading">Aktifkan Notifikasi Telegram</span>
                    <span class="block text-xs dk-text-muted mt-0.5">Notifikasi otomatis terkirim saat: member baru daftar, order manual baru dibuat, bukti transfer diupload.</span>
                </span>
            </label>

            <div x-show="telegramOn" x-transition class="mt-6 space-y-4 dk-divider pt-6">
                <div class="p-3 rounded-lg text-xs" style="background:rgba(59,130,246,0.1);border:1px solid rgba(59,130,246,0.3);color:#93c5fd space-y-1">
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
                    <label for="telegram_bot_token" class="dk-label">Bot Token <span class="text-red-500">*</span></label>
                    <input type="text" name="telegram_bot_token" id="telegram_bot_token" value="{{ old('telegram_bot_token', $telegramBotToken) }}" placeholder="contoh: 1234567890:AAH..." class="w-full dk-input font-mono text-sm dk-input" autocomplete="off">
                    @error('telegram_bot_token') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="telegram_chat_id" class="dk-label">Chat ID <span class="text-red-500">*</span></label>
                    <input type="text" name="telegram_chat_id" id="telegram_chat_id" value="{{ old('telegram_chat_id', $telegramChatId) }}" placeholder="contoh: 123456789" class="w-full dk-input font-mono text-sm dk-input" autocomplete="off">
                    <p class="text-xs mt-1 dk-text-muted">ID chat tujuan notifikasi (biasanya chat pribadi kamu dengan bot — angka bulat).</p>
                    @error('telegram_chat_id') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                @if($telegramWebhookUrl)
                    <div>
                        <label class="dk-label">URL Webhook</label>
                        <input type="text" value="{{ $telegramWebhookUrl }}" readonly class="w-full dk-input font-mono text-xs">
                        <p class="text-xs mt-1 dk-text-muted">URL ini dipakai bot Telegram untuk kirim callback saat tombol di chat ditekan. Pasang dengan tombol di bawah setelah simpan.</p>
                    </div>
                @endif
            </div>
        </div>

        <div class="flex flex-wrap items-center gap-3">
            <button type="submit"
                    class="px-6 py-2.5 rounded-lg font-medium"
                    class="dk-btn dk-btn-primary" style="
                    onmouseover="this.style.backgroundColor='#4338ca'"
                    onmouseout="this.style.backgroundColor='#4f46e5'">Simpan</button>
        </div>
    </form>

    <div class="mt-6 flex flex-wrap items-center gap-3" x-data="{ telegramOn: {{ $telegramEnabled ? 'true' : 'false' }} }" x-show="telegramOn">
        <form method="POST" action="{{ route('admin.settings.telegram.test') }}">
            @csrf
            <button type="submit"
                    class="px-5 py-2 rounded-lg font-medium text-sm"
                    class="dk-btn" style="background:linear-gradient(135deg,#0ea5e9,#6366f1); color:#fff;
                    onmouseover="this.style.backgroundColor='#0284c7'"
                    onmouseout="this.style.backgroundColor='#0ea5e9'">Tes Koneksi Telegram</button>
        </form>
        <form method="POST" action="{{ route('admin.settings.telegram.setup-webhook') }}">
            @csrf
            <button type="submit"
                    class="px-5 py-2 rounded-lg font-medium text-sm"
                    class="dk-btn dk-btn-success" style="
                    onmouseover="this.style.backgroundColor='#15803d'"
                    onmouseout="this.style.backgroundColor='#16a34a'">Pasang Webhook</button>
        </form>
    </div>
</div>
@endsection
