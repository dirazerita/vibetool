@extends('layouts.public')
@section('title', 'Pembayaran Berhasil - VibeTool.Id')

@section('content')
<div style="max-width: 42rem; margin: 0 auto; padding: 48px 1rem;">
    <div style="background-color: #1a2332; border-radius: 12px; border: 1px solid #2d3a4a; padding: 32px; text-align: center; box-shadow: 0 4px 15px rgba(0,0,0,0.2);">
        <div style="width: 64px; height: 64px; background-color: #1a3b2a; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 24px;">
            <svg style="width: 32px; height: 32px; color: #86efac;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
        </div>
        <h1 style="font-size: 1.5rem; font-weight: 700; color: #e2e8f0; margin-bottom: 8px;">Terima Kasih!</h1>
        <p style="color: #94a3b8; margin-bottom: 24px;">Pesanan Anda sedang diproses. Anda akan menerima link download setelah pembayaran dikonfirmasi.</p>

        @if($order->isPaid() && $order->download_token)
            <a href="{{ route('download', $order->download_token) }}" style="display: inline-block; background: linear-gradient(135deg, #4f46e5, #7c3aed); color: #ffffff; padding: 12px 24px; border-radius: 8px; font-weight: 700; text-decoration: none;">
                Download Produk
            </a>
        @else
            <div style="background-color: #3b351a; border: 1px solid #854d0e; color: #fde68a; padding: 12px 16px; border-radius: 8px;">
                Menunggu konfirmasi pembayaran. Silakan cek email Anda untuk link download.
            </div>
        @endif

        <div class="mt-6">
            <a href="{{ route('home') }}" style="color: #818cf8; font-weight: 500; text-decoration: none;">Kembali ke Beranda</a>
        </div>
    </div>
</div>
@endsection
