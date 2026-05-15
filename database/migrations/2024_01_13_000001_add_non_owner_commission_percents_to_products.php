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
            $table->decimal('commission_percent_non_owner', 5, 2)->nullable()->after('commission_percent');
            $table->decimal('upline_percent_non_owner', 5, 2)->nullable()->after('upline_percent');
        });

        DB::statement('UPDATE products SET commission_percent_non_owner = commission_percent WHERE commission_percent_non_owner IS NULL');
        DB::statement('UPDATE products SET upline_percent_non_owner = upline_percent WHERE upline_percent_non_owner IS NULL');
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['commission_percent_non_owner', 'upline_percent_non_owner']);
        });
    }
};
