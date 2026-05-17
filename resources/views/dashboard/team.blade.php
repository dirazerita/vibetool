@extends('layouts.dashboard')
@section('title', 'Tim / Downline')

@section('content')
<style>
    .tree-wrap { position: relative; }
    .tree-svg { position: absolute; top: 0; left: 0; width: 100%; height: 100%; pointer-events: none; z-index: 0; }
    .tree-svg line { stroke: #d1d5db; stroke-width: 1.5; }
    .tree-levels { position: relative; z-index: 1; }
</style>

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
<div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 overflow-x-auto">
    <div id="tree-wrap" class="tree-wrap" style="min-height: 200px;">
        <svg id="tree-svg" class="tree-svg"></svg>
        <div class="tree-levels">

            {{-- Level 1: User (Anda) --}}
            <div class="flex justify-center mb-0">
                <div class="inline-block" data-node="root">
                    <div class="bg-indigo-600 text-white rounded-lg shadow-md px-4 py-2.5 text-center">
                        <div class="font-bold text-sm">{{ $user->name }}</div>
                        <div class="text-indigo-200 text-[10px]">{{ $user->email }}</div>
                        <div class="flex justify-center gap-2 mt-1 text-[10px]">
                            <span class="bg-indigo-500 rounded-full px-1.5 py-0.5">{{ $userTotalSales }} penj.</span>
                            <span class="bg-indigo-500 rounded-full px-1.5 py-0.5">Rp {{ number_format($userTotalRevenue, 0, ',', '.') }}</span>
                        </div>
                    </div>
                </div>
            </div>

            @if($downlines->count() > 0)
            {{-- Level 2: Tim Langsung --}}
            <div class="flex justify-center flex-wrap gap-1.5 mt-10">
                @foreach($downlines as $i => $member)
                <div class="inline-block" data-node="l2-{{ $i }}" data-parent="root">
                    <div class="bg-white border-2 border-emerald-400 rounded-lg shadow-sm px-3 py-2 text-center" style="min-width: 115px;">
                        <div class="font-semibold text-[11px] text-gray-900 truncate" style="max-width: 110px;">{{ $member->name }}</div>
                        <div class="text-gray-400 text-[8px] truncate" style="max-width: 110px;">{{ $member->email }}</div>
                        <div class="text-gray-400 text-[8px]">{{ $member->created_at->format('d/m/Y') }}</div>
                        <div class="flex justify-center gap-1 mt-1 text-[8px]">
                            <span class="bg-emerald-50 text-emerald-700 rounded-full px-1 py-0.5">{{ $member->total_sales }} penj.</span>
                            <span class="bg-emerald-50 text-emerald-700 rounded-full px-1 py-0.5">Rp {{ number_format($member->total_revenue ?? 0, 0, ',', '.') }}</span>
                        </div>
                        @if($member->downlines->count() > 0)
                        <div class="text-[8px] text-gray-400 mt-0.5">{{ $member->downlines->count() }} downline</div>
                        @endif
                    </div>
                </div>
                @endforeach
            </div>

            {{-- Level 3: Downline dari tim --}}
            @php $hasLevel3 = $downlines->contains(fn($m) => $m->downlines->count() > 0); @endphp
            @if($hasLevel3)
            <div class="flex justify-center flex-wrap gap-1.5 mt-10">
                @foreach($downlines as $i => $member)
                    @foreach($member->downlines as $j => $sub)
                    <div class="inline-block" data-node="l3-{{ $i }}-{{ $j }}" data-parent="l2-{{ $i }}">
                        <div class="bg-white border border-amber-300 rounded-lg shadow-sm px-2.5 py-1.5 text-center" style="min-width: 105px;">
                            <div class="font-medium text-[10px] text-gray-900 truncate" style="max-width: 100px;">{{ $sub->name }}</div>
                            <div class="text-gray-400 text-[8px] truncate" style="max-width: 100px;">{{ $sub->email }}</div>
                            <div class="text-gray-400 text-[7px]">{{ $sub->created_at->format('d/m/Y') }}</div>
                            <div class="flex justify-center gap-1 mt-0.5 text-[7px]">
                                <span class="bg-amber-50 text-amber-700 rounded-full px-1 py-0.5">{{ $sub->total_sales }} penj.</span>
                                <span class="bg-amber-50 text-amber-700 rounded-full px-1 py-0.5">Rp {{ number_format($sub->total_revenue ?? 0, 0, ',', '.') }}</span>
                            </div>
                        </div>
                    </div>
                    @endforeach
                @endforeach
            </div>
            @endif
            @else
            <div class="mt-8 text-center text-gray-500 py-8">
                Belum ada downline. Bagikan link referral Anda untuk merekrut member baru!
            </div>
            @endif

        </div>
    </div>
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

<script>
document.addEventListener('DOMContentLoaded', function() {
    var wrap = document.getElementById('tree-wrap');
    var svg = document.getElementById('tree-svg');
    if (!wrap || !svg) return;

    function draw() {
        svg.setAttribute('width', wrap.scrollWidth);
        svg.setAttribute('height', wrap.scrollHeight);
        svg.innerHTML = '';

        var wrapRect = wrap.getBoundingClientRect();
        var nodes = wrap.querySelectorAll('[data-parent]');

        nodes.forEach(function(node) {
            var pid = node.getAttribute('data-parent');
            var parent = wrap.querySelector('[data-node="' + pid + '"]');
            if (!parent) return;

            var pR = parent.getBoundingClientRect();
            var cR = node.getBoundingClientRect();

            var x1 = pR.left + pR.width / 2 - wrapRect.left;
            var y1 = pR.bottom - wrapRect.top;
            var x2 = cR.left + cR.width / 2 - wrapRect.left;
            var y2 = cR.top - wrapRect.top;
            var my = y1 + (y2 - y1) / 2;

            var ns = 'http://www.w3.org/2000/svg';
            function addLine(ax, ay, bx, by) {
                var l = document.createElementNS(ns, 'line');
                l.setAttribute('x1', ax); l.setAttribute('y1', ay);
                l.setAttribute('x2', bx); l.setAttribute('y2', by);
                svg.appendChild(l);
            }
            addLine(x1, y1, x1, my);
            addLine(x1, my, x2, my);
            addLine(x2, my, x2, y2);
        });
    }

    setTimeout(draw, 150);
    window.addEventListener('resize', draw);
});
</script>
@endsection
