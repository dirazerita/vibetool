@extends('layouts.admin')
@section('title', 'Semua Member')

@section('content')
<h1 class="text-2xl font-bold text-gray-900 mb-6">Semua Member</h1>

@if(session('success'))
    <div class="mb-4 p-3 bg-green-50 border border-green-200 rounded-lg text-sm text-green-800">
        {{ session('success') }}
    </div>
@endif

<div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
    <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
            <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nama</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Email</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">WhatsApp</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Kode Referral</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Upline</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Downline</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Saldo</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Bergabung</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Aksi</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-200">
            @forelse($members as $member)
            <tr>
                <td class="px-6 py-4 text-sm font-medium text-gray-900">{{ $member->name }}</td>
                <td class="px-6 py-4 text-sm text-gray-600">{{ $member->email }}</td>
                <td class="px-6 py-4 text-sm text-gray-600">{{ $member->whatsapp_number ?? '-' }}</td>
                <td class="px-6 py-4 text-sm">
                    @if(($member->status ?? 'active') === 'active')
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800">Active</span>
                    @else
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-yellow-100 text-yellow-800">Pending</span>
                    @endif
                </td>
                <td class="px-6 py-4 text-sm font-mono text-indigo-600">{{ $member->referral_code }}</td>
                <td class="px-6 py-4 text-sm text-gray-600">{{ $member->upline->name ?? '-' }}</td>
                <td class="px-6 py-4 text-sm text-gray-600">{{ $member->downlines_count }}</td>
                <td class="px-6 py-4 text-sm font-medium text-gray-900">Rp {{ number_format($member->balance, 0, ',', '.') }}</td>
                <td class="px-6 py-4 text-sm text-gray-600">{{ $member->created_at->format('d M Y') }}</td>
                <td class="px-6 py-4">
                    <div class="flex items-center gap-3">
                        @if(($member->status ?? 'active') === 'pending')
                            <form method="POST" action="{{ route('admin.members.activate', $member) }}">
                                @csrf @method('PATCH')
                                <button type="submit" class="text-green-600 hover:text-green-800 text-sm font-medium">Aktifkan</button>
                            </form>
                        @endif
                        <a href="{{ route('admin.members.edit', $member) }}" class="text-indigo-600 hover:text-indigo-800 text-sm font-medium">Edit</a>
                        <form method="POST" action="{{ route('admin.members.destroy', $member) }}" onsubmit="return confirm('Hapus member {{ $member->name }}? Data komisi dan order terkait tidak akan dihapus.')">
                            @csrf @method('DELETE')
                            <button type="submit" class="text-red-600 hover:text-red-800 text-sm font-medium">Hapus</button>
                        </form>
                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="10" class="px-6 py-8 text-center text-gray-500">Belum ada member.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>
<div class="mt-4">{{ $members->links() }}</div>
@endsection
