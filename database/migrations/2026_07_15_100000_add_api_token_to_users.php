<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Token API untuk login dari aplikasi Android native (folder "ANDROID NATIVE").
     * Satu token aktif per user; dibuat ulang setiap login, dihapus saat logout.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('api_token', 80)->nullable()->unique()->after('remember_token');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('api_token');
        });
    }
};
