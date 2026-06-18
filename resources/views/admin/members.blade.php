@extends('layouts.admin')
@section('title', 'Semua Member')

@section('content')
<div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:24px;">
    <h1 class="text-2xl font-bold dk-heading">Semua Member</h1>
    <form method="GET" action="{{ route('admin.members') }}" style="display:flex; gap:8px; align-items:center;">
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama, email, WhatsApp, kode referral..." style="width:320px; padding:8px 14px; font-size:14px; border-radius:8px; border:1px solid #2d3a4a; background:#151e2d; color:#e2e8f0; outline:none;">
        <button type="submit" style="padding:8px 16px; font-size:14px; font-weight:600; border-radius:8px; border:none; background:#6366f1; color:#fff; cursor:pointer;">Cari</button>
        @if(request('search'))
            <a href="{{ route('admin.members') }}" style="padding:8px 16px; font-size:14px; font-weight:600; border-radius:8px; border:1px solid #2d3a4a; background:transparent; color:#94a3b8; text-decoration:none;">Reset</a>
        @endif
    </form>
</div>

@if(request('search'))
    <div style="margin-bottom:16px; padding:10px 16px; background:rgba(99,102,241,0.1); border:1px solid rgba(99,102,241,0.3); border-radius:8px; color:#a5b4fc; font-size:14px;">
        Hasil pencarian untuk "<strong>{{ request('search') }}</strong>" — {{ $members->total() }} member ditemukan
    </div>
@endif

{{-- Filter status verifikasi email --}}
<div class="flex flex-wrap items-center gap-2 mb-4">
    <a href="{{ route('admin.members', array_filter(['search' => request('search')])) }}"
       class="px-3 py-1.5 rounded-md text-xs font-medium"
       style="{{ empty($verification) ? 'background:#4f46e5;color:#fff' : 'background:#1a2332;color:#94a3b8;border:1px solid #2d3a4a' }}">
        Semua
    </a>
    <a href="{{ route('admin.members', array_filter(['search' => request('search'), 'verification' => 'verified'])) }}"
       class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-md text-xs font-medium"
       style="{{ $verification === 'verified' ? 'background:#047857;color:#fff' : 'background:#1a2332;color:#94a3b8;border:1px solid #2d3a4a' }}">
        Email Terverifikasi
        <span class="inline-flex items-center justify-center min-w-[18px] h-[18px] px-1 rounded-full text-[10px] font-semibold" style="background:rgba(16,185,129,0.25);color:#6ee7b7">{{ $verifiedCount }}</span>
    </a>
    <a href="{{ route('admin.members', array_filter(['search' => request('search'), 'verification' => 'unverified'])) }}"
       class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-md text-xs font-medium"
       style="{{ $verification === 'unverified' ? 'background:#b45309;color:#fff' : 'background:#1a2332;color:#94a3b8;border:1px solid #2d3a4a' }}">
        Belum Verifikasi
        <span class="inline-flex items-center justify-center min-w-[18px] h-[18px] px-1 rounded-full text-[10px] font-semibold" style="background:rgba(234,179,8,0.25);color:#fde047">{{ $unverifiedCount }}</span>
    </a>
</div>

@if(session('success'))
    <div class="dk-alert-success">
        {{ session('success') }}
    </div>
@endif

<div class="dk-table">
    <table class="min-w-full">
        <thead>
            <tr>
                <th class="px-6 py-3 text-left text-xs font-medium uppercase" style="color:#94a3b8">Nama</th>
                <th class="px-6 py-3 text-left text-xs font-medium uppercase" style="color:#94a3b8">Email</th>
                <th class="px-6 py-3 text-left text-xs font-medium uppercase" style="color:#94a3b8">WhatsApp</th>
                <th class="px-6 py-3 text-left text-xs font-medium uppercase" style="color:#94a3b8">Status</th>
                <th class="px-6 py-3 text-left text-xs font-medium uppercase" style="color:#94a3b8">Verifikasi Email</th>
                <th class="px-6 py-3 text-left text-xs font-medium uppercase" style="color:#94a3b8">Konteks Aktivasi</th>
                <th class="px-6 py-3 text-left text-xs font-medium uppercase" style="color:#94a3b8">Kode Referral</th>
                <th class="px-6 py-3 text-left text-xs font-medium uppercase" style="color:#94a3b8">Upline</th>
                <th class="px-6 py-3 text-left text-xs font-medium uppercase" style="color:#94a3b8">Downline</th>
                <th class="px-6 py-3 text-left text-xs font-medium uppercase" style="color:#94a3b8">Saldo</th>
                <th class="px-6 py-3 text-left text-xs font-medium uppercase" style="color:#94a3b8">Bergabung</th>
                <th class="px-6 py-3 text-left text-xs font-medium uppercase" style="color:#94a3b8">Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse($members as $member)
            <tr>
                <td class="px-6 py-4 text-sm font-medium" style="color:#e2e8f0">{{ $member->name }}</td>
                <td class="px-6 py-4 text-sm" style="color:#94a3b8">{{ $member->email }}</td>
                <td class="px-6 py-4 text-sm" style="color:#94a3b8">{{ $member->whatsapp_number ?? '-' }}</td>
                <td class="px-6 py-4 text-sm">
                    @if(($member->status ?? 'active') === 'active')
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium dk-badge" style="background:rgba(16,185,129,0.15);color:#6ee7b7">Active</span>
                    @else
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium dk-badge" style="background:rgba(234,179,8,0.15);color:#fde047">Pending</span>
                    @endif
                </td>
                <td class="px-6 py-4 text-sm">
                    @if($member->email_verified_at)
                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-xs font-medium dk-badge" style="background:rgba(16,185,129,0.15);color:#6ee7b7" title="Terverifikasi {{ $member->email_verified_at->format('d M Y H:i') }}">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            Terverifikasi
                        </span>
                    @else
                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-xs font-medium" style="background:rgba(234,179,8,0.15);color:#fde047" title="Email belum diverifikasi">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            Belum
                        </span>
                    @endif
                </td>
                <td class="px-6 py-4 text-sm" style="color:#94a3b8">
                    @if($member->intendedProduct)
                        <div class="flex flex-col gap-0.5">
                            <span class="text-[11px] uppercase tracking-wide " style="color:#4a5568">Ingin beli</span>
                            <a href="{{ route('product.show', $member->intendedProduct->slug) }}" target="_blank" class="font-medium" style="color:#818cf8">{{ $member->intendedProduct->title }}</a>
                            <span class="dk-text-muted" style="font-size:12px">Rp {{ number_format($member->intendedProduct->price, 0, ',', '.') }}</span>
                            @if($member->upline)
                                <span class="dk-text-muted" style="font-size:12px">via afiliasi <span class="font-medium dk-text">{{ $member->upline->name }}</span></span>
                            @endif
                        </div>
                    @else
                        <span style="color:#4a5568">-</span>
                    @endif
                </td>
                <td class="px-6 py-4 text-sm font-mono" style="color:#818cf8">{{ $member->referral_code }}</td>
                <td class="px-6 py-4 text-sm" style="color:#94a3b8">{{ $member->upline->name ?? '-' }}</td>
                <td class="px-6 py-4 text-sm" style="color:#94a3b8">{{ $member->downlines_count }}</td>
                <td class="px-6 py-4 text-sm font-medium" style="color:#e2e8f0">Rp {{ number_format($member->balance, 0, ',', '.') }}</td>
                <td class="px-6 py-4 text-sm" style="color:#94a3b8">{{ $member->created_at->format('d M Y') }}</td>
                <td class="px-6 py-4">
                    <div class="flex items-center gap-3">
                        @if(($member->status ?? 'active') === 'pending')
                            <form method="POST" action="{{ route('admin.members.activate', $member) }}">
                                @csrf @method('PATCH')
                                <button type="submit" class="text-sm font-medium" style="color:#6ee7b7">Aktifkan</button>
                            </form>
                        @endif
                        <a href="{{ route('admin.members.edit', $member) }}" class="text-sm font-medium" style="color:#818cf8">Edit</a>
                        <form method="POST" action="{{ route('admin.members.destroy', $member) }}" onsubmit="return confirm('Hapus member {{ $member->name }}? Data komisi dan order terkait tidak akan dihapus.')">
                            @csrf @method('DELETE')
                            <button type="submit" class="text-sm font-medium" style="color:#fca5a5">Hapus</button>
                        </form>
                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="12" class="px-6 py-8 text-center" style="color:#64748b">Belum ada member.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>
<div class="mt-4">{{ $members->links() }}</div>
@endsection
