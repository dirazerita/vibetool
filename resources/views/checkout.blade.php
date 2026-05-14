@extends('layouts.public')
@section('title', 'Checkout - ' . $product->title)

@section('content')
@php
    $showDuplicateWarning = ($alreadyPurchased ?? false) || isset($pendingOrder);
@endphp
<div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8 py-12" x-data="{ showWarning: {{ $showDuplicateWarning ? 'true' : 'false' }} }">

    @if($showDuplicateWarning)
    <div
        x-show="showWarning"
        x-cloak
        x-transition.opacity
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 px-4"
    >
        <div class="bg-white rounded-2xl shadow-xl max-w-md w-full p-6" @click.outside="showWarning = false">
            <div class="flex items-start gap-3 mb-4">
                <div class="flex-shrink-0 w-10 h-10 rounded-full bg-yellow-100 flex items-center justify-center">
                    <svg class="w-5 h-5 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M4.93 19h14.14a2 2 0 001.74-3L13.74 4a2 2 0 00-3.48 0L3.19 16a2 2 0 001.74 3z"></path></svg>
                </div>
                <div class="flex-1">
                    <h3 class="text-lg font-semibold text-gray-900">
                        @if($alreadyPurchased ?? false)
                            Anda sudah pernah membeli produk ini
                        @else
                            Pesanan masih menunggu pembayaran
                        @endif
                    </h3>
                    <div class="text-sm text-gray-600 mt-2 space-y-2">
                        @if($alreadyPurchased ?? false)
                            <p>Produk <strong>{{ $product->title }}</strong> sudah ada di akun Anda. Apakah Anda yakin ingin membelinya lagi?</p>
                        @endif
                        @if(isset($pendingOrder))
                            <p>Anda masih memiliki pesanan <strong>#{{ $pendingOrder->id }}</strong> untuk produk ini yang menunggu pembayaran/konfirmasi. Sebaiknya selesaikan atau batalkan pesanan tersebut terlebih dahulu.</p>
                        @endif
                    </div>
                </div>
            </div>
            <div class="flex flex-col-reverse sm:flex-row gap-2 sm:gap-3 mt-2">
                <a href="{{ route('product.show', $product->slug) }}"
                   class="flex-1 text-center px-4 py-2.5 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50 font-medium text-sm">
                    Batal
                </a>
                @if(isset($pendingOrder))
                    <a href="{{ route('checkout.manual', $pendingOrder->id) }}"
                       class="flex-1 text-center px-4 py-2.5 rounded-lg bg-yellow-500 text-white hover:bg-yellow-600 font-medium text-sm">
                        Lihat Pesanan Pending
                    </a>
                @endif
                <button type="button" @click="showWarning = false"
                        class="flex-1 px-4 py-2.5 rounded-lg bg-indigo-600 text-white hover:bg-indigo-700 font-medium text-sm">
                    Ya, saya mengerti, lanjutkan
                </button>
            </div>
        </div>
    </div>
    @endif

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-8">
        <h1 class="text-2xl font-bold text-gray-900 mb-6">Checkout</h1>

        <div class="border border-gray-200 rounded-lg p-6 mb-6">
            <div class="flex gap-4 items-start">
                @php
                    $thumbUrl = null;
                    if ($product->thumbnail) {
                        $thumbUrl = asset('storage/' . $product->thumbnail);
                    } elseif ($product->landingPage && $product->landingPage->hero_image) {
                        $thumbUrl = asset('storage/' . $product->landingPage->hero_image);
                    }
                @endphp
                <div class="flex-shrink-0">
                    @if($thumbUrl)
                        <img src="{{ $thumbUrl }}" alt="{{ $product->title }}" class="rounded-lg object-cover" style="width: 80px; height: 80px;">
                    @else
                        <div class="rounded-lg bg-gray-100 flex items-center justify-center" style="width: 80px; height: 80px;">
                            <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                        </div>
                    @endif
                </div>
                <div class="flex-1 min-w-0">
                    <h2 class="text-lg font-semibold text-gray-900">{{ $product->title }}</h2>
                    <p class="text-gray-600 text-sm mt-1">{{ Str::limit($product->description, 100) }}</p>
                    @if(isset($autoCouponData) && $autoCouponData)
                        <div class="mt-3 flex items-baseline gap-2">
                            <span class="text-base text-gray-400 line-through">Rp {{ number_format($product->price, 0, ',', '.') }}</span>
                            <span class="text-2xl font-bold text-indigo-600" id="product-price">{{ $autoCouponData['final_price_formatted'] }}</span>
                        </div>
                    @else
                        <div class="mt-3 text-2xl font-bold text-indigo-600" id="product-price">Rp {{ number_format($product->price, 0, ',', '.') }}</div>
                    @endif
                </div>
            </div>
        </div>

        @if(request()->cookie('ref'))
            <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg mb-6 text-sm">
                Kode referral terdeteksi: <strong>{{ request()->cookie('ref') }}</strong>
            </div>
        @endif

        @if(isset($autoCouponData) && $autoCouponData)
            <div class="bg-green-50 border border-green-200 rounded-lg p-4 mb-6">
                <div class="flex items-center gap-2 mb-2">
                    <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    <span class="text-sm font-medium text-green-700">
                        Kupon <strong>{{ $autoCouponData['code'] }}</strong>@if($autoCouponData['member_name']) dari {{ $autoCouponData['member_name'] }}@endif telah diterapkan — diskon {{ $autoCouponData['discount_label'] }} ({{ $autoCouponData['discount_formatted'] }})
                    </span>
                </div>
                <div class="flex justify-between text-sm">
                    <span class="text-gray-600">Harga asli</span>
                    <span class="text-gray-900">Rp {{ number_format($product->price, 0, ',', '.') }}</span>
                </div>
                <div class="flex justify-between text-sm mt-1">
                    <span class="text-red-600">Diskon ({{ $autoCouponData['name'] }})</span>
                    <span class="text-red-600">-{{ $autoCouponData['discount_formatted'] }}</span>
                </div>
                <div class="border-t border-green-200 mt-2 pt-2 flex justify-between">
                    <span class="text-sm font-semibold text-gray-900">Total bayar</span>
                    <span class="text-lg font-bold text-indigo-600">{{ $autoCouponData['final_price_formatted'] }}</span>
                </div>
            </div>
        @endif

        <div class="border border-gray-200 rounded-lg p-4 mb-6">
            <label for="coupon_input" class="block text-sm font-medium text-gray-700 mb-2">Punya kode kupon?</label>
            <div class="flex gap-2">
                <input type="text" id="coupon_input" placeholder="Masukkan kode kupon"
                    value="{{ isset($autoCouponData) && $autoCouponData ? $autoCouponData['code'] : '' }}"
                    @if(isset($autoCouponData) && $autoCouponData) readonly @endif
                    class="flex-1 border-gray-300 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500 uppercase text-sm @if(isset($autoCouponData) && $autoCouponData) bg-gray-100 cursor-not-allowed @endif">
                <button type="button" id="apply-coupon" @if(isset($autoCouponData) && $autoCouponData) disabled @endif class="bg-gray-800 text-white px-4 py-2 rounded-lg hover:bg-gray-900 font-medium text-sm whitespace-nowrap @if(isset($autoCouponData) && $autoCouponData) opacity-50 cursor-not-allowed @endif">Terapkan</button>
            </div>
            <div id="coupon-message" class="mt-2 text-sm hidden"></div>
            <div id="coupon-summary" class="mt-3 {{ isset($autoCouponData) && $autoCouponData ? '' : 'hidden' }}">
                <div class="flex justify-between text-sm">
                    <span class="text-gray-600">Harga asli</span>
                    <span class="text-gray-900">Rp {{ number_format($product->price, 0, ',', '.') }}</span>
                </div>
                <div class="flex justify-between text-sm mt-1">
                    <span class="text-red-600">Diskon (<span id="coupon-name">{{ isset($autoCouponData) && $autoCouponData ? $autoCouponData['name'] : '' }}</span>)</span>
                    <span class="text-red-600" id="discount-value">{{ isset($autoCouponData) && $autoCouponData ? '-' . $autoCouponData['discount_formatted'] : '' }}</span>
                </div>
                <div class="border-t border-gray-200 mt-2 pt-2 flex justify-between">
                    <span class="text-sm font-semibold text-gray-900">Total bayar</span>
                    <span class="text-lg font-bold text-indigo-600" id="final-price">{{ isset($autoCouponData) && $autoCouponData ? $autoCouponData['final_price_formatted'] : '' }}</span>
                </div>
            </div>
        </div>

        <form method="POST" action="{{ route('checkout.process', $product->slug) }}" id="checkout-form">
            @csrf
            <input type="hidden" name="coupon_code" id="coupon_code_hidden" value="{{ isset($autoCouponData) && $autoCouponData ? $autoCouponData['code'] : '' }}">
            <button type="submit" class="w-full bg-indigo-600 text-white py-3 rounded-lg hover:bg-indigo-700 font-bold text-lg">
                Bayar Sekarang
            </button>
        </form>

        <p class="text-center text-gray-500 text-sm mt-4">Anda akan diarahkan ke halaman pembayaran Xendit</p>
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
