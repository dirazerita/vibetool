@extends('layouts.dashboard')
@section('title', 'Kirim Request Software')

@section('content')
<style>
    .sr-form-section { background:#0f1729; border:1px solid #1e2b3d; border-radius:14px; padding:20px; margin-bottom:16px; }
    .sr-label { display:block; font-weight:600; color:#e2e8f0; font-size:14px; margin-bottom:4px; }
    .sr-help { font-size:12px; color:#94a3b8; margin-bottom:8px; line-height:1.5; }
    .sr-help-ex { display:block; margin-top:4px; font-style:italic; color:#64748b; font-size:12px; }
    .sr-input, .sr-textarea, .sr-select { width:100%; background:#151e2d; border:1px solid #2d3a4a; color:#e2e8f0; padding:10px 12px; border-radius:8px; font-size:14px; font-family:inherit; }
    .sr-input:focus, .sr-textarea:focus, .sr-select:focus { outline:none; border-color:#6366f1; }
    .sr-textarea { resize:vertical; min-height:80px; }
    .sr-platform-grid { display:grid; grid-template-columns:repeat(auto-fill, minmax(180px, 1fr)); gap:8px; }
    .sr-platform-option { display:flex; align-items:center; gap:8px; padding:10px 12px; background:#151e2d; border:1px solid #2d3a4a; border-radius:8px; cursor:pointer; transition:all 0.15s; user-select:none; }
    .sr-platform-option:hover { border-color:#475569; }
    .sr-platform-option input { accent-color:#6366f1; }
    .sr-platform-option.checked { border-color:#6366f1; background:rgba(99,102,241,0.1); }
    .sr-platform-label { font-size:14px; color:#cbd5e1; }
    .sr-error { color:#fca5a5; font-size:12px; margin-top:6px; }
    .sr-actions { display:flex; gap:12px; align-items:center; flex-wrap:wrap; }
    .sr-attachment-name { font-size:13px; color:#94a3b8; margin-top:6px; }
    .sr-required { color:#fca5a5; }
</style>

<div style="margin-bottom:24px;">
    <a href="{{ route('dashboard.software-requests.index') }}" style="color:#94a3b8; font-size:13px; text-decoration:none; display:inline-flex; align-items:center; gap:4px; margin-bottom:8px;">
        <svg style="width:14px; height:14px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
        Kembali
    </a>
    <h1 class="text-2xl font-bold dk-heading">Kirim Request Software</h1>
    <p class="dk-text-muted" style="font-size:14px; margin-top:4px; max-width:640px;">
        Cerita aja apa adanya pakai bahasa sehari-hari. Tidak perlu bahasa teknis — tim kami yang akan terjemahkan ke bahasa programmer.
    </p>
</div>

<form method="POST" action="{{ route('dashboard.software-requests.store') }}" enctype="multipart/form-data">
    @csrf

    {{-- 1. Title --}}
    <div class="sr-form-section">
        <label class="sr-label" for="title">Nama aplikasinya mau apa? <span class="sr-required">*</span></label>
        <p class="sr-help">
            Tulis singkat saja, satu kalimat.
            <span class="sr-help-ex">Contoh: "Aplikasi kasir warung kopi", "Software edit foto sederhana"</span>
        </p>
        <input id="title" name="title" type="text" class="sr-input" maxlength="200" required value="{{ old('title') }}" placeholder="Aplikasi kasir warung saya">
        @error('title') <p class="sr-error">{{ $message }}</p> @enderror
    </div>

    {{-- 2. Purpose --}}
    <div class="sr-form-section">
        <label class="sr-label" for="purpose">Cerita ya, aplikasi ini gunanya untuk apa? <span class="sr-required">*</span></label>
        <p class="sr-help">
            Pakai bahasa sehari-hari, seperti cerita ke teman. Semakin detail semakin baik.
            <span class="sr-help-ex">Contoh: "Saya jualan kopi di rumah, ingin punya aplikasi yang bisa catat pesanan, hitung uang masuk per hari, dan tahu menu mana yang paling laris."</span>
        </p>
        <textarea id="purpose" name="purpose" class="sr-textarea" rows="4" maxlength="5000" required placeholder="Cerita aplikasinya kira-kira untuk apa...">{{ old('purpose') }}</textarea>
        @error('purpose') <p class="sr-error">{{ $message }}</p> @enderror
    </div>

    {{-- 3. Target users --}}
    <div class="sr-form-section">
        <label class="sr-label" for="target_users">Siapa yang akan pakai aplikasi ini? <span class="sr-required">*</span></label>
        <p class="sr-help">
            <span class="sr-help-ex">Contoh: "Saya sendiri", "Saya dan 2 karyawan", "Pelanggan saya yang mau pesan online"</span>
        </p>
        <input id="target_users" name="target_users" type="text" class="sr-input" maxlength="500" required value="{{ old('target_users') }}" placeholder="Saya sendiri, atau...">
        @error('target_users') <p class="sr-error">{{ $message }}</p> @enderror
    </div>

    {{-- 4. Problem --}}
    <div class="sr-form-section">
        <label class="sr-label" for="problem_to_solve">Masalahnya sekarang seperti apa? <span class="sr-required">*</span></label>
        <p class="sr-help">
            Cerita kondisi sekarang dan kenapa butuh aplikasi.
            <span class="sr-help-ex">Contoh: "Sekarang masih catat pesanan manual di buku. Sering hilang dan susah hitung total per hari. Mau yang otomatis."</span>
        </p>
        <textarea id="problem_to_solve" name="problem_to_solve" class="sr-textarea" rows="4" maxlength="5000" required placeholder="Sekarang masalahnya...">{{ old('problem_to_solve') }}</textarea>
        @error('problem_to_solve') <p class="sr-error">{{ $message }}</p> @enderror
    </div>

    {{-- 5. Similar apps --}}
    <div class="sr-form-section">
        <label class="sr-label" for="similar_apps">Ada aplikasi sejenis yang Anda kenal? <span style="color:#64748b; font-weight:400; font-size:12px;">(opsional)</span></label>
        <p class="sr-help">
            Kalau tahu ada aplikasi yang mirip, tulis namanya. Kosongkan kalau tidak tahu — tidak masalah.
            <span class="sr-help-ex">Contoh: "Mirip Moka POS tapi simpel", "Seperti Gojek tapi untuk laundry"</span>
        </p>
        <input id="similar_apps" name="similar_apps" type="text" class="sr-input" maxlength="500" value="{{ old('similar_apps') }}" placeholder="Mirip aplikasi apa? (boleh kosong)">
        @error('similar_apps') <p class="sr-error">{{ $message }}</p> @enderror
    </div>

    {{-- 6. Platforms --}}
    <div class="sr-form-section">
        <label class="sr-label">Aplikasinya jalan di mana? <span class="sr-required">*</span></label>
        <p class="sr-help">Pilih satu atau lebih. Kalau tidak tahu, pilih "Belum tahu".</p>
        <div class="sr-platform-grid">
            @foreach(\App\Models\SoftwareRequest::PLATFORMS as $key => $label)
                @php $checked = in_array($key, old('platforms', []), true); @endphp
                <label class="sr-platform-option {{ $checked ? 'checked' : '' }}">
                    <input type="checkbox" name="platforms[]" value="{{ $key }}" {{ $checked ? 'checked' : '' }} onchange="this.closest('.sr-platform-option').classList.toggle('checked', this.checked)">
                    <span class="sr-platform-label">{{ $label }}</span>
                </label>
            @endforeach
        </div>
        @error('platforms') <p class="sr-error">{{ $message }}</p> @enderror
        @error('platforms.*') <p class="sr-error">{{ $message }}</p> @enderror
    </div>

    {{-- 7. Key features --}}
    <div class="sr-form-section">
        <label class="sr-label" for="key_features">Fitur penting apa saja yang harus ada? <span class="sr-required">*</span></label>
        <p class="sr-help">
            Tulis satu per satu, sebanyak-banyaknya tidak apa-apa. Tidak perlu pakai bahasa teknis.
            <span class="sr-help-ex">Contoh:<br>1. Bisa input pesanan dari HP<br>2. Print struk otomatis<br>3. Lihat laporan harian<br>4. Bisa simpan menu favorit pelanggan</span>
        </p>
        <textarea id="key_features" name="key_features" class="sr-textarea" rows="6" maxlength="5000" required placeholder="1. ...&#10;2. ...&#10;3. ...">{{ old('key_features') }}</textarea>
        @error('key_features') <p class="sr-error">{{ $message }}</p> @enderror
    </div>

    {{-- 8. Attachment --}}
    <div class="sr-form-section">
        <label class="sr-label" for="attachment">Lampiran <span style="color:#64748b; font-weight:400; font-size:12px;">(opsional)</span></label>
        <p class="sr-help">
            Kalau punya sketsa, mockup, atau screenshot referensi, upload di sini. Format: gambar / PDF / dokumen, maks 5 MB.
        </p>
        <input id="attachment" name="attachment" type="file" class="sr-input" accept=".jpg,.jpeg,.png,.gif,.webp,.pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.zip,.csv,.txt" onchange="document.getElementById('attachmentName').textContent = this.files[0] ? this.files[0].name : '';">
        <div id="attachmentName" class="sr-attachment-name"></div>
        @error('attachment') <p class="sr-error">{{ $message }}</p> @enderror
    </div>

    {{-- 9. Budget --}}
    <div class="sr-form-section">
        <label class="sr-label" for="budget_range">Estimasi budget <span style="color:#64748b; font-weight:400; font-size:12px;">(opsional)</span></label>
        <p class="sr-help">Kalau Anda punya gambaran budget. Kosongkan kalau belum tahu — tim kami yang akan estimasi.</p>
        <select id="budget_range" name="budget_range" class="sr-select">
            <option value="">— Pilih (opsional) —</option>
            @foreach(\App\Models\SoftwareRequest::BUDGETS as $key => $label)
                <option value="{{ $key }}" @selected(old('budget_range') === $key)>{{ $label }}</option>
            @endforeach
        </select>
        @error('budget_range') <p class="sr-error">{{ $message }}</p> @enderror
    </div>

    {{-- 10. Urgency --}}
    <div class="sr-form-section">
        <label class="sr-label" for="urgency">Kapan butuhnya? <span style="color:#64748b; font-weight:400; font-size:12px;">(opsional)</span></label>
        <select id="urgency" name="urgency" class="sr-select">
            <option value="">— Pilih (opsional) —</option>
            @foreach(\App\Models\SoftwareRequest::URGENCIES as $key => $label)
                <option value="{{ $key }}" @selected(old('urgency') === $key)>{{ $label }}</option>
            @endforeach
        </select>
        @error('urgency') <p class="sr-error">{{ $message }}</p> @enderror
    </div>

    {{-- 11. Notes --}}
    <div class="sr-form-section">
        <label class="sr-label" for="additional_notes">Catatan tambahan <span style="color:#64748b; font-weight:400; font-size:12px;">(opsional)</span></label>
        <p class="sr-help">Hal lain yang ingin ditambahkan.</p>
        <textarea id="additional_notes" name="additional_notes" class="sr-textarea" rows="3" maxlength="5000" placeholder="Hal lain yang ingin ditambahkan...">{{ old('additional_notes') }}</textarea>
        @error('additional_notes') <p class="sr-error">{{ $message }}</p> @enderror
    </div>

    <div class="sr-actions">
        <button type="submit" class="dk-btn dk-btn-primary" style="padding:12px 28px; font-size:15px;">
            <svg style="width:16px; height:16px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg>
            Kirim Request
        </button>
        <a href="{{ route('dashboard.software-requests.index') }}" class="dk-btn dk-btn-outline">Batal</a>
    </div>
</form>
@endsection
