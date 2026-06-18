@extends('layouts.dashboard')
@section('title', 'Tim / Downline')

@section('content')
<h1 class="text-2xl font-bold dk-heading mb-6">Tim / Downline</h1>

{{-- Ringkasan --}}
<div class="dk-grid-3" style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 16px; margin-bottom: 24px;">
    <div style="background: #1a2332; border-radius: 14px; border: 1px solid #2d3a4a; padding: 24px; text-align: center; box-shadow: 0 1px 3px rgba(0,0,0,0.2);">
        <div style="width: 48px; height: 48px; border-radius: 12px; margin: 0 auto 12px; display: flex; align-items: center; justify-content: center; background: rgba(99,102,241,0.15); color: #818cf8;">
            <svg style="width: 24px; height: 24px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
        </div>
        <div style="font-size: 11px; font-weight: 600; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.05em;">Total Tim Langsung</div>
        <div style="margin-top: 6px; font-size: 28px; font-weight: 700; color: #818cf8;">{{ $downlines->count() }}</div>
    </div>
    <div style="background: #1a2332; border-radius: 14px; border: 1px solid #2d3a4a; padding: 24px; text-align: center; box-shadow: 0 1px 3px rgba(0,0,0,0.2);">
        <div style="width: 48px; height: 48px; border-radius: 12px; margin: 0 auto 12px; display: flex; align-items: center; justify-content: center; background: rgba(16,185,129,0.15); color: #6ee7b7;">
            <svg style="width: 24px; height: 24px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
        </div>
        <div style="font-size: 11px; font-weight: 600; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.05em;">Total Semua Downline</div>
        <div style="margin-top: 6px; font-size: 28px; font-weight: 700; color: #6ee7b7;">{{ $downlines->count() + $downlines->sum(fn($d) => $d->downlines->count()) }}</div>
    </div>
    <div style="background: #1a2332; border-radius: 14px; border: 1px solid #2d3a4a; padding: 24px; text-align: center; box-shadow: 0 1px 3px rgba(0,0,0,0.2);">
        <div style="width: 48px; height: 48px; border-radius: 12px; margin: 0 auto 12px; display: flex; align-items: center; justify-content: center; background: rgba(168,85,247,0.15); color: #c4b5fd;">
            <svg style="width: 24px; height: 24px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path></svg>
        </div>
        <div style="font-size: 11px; font-weight: 600; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.05em;">Total Penjualan Tim</div>
        <div style="margin-top: 6px; font-size: 28px; font-weight: 700; color: #c4b5fd;">{{ $downlines->sum('total_sales') + $downlines->sum(fn($d) => $d->downlines->sum('total_sales')) }}</div>
    </div>
</div>

{{-- Diagram Pohon --}}
<div class="dk-card" style="padding:24px;;overflow-x: auto;">

    {{-- Level 1: Root User --}}
    <div style="text-align: center;">
        <div style="display: inline-block; background: #4f46e5; color: #fff; border-radius: 8px; padding: 10px 20px; box-shadow: 0 2px 8px rgba(0,0,0,0.15);">
            <div style="font-weight: 700; font-size: 14px;">{{ $user->name }}</div>
            <div style="color: #a5b4fc; font-size: 10px;">{{ $user->email }}</div>
            <div style="margin-top: 4px; font-size: 10px;">
                <span style="background: rgba(99,102,241,0.5); border-radius: 9999px; padding: 2px 6px;">{{ $userTotalSales }} penj.</span>
                <span style="background: rgba(99,102,241,0.5); border-radius: 9999px; padding: 2px 6px; margin-left: 4px;">Rp {{ number_format($userTotalRevenue, 0, ',', '.') }}</span>
            </div>
        </div>
    </div>

    @if($downlines->count() > 0)
    {{-- Vertical line from root --}}
    <div style="text-align: center;"><div style="display: inline-block; width: 2px; height: 20px; background: #d1d5db;"></div></div>

    {{-- Horizontal bar --}}
    <div style="border-top: 2px solid #6ee7b7; margin: 0 20px;"></div>

    {{-- Level 2: Tim Langsung --}}
    <div style="display: flex; flex-wrap: wrap; justify-content: center; gap: 6px; padding: 0 4px;">
        @foreach($downlines as $i => $member)
        <div style="text-align: center; flex: 0 0 auto;">
            {{-- Vertical stub --}}
            <div style="margin: 0 auto; width: 2px; height: 12px; background: #d1d5db;"></div>
            {{-- Node --}}
            <a href="{{ route('dashboard.team.show', $member->id) }}" style="display:block;text-decoration:none;border: 2px solid #34d399; border-radius: 8px; padding: 6px 8px; min-width: 100px; max-width: 120px; background: #fff; transition: box-shadow .15s;" title="Lihat detail {{ $member->name }}" onmouseover="this.style.boxShadow='0 0 0 3px rgba(52,211,153,0.4)'" onmouseout="this.style.boxShadow='none'">
                <div style="font-weight: 600; font-size: 10px; color: #111; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">{{ $member->name }}</div>
                <div style="font-size: 7px; color: #9ca3af; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">{{ $member->email }}</div>
                <div style="font-size: 7px; color: #9ca3af;">{{ $member->created_at->format('d/m/Y') }}</div>
                <div style="font-size: 7px; margin-top: 2px;">
                    <span style="background: #ecfdf5; color: #047857; border-radius: 9999px; padding: 1px 4px;">{{ $member->total_sales }} penj.</span>
                    <span style="background: #ecfdf5; color: #047857; border-radius: 9999px; padding: 1px 4px;">Rp {{ number_format($member->total_revenue ?? 0, 0, ',', '.') }}</span>
                </div>
                @if($member->downlines->count() > 0)
                <div style="font-size: 7px; color: #9ca3af; margin-top: 2px;">{{ $member->downlines->count() }} downline</div>
                @endif
            </a>
        </div>
        @endforeach
    </div>

    {{-- Level 3: Downline dari tim --}}
    @php $hasLevel3 = $downlines->contains(fn($m) => $m->downlines->count() > 0); @endphp
    @if($hasLevel3)
    <div style="margin-top: 16px;">
        {{-- Horizontal bar level 3 --}}
        <div style="border-top: 2px solid #fcd34d; margin: 0 20px;"></div>

        @foreach($downlines as $i => $member)
            @if($member->downlines->count() > 0)
            <div style="margin: 10px 0 10px 10px; padding-left: 12px; border-left: 2px solid #fcd34d;">
                <div style="font-size: 10px; color: #6b7280; margin-bottom: 6px;">
                    Dari <span style="font-weight: 600; color: #374151;">{{ $member->name }}</span>
                    <span style="color: #9ca3af;">({{ $member->downlines->count() }} orang)</span>
                </div>
                <div style="display: flex; flex-wrap: wrap; gap: 6px;">
                    @foreach($member->downlines as $j => $sub)
                    <a href="{{ route('dashboard.team.show', $sub->id) }}" style="display:block;text-decoration:none;border: 1px solid #fcd34d; border-radius: 8px; padding: 5px 8px; min-width: 95px; max-width: 115px; background: #fff; text-align: center; transition: box-shadow .15s;" title="Lihat detail {{ $sub->name }}" onmouseover="this.style.boxShadow='0 0 0 3px rgba(252,211,77,0.4)'" onmouseout="this.style.boxShadow='none'">
                        <div style="font-weight: 500; font-size: 9px; color: #111; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">{{ $sub->name }}</div>
                        <div style="font-size: 7px; color: #9ca3af; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">{{ $sub->email }}</div>
                        <div style="font-size: 7px; color: #9ca3af;">{{ $sub->created_at->format('d/m/Y') }}</div>
                        <div style="font-size: 7px; margin-top: 2px;">
                            <span style="background: #fffbeb; color: #b45309; border-radius: 9999px; padding: 1px 4px;">{{ $sub->total_sales }} penj.</span>
                            <span style="background: #fffbeb; color: #b45309; border-radius: 9999px; padding: 1px 4px;">Rp {{ number_format($sub->total_revenue ?? 0, 0, ',', '.') }}</span>
                        </div>
                    </a>
                    @endforeach
                </div>
            </div>
            @endif
        @endforeach
    </div>
    @endif

    @else
    <div style="margin-top: 32px; text-align: center; color: #6b7280; padding: 32px 0;">
        Belum ada downline. Bagikan link referral Anda untuk merekrut member baru!
    </div>
    @endif

</div>

{{-- Legend --}}
<div class="mt-4 dk-card p-4">
    <div style="font-size: 10px; font-weight: 600; color: #6b7280; text-transform: uppercase; margin-bottom: 8px;">Keterangan Warna</div>
    <div style="display: flex; flex-wrap: wrap; gap: 16px; font-size: 13px;">
        <div style="display: flex; align-items: center; gap: 6px;">
            <div style="width: 16px; height: 16px; border-radius: 4px; background: #4f46e5;"></div>
            <span style="color: #374151;">Level 1 — Anda</span>
        </div>
        <div style="display: flex; align-items: center; gap: 6px;">
            <div style="width: 16px; height: 16px; border-radius: 4px; border: 2px solid #34d399; background: #fff;"></div>
            <span style="color: #374151;">Level 2 — Tim Langsung</span>
        </div>
        <div style="display: flex; align-items: center; gap: 6px;">
            <div style="width: 16px; height: 16px; border-radius: 4px; border: 1px solid #fcd34d; background: #fff;"></div>
            <span style="color: #374151;">Level 3 — Downline Tim</span>
        </div>
    </div>
</div>
@endsection
