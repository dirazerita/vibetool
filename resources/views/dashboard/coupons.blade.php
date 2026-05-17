@extends('layouts.dashboard')
@section('title', 'Kuponku')

@section('content')
<h1 class="text-2xl font-bold text-gray-900 mb-6">Kuponku</h1>

{{-- Kupon yang di-assign ke member --}}
@if($assignedCoupons->count() > 0)
<div style="margin-bottom: 32px;">
    <h2 class="text-lg font-semibold text-gray-900 mb-4">Kupon Khusus Saya</h2>
    <div style="display: flex; flex-direction: column; gap: 16px;">
        @foreach($assignedCoupons as $coupon)
        <div style="background: #fff; border-radius: 12px; box-shadow: 0 1px 3px rgba(0,0,0,0.06); border: 1px solid #e5e7eb; overflow: hidden; display: flex; flex-direction: row;">
            {{-- Bagian kiri: kode kupon --}}
            <div style="width: 180px; flex-shrink: 0; background: #4f46e5; color: #fff; padding: 20px 16px; display: flex; flex-direction: column; align-items: center; justify-content: center;">
                <div style="font-size: 10px; font-weight: 500; text-transform: uppercase; letter-spacing: 0.05em; opacity: 0.75;">Kode Kupon</div>
                <div style="font-size: 18px; font-weight: 700; margin-top: 4px; letter-spacing: 0.05em;">{{ $coupon->code }}</div>
                <div style="margin-top: 8px; font-size: 24px; font-weight: 700;">
                    @if($coupon->discount_type === 'percent')
                        {{ rtrim(rtrim(number_format($coupon->discount_value, 2, '.', ''), '0'), '.') }}%
                    @else
                        Rp {{ number_format($coupon->discount_value, 0, ',', '.') }}
                    @endif
                </div>
                <div style="font-size: 11px; opacity: 0.75;">diskon</div>
            </div>
            {{-- Bagian kanan: detail --}}
            <div style="flex: 1; padding: 20px;">
                <div style="display: flex; align-items: flex-start; justify-content: space-between;">
                    <div>
                        <h3 style="font-weight: 600; color: #111827; font-size: 15px;">{{ $coupon->name }}</h3>
                        <p style="font-size: 13px; color: #6b7280; margin-top: 4px;">
                            Diskon {{ $coupon->discount_type === 'percent' ? 'persentase' : 'nominal tetap' }}
                            @if($coupon->min_purchase > 0)
                                &middot; Min. pembelian Rp {{ number_format($coupon->min_purchase, 0, ',', '.') }}
                            @endif
                        </p>
                    </div>
                    @if($coupon->is_active && (!$coupon->expired_at || !$coupon->expired_at->isPast()) && (!$coupon->max_uses || $coupon->used_count < $coupon->max_uses))
                        <span style="display: inline-flex; align-items: center; padding: 2px 10px; border-radius: 9999px; font-size: 11px; font-weight: 500; background: #dcfce7; color: #166534;">Aktif</span>
                    @elseif($coupon->expired_at && $coupon->expired_at->isPast())
                        <span style="display: inline-flex; align-items: center; padding: 2px 10px; border-radius: 9999px; font-size: 11px; font-weight: 500; background: #fee2e2; color: #991b1b;">Kedaluwarsa</span>
                    @elseif($coupon->max_uses && $coupon->used_count >= $coupon->max_uses)
                        <span style="display: inline-flex; align-items: center; padding: 2px 10px; border-radius: 9999px; font-size: 11px; font-weight: 500; background: #f3f4f6; color: #1f2937;">Kuota Habis</span>
                    @else
                        <span style="display: inline-flex; align-items: center; padding: 2px 10px; border-radius: 9999px; font-size: 11px; font-weight: 500; background: #f3f4f6; color: #1f2937;">Nonaktif</span>
                    @endif
                </div>

                <div style="margin-top: 12px; display: grid; grid-template-columns: 1fr 1fr; gap: 12px; font-size: 13px;">
                    <div>
                        <span style="color: #6b7280;">Berlaku hingga:</span>
                        <span style="font-weight: 500; color: #111827;">{{ $coupon->expired_at ? $coupon->expired_at->format('d M Y') : 'Tidak terbatas' }}</span>
                    </div>
                    <div>
                        <span style="color: #6b7280;">Penggunaan:</span>
                        <span style="font-weight: 500; color: #111827;">{{ $coupon->used_count }}{{ $coupon->max_uses ? ' / ' . $coupon->max_uses : '' }}</span>
                    </div>
                </div>

                @if($coupon->products->count() > 0)
                <div style="margin-top: 12px;">
                    <span style="font-size: 13px; color: #6b7280;">Berlaku untuk:</span>
                    <div style="display: flex; flex-wrap: wrap; gap: 6px; margin-top: 4px;">
                        @foreach($coupon->products as $product)
                        <span style="display: inline-flex; align-items: center; padding: 2px 8px; border-radius: 6px; font-size: 11px; font-weight: 500; background: #eef2ff; color: #4338ca; border: 1px solid #e0e7ff;">{{ $product->title }}</span>
                        @endforeach
                    </div>
                </div>
                @else
                <div style="margin-top: 12px;">
                    <span style="font-size: 13px; color: #6b7280;">Berlaku untuk:</span>
                    <span style="font-size: 13px; font-weight: 500; color: #111827;">Semua produk</span>
                </div>
                @endif
            </div>
        </div>
        @endforeach
    </div>
</div>
@endif

{{-- Kupon global (untuk semua member) --}}
@if($globalCoupons->count() > 0)
<div style="margin-bottom: 32px;">
    <h2 class="text-lg font-semibold text-gray-900 mb-4">Kupon Umum</h2>
    <p style="font-size: 13px; color: #6b7280; margin-bottom: 16px;">Kupon ini tersedia untuk semua member.</p>
    <div style="display: flex; flex-direction: column; gap: 16px;">
        @foreach($globalCoupons as $coupon)
        <div style="background: #fff; border-radius: 12px; box-shadow: 0 1px 3px rgba(0,0,0,0.06); border: 1px solid #e5e7eb; overflow: hidden; display: flex; flex-direction: row;">
            {{-- Bagian kiri: kode kupon --}}
            <div style="width: 180px; flex-shrink: 0; background: #059669; color: #fff; padding: 20px 16px; display: flex; flex-direction: column; align-items: center; justify-content: center;">
                <div style="font-size: 10px; font-weight: 500; text-transform: uppercase; letter-spacing: 0.05em; opacity: 0.75;">Kode Kupon</div>
                <div style="font-size: 18px; font-weight: 700; margin-top: 4px; letter-spacing: 0.05em;">{{ $coupon->code }}</div>
                <div style="margin-top: 8px; font-size: 24px; font-weight: 700;">
                    @if($coupon->discount_type === 'percent')
                        {{ rtrim(rtrim(number_format($coupon->discount_value, 2, '.', ''), '0'), '.') }}%
                    @else
                        Rp {{ number_format($coupon->discount_value, 0, ',', '.') }}
                    @endif
                </div>
                <div style="font-size: 11px; opacity: 0.75;">diskon</div>
            </div>
            {{-- Bagian kanan: detail --}}
            <div style="flex: 1; padding: 20px;">
                <div style="display: flex; align-items: flex-start; justify-content: space-between;">
                    <div>
                        <h3 style="font-weight: 600; color: #111827; font-size: 15px;">{{ $coupon->name }}</h3>
                        <p style="font-size: 13px; color: #6b7280; margin-top: 4px;">
                            Diskon {{ $coupon->discount_type === 'percent' ? 'persentase' : 'nominal tetap' }}
                            @if($coupon->min_purchase > 0)
                                &middot; Min. pembelian Rp {{ number_format($coupon->min_purchase, 0, ',', '.') }}
                            @endif
                        </p>
                    </div>
                    <span style="display: inline-flex; align-items: center; padding: 2px 10px; border-radius: 9999px; font-size: 11px; font-weight: 500; background: #dcfce7; color: #166534;">Aktif</span>
                </div>

                <div style="margin-top: 12px; display: grid; grid-template-columns: 1fr 1fr; gap: 12px; font-size: 13px;">
                    <div>
                        <span style="color: #6b7280;">Berlaku hingga:</span>
                        <span style="font-weight: 500; color: #111827;">{{ $coupon->expired_at ? $coupon->expired_at->format('d M Y') : 'Tidak terbatas' }}</span>
                    </div>
                    <div>
                        <span style="color: #6b7280;">Penggunaan:</span>
                        <span style="font-weight: 500; color: #111827;">{{ $coupon->used_count }}{{ $coupon->max_uses ? ' / ' . $coupon->max_uses : '' }}</span>
                    </div>
                </div>

                @if($coupon->products->count() > 0)
                <div style="margin-top: 12px;">
                    <span style="font-size: 13px; color: #6b7280;">Berlaku untuk:</span>
                    <div style="display: flex; flex-wrap: wrap; gap: 6px; margin-top: 4px;">
                        @foreach($coupon->products as $product)
                        <span style="display: inline-flex; align-items: center; padding: 2px 8px; border-radius: 6px; font-size: 11px; font-weight: 500; background: #ecfdf5; color: #065f46; border: 1px solid #d1fae5;">{{ $product->title }}</span>
                        @endforeach
                    </div>
                </div>
                @else
                <div style="margin-top: 12px;">
                    <span style="font-size: 13px; color: #6b7280;">Berlaku untuk:</span>
                    <span style="font-size: 13px; font-weight: 500; color: #111827;">Semua produk</span>
                </div>
                @endif
            </div>
        </div>
        @endforeach
    </div>
</div>
@endif

{{-- Empty state --}}
@if($assignedCoupons->count() === 0 && $globalCoupons->count() === 0)
<div style="background: #fff; border-radius: 12px; box-shadow: 0 1px 3px rgba(0,0,0,0.06); border: 1px solid #e5e7eb; padding: 48px; text-align: center;">
    <svg style="width: 64px; height: 64px; margin: 0 auto; color: #d1d5db;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path></svg>
    <h3 style="margin-top: 16px; font-size: 18px; font-weight: 500; color: #111827;">Belum ada kupon</h3>
    <p style="margin-top: 8px; font-size: 14px; color: #6b7280;">Saat ini tidak ada kupon yang tersedia untuk akun Anda.</p>
</div>
@endif

@endsection
