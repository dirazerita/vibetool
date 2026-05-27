<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('can_upload_product')->default(false)->after('status');
        });

        Schema::table('products', function (Blueprint $table) {
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete()->after('is_active');
            $table->string('approval_status')->default('approved')->after('created_by');
            $table->text('rejection_reason')->nullable()->after('approval_status');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropForeign(['created_by']);
            $table->dropColumn(['created_by', 'approval_status', 'rejection_reason']);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('can_upload_product');
        });
    }
};
