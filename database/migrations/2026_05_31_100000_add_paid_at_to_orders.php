<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->timestamp('paid_at')->nullable()->after('status')->index();
        });

        // Backfill: untuk order yang sudah paid tapi belum punya paid_at,
        // gunakan updated_at sebagai approximation (waktu terakhir order di-update,
        // kemungkinan besar = waktu marker paid). Ini diperlukan agar logic
        // time-based commission rate yang baru bisa mengevaluasi ownership history
        // untuk order existing.
        DB::table('orders')
            ->where('status', 'paid')
            ->whereNull('paid_at')
            ->update(['paid_at' => DB::raw('updated_at')]);
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropIndex(['paid_at']);
            $table->dropColumn('paid_at');
        });
    }
};
