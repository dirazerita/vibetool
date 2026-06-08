<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('promo_template_media', function (Blueprint $table) {
            $table->id();
            $table->foreignId('promo_template_id')->constrained('promo_templates')->cascadeOnDelete();
            $table->enum('type', ['image', 'video']);
            $table->string('path');
            $table->string('original_name')->nullable();
            $table->string('mime')->nullable();
            $table->unsignedBigInteger('size_bytes')->default(0);
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            $table->index(['promo_template_id', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('promo_template_media');
    }
};
