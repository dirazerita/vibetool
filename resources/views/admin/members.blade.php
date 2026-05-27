@extends('layouts.admin')
@section('title', 'Semua Member')

@section('content')
<h1 class="text-2xl font-bold dk-heading mb-6">Semua Member</h1>

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
                <td colspan="11" class="px-6 py-8 text-center" style="color:#64748b">Belum ada member.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>
<div class="mt-4">{{ $members->links() }}</div>
@endsection
