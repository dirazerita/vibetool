@extends('layouts.dashboard')
@section('title', 'Dashboard')

@section('content')
<h1 class="text-2xl font-bold text-gray-900 mb-6">Dashboard</h1>

<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <div class="text-sm text-gray-500 mb-1">Saldo</div>
        <div class="text-2xl font-bold text-indigo-600">Rp {{ number_format($user->balance, 0, ',', '.') }}</div>
    </div>
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <div class="text-sm text-gray-500 mb-1">Total Komisi</div>
        <div class="text-2xl font-bold text-green-600">Rp {{ number_format($totalCommissions, 0, ',', '.') }}</div>
    </div>
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <div class="text-sm text-gray-500 mb-1">Total Penjualan</div>
        <div class="text-2xl font-bold text-blue-600">{{ $totalOrders }}</div>
    </div>
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <div class="text-sm text-gray-500 mb-1">Total Downline</div>
        <div class="text-2xl font-bold text-purple-600">{{ $totalDownlines }}</div>
    </div>
</div>

<div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
    <h2 class="text-lg font-semibold text-gray-900 mb-4">Kode Referral Anda</h2>
    <div class="flex items-center gap-4">
        <div class="bg-gray-100 px-6 py-3 rounded-lg font-mono text-lg font-bold text-indigo-600">{{ $user->referral_code }}</div>
        <div class="text-sm text-gray-500">
            <p>Link referral registrasi:</p>
            <code class="text-xs bg-gray-100 px-2 py-1 rounded">{{ url('/register?ref=' . $user->referral_code) }}</code>
        </div>
    </div>
</div>
@endsection
