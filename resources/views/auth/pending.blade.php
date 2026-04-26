<x-guest-layout>
    <div class="text-center mb-6">
        <div class="inline-flex items-center justify-center w-16 h-16 bg-green-100 rounded-full mb-4">
            <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
            </svg>
        </div>
        <h1 class="text-2xl font-bold text-gray-900">Registrasi Berhasil!</h1>
        <p class="text-gray-600 mt-2 text-sm">
            Akun Anda sedang menunggu aktivasi oleh admin. Klik tombol di bawah untuk
            menghubungi admin via WhatsApp dan minta aktivasi akun Anda.
        </p>
    </div>

    @if(session('warning'))
        <div class="mb-4 p-3 bg-yellow-50 border border-yellow-200 rounded-lg text-sm text-yellow-800">
            {{ session('warning') }}
        </div>
    @endif

    <div class="bg-gray-50 rounded-lg p-4 mb-6 text-sm space-y-1">
        <p class="text-gray-700"><span class="text-gray-500">Nama:</span> <strong>{{ $name }}</strong></p>
        <p class="text-gray-700"><span class="text-gray-500">Email:</span> <strong>{{ $email }}</strong></p>
        <p class="text-gray-700"><span class="text-gray-500">No WA:</span> <strong>{{ $whatsappNumber ?: '-' }}</strong></p>
        @if(!empty($product) && !empty($product['title']))
            <p class="text-gray-700"><span class="text-gray-500">Produk:</span> <strong>{{ $product['title'] }}</strong></p>
            @if(!empty($product['price']))
                <p class="text-gray-700"><span class="text-gray-500">Harga:</span> <strong>Rp {{ number_format((float) $product['price'], 0, ',', '.') }}</strong></p>
            @endif
        @endif
    </div>

    @if($activationLink)
        {{-- Inline style dipakai sebagai fallback jika CSS Tailwind belum di-rebuild di server target. --}}
        <a href="{{ $activationLink }}" target="_blank" rel="noopener"
           style="display:flex;align-items:center;justify-content:center;gap:0.5rem;width:100%;background-color:#16a34a;color:#ffffff;font-weight:700;padding:0.85rem 1rem;border-radius:0.5rem;box-shadow:0 4px 10px rgba(22,163,74,0.25);text-decoration:none;font-size:1rem;line-height:1.25rem;"
           onmouseover="this.style.backgroundColor='#15803d'" onmouseout="this.style.backgroundColor='#16a34a'">
            <svg width="20" height="20" viewBox="0 0 24 24" style="fill:#ffffff;flex-shrink:0;" aria-hidden="true">
                <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
            </svg>
            <span style="color:#ffffff;">Hubungi Admin via WhatsApp</span>
        </a>
    @else
        <div class="p-4 bg-red-50 border border-red-200 rounded-lg text-sm text-red-800">
            Nomor WhatsApp admin belum dikonfigurasi. Silakan hubungi admin melalui kanal lain.
        </div>
    @endif

    <p class="text-xs text-gray-500 mt-4 text-center">
        Setelah admin mengaktifkan akun Anda, Anda dapat login dan mulai berbelanja.
    </p>

    <div class="mt-6 text-center text-sm text-gray-600">
        Sudah diaktifkan?
        <a href="{{ route('login') }}" class="font-medium text-indigo-600 hover:text-indigo-500">Login di sini</a>
    </div>
</x-guest-layout>
