<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('webhook_deliveries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('license_id')->nullable()->constrained()->nullOnDelete();
            $table->string('event', 64);                 // license.issued | license.revoked | license.renewed
            $table->string('url', 500);                  // captured at dispatch (in case product.webhook_url changes later)
            $table->json('payload');                     // payload sent
            $table->string('signature', 200)->nullable();
            $table->unsignedSmallInteger('status_code')->nullable();
            $table->text('response_body')->nullable();
            $table->text('error_message')->nullable();
            $table->unsignedTinyInteger('attempt')->default(1);
            $table->string('result', 16);                // success | failed
            $table->timestamp('delivered_at')->nullable();
            $table->timestamps();

            $table->index(['product_id', 'created_at']);
            $table->index(['license_id', 'event']);
            $table->index('result');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('webhook_deliveries');
    }
};
