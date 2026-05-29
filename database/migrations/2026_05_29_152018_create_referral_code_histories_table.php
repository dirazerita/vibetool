<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('referral_code_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('old_code', 50)->nullable();
            $table->string('new_code', 50);
            $table->foreignId('changed_by_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('changed_by_role', 20)->default('self');
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->text('reason')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['user_id', 'created_at']);
            $table->index('old_code');
            $table->index('new_code');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('referral_code_histories');
    }
};
