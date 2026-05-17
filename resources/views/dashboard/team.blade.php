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
<div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">

    {{-- Level 1: Root User --}}
    <div class="flex justify-center">
        <div class="bg-indigo-600 text-white rounded-lg shadow-md px-5 py-3 text-center">
            <div class="font-bold text-sm">{{ $user->name }}</div>
            <div class="text-indigo-200 text-[10px]">{{ $user->email }}</div>
            <div class="flex justify-center gap-2 mt-1 text-[10px]">
                <span class="bg-indigo-500/60 rounded-full px-1.5 py-0.5">{{ $userTotalSales }} penj.</span>
                <span class="bg-indigo-500/60 rounded-full px-1.5 py-0.5">Rp {{ number_format($userTotalRevenue, 0, ',', '.') }}</span>
            </div>
        </div>
    </div>

    @if($downlines->count() > 0)
    {{-- Connector root to level 2 --}}
    <div class="flex justify-center"><div class="w-0.5 h-5 bg-gray-300"></div></div>

    {{-- Level 2 header --}}
    <div class="flex items-center gap-2 mb-2">
        <div class="h-0.5 flex-1 bg-emerald-200"></div>
        <span class="text-[10px] text-emerald-600 font-semibold whitespace-nowrap">TIM LANGSUNG ({{ $downlines->count() }})</span>
        <div class="h-0.5 flex-1 bg-emerald-200"></div>
    </div>

    {{-- Level 2: Tim Langsung grid --}}
    <div class="grid grid-cols-4 sm:grid-cols-6 md:grid-cols-8 gap-1.5">
        @foreach($downlines as $i => $member)
        <div class="relative pt-3">
            <div class="absolute top-0 left-1/2 -translate-x-1/2 w-0.5 h-3 bg-gray-300"></div>
            <div class="bg-white border-2 border-emerald-400 rounded-lg px-2 py-1.5 text-center h-full">
                <div class="font-semibold text-[10px] text-gray-900 truncate">{{ $member->name }}</div>
                <div class="text-gray-400 text-[7px] truncate">{{ $member->email }}</div>
                <div class="text-gray-400 text-[7px]">{{ $member->created_at->format('d/m/Y') }}</div>
                <div class="flex justify-center gap-0.5 mt-0.5 text-[7px]">
                    <span class="bg-emerald-50 text-emerald-700 rounded-full px-1 py-0.5">{{ $member->total_sales }} penj.</span>
                    <span class="bg-emerald-50 text-emerald-700 rounded-full px-1 py-0.5">Rp {{ number_format($member->total_revenue ?? 0, 0, ',', '.') }}</span>
                </div>
                @if($member->downlines->count() > 0)
                <div class="text-[7px] text-gray-400 mt-0.5">{{ $member->downlines->count() }} downline</div>
                @endif
            </div>
        </div>
        @endforeach
    </div>

    {{-- Level 3: Downline dari tim --}}
    @php $hasLevel3 = $downlines->contains(fn($m) => $m->downlines->count() > 0); @endphp
    @if($hasLevel3)
    <div class="mt-5">
        {{-- Level 3 header --}}
        <div class="flex items-center gap-2 mb-3">
            <div class="h-0.5 flex-1 bg-amber-200"></div>
            <span class="text-[10px] text-amber-600 font-semibold whitespace-nowrap">DOWNLINE TIM</span>
            <div class="h-0.5 flex-1 bg-amber-200"></div>
        </div>

        @foreach($downlines as $i => $member)
            @if($member->downlines->count() > 0)
            <div class="mb-3 ml-2 pl-3 border-l-2 border-amber-300">
                <div class="text-[10px] text-gray-500 mb-1.5">
                    Dari <span class="font-semibold text-gray-700">{{ $member->name }}</span>
                    <span class="text-gray-400">({{ $member->downlines->count() }} orang)</span>
                </div>
                <div class="grid grid-cols-4 sm:grid-cols-6 md:grid-cols-8 gap-1.5">
                    @foreach($member->downlines as $j => $sub)
                    <div class="bg-white border border-amber-300 rounded-lg px-2 py-1.5 text-center">
                        <div class="font-medium text-[9px] text-gray-900 truncate">{{ $sub->name }}</div>
                        <div class="text-gray-400 text-[7px] truncate">{{ $sub->email }}</div>
                        <div class="text-gray-400 text-[7px]">{{ $sub->created_at->format('d/m/Y') }}</div>
                        <div class="flex justify-center gap-0.5 mt-0.5 text-[7px]">
                            <span class="bg-amber-50 text-amber-700 rounded-full px-1 py-0.5">{{ $sub->total_sales }} penj.</span>
                            <span class="bg-amber-50 text-amber-700 rounded-full px-1 py-0.5">Rp {{ number_format($sub->total_revenue ?? 0, 0, ',', '.') }}</span>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif
        @endforeach
    </div>
    @endif

    @else
    <div class="mt-8 text-center text-gray-500 py-8">
        Belum ada downline. Bagikan link referral Anda untuk merekrut member baru!
    </div>
    @endif

</div>

{{-- Legend --}}
<div class="mt-4 bg-white rounded-xl shadow-sm border border-gray-200 p-4">
    <h3 class="text-xs font-semibold text-gray-500 uppercase mb-3">Keterangan Warna</h3>
    <div class="flex flex-wrap gap-6 text-sm">
        <div class="flex items-center gap-2">
            <div class="w-4 h-4 rounded bg-indigo-600"></div>
            <span class="text-gray-700">Level 1 — Anda</span>
        </div>
        <div class="flex items-center gap-2">
            <div class="w-4 h-4 rounded border-2 border-emerald-400 bg-white"></div>
            <span class="text-gray-700">Level 2 — Tim Langsung</span>
        </div>
        <div class="flex items-center gap-2">
            <div class="w-4 h-4 rounded border border-amber-300 bg-white"></div>
            <span class="text-gray-700">Level 3 — Downline Tim</span>
        </div>
    </div>
</div>
@endsection
