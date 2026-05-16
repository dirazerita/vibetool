<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->string('license_duration')->default('lifetime')->after('product_type');
        });

        Schema::table('licenses', function (Blueprint $table) {
            $table->timestamp('expires_at')->nullable()->after('assigned_at');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('license_duration');
        });

        Schema::table('licenses', function (Blueprint $table) {
            $table->dropColumn('expires_at');
        });
    }
};
