<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Privasi per-member: kalau false, kontak member ini disembunyikan
            // dari kartu "Upline kamu" di menu produk downline-nya.
            // Default true supaya perilaku lama (selalu tampil) tidak berubah.
            $table->boolean('show_upline_info')->default(true)->after('can_upload_product');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('show_upline_info');
        });
    }
};
