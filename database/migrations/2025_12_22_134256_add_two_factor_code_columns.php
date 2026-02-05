<?php


use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'is_2fa_enabled')) {
                $table->boolean('is_2fa_enabled')->default(true);
            }
            if (!Schema::hasColumn('users', 'code_2FA')) {
                $table->string('code_2FA')->nullable();
            }
            if (!Schema::hasColumn('users', 'code_2FA_expiry')) {
                $table->dateTime('code_2FA_expiry')->nullable();
            }
        });
    }

    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'is_2fa_enabled')) {
                $table->dropColumn('is_2fa_enabled');
            }
            if (Schema::hasColumn('users', 'code_2FA')) {
                $table->dropColumn('code_2FA');
            }
            if (Schema::hasColumn('users', 'code_2FA_expiry')) {
                $table->dropColumn('code_2FA_expiry');
            }
        });
    }
};