<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('software_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('title', 200);
            $table->text('purpose');
            $table->string('target_users', 500);
            $table->text('problem_to_solve');
            $table->string('similar_apps', 500)->nullable();
            $table->json('platforms');
            $table->text('key_features');
            $table->string('attachment_path')->nullable();
            $table->string('attachment_name')->nullable();
            $table->string('attachment_mime', 100)->nullable();
            $table->unsignedInteger('attachment_size')->nullable();
            $table->string('budget_range', 30)->nullable();
            $table->string('urgency', 30)->nullable();
            $table->text('additional_notes')->nullable();
            $table->string('status', 30)->default('pending');
            $table->text('admin_notes')->nullable();
            $table->text('admin_response')->nullable();
            $table->foreignId('product_id')->nullable()->constrained('products')->nullOnDelete();
            $table->timestamp('admin_responded_at')->nullable();
            $table->timestamp('user_seen_response_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'status']);
            $table->index(['status', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('software_requests');
    }
};
