@extends('layouts.dashboard')
@section('title', 'Tim / Downline')

@section('content')
<h1 class="text-2xl font-bold text-gray-900 mb-6">Tim / Downline</h1>

{{-- Ringkasan --}}
<div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 16px; margin-bottom: 24px;">
    <div style="background: #fff; border-radius: 12px; box-shadow: 0 1px 3px rgba(0,0,0,0.06); border: 1px solid #e5e7eb; padding: 16px;">
        <div style="font-size: 10px; font-weight: 500; color: #6b7280; text-transform: uppercase; letter-spacing: 0.05em;">Total Tim Langsung</div>
        <div style="margin-top: 4px; font-size: 24px; font-weight: 700; color: #111827;">{{ $downlines->count() }}</div>
    </div>
    <div style="background: #fff; border-radius: 12px; box-shadow: 0 1px 3px rgba(0,0,0,0.06); border: 1px solid #e5e7eb; padding: 16px;">
        <div style="font-size: 10px; font-weight: 500; color: #6b7280; text-transform: uppercase; letter-spacing: 0.05em;">Total Semua Downline</div>
        <div style="margin-top: 4px; font-size: 24px; font-weight: 700; color: #111827;">{{ $downlines->count() + $downlines->sum(fn($d) => $d->downlines->count()) }}</div>
    </div>
    <div style="background: #fff; border-radius: 12px; box-shadow: 0 1px 3px rgba(0,0,0,0.06); border: 1px solid #e5e7eb; padding: 16px;">
        <div style="font-size: 10px; font-weight: 500; color: #6b7280; text-transform: uppercase; letter-spacing: 0.05em;">Total Penjualan Tim</div>
        <div style="margin-top: 4px; font-size: 24px; font-weight: 700; color: #4f46e5;">{{ $downlines->sum('total_sales') + $downlines->sum(fn($d) => $d->downlines->sum('total_sales')) }}</div>
    </div>
</div>

{{-- Diagram Pohon --}}
<div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6" style="overflow-x: auto;">

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
            <div style="border: 2px solid #34d399; border-radius: 8px; padding: 6px 8px; min-width: 100px; max-width: 120px; background: #fff;">
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
            </div>
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
                    <div style="border: 1px solid #fcd34d; border-radius: 8px; padding: 5px 8px; min-width: 95px; max-width: 115px; background: #fff; text-align: center;">
                        <div style="font-weight: 500; font-size: 9px; color: #111; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">{{ $sub->name }}</div>
                        <div style="font-size: 7px; color: #9ca3af; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">{{ $sub->email }}</div>
                        <div style="font-size: 7px; color: #9ca3af;">{{ $sub->created_at->format('d/m/Y') }}</div>
                        <div style="font-size: 7px; margin-top: 2px;">
                            <span style="background: #fffbeb; color: #b45309; border-radius: 9999px; padding: 1px 4px;">{{ $sub->total_sales }} penj.</span>
                            <span style="background: #fffbeb; color: #b45309; border-radius: 9999px; padding: 1px 4px;">Rp {{ number_format($sub->total_revenue ?? 0, 0, ',', '.') }}</span>
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
    <div style="margin-top: 32px; text-align: center; color: #6b7280; padding: 32px 0;">
        Belum ada downline. Bagikan link referral Anda untuk merekrut member baru!
    </div>
    @endif

</div>

{{-- Legend --}}
<div class="mt-4 bg-white rounded-xl shadow-sm border border-gray-200 p-4">
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
