<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('product_landing_pages', function (Blueprint $table) {
            // Kolom custom HTML untuk landing page builder. Admin/member
            // bisa menulis HTML murni sebagai konten tambahan landing page.
            $table->text('custom_html')->nullable()->after('is_published');
        });
    }

    public function down(): void
    {
        Schema::table('product_landing_pages', function (Blueprint $table) {
            $table->dropColumn('custom_html');
        });
    }
};