<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('promo_templates', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->enum('category', ['member', 'product']);
            $table->foreignId('product_id')->nullable()->constrained('products')->nullOnDelete();
            $table->text('body');
            $table->boolean('is_active')->default(true);
            $table->integer('order')->default(0);
            $table->timestamps();

            $table->index(['category', 'is_active', 'order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('promo_templates');
    }
};
