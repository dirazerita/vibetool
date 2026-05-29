<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('messages', function (Blueprint $table) {
            $table->foreignId('broadcast_id')->nullable()->after('sender_id')->constrained('broadcasts')->nullOnDelete();
            $table->index('broadcast_id');
        });
    }

    public function down(): void
    {
        Schema::table('messages', function (Blueprint $table) {
            $table->dropForeign(['broadcast_id']);
            $table->dropIndex(['broadcast_id']);
            $table->dropColumn('broadcast_id');
        });
    }
};
