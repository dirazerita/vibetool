<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'mysql' || $driver === 'mariadb') {
            DB::statement("ALTER TABLE `orders` MODIFY COLUMN `status` ENUM('pending','paid','expired','failed','cancelled') NOT NULL DEFAULT 'pending'");
        }
        // SQLite, Postgres, dll: kolom status biasanya string fleksibel,
        // jadi tidak perlu perubahan struktur.
    }

    public function down(): void
    {
        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'mysql' || $driver === 'mariadb') {
            // Pastikan tidak ada baris dengan status 'cancelled' sebelum revert.
            DB::table('orders')->where('status', 'cancelled')->update(['status' => 'failed']);
            DB::statement("ALTER TABLE `orders` MODIFY COLUMN `status` ENUM('pending','paid','expired','failed') NOT NULL DEFAULT 'pending'");
        }
    }
};
