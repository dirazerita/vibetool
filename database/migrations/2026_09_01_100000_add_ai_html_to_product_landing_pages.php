<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('product_landing_pages', function (Blueprint $table) {
            $table->longText('ai_html')->nullable()->after('builder_json');
            $table->timestamp('ai_generated_at')->nullable()->after('ai_html');
        });
    }

    public function down(): void
    {
        Schema::table('product_landing_pages', function (Blueprint $table) {
            $table->dropColumn(['ai_html', 'ai_generated_at']);
        });
    }
};
