<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('product_landing_pages', function (Blueprint $table) {
            $table->string('hero_title_font', 50)->default('Poppins');
            $table->string('hero_title_size', 10)->default('48px');
            $table->string('hero_title_color', 10)->default('#ffffff');
            $table->string('hero_subtitle_font', 50)->default('Poppins');
            $table->string('hero_subtitle_color', 10)->default('#e2e8f0');
            $table->string('about_font', 50)->default('Poppins');
            $table->string('about_color', 10)->default('#374151');
            $table->string('about_bg_color', 10)->default('#ffffff');
            $table->string('testimonial_title_color', 10)->default('#111827');
            $table->string('testimonial_bg_color', 10)->default('#f9fafb');
        });
    }

    public function down(): void
    {
        Schema::table('product_landing_pages', function (Blueprint $table) {
            $table->dropColumn([
                'hero_title_font',
                'hero_title_size',
                'hero_title_color',
                'hero_subtitle_font',
                'hero_subtitle_color',
                'about_font',
                'about_color',
                'about_bg_color',
                'testimonial_title_color',
                'testimonial_bg_color',
            ]);
        });
    }
};
