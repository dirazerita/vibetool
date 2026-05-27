@extends('layouts.admin')
@section('title', 'Edit Member')

@section('content')
<h1 class="text-2xl font-bold dk-heading mb-6">Edit Member: {{ $user->name }}</h1>

<div class="max-w-2xl">
    <div class="dk-card" style="padding:24px;">
        <form method="POST" action="{{ route('admin.members.update', $user) }}">
            @csrf @method('PUT')
            <div class="space-y-6">
                <div>
                    <label for="name" class="dk-label">Nama</label>
                    <input type="text" name="name" id="name" value="{{ old('name', $user->name) }}" class="w-full dk-input" required>
                    @error('name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="email" class="dk-label">Email</label>
                    <input type="email" name="email" id="email" value="{{ old('email', $user->email) }}" class="w-full dk-input" required>
                    @error('email') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="whatsapp_number" class="dk-label">Nomor WhatsApp</label>
                    <input type="tel" name="whatsapp_number" id="whatsapp_number" value="{{ old('whatsapp_number', $user->whatsapp_number) }}" class="w-full dk-input" placeholder="Contoh: 08123456789">
                    @error('whatsapp_number') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="referral_code" class="dk-label">Kode Referral</label>
                    <input type="text" name="referral_code" id="referral_code" value="{{ old('referral_code', $user->referral_code) }}" class="w-full dk-input uppercase" required>
                    @error('referral_code') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="password" class="dk-label">Password Baru (kosongkan jika tidak ingin mengubah)</label>
                    <input type="password" name="password" id="password" class="w-full dk-input" placeholder="Masukkan password baru">
                    @error('password') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="upline_id" class="dk-label">Upline</label>
                    <select name="upline_id" id="upline_id" class="w-full dk-input">
                        <option value="">-- Tidak ada upline --</option>
                        @foreach($members as $member)
                            <option value="{{ $member->id }}" {{ old('upline_id', $user->upline_id) == $member->id ? 'selected' : '' }}>{{ $member->name }} ({{ $member->email }})</option>
                        @endforeach
                    </select>
                    @error('upline_id') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div class="dk-card" style="padding:16px; border-left:4px solid #6366f1;">
                    <div style="display:flex; align-items:center; gap:12px;">
                        <label style="position:relative; display:inline-block; width:48px; height:26px; cursor:pointer;">
                            <input type="hidden" name="can_upload_product" value="0">
                            <input type="checkbox" name="can_upload_product" value="1" id="can_upload_product" {{ old('can_upload_product', $user->can_upload_product) ? 'checked' : '' }} style="opacity:0; width:0; height:0; position:absolute;">
                            <span id="toggle-track" style="position:absolute; cursor:pointer; top:0; left:0; right:0; bottom:0; border-radius:26px; transition:0.3s; {{ old('can_upload_product', $user->can_upload_product) ? 'background:#6366f1;' : 'background:#2d3a4a;' }}"></span>
                            <span id="toggle-dot" style="position:absolute; height:20px; width:20px; bottom:3px; border-radius:50%; transition:0.3s; background:white; {{ old('can_upload_product', $user->can_upload_product) ? 'left:25px;' : 'left:3px;' }}"></span>
                        </label>
                        <div>
                            <span class="text-sm font-medium dk-heading">Izinkan Upload Produk</span>
                            <p class="text-xs dk-text-muted" style="margin-top:2px;">Jika aktif, member ini bisa mengupload produk dari dashboard. Produk tetap harus di-approve admin.</p>
                        </div>
                    </div>
                </div>
                <script>
                    (function(){
                        var cb = document.getElementById('can_upload_product');
                        var track = document.getElementById('toggle-track');
                        var dot = document.getElementById('toggle-dot');
                        cb.parentElement.addEventListener('click', function(e){
                            if(e.target === cb) return;
                            cb.checked = !cb.checked;
                            updateToggle();
                        });
                        cb.addEventListener('change', updateToggle);
                        function updateToggle(){
                            if(cb.checked){
                                track.style.background = '#6366f1';
                                dot.style.left = '25px';
                            } else {
                                track.style.background = '#2d3a4a';
                                dot.style.left = '3px';
                            }
                        }
                    })();
                </script>

                <div class="dk-card" style="padding:16px">
                    <p class="dk-text" style="font-size:14px">Status:
                        @if(($user->status ?? 'active') === 'active')
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium dk-badge" style="background:rgba(16,185,129,0.15);color:#6ee7b7">Active</span>
                        @else
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium dk-badge" style="background:rgba(234,179,8,0.15);color:#fde047">Pending</span>
                        @endif
                    </p>
                    <p class="dk-text" style="font-size:14px">Saldo: <strong>Rp {{ number_format($user->balance, 0, ',', '.') }}</strong></p>
                    <p class="dk-text" style="font-size:14px">Bergabung: <strong>{{ $user->created_at->format('d M Y H:i') }}</strong></p>
                </div>
            </div>

            <div class="mt-6 flex gap-4">
                <button type="submit" class="dk-btn dk-btn-primary">Update Member</button>
                <a href="{{ route('admin.members') }}" class="dk-btn dk-btn-outline">Batal</a>
            </div>
        </form>

        @if(($user->status ?? 'active') === 'pending')
            <form method="POST" action="{{ route('admin.members.activate', $user) }}" class="mt-4 pt-4 dk-divider">
                @csrf @method('PATCH')
                <button type="submit"
                        class="px-6 py-2.5 rounded-lg font-medium"
                        class="dk-btn dk-btn-success" style="
                        onmouseover="this.style.backgroundColor='#15803d'"
                        onmouseout="this.style.backgroundColor='#16a34a'">Aktifkan Member</button>
            </form>
        @else
            <form method="POST" action="{{ route('admin.members.deactivate', $user) }}" class="mt-4 pt-4 dk-divider" onsubmit="return confirm('Set status member ini menjadi pending? Member tidak akan bisa login sampai diaktifkan kembali.')">
                @csrf @method('PATCH')
                <button type="submit" class="dk-btn dk-btn-warning">Nonaktifkan (Set ke Pending)</button>
            </form>
        @endif
    </div>
</div>
@endsection
