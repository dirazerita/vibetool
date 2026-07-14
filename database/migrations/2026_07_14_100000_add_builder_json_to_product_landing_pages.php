<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * State Page Builder (drag & drop) disimpan sebagai JSON agar halaman
     * bisa dibuka & diedit ulang di builder. Hasil kompilasinya (HTML utuh
     * yang mobile-friendly) tetap disimpan ke kolom full_html yang sudah ada,
     * sehingga pipeline render publik (HomeController@show) tidak berubah.
     */
    public function up(): void
    {
        Schema::table('product_landing_pages', function (Blueprint $table) {
            $table->longText('builder_json')->nullable()->after('use_full_html');
        });
    }

    public function down(): void
    {
        Schema::table('product_landing_pages', function (Blueprint $table) {
            $table->dropColumn('builder_json');
        });
    }
};
