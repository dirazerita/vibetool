@extends('layouts.public')
@section('title', 'Checkout - ' . $product->title)

@section('content')
@php
    $showDuplicateWarning = ($alreadyPurchased ?? false) || isset($pendingOrder);
@endphp
<div style="max-width: 42rem; margin: 0 auto; padding: 48px 1rem;" x-data="{ showWarning: {{ $showDuplicateWarning ? 'true' : 'false' }} }">

    @if($showDuplicateWarning)
    <div
        x-show="showWarning"
        x-cloak
        x-transition.opacity
        style="position: fixed; inset: 0; z-index: 50; display: flex; align-items: center; justify-content: center; background-color: rgba(0,0,0,0.5); padding: 0 16px;"
    >
        <div style="background-color: #1a2332; border-radius: 16px; box-shadow: 0 10px 25px rgba(0,0,0,0.3); max-width: 28rem; width: 100%; padding: 24px; border: 1px solid #2d3a4a;" @click.outside="showWarning = false">
            <div style="display: flex; align-items: flex-start; gap: 12px; margin-bottom: 16px;">
                <div style="flex-shrink: 0; width: 40px; height: 40px; border-radius: 50%; background-color: #3b351a; display: flex; align-items: center; justify-content: center;">
                    <svg style="width: 20px; height: 20px; color: #fde68a;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M4.93 19h14.14a2 2 0 001.74-3L13.74 4a2 2 0 00-3.48 0L3.19 16a2 2 0 001.74 3z"></path></svg>
                </div>
                <div style="flex: 1;">
                    <h3 style="font-size: 1.125rem; font-weight: 600; color: #e2e8f0;">
                        @if($alreadyPurchased ?? false)
                            Anda sudah pernah membeli produk ini
                        @else
                            Pesanan masih menunggu pembayaran
                        @endif
                    </h3>
                    <div style="font-size: 0.875rem; color: #94a3b8; margin-top: 8px;">
                        @if($alreadyPurchased ?? false)
                            <p>Produk <strong>{{ $product->title }}</strong> sudah ada di akun Anda. Apakah Anda yakin ingin membelinya lagi?</p>
                        @endif
                        @if(isset($pendingOrder))
                            <p>Anda masih memiliki pesanan <strong>#{{ $pendingOrder->id }}</strong> untuk produk ini yang menunggu pembayaran/konfirmasi. Sebaiknya selesaikan atau batalkan pesanan tersebut terlebih dahulu.</p>
                        @endif
                    </div>
                </div>
            </div>
            <div class="vt-modal-actions" style="display: flex; gap: 12px; margin-top: 8px;">
                <a href="{{ route('product.show', $product->slug) }}"
                   style="flex: 1; text-align: center; padding: 10px 16px; border-radius: 8px; border: 1px solid #2d3a4a; color: #cbd5e1; font-weight: 500; font-size: 0.875rem; text-decoration: none;">
                    Batal
                </a>
                @if(isset($pendingOrder))
                    <a href="{{ route('checkout.manual', $pendingOrder->id) }}"
                       style="flex: 1; text-align: center; padding: 10px 16px; border-radius: 8px; background-color: #854d0e; color: #ffffff; font-weight: 500; font-size: 0.875rem; text-decoration: none;">
                        Lihat Pesanan Pending
                    </a>
                @endif
                <button type="button" @click="showWarning = false"
                        style="flex: 1; padding: 10px 16px; border-radius: 8px; background: linear-gradient(135deg, #4f46e5, #7c3aed); color: #ffffff; font-weight: 500; font-size: 0.875rem; border: none; cursor: pointer;">
                    Ya, saya mengerti, lanjutkan
                </button>
            </div>
        </div>
    </div>
    @endif

    <div style="background-color: #1a2332; border-radius: 12px; border: 1px solid #2d3a4a; padding: 32px; box-shadow: 0 4px 15px rgba(0,0,0,0.2);">
        <h1 style="font-size: 1.5rem; font-weight: 700; color: #e2e8f0; margin-bottom: 24px;">Checkout</h1>

        <div style="border: 1px solid #2d3a4a; border-radius: 8px; padding: 24px; margin-bottom: 24px; background-color: #151e2d;">
            <div style="display: flex; gap: 16px; align-items: flex-start;">
                @php
                    $thumbUrl = null;
                    if ($product->thumbnail) {
                        $thumbUrl = asset('storage/' . $product->thumbnail);
                    } elseif ($product->landingPage && $product->landingPage->hero_image) {
                        $thumbUrl = asset('storage/' . $product->landingPage->hero_image);
                    }
                @endphp
                <div style="flex-shrink: 0;">
                    @if($thumbUrl)
                        <img src="{{ $thumbUrl }}" alt="{{ $product->title }}" style="border-radius: 8px; object-fit: cover; width: 80px; height: 80px;">
                    @else
                        <div style="border-radius: 8px; background-color: #151e2d; display: flex; align-items: center; justify-content: center; width: 80px; height: 80px;">
                            <svg style="width: 32px; height: 32px; color: #475569;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                        </div>
                    @endif
                </div>
                @php
                    $checkoutBasePrice = isset($selectedPackage) && $selectedPackage ? (float) $selectedPackage->price : (float) $product->price;
                    $checkoutCompareAt = null;
                    if (isset($selectedPackage) && $selectedPackage) {
                        if ($selectedPackage->compare_at_price !== null && (float) $selectedPackage->compare_at_price > (float) $selectedPackage->price) {
                            $checkoutCompareAt = (float) $selectedPackage->compare_at_price;
                        }
                    } elseif ($product->compare_at_price !== null && (float) $product->compare_at_price > (float) $product->price) {
                        $checkoutCompareAt = (float) $product->compare_at_price;
                    }
                    $pkgDurationLabel = null;
                    if (isset($selectedPackage) && $selectedPackage) {
                        $pkgDurationLabel = match($selectedPackage->duration_type) {
                            '1_month' => '1 Bulan',
                            '6_months' => '6 Bulan',
                            '1_year' => '1 Tahun',
                            'lifetime' => 'Lifetime',
                            default => $selectedPackage->duration_type,
                        };
                    }
                @endphp
                <div style="flex: 1; min-width: 0;">
                    <h2 style="font-size: 1.125rem; font-weight: 600; color: #e2e8f0;">{{ $product->title }}</h2>
                    <p style="color: #94a3b8; font-size: 0.875rem; margin-top: 4px;">{{ Str::limit($product->description, 100) }}</p>
                    @if(isset($selectedPackage) && $selectedPackage)
                        <div style="margin-top: 8px; display: inline-flex; align-items: center; gap: 6px; padding: 4px 10px; border-radius: 999px; background-color: rgba(99,102,241,0.15); border: 1px solid rgba(99,102,241,0.4);">
                            <span style="font-size: 0.75rem; color: #a5b4fc;">Paket: <strong>{{ $selectedPackage->displayLabel() }}</strong> · {{ $pkgDurationLabel }}</span>
                        </div>
                    @endif
                    @if(isset($autoCouponData) && $autoCouponData)
                        <div style="margin-top: 12px; display: flex; align-items: baseline; gap: 8px;">
                            <span style="font-size: 1rem; color: #64748b; text-decoration: line-through;">Rp {{ number_format($checkoutBasePrice, 0, ',', '.') }}</span>
                            <span style="font-size: 1.5rem; font-weight: 700; color: #818cf8;" id="product-price">{{ $autoCouponData['final_price_formatted'] }}</span>
                        </div>
                    @else
                        @if($checkoutCompareAt !== null)
                            <div class="mt-2 text-sm" style="color: #64748b; text-decoration: line-through;">Rp {{ number_format($checkoutCompareAt, 0, ',', '.') }}</div>
                        @endif
                        <div class="mt-1 text-2xl font-bold text-indigo-600" id="product-price">Rp {{ number_format($checkoutBasePrice, 0, ',', '.') }}</div>
                    @endif
                </div>
            </div>
        </div>

        @if(request()->cookie('ref'))
            <div style="background-color: #1a3b2a; border: 1px solid #166534; color: #86efac; padding: 12px 16px; border-radius: 8px; margin-bottom: 24px; font-size: 0.875rem;">
                Kode referral terdeteksi: <strong>{{ request()->cookie('ref') }}</strong>
            </div>
        @endif

        @if(isset($autoCouponData) && $autoCouponData)
            <div style="background-color: #1a3b2a; border: 1px solid #166534; border-radius: 8px; padding: 16px; margin-bottom: 24px;">
                <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 8px;">
                    <svg style="width: 20px; height: 20px; color: #86efac;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    <span style="font-size: 0.875rem; font-weight: 500; color: #86efac;">
                        Kupon <strong>{{ $autoCouponData['code'] }}</strong>@if($autoCouponData['member_name']) dari {{ $autoCouponData['member_name'] }}@endif telah diterapkan — diskon {{ $autoCouponData['discount_label'] }} ({{ $autoCouponData['discount_formatted'] }})
                    </span>
                </div>
                <div style="display: flex; justify-content: space-between; font-size: 0.875rem;">
                    <span class="text-gray-600">Harga asli</span>
                    <span style="color: #e2e8f0;">Rp {{ number_format($checkoutBasePrice, 0, ',', '.') }}</span>
                </div>
                <div style="display: flex; justify-content: space-between; font-size: 0.875rem; margin-top: 4px;">
                    <span class="text-red-600">Diskon ({{ $autoCouponData['name'] }})</span>
                    <span class="text-red-600">-{{ $autoCouponData['discount_formatted'] }}</span>
                </div>
                <div style="border-top: 1px solid #166534; margin-top: 8px; padding-top: 8px; display: flex; justify-content: space-between;">
                    <span style="font-size: 0.875rem; font-weight: 600; color: #e2e8f0;">Total bayar</span>
                    <span style="font-size: 1.125rem; font-weight: 700; color: #818cf8;">{{ $autoCouponData['final_price_formatted'] }}</span>
                </div>
            </div>
        @endif

        <div style="border: 1px solid #2d3a4a; border-radius: 8px; padding: 16px; margin-bottom: 24px; background-color: #151e2d;">
            <label for="coupon_input" style="display: block; font-size: 0.875rem; font-weight: 500; color: #cbd5e1; margin-bottom: 8px;">Punya kode kupon?</label>
            <div style="display: flex; gap: 8px;">
                <input type="text" id="coupon_input" placeholder="Masukkan kode kupon"
                    value="{{ isset($autoCouponData) && $autoCouponData ? $autoCouponData['code'] : '' }}"
                    @if(isset($autoCouponData) && $autoCouponData) readonly @endif
                    style="flex: 1; background-color: #151e2d; border: 1px solid #2d3a4a; color: #e2e8f0; border-radius: 8px; padding: 8px 12px; text-transform: uppercase; font-size: 0.875rem;" class=" @if(isset($autoCouponData) && $autoCouponData) bg-gray-100 cursor-not-allowed @endif">
                <button type="button" id="apply-coupon" @if(isset($autoCouponData) && $autoCouponData) disabled @endif style="background: linear-gradient(135deg, #4f46e5, #7c3aed); color: #ffffff; padding: 8px 16px; border-radius: 8px; font-weight: 500; font-size: 0.875rem; white-space: nowrap; border: none; cursor: pointer;" class=" @if(isset($autoCouponData) && $autoCouponData) opacity-50 cursor-not-allowed @endif">Terapkan</button>
            </div>
            <div id="coupon-message" class="mt-2 text-sm hidden"></div>
            <div id="coupon-summary" class="mt-3 {{ isset($autoCouponData) && $autoCouponData ? '' : 'hidden' }}">
                <div style="display: flex; justify-content: space-between; font-size: 0.875rem;">
                    <span class="text-gray-600">Harga asli</span>
                    <span style="color: #e2e8f0;">Rp {{ number_format($checkoutBasePrice, 0, ',', '.') }}</span>
                </div>
                <div style="display: flex; justify-content: space-between; font-size: 0.875rem; margin-top: 4px;">
                    <span class="text-red-600">Diskon (<span id="coupon-name">{{ isset($autoCouponData) && $autoCouponData ? $autoCouponData['name'] : '' }}</span>)</span>
                    <span class="text-red-600" id="discount-value">{{ isset($autoCouponData) && $autoCouponData ? '-' . $autoCouponData['discount_formatted'] : '' }}</span>
                </div>
                <div style="border-top: 1px solid #2d3a4a; margin-top: 8px; padding-top: 8px; display: flex; justify-content: space-between;">
                    <span style="font-size: 0.875rem; font-weight: 600; color: #e2e8f0;">Total bayar</span>
                    <span style="font-size: 1.125rem; font-weight: 700; color: #818cf8;" id="final-price">{{ isset($autoCouponData) && $autoCouponData ? $autoCouponData['final_price_formatted'] : '' }}</span>
                </div>
            </div>
        </div>

        <form method="POST" action="{{ route('checkout.process', $product->slug) }}" id="checkout-form">
            @csrf
            <input type="hidden" name="coupon_code" id="coupon_code_hidden" value="{{ isset($autoCouponData) && $autoCouponData ? $autoCouponData['code'] : '' }}">
            @if(isset($selectedPackage) && $selectedPackage)
                <input type="hidden" name="package_id" value="{{ $selectedPackage->id }}">
            @endif
            <button type="submit" style="width: 100%; background: linear-gradient(135deg, #4f46e5, #7c3aed); color: #ffffff; padding: 12px; border-radius: 8px; font-weight: 700; font-size: 1.125rem; border: none; cursor: pointer;">
                Bayar Sekarang
            </button>
        </form>

        <p style="text-align: center; color: #64748b; font-size: 0.875rem; margin-top: 16px;">Anda akan diarahkan ke halaman pembayaran Xendit</p>
    </div>
</div>

<script>
document.getElementById('apply-coupon').addEventListener('click', function() {
    const code = document.getElementById('coupon_input').value.trim();
    if (!code) return;

    const btn = this;
    btn.disabled = true;
    btn.textContent = 'Memproses...';

    fetch('{{ route("checkout.apply-coupon", $product->slug) }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json'
        },
        body: JSON.stringify({ coupon_code: code })
    })
    .then(response => response.json())
    .then(data => {
        const msgEl = document.getElementById('coupon-message');
        const summaryEl = document.getElementById('coupon-summary');
        msgEl.classList.remove('hidden');

        if (data.success) {
            msgEl.className = 'mt-2 text-sm text-green-600';
            msgEl.textContent = data.message;
            summaryEl.classList.remove('hidden');
            document.getElementById('coupon-name').textContent = data.coupon_name;
            document.getElementById('discount-value').textContent = '-' + data.discount_formatted;
            document.getElementById('final-price').textContent = data.final_price_formatted;
            document.getElementById('coupon_code_hidden').value = code.toUpperCase();
        } else {
            msgEl.className = 'mt-2 text-sm text-red-600';
            msgEl.textContent = data.message;
            summaryEl.classList.add('hidden');
            document.getElementById('coupon_code_hidden').value = '';
        }
    })
    .catch(() => {
        const msgEl = document.getElementById('coupon-message');
        msgEl.classList.remove('hidden');
        msgEl.className = 'mt-2 text-sm text-red-600';
        msgEl.textContent = 'Terjadi kesalahan. Silakan coba lagi.';
    })
    .finally(() => {
        btn.disabled = false;
        btn.textContent = 'Terapkan';
    });
});
</script>
@endsection
