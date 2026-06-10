<x-mail::message>
# Halo, {{ $name }}

Berikut kode verifikasi email Anda untuk VibeTool.id:

<x-mail::panel>
<div style="text-align:center; font-size:32px; font-weight:bold; letter-spacing:8px; font-family: monospace;">{{ $code }}</div>
</x-mail::panel>

Masukkan kode ini di halaman **Verifikasi Email** di akun member Anda.

Kode ini berlaku selama **{{ $minutes }} menit** sejak email ini dikirim.

Jika Anda tidak meminta kode verifikasi ini, abaikan email ini — kode ini tidak akan bisa dipakai oleh siapapun selain Anda.

Terima kasih,<br>
{{ config('app.name') }}
</x-mail::message>
