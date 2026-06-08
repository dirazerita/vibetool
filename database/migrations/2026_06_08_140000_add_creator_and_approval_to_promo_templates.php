<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('promo_templates', function (Blueprint $table) {
            $table->foreignId('created_by_user_id')->nullable()->after('product_id')
                ->constrained('users')->nullOnDelete();
            $table->enum('approval_status', ['pending', 'approved', 'rejected'])
                ->default('approved')->after('created_by_user_id');
            $table->text('rejection_reason')->nullable()->after('approval_status');
            $table->timestamp('reviewed_at')->nullable()->after('rejection_reason');
            $table->foreignId('reviewed_by_user_id')->nullable()->after('reviewed_at')
                ->constrained('users')->nullOnDelete();

            $table->index(['approval_status', 'is_active']);
            $table->index('created_by_user_id');
        });
    }

    public function down(): void
    {
        Schema::table('promo_templates', function (Blueprint $table) {
            $table->dropForeign(['reviewed_by_user_id']);
            $table->dropForeign(['created_by_user_id']);
            $table->dropIndex(['approval_status', 'is_active']);
            $table->dropIndex(['created_by_user_id']);
            $table->dropColumn(['created_by_user_id', 'approval_status', 'rejection_reason', 'reviewed_at', 'reviewed_by_user_id']);
        });
    }
};
