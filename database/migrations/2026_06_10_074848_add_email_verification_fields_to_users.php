<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Hash dari 6-digit kode OTP yang dikirim ke email user.
            $table->string('email_verification_code_hash', 64)->nullable()->after('email_verified_at');
            $table->timestamp('email_verification_expires_at')->nullable()->after('email_verification_code_hash');
            $table->unsignedSmallInteger('email_verification_attempts')->default(0)->after('email_verification_expires_at');
            $table->timestamp('email_verification_last_sent_at')->nullable()->after('email_verification_attempts');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'email_verification_code_hash',
                'email_verification_expires_at',
                'email_verification_attempts',
                'email_verification_last_sent_at',
            ]);
        });
    }
};
