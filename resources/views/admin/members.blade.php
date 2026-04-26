@extends('layouts.admin')
@section('title', 'Semua Member')

@section('content')
<h1 class="text-2xl font-bold text-gray-900 mb-6">Semua Member</h1>

<div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
    <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
            <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nama</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Email</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">WhatsApp</th>
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
                <td class="px-6 py-4 text-sm font-mono text-indigo-600">{{ $member->referral_code }}</td>
                <td class="px-6 py-4 text-sm text-gray-600">{{ $member->upline->name ?? '-' }}</td>
                <td class="px-6 py-4 text-sm text-gray-600">{{ $member->downlines_count }}</td>
                <td class="px-6 py-4 text-sm font-medium text-gray-900">Rp {{ number_format($member->balance, 0, ',', '.') }}</td>
                <td class="px-6 py-4 text-sm text-gray-600">{{ $member->created_at->format('d M Y') }}</td>
                <td class="px-6 py-4">
                    <div class="flex items-center gap-3">
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
                <td colspan="9" class="px-6 py-8 text-center text-gray-500">Belum ada member.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>
<div class="mt-4">{{ $members->links() }}</div>
@endsection
