@extends('layouts.dashboard')
@section('title', 'Tim / Downline')

@section('content')
<h1 class="text-2xl font-bold text-gray-900 mb-6">Tim / Downline</h1>

{{-- Ringkasan --}}
<div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-8">
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
        <div class="text-xs font-medium text-gray-500 uppercase">Total Tim Langsung</div>
        <div class="mt-1 text-2xl font-bold text-gray-900">{{ $downlines->count() }}</div>
    </div>
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
        <div class="text-xs font-medium text-gray-500 uppercase">Total Semua Downline</div>
        <div class="mt-1 text-2xl font-bold text-gray-900">{{ $downlines->count() + $downlines->sum(fn($d) => $d->downlines->count()) }}</div>
    </div>
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
        <div class="text-xs font-medium text-gray-500 uppercase">Total Penjualan Tim</div>
        <div class="mt-1 text-2xl font-bold text-indigo-600">{{ $downlines->sum('total_sales') + $downlines->sum(fn($d) => $d->downlines->sum('total_sales')) }}</div>
    </div>
</div>

{{-- Diagram Pohon --}}
<div class="overflow-x-auto pb-4">
    <div class="flex flex-col items-center min-w-max">

        {{-- Level 1: User (Anda) --}}
        <div class="flex flex-col items-center">
            <div class="bg-indigo-600 text-white rounded-xl shadow-lg px-6 py-4 text-center min-w-[220px]">
                <div class="flex items-center justify-center mb-2">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.121 17.804A13.937 13.937 0 0112 16c2.5 0 4.847.655 6.879 1.804M15 10a3 3 0 11-6 0 3 3 0 016 0zm6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </div>
                <div class="font-bold text-lg">{{ $user->name }}</div>
                <div class="text-indigo-200 text-xs mt-0.5">{{ $user->email }}</div>
                <div class="mt-2 flex items-center justify-center gap-3 text-xs">
                    <span class="bg-indigo-500 rounded-full px-2 py-0.5">{{ $userTotalSales }} penjualan</span>
                    <span class="bg-indigo-500 rounded-full px-2 py-0.5">Rp {{ number_format($userTotalRevenue, 0, ',', '.') }}</span>
                </div>
            </div>
        </div>

        @if($downlines->count() > 0)
        {{-- Garis vertikal dari user ke level 2 --}}
        <div class="w-px h-8 bg-gray-300"></div>

        {{-- Garis horizontal penghubung level 2 --}}
        @if($downlines->count() > 1)
        <div class="relative flex items-center justify-center" style="width: {{ $downlines->count() * 260 }}px;">
            <div class="absolute top-0 left-[calc(50%/{{ $downlines->count() }}*1)] right-[calc(50%/{{ $downlines->count() }}*1)] h-px bg-gray-300"
                 style="left: calc(100% / {{ $downlines->count() }} / 2); right: calc(100% / {{ $downlines->count() }} / 2);"></div>
        </div>
        @endif

        {{-- Level 2: Tim Langsung --}}
        <div class="flex gap-4 items-start">
            @foreach($downlines as $member)
            <div class="flex flex-col items-center" style="min-width: 240px;">
                {{-- Garis vertikal ke node --}}
                <div class="w-px h-4 bg-gray-300"></div>

                {{-- Node Member Level 2 --}}
                <div class="bg-white border-2 border-emerald-400 rounded-xl shadow-sm px-5 py-3 text-center min-w-[220px]">
                    <div class="flex items-center justify-center mb-1">
                        <svg class="w-6 h-6 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                    </div>
                    <div class="font-semibold text-sm text-gray-900">{{ $member->name }}</div>
                    <div class="text-gray-500 text-xs">{{ $member->email }}</div>
                    <div class="text-gray-400 text-xs mt-1">Gabung: {{ $member->created_at->format('d M Y') }}</div>
                    <div class="mt-2 flex items-center justify-center gap-2 text-xs">
                        <span class="bg-emerald-50 text-emerald-700 rounded-full px-2 py-0.5 font-medium">{{ $member->total_sales }} penjualan</span>
                        <span class="bg-emerald-50 text-emerald-700 rounded-full px-2 py-0.5 font-medium">Rp {{ number_format($member->total_revenue ?? 0, 0, ',', '.') }}</span>
                    </div>
                    @if($member->downlines->count() > 0)
                    <div class="mt-1 text-xs text-gray-400">{{ $member->downlines->count() }} downline</div>
                    @endif
                </div>

                {{-- Level 3: Downline dari member --}}
                @if($member->downlines->count() > 0)
                <div class="w-px h-6 bg-gray-300"></div>

                @if($member->downlines->count() > 1)
                <div class="relative flex items-center justify-center" style="width: {{ $member->downlines->count() * 210 }}px;">
                    <div class="absolute top-0 h-px bg-gray-300"
                         style="left: calc(100% / {{ $member->downlines->count() }} / 2); right: calc(100% / {{ $member->downlines->count() }} / 2);"></div>
                </div>
                @endif

                <div class="flex gap-3 items-start">
                    @foreach($member->downlines as $sub)
                    <div class="flex flex-col items-center" style="min-width: 190px;">
                        <div class="w-px h-4 bg-gray-300"></div>
                        <div class="bg-white border border-amber-300 rounded-lg shadow-sm px-4 py-2.5 text-center min-w-[180px]">
                            <div class="flex items-center justify-center mb-1">
                                <svg class="w-5 h-5 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                            </div>
                            <div class="font-medium text-xs text-gray-900">{{ $sub->name }}</div>
                            <div class="text-gray-500 text-[10px]">{{ $sub->email }}</div>
                            <div class="text-gray-400 text-[10px] mt-0.5">Gabung: {{ $sub->created_at->format('d M Y') }}</div>
                            <div class="mt-1.5 flex items-center justify-center gap-1.5 text-[10px]">
                                <span class="bg-amber-50 text-amber-700 rounded-full px-1.5 py-0.5 font-medium">{{ $sub->total_sales }} penjualan</span>
                                <span class="bg-amber-50 text-amber-700 rounded-full px-1.5 py-0.5 font-medium">Rp {{ number_format($sub->total_revenue ?? 0, 0, ',', '.') }}</span>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
                @endif
            </div>
            @endforeach
        </div>
        @else
        <div class="mt-8 text-center text-gray-500 bg-white rounded-xl shadow-sm border border-gray-200 px-6 py-8">
            Belum ada downline. Bagikan link referral Anda untuk merekrut member baru!
        </div>
        @endif
    </div>
</div>

{{-- Legend --}}
<div class="mt-6 bg-white rounded-xl shadow-sm border border-gray-200 p-4">
    <h3 class="text-xs font-semibold text-gray-500 uppercase mb-3">Keterangan Warna</h3>
    <div class="flex flex-wrap gap-6 text-sm">
        <div class="flex items-center gap-2">
            <div class="w-4 h-4 rounded bg-indigo-600"></div>
            <span class="text-gray-700">Level 1 — Anda</span>
        </div>
        <div class="flex items-center gap-2">
            <div class="w-4 h-4 rounded border-2 border-emerald-400 bg-white"></div>
            <span class="text-gray-700">Level 2 — Tim Langsung (rekrutan Anda)</span>
        </div>
        <div class="flex items-center gap-2">
            <div class="w-4 h-4 rounded border border-amber-300 bg-white"></div>
            <span class="text-gray-700">Level 3 — Downline Tim (rekrutan dari tim Anda)</span>
        </div>
    </div>
</div>
@endsection
