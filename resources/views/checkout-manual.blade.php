@extends('layouts.public')
@section('title', 'Instruksi Pembayaran - VibeTool.Id')

@section('content')
<div style="max-width: 42rem; margin: 0 auto; padding: 48px 1rem;" x-data="{ showUpload: false }">
    @if(session('success'))
        <div style="margin-bottom: 24px; background-color: #1a3b2a; border: 1px solid #166534; color: #86efac; padding: 12px 16px; border-radius: 8px; font-size: 0.875rem;">
            {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div style="margin-bottom: 24px; background-color: #3b1a1a; border: 1px solid #7f1d1d; color: #fca5a5; padding: 12px 16px; border-radius: 8px; font-size: 0.875rem;">
            {{ session('error') }}
        </div>
    @endif

    <div style="background-color: #1a2332; border-radius: 12px; border: 1px solid #2d3a4a; padding: 32px; box-shadow: 0 4px 15px rgba(0,0,0,0.2);">
        <div style="text-align: center; margin-bottom: 24px;">
            @if($order->product && $order->product->thumbnail)
                <img src="{{ asset('storage/' . $order->product->thumbnail) }}" alt="{{ $order->product->title }}" style="width: 128px; height: 128px; object-fit: cover; border-radius: 12px; margin: 0 auto 16px; border: 1px solid #2d3a4a;">
            @else
                <div style="width: 56px; height: 56px; background-color: #312e81; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 16px;">
                    <svg style="width: 28px; height: 28px; color: #818cf8;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path></svg>
                </div>
            @endif
            <h1 style="font-size: 1.5rem; font-weight: 700; color: #e2e8f0;">Instruksi Pembayaran</h1>
            <p style="color: #94a3b8; font-size: 0.875rem; margin-top: 4px;">Pesanan #{{ $order->id }} &middot; {{ $order->product->title ?? 'Produk' }}</p>
        </div>

        <div style="background-color: #1e1b4b; border: 1px solid #312e81; border-radius: 8px; padding: 20px; margin-bottom: 24px;">
            <div style="font-size: 0.875rem; color: #94a3b8; margin-bottom: 4px;">Total yang harus ditransfer</div>
            <div style="font-size: 1.875rem; font-weight: 700; color: #818cf8;">Rp {{ number_format($order->amount, 0, ',', '.') }}</div>
        </div>

        @if($adminWhatsappLink)
            <a href="{{ $adminWhatsappLink }}" target="_blank" rel="noopener" style="display: flex; align-items: center; justify-content: center; gap: 12px; width: 100%; padding: 16px 24px; margin-bottom: 24px; background-color: #16a34a; color: #ffffff; border-radius: 12px; font-weight: 700; font-size: 1rem; text-decoration: none; box-shadow: 0 4px 10px rgba(22,163,74,0.3);">
                <svg style="width: 24px; height: 24px;" fill="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path d="M.057 24l1.687-6.163a11.867 11.867 0 01-1.587-5.946C.16 5.335 5.495 0 12.05 0a11.817 11.817 0 018.413 3.488 11.824 11.824 0 013.48 8.414c-.003 6.557-5.338 11.892-11.893 11.892a11.9 11.9 0 01-5.688-1.448L.057 24zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884a9.86 9.86 0 001.595 5.392l-.999 3.648 3.893-1.022zm11.387-5.464c-.074-.124-.272-.198-.57-.347-.297-.149-1.758-.868-2.031-.967-.272-.099-.47-.149-.669.149-.198.297-.768.967-.941 1.165-.173.198-.347.223-.644.074-.297-.149-1.255-.462-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.521.151-.172.2-.296.3-.495.099-.198.05-.372-.025-.521-.075-.148-.669-1.611-.916-2.206-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372s-1.04 1.016-1.04 2.479 1.065 2.876 1.213 3.074c.149.198 2.095 3.2 5.076 4.487.709.306 1.263.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413z"/></svg>
                Hubungi Admin via WhatsApp
            </a>
        @endif

        <div style="border: 1px solid #2d3a4a; border-radius: 8px; margin-bottom: 24px; overflow: hidden;">
            <div style="display: flex; align-items: center; justify-content: space-between; padding: 12px 16px; border-bottom: 1px solid #2d3a4a;">
                <span style="font-size: 0.875rem; color: #94a3b8;">Bank</span>
                <span style="font-weight: 500; color: #e2e8f0;">{{ $bankInfo['bank_name'] ?: '-' }}</span>
            </div>
            <div style="display: flex; align-items: center; justify-content: space-between; padding: 12px 16px; border-bottom: 1px solid #2d3a4a;">
                <span style="font-size: 0.875rem; color: #94a3b8;">Nomor Rekening</span>
                <span style="font-family: monospace; font-weight: 500; color: #e2e8f0;" id="bank-account">{{ $bankInfo['bank_account'] ?: '-' }}</span>
            </div>
            <div style="display: flex; align-items: center; justify-content: space-between; padding: 12px 16px; border-bottom: 1px solid #2d3a4a;">
                <span style="font-size: 0.875rem; color: #94a3b8;">Atas Nama</span>
                <span style="font-weight: 500; color: #e2e8f0;">{{ $bankInfo['bank_holder'] ?: '-' }}</span>
            </div>
            <div style="display: flex; align-items: center; justify-content: space-between; padding: 12px 16px; border-bottom: 1px solid #2d3a4a;">
                <span style="font-size: 0.875rem; color: #94a3b8;">ID Pesanan</span>
                <span style="font-family: monospace; font-weight: 500; color: #e2e8f0;">#{{ $order->id }}</span>
            </div>
        </div>

        @if($bankInfo['note'])
            <div style="background-color: #3b351a; border: 1px solid #854d0e; border-radius: 8px; padding: 16px; margin-bottom: 24px; font-size: 0.875rem; color: #fde68a; white-space: pre-line;">{{ $bankInfo['note'] }}</div>
        @endif

        @if($order->status === 'paid')
            <div style="background-color: #1a3b2a; border: 1px solid #166534; color: #86efac; border-radius: 8px; padding: 16px; margin-bottom: 24px; font-size: 0.875rem;">
                <strong>Pembayaran sudah dikonfirmasi.</strong> Silakan akses produk dari menu <a href="{{ route('dashboard.purchases') }}" style="text-decoration: underline; color: #86efac;">Pembelian Saya</a>.
            </div>
        @elseif($order->payment_proof)
            <div style="background-color: #1a2a3b; border: 1px solid #1e40af; color: #93c5fd; border-radius: 8px; padding: 16px; margin-bottom: 24px; font-size: 0.875rem;">
                <strong>Bukti transfer sudah diupload.</strong> Menunggu konfirmasi admin. Pesanan akan otomatis aktif setelah admin verifikasi.
            </div>

            <div class="mb-6">
                <p style="font-size: 0.875rem; font-weight: 500; color: #cbd5e1; margin-bottom: 8px;">Bukti transfer terakhir:</p>
                <a href="{{ asset('storage/' . $order->payment_proof) }}" target="_blank" class="inline-block">
                    <img src="{{ asset('storage/' . $order->payment_proof) }}" alt="Bukti transfer" style="max-height: 256px; border-radius: 8px; border: 1px solid #2d3a4a;">
                </a>
            </div>
        @endif

        @if($order->status === 'pending')
            <div x-show="!showUpload">
                <button type="button" @click="showUpload = true" style="width: 100%; background: linear-gradient(135deg, #4f46e5, #7c3aed); color: #ffffff; padding: 12px 24px; border-radius: 8px; font-weight: 500; border: none; cursor: pointer;">
                    {{ $order->payment_proof ? 'Upload Ulang Bukti Transfer' : 'Saya Sudah Transfer' }}
                </button>
            </div>

            <form x-show="showUpload" x-transition method="POST" action="{{ route('checkout.manual.proof', $order->id) }}" enctype="multipart/form-data" style="display: flex; flex-direction: column; gap: 16px;">
                @csrf
                <div>
                    <label for="proof" class="block text-sm font-medium text-gray-700 mb-1">Upload Bukti Transfer <span class="text-red-500">*</span></label>
                    <input type="file" name="proof" id="proof" accept="image/jpeg,image/png,image/webp" required style="display: block; width: 100%; font-size: 0.875rem; color: #94a3b8;">
                    <p style="font-size: 0.75rem; color: #64748b; margin-top: 4px;">Format: JPG, PNG, atau WEBP. Maksimal 5 MB.</p>
                    @error('proof') <p style="color: #fca5a5; font-size: 0.75rem; margin-top: 4px;">{{ $message }}</p> @enderror
                </div>
                <div style="display: flex; gap: 12px;">
                    <button type="submit" style="flex: 1; background: linear-gradient(135deg, #4f46e5, #7c3aed); color: #ffffff; padding: 10px 24px; border-radius: 8px; font-weight: 500; border: none; cursor: pointer;">Kirim Bukti Transfer</button>
                    <button type="button" @click="showUpload = false" style="padding: 10px 16px; border-radius: 8px; border: 1px solid #2d3a4a; color: #cbd5e1; background: none; cursor: pointer;">Batal</button>
                </div>
            </form>

            <form method="POST" action="{{ route('checkout.manual.cancel', $order->id) }}" class="mt-3" onsubmit="return confirm('Batalkan pesanan #{{ $order->id }}? Tindakan ini tidak bisa dibatalkan.');">
                @csrf
                <button type="submit" style="width: 100%; padding: 10px 16px; border-radius: 8px; border: 1px solid #7f1d1d; color: #fca5a5; background: none; font-weight: 500; font-size: 0.875rem; cursor: pointer;">
                    Batalkan Pesanan
                </button>
            </form>
        @endif

        <div style="margin-top: 24px; text-align: center;">
            <a href="{{ route('dashboard.purchases') }}" style="font-size: 0.875rem; color: #818cf8; font-weight: 500; text-decoration: none;">Lihat semua pembelian saya</a>
        </div>
    </div>
</div>
@endsection
