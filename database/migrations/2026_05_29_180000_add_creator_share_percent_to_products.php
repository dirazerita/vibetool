<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->decimal('creator_share_percent', 5, 2)->default(0)->after('upline_percent_non_owner');
        });

        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'mysql' || $driver === 'mariadb') {
            DB::statement("ALTER TABLE `commissions` MODIFY COLUMN `type` ENUM('direct','upline','creator') NOT NULL");
        }
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('creator_share_percent');
        });

        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'mysql' || $driver === 'mariadb') {
            DB::table('commissions')->where('type', 'creator')->update(['type' => 'direct']);
            DB::statement("ALTER TABLE `commissions` MODIFY COLUMN `type` ENUM('direct','upline') NOT NULL");
        }
    }
};
