<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Adds indexes for the app's hottest queries:
     *  - audit_logs.created_at: every log page runs latest() (ORDER BY created_at DESC)
     *  - notifications (user_id, is_read): shared Inertia props filter unread
     *    counts/lists per user on EVERY request.
     *    (audience_type, is_read) already exists from an earlier migration.
     * Idempotent: skips indexes that already exist.
     */
    public function up(): void
    {
        Schema::table('audit_logs', function (Blueprint $table) {
            if (!Schema::hasIndex('audit_logs', 'audit_logs_created_at_index')) {
                $table->index('created_at');
            }
        });

        Schema::table('notifications', function (Blueprint $table) {
            if (!Schema::hasIndex('notifications', 'notifications_user_id_is_read_index')) {
                $table->index(['user_id', 'is_read']);
            }
        });
    }

    public function down(): void
    {
        Schema::table('audit_logs', function (Blueprint $table) {
            if (Schema::hasIndex('audit_logs', 'audit_logs_created_at_index')) {
                $table->dropIndex(['created_at']);
            }
        });

        Schema::table('notifications', function (Blueprint $table) {
            if (Schema::hasIndex('notifications', 'notifications_user_id_is_read_index')) {
                $table->dropIndex(['user_id', 'is_read']);
            }
        });
    }
};
