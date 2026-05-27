<x-guest-layout>
    <div style="text-align: center; margin-bottom: 24px;">
        <div style="display: inline-flex; align-items: center; justify-content: center; width: 64px; height: 64px; background-color: #1a3b2a; border-radius: 50%; margin-bottom: 16px;">
            <svg style="width: 32px; height: 32px; color: #86efac;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
            </svg>
        </div>
        <h1 style="font-size: 1.5rem; font-weight: 700; color: #e2e8f0;">Registrasi Berhasil!</h1>
        <p style="color: #94a3b8; margin-top: 8px; font-size: 0.875rem;">
            Akun Anda sedang menunggu aktivasi oleh admin. Klik tombol di bawah untuk
            menghubungi admin via WhatsApp dan minta aktivasi akun Anda.
        </p>
    </div>

    @if(session('warning'))
        <div style="margin-bottom: 16px; padding: 12px; background-color: #3b351a; border: 1px solid #854d0e; border-radius: 8px; font-size: 0.875rem; color: #fde68a;">
            {{ session('warning') }}
        </div>
    @endif

    <div style="background-color: #151e2d; border-radius: 8px; padding: 16px; margin-bottom: 24px; font-size: 0.875rem;">
        <p style="color: #cbd5e1; margin-bottom: 4px;"><span style="color: #94a3b8;">Nama:</span> <strong>{{ $name }}</strong></p>
        <p style="color: #cbd5e1; margin-bottom: 4px;"><span style="color: #94a3b8;">Email:</span> <strong>{{ $email }}</strong></p>
        <p style="color: #cbd5e1; margin-bottom: 4px;"><span style="color: #94a3b8;">No WA:</span> <strong>{{ $whatsappNumber ?: '-' }}</strong></p>
        @if(!empty($product) && !empty($product['title']))
            <p style="color: #cbd5e1; margin-bottom: 4px;"><span style="color: #94a3b8;">Produk:</span> <strong>{{ $product['title'] }}</strong></p>
            @if(!empty($product['price']))
                <p style="color: #cbd5e1;"><span style="color: #94a3b8;">Harga:</span> <strong>Rp {{ number_format((float) $product['price'], 0, ',', '.') }}</strong></p>
            @endif
        @endif
    </div>

    @if($activationLink)
        <a href="{{ $activationLink }}" target="_blank" rel="noopener"
           style="display:flex;align-items:center;justify-content:center;gap:0.5rem;width:100%;background-color:#16a34a;color:#ffffff;font-weight:700;padding:0.85rem 1rem;border-radius:0.5rem;box-shadow:0 4px 10px rgba(22,163,74,0.25);text-decoration:none;font-size:1rem;line-height:1.25rem;"
           onmouseover="this.style.backgroundColor='#15803d'" onmouseout="this.style.backgroundColor='#16a34a'">
            <svg width="20" height="20" viewBox="0 0 24 24" style="fill:#ffffff;flex-shrink:0;" aria-hidden="true">
                <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
            </svg>
            <span style="color:#ffffff;">Hubungi Admin via WhatsApp</span>
        </a>
    @else
        <div style="padding: 16px; background-color: #3b1a1a; border: 1px solid #7f1d1d; border-radius: 8px; font-size: 0.875rem; color: #fca5a5;">
            Nomor WhatsApp admin belum dikonfigurasi. Silakan hubungi admin melalui kanal lain.
        </div>
    @endif

    <p style="font-size: 0.75rem; color: #64748b; margin-top: 16px; text-align: center;">
        Setelah admin mengaktifkan akun Anda, Anda dapat login dan mulai berbelanja.
    </p>

    <div style="margin-top: 24px; text-align: center; font-size: 0.875rem; color: #94a3b8;">
        Sudah diaktifkan?
        <a href="{{ route('login') }}" style="font-weight: 500; color: #818cf8; text-decoration: none;">Login di sini</a>
    </div>
</x-guest-layout>
