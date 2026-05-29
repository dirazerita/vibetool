@extends('layouts.admin')
@section('title', 'Buat Broadcast')

@section('content')
<div style="display:flex; align-items:center; gap:12px; margin-bottom:24px;">
    <a href="{{ route('admin.broadcasts.index') }}" class="dk-btn dk-btn-outline" style="padding:6px 12px;">
        <svg style="width:16px; height:16px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
        Kembali
    </a>
    <div>
        <h1 class="text-2xl font-bold dk-heading">Buat Broadcast</h1>
        <p class="dk-text-muted" style="font-size:14px; margin-top:4px;">Pesan akan masuk ke thread chat masing-masing member yang masuk audience.</p>
    </div>
</div>

<form method="POST" action="{{ route('admin.broadcasts.store') }}" enctype="multipart/form-data" class="dk-card" style="padding:24px; max-width:760px;" onsubmit="return confirmSend();">
    @csrf

    <div style="margin-bottom:20px;">
        <label class="dk-label">Audience</label>
        <div style="display:flex; flex-direction:column; gap:10px; margin-top:6px;">
            <label style="display:flex; align-items:flex-start; gap:10px; cursor:pointer; padding:12px 14px; background:#151e2d; border:1px solid #2d3a4a; border-radius:10px;">
                <input type="radio" name="audience_scope" value="active" {{ old('audience_scope', 'active') === 'active' ? 'checked' : '' }} class="dk-checkbox" style="border-radius:50%; margin-top:1px;">
                <div>
                    <div style="color:#e2e8f0; font-weight:600;">Member aktif</div>
                    <div class="dk-text-muted" style="font-size:13px;">{{ number_format($audienceCounts['active']) }} member dengan status <code>active</code>.</div>
                </div>
            </label>
            <label style="display:flex; align-items:flex-start; gap:10px; cursor:pointer; padding:12px 14px; background:#151e2d; border:1px solid #2d3a4a; border-radius:10px;">
                <input type="radio" name="audience_scope" value="all" {{ old('audience_scope') === 'all' ? 'checked' : '' }} class="dk-checkbox" style="border-radius:50%; margin-top:1px;">
                <div>
                    <div style="color:#e2e8f0; font-weight:600;">Semua member</div>
                    <div class="dk-text-muted" style="font-size:13px;">{{ number_format($audienceCounts['all']) }} member, termasuk yang pending aktivasi.</div>
                </div>
            </label>
        </div>
        @error('audience_scope') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
    </div>

    <div style="margin-bottom:20px;">
        <label for="body" class="dk-label">Isi pesan</label>
        <textarea name="body" id="body" rows="6" class="w-full dk-input" placeholder="Tulis pesan broadcast..." maxlength="5000">{{ old('body') }}</textarea>
        @error('body') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
    </div>

    <div style="margin-bottom:24px;">
        <label class="dk-label">Lampiran (opsional)</label>
        <label class="dk-btn dk-btn-outline" style="cursor:pointer;" for="attachment">
            <svg style="width:16px; height:16px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/></svg>
            Pilih file
        </label>
        <input type="file" name="attachment" id="attachment" style="display:none;" onchange="document.getElementById('attachmentName').textContent=this.files[0]?.name || ''">
        <span id="attachmentName" class="dk-text" style="margin-left:10px; font-size:13px;"></span>
        <p class="dk-text-muted" style="font-size:12px; margin-top:6px;">Maks 5 MB. Format: gambar (jpg/png/gif/webp), PDF, Office (doc/xls/ppt), ZIP, CSV, TXT.</p>
        @error('attachment') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
    </div>

    <div style="display:flex; gap:12px;">
        <button type="submit" class="dk-btn dk-btn-primary">
            <svg style="width:16px; height:16px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg>
            Kirim Broadcast
        </button>
        <a href="{{ route('admin.broadcasts.index') }}" class="dk-btn dk-btn-outline">Batal</a>
    </div>
</form>

<script>
    function confirmSend() {
        var scope = document.querySelector('input[name="audience_scope"]:checked')?.value || 'active';
        var counts = @json($audienceCounts);
        var n = counts[scope] || 0;
        if (n === 0) {
            alert('Audience ini tidak memiliki member.');
            return false;
        }
        return confirm('Kirim broadcast ke ' + n + ' member?');
    }
</script>
@endsection
