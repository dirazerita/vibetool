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
    <form method="POST" action="{{ route('admin.settings.update') }}" x-data="{ manualOn: {{ old('manual_payment_enabled', $manualPaymentEnabled) ? 'true' : 'false' }}, pakasirOn: {{ old('pakasir_enabled', $pakasirEnabled) ? 'true' : 'false' }}, telegramOn: {{ old('telegram_enabled', $telegramEnabled) ? 'true' : 'false' }} }">
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

        <div class="dk-card" style="padding:24px; margin-bottom:24px;">
            <h2 class="text-lg font-semibold dk-heading mb-1">Pembayaran via Pakasir.com</h2>
            <p class="text-xs dk-text-muted mb-4">Payment gateway QRIS & Virtual Account via <a href="https://pakasir.com" target="_blank" style="color:#818cf8">pakasir.com</a>. Kalau diaktifkan, checkout akan diarahkan ke halaman pembayaran Pakasir (Xendit tidak dipakai).</p>

            <label class="flex items-start gap-3 cursor-pointer">
                <input type="checkbox" name="pakasir_enabled" value="1" x-model="pakasirOn" class="dk-checkbox">
                <span>
                    <span class="block text-sm font-medium dk-heading">Aktifkan Pakasir.com</span>
                    <span class="block text-xs dk-text-muted mt-0.5">Prioritas: Pembayaran Manual &gt; Pakasir &gt; Xendit. Kalau pembayaran manual juga aktif, manual tetap dipakai.</span>
                </span>
            </label>

            <div x-show="pakasirOn" x-transition class="mt-6 space-y-4 dk-divider pt-6">
                <div>
                    <label for="pakasir_slug" class="dk-label">Slug Proyek <span class="text-red-500">*</span></label>
                    <input type="text" name="pakasir_slug" id="pakasir_slug" value="{{ old('pakasir_slug', $pakasirSlug) }}" placeholder="contoh: vibetool" class="w-full dk-input">
                    <p class="text-xs mt-1 dk-text-muted">Slug proyek Anda di Pakasir.com (lihat di halaman detail proyek).</p>
                    @error('pakasir_slug') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="pakasir_api_key" class="dk-label">API Key <span class="text-red-500">*</span></label>
                    <input type="text" name="pakasir_api_key" id="pakasir_api_key" value="{{ old('pakasir_api_key', $pakasirApiKey) }}" placeholder="API Key dari proyek Pakasir Anda" class="w-full dk-input">
                    <p class="text-xs mt-1 dk-text-muted">API Key proyek Anda di Pakasir.com (lihat di halaman detail proyek). Dibutuhkan untuk verifikasi webhook.</p>
                    @error('pakasir_api_key') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div style="background:rgba(99,102,241,0.08); border:1px solid rgba(99,102,241,0.2); border-radius:8px; padding:12px 16px;">
                    <p class="text-xs font-medium" style="color:#a5b4fc; margin-bottom:4px;">Webhook URL (isi di dashboard Pakasir):</p>
                    <code class="text-xs" style="color:#e2e8f0; word-break:break-all;">{{ url('/webhook/pakasir') }}</code>
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

        <style>[x-cloak]{display:none!important}</style>
        <div class="dk-card" style="padding:24px; margin-bottom:24px;">
            <h2 class="text-lg font-semibold dk-heading mb-1">AI Agent (FAL.AI)</h2>
            <p class="text-xs dk-text-muted mb-4">Dipakai fitur "Buat Produk dengan AI" di halaman Tambah Produk: membaca link repo, membuat judul, deskripsi, thumbnail, dan landing page otomatis. Ambil API Key di <a href="https://fal.ai/dashboard/keys" target="_blank" style="color:#818cf8">fal.ai/dashboard/keys</a>.</p>
            <div class="space-y-4">
                <div x-data="falKeys()">
                    <label class="dk-label">FAL API Keys <span class="text-xs dk-text-muted font-normal">— bisa lebih dari satu; pilih yang Aktif</span></label>
                    <template x-for="(row, i) in rows" :key="row.id">
                        <div class="mb-2">
                            <div class="flex items-center gap-2">
                                <input type="radio" name="fal_api_key_active" :value="i" x-model.number="active" title="Jadikan key aktif" style="accent-color:#6366f1; flex-shrink:0;">
                                <input type="password" name="fal_api_keys[]" x-model="row.key" placeholder="key_id:key_secret" class="w-full dk-input" autocomplete="off">
                                <button type="button" @click="check(i)" :disabled="row.checking"
                                        class="px-3 py-2 rounded-lg text-sm" title="Cek saldo key ini"
                                        style="border:1px solid #6366f1; color:#a5b4fc; background:rgba(99,102,241,.08); flex-shrink:0;"
                                        x-text="row.checking ? '⏳' : '💰'"></button>
                                <button type="button" @click="remove(i)"
                                        class="px-3 py-2 rounded-lg text-sm" title="Hapus key ini"
                                        style="border:1px solid #475569; color:#94a3b8; flex-shrink:0;">🗑</button>
                            </div>
                            <p class="text-xs mt-1" style="margin-left:26px;" x-show="row.result" x-cloak
                               :style="row.error ? 'color:#f87171; margin-left:26px;' : 'color:#34d399; margin-left:26px;'"
                               x-text="row.result"></p>
                            <p class="text-xs mt-0.5 dk-text-muted" style="margin-left:26px;" x-show="i === active" x-cloak>
                                Key aktif — dipakai duluan. Jika error/kehabisan saldo, agent otomatis pindah ke key berikutnya.
                            </p>
                        </div>
                    </template>
                    <button type="button" @click="add()"
                            class="px-4 py-2 rounded-lg text-sm font-medium mt-1"
                            style="border:1px dashed #6366f1; color:#a5b4fc;">➕ Add Key</button>
                    <p class="text-xs mt-2 dk-text-muted">Kosongkan semua untuk menonaktifkan AI Agent. Jangan lupa klik <b>Simpan</b> setelah menambah/mengubah key.</p>
                    @error('fal_api_keys') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    @error('fal_api_keys.*') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <script>
                function falKeys() {
                    const initial = @json($falApiKeys);
                    const activeKey = @json($falApiKey);
                    let nextId = 1;
                    const mkRow = (k) => ({ id: nextId++, key: k, result: '', error: false, checking: false });
                    return {
                        rows: (initial.length ? initial : ['']).map(mkRow),
                        active: Math.max(0, initial.indexOf(activeKey)),
                        add() { this.rows.push(mkRow('')); },
                        remove(i) {
                            this.rows.splice(i, 1);
                            if (!this.rows.length) this.add();
                            if (this.active >= this.rows.length) this.active = 0;
                        },
                        async check(i) {
                            const row = this.rows[i];
                            if (!row.key.trim()) { row.error = true; row.result = 'Isi key dulu.'; return; }
                            row.checking = true; row.error = false; row.result = 'Menghubungi FAL.AI…';
                            try {
                                const res = await fetch('{{ route('admin.ai-agent.balance') }}', {
                                    method: 'POST',
                                    headers: {
                                        'Content-Type': 'application/json',
                                        'Accept': 'application/json',
                                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                                    },
                                    body: JSON.stringify({ key: row.key }),
                                });
                                const data = await res.json().catch(() => ({}));
                                if (!res.ok || !data.ok) throw new Error(data.message || ('Gagal (HTTP ' + res.status + ')'));
                                row.result = 'Saldo: ' + data.formatted;
                            } catch (e) {
                                row.error = true;
                                row.result = e.message;
                            } finally {
                                row.checking = false;
                            }
                        },
                    };
                }
                </script>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="fal_llm_model" class="dk-label">Model Teks (any-llm) <span class="text-xs dk-text-muted font-normal">— opsional</span></label>
                        <input type="text" name="fal_llm_model" id="fal_llm_model" value="{{ old('fal_llm_model', $falLlmModel) }}" placeholder="{{ \App\Services\FalAiService::DEFAULT_LLM_MODEL }}" class="w-full dk-input">
                        <p class="text-xs mt-1 dk-text-muted">Kosongkan untuk pakai default.</p>
                    </div>
                    <div>
                        <label for="fal_image_model" class="dk-label">Model Gambar <span class="text-xs dk-text-muted font-normal">— opsional</span></label>
                        <input type="text" name="fal_image_model" id="fal_image_model" value="{{ old('fal_image_model', $falImageModel) }}" placeholder="{{ \App\Services\FalAiService::DEFAULT_IMAGE_MODEL }}" class="w-full dk-input">
                        <p class="text-xs mt-1 dk-text-muted">Kosongkan untuk pakai default (FLUX schnell).</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="flex flex-wrap items-center gap-3">
            <button type="submit"
                    class="dk-btn dk-btn-primary px-6 py-2.5 rounded-lg font-medium"
                    style="background-color:#4f46e5; color:#fff;"
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
