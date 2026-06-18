<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('withdrawals', function (Blueprint $table) {
            // Path bukti transfer (foto) yang diupload admin saat menyetujui
            // penarikan, agar member bisa melihat bukti pembayaran.
            $table->string('transfer_proof')->nullable()->after('note');
        });
    }

    public function down(): void
    {
        Schema::table('withdrawals', function (Blueprint $table) {
            $table->dropColumn('transfer_proof');
        });
    }
};
