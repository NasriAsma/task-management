<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'is_2fa_enabled')) {
                $table->boolean('is_2fa_enabled')->default(false)->after('remember_token');
            }
            if (!Schema::hasColumn('users', 'code_2FA')) {
                $table->string('code_2FA', 10)->nullable()->after('is_2fa_enabled');
            }
            if (!Schema::hasColumn('users', 'code_2FA_expiry')) {
                $table->timestamp('code_2FA_expiry')->nullable()->after('code_2FA');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'code_2FA_expiry')) {
                $table->dropColumn('code_2FA_expiry');
            }
            if (Schema::hasColumn('users', 'code_2FA')) {
                $table->dropColumn('code_2FA');
            }
            if (Schema::hasColumn('users', 'is_2fa_enabled')) {
                $table->dropColumn('is_2fa_enabled');
            }
        });
    }
};
