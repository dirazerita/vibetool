<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('product_landing_pages', function (Blueprint $table) {
            // Full HTML kustom — user paste HTML page utuh, jadi halaman landing
            // page sepenuhnya pakai HTML user (bukan sistem hero/gallery/testimonial).
            $table->longText('full_html')->nullable()->after('custom_html');

            // Toggle: true = pakai full HTML user, false = pakai landing page sistem.
            $table->boolean('use_full_html')->default(false)->after('full_html');
        });
    }

    public function down(): void
    {
        Schema::table('product_landing_pages', function (Blueprint $table) {
            $table->dropColumn(['full_html', 'use_full_html']);
        });
    }
};