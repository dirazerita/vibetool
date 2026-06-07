@extends('layouts.dashboard')
@section('title', 'Pengaturan')

@section('content')
<h1 class="text-2xl font-bold dk-heading mb-6">Pengaturan Profil</h1>

<div class="max-w-2xl">
    <div class="dk-card" style="padding:24px;">
        <form method="POST" action="{{ route('dashboard.settings.update') }}" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <div class="space-y-6">
                {{-- Foto Profil --}}
                <div>
                    <label class="dk-label">Foto Profil</label>
                    <div style="display:flex; align-items:center; gap:16px; margin-top:8px;">
                        <div style="position:relative; width:80px; height:80px; flex-shrink:0;">
                            @if($user->profile_photo)
                                <img src="{{ asset('storage/' . $user->profile_photo) }}" alt="{{ $user->name }}"
                                     style="width:80px; height:80px; border-radius:50%; object-fit:cover; border:2px solid #2d3a4a;"
                                     id="photo-preview">
                            @else
                                <div style="width:80px; height:80px; border-radius:50%; background:linear-gradient(135deg,#4f46e5,#7c3aed); display:flex; align-items:center; justify-content:center; font-size:28px; font-weight:700; color:#fff; border:2px solid #2d3a4a;"
                                     id="photo-preview-placeholder">
                                    {{ strtoupper(substr($user->name, 0, 1)) }}
                                </div>
                                <img src="" alt="" style="width:80px; height:80px; border-radius:50%; object-fit:cover; border:2px solid #2d3a4a; display:none;"
                                     id="photo-preview">
                            @endif
                        </div>
                        <div style="flex:1;">
                            <input type="file" name="profile_photo" id="profile_photo" accept="image/jpeg,image/png,image/webp"
                                   style="display:none;"
                                   onchange="previewPhoto(this)">
                            <div style="display:flex; gap:8px; flex-wrap:wrap;">
                                <button type="button" onclick="document.getElementById('profile_photo').click()"
                                        class="dk-btn dk-btn-outline" style="font-size:13px; padding:6px 14px;">
                                    <svg style="width:16px; height:16px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                    Upload Foto
                                </button>
                                @if($user->profile_photo)
                                <label style="display:inline-flex; align-items:center; gap:4px; font-size:13px; color:#f87171; cursor:pointer;">
                                    <input type="checkbox" name="remove_photo" value="1" style="accent-color:#f87171;">
                                    Hapus foto
                                </label>
                                @endif
                            </div>
                            <p style="font-size:12px; color:#64748b; margin-top:6px;">JPG, PNG, atau WebP. Maksimal 2MB.</p>
                        </div>
                    </div>
                    @error('profile_photo') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

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
                    <label for="referral_code" class="dk-label">Kode Referral</label>
                    <input
                        type="text"
                        name="referral_code"
                        id="referral_code"
                        value="{{ old('referral_code', $user->referral_code) }}"
                        class="w-full dk-input font-mono uppercase"
                        style="font-family:ui-monospace,SFMono-Regular,Menlo,Monaco,Consolas,monospace; letter-spacing:0.05em;"
                        maxlength="20"
                        pattern="[A-Za-z0-9]{4,20}"
                        autocomplete="off"
                        spellcheck="false"
                        required
                    >
                    <p class="text-xs mt-1" style="color:#8b95a7;">
                        4-20 karakter, hanya huruf (A-Z) dan angka (0-9). Otomatis di-uppercase.
                        Mengubah kode akan membuat link affiliate lama (<code>?ref={{ $user->referral_code }}</code>) tidak berlaku.
                    </p>
                    @error('referral_code') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                {{-- Sosial Media --}}
                <div class="dk-divider pt-6">
                    <h3 class="text-lg font-medium dk-heading mb-4">Sosial Media</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="social_instagram" class="dk-label">
                                <span style="display:inline-flex; align-items:center; gap:6px;">
                                    <svg style="width:16px; height:16px; color:#e1306c;" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zM12 0C8.741 0 8.333.014 7.053.072 2.695.272.273 2.69.073 7.052.014 8.333 0 8.741 0 12c0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98C8.333 23.986 8.741 24 12 24c3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98C15.668.014 15.259 0 12 0zm0 5.838a6.162 6.162 0 100 12.324 6.162 6.162 0 000-12.324zM12 16a4 4 0 110-8 4 4 0 010 8zm6.406-11.845a1.44 1.44 0 100 2.881 1.44 1.44 0 000-2.881z"/></svg>
                                    Instagram
                                </span>
                            </label>
                            <input type="text" name="social_instagram" id="social_instagram" value="{{ old('social_instagram', $user->social_instagram) }}" class="w-full dk-input" placeholder="@username">
                        </div>
                        <div>
                            <label for="social_facebook" class="dk-label">
                                <span style="display:inline-flex; align-items:center; gap:6px;">
                                    <svg style="width:16px; height:16px; color:#1877f2;" fill="currentColor" viewBox="0 0 24 24"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>
                                    Facebook
                                </span>
                            </label>
                            <input type="text" name="social_facebook" id="social_facebook" value="{{ old('social_facebook', $user->social_facebook) }}" class="w-full dk-input" placeholder="Username atau URL">
                        </div>
                        <div>
                            <label for="social_twitter" class="dk-label">
                                <span style="display:inline-flex; align-items:center; gap:6px;">
                                    <svg style="width:16px; height:16px; color:#fff;" fill="currentColor" viewBox="0 0 24 24"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/></svg>
                                    X (Twitter)
                                </span>
                            </label>
                            <input type="text" name="social_twitter" id="social_twitter" value="{{ old('social_twitter', $user->social_twitter) }}" class="w-full dk-input" placeholder="@username">
                        </div>
                        <div>
                            <label for="social_tiktok" class="dk-label">
                                <span style="display:inline-flex; align-items:center; gap:6px;">
                                    <svg style="width:16px; height:16px; color:#fff;" fill="currentColor" viewBox="0 0 24 24"><path d="M12.525.02c1.31-.02 2.61-.01 3.91-.02.08 1.53.63 3.09 1.75 4.17 1.12 1.11 2.7 1.62 4.24 1.79v4.03c-1.44-.05-2.89-.35-4.2-.97-.57-.26-1.1-.59-1.62-.93-.01 2.92.01 5.84-.02 8.75-.08 1.4-.54 2.79-1.35 3.94-1.31 1.92-3.58 3.17-5.91 3.21-1.43.08-2.86-.31-4.08-1.03-2.02-1.19-3.44-3.37-3.65-5.71-.02-.5-.03-1-.01-1.49.18-1.9 1.12-3.72 2.58-4.96 1.66-1.44 3.98-2.13 6.15-1.72.02 1.48-.04 2.96-.04 4.44-.99-.32-2.15-.23-3.02.37-.63.41-1.11 1.04-1.36 1.75-.21.51-.15 1.07-.14 1.61.24 1.64 1.82 3.02 3.5 2.87 1.12-.01 2.19-.66 2.77-1.61.19-.33.4-.67.41-1.06.1-1.79.06-3.57.07-5.36.01-4.03-.01-8.05.02-12.07z"/></svg>
                                    TikTok
                                </span>
                            </label>
                            <input type="text" name="social_tiktok" id="social_tiktok" value="{{ old('social_tiktok', $user->social_tiktok) }}" class="w-full dk-input" placeholder="@username">
                        </div>
                        <div>
                            <label for="social_youtube" class="dk-label">
                                <span style="display:inline-flex; align-items:center; gap:6px;">
                                    <svg style="width:16px; height:16px; color:#ff0000;" fill="currentColor" viewBox="0 0 24 24"><path d="M23.498 6.186a3.016 3.016 0 00-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 00.502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 002.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 002.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z"/></svg>
                                    YouTube
                                </span>
                            </label>
                            <input type="text" name="social_youtube" id="social_youtube" value="{{ old('social_youtube', $user->social_youtube) }}" class="w-full dk-input" placeholder="URL channel">
                        </div>
                        <div>
                            <label for="social_website" class="dk-label">
                                <span style="display:inline-flex; align-items:center; gap:6px;">
                                    <svg style="width:16px; height:16px; color:#60a5fa;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"></path></svg>
                                    Website
                                </span>
                            </label>
                            <input type="url" name="social_website" id="social_website" value="{{ old('social_website', $user->social_website) }}" class="w-full dk-input" placeholder="https://example.com">
                        </div>
                    </div>
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

<script>
function previewPhoto(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            const preview = document.getElementById('photo-preview');
            const placeholder = document.getElementById('photo-preview-placeholder');
            preview.src = e.target.result;
            preview.style.display = 'block';
            if (placeholder) placeholder.style.display = 'none';
        };
        reader.readAsDataURL(input.files[0]);
    }
}
</script>
@endsection
