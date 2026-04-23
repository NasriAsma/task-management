<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('audits', function (Blueprint $table) {
            $table->index(['user_id', 'created_at'], 'audits_user_created_at_idx');
            $table->index('created_at', 'audits_created_at_idx');
            $table->index('event', 'audits_event_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('audits', function (Blueprint $table) {
            $table->dropIndex('audits_user_created_at_idx');
            $table->dropIndex('audits_created_at_idx');
            $table->dropIndex('audits_event_idx');
        });
    }
};
