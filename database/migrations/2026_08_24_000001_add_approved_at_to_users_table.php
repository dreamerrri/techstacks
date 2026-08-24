<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tracks self-registration approval:
     *  - approved_at IS NULL                  -> registration awaiting HR/Admin approval
     *  - approved_at NOT NULL, is_active true -> active account
     *  - approved_at NOT NULL, is_active false-> deactivated by admin
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->timestamp('approved_at')->nullable()->after('is_active');
        });

        // ── TEMPORARY GRANDFATHERING ────────────────────────────────────────
        // Approves every account that existed before the registration-approval
        // workflow was introduced, so current users aren't locked out.
        //
        // Safe to keep while the team migrates: new self-registrations are only
        // created through the app (which leaves approved_at NULL), so re-running
        // logic here never approves them.
        //
        // >>> Once EVERY developer/environment sharing this code has migrated,
        // >>> you may delete this block entirely — Laravel tracks this migration
        // >>> as already-run, so removing it afterwards changes nothing.
        \Illuminate\Support\Facades\DB::table('users')
            ->whereNull('approved_at')
            ->where('created_at', '<', '2026-08-24 00:00:00') // pre-feature accounts only
            ->update(['approved_at' => now()]);
        // ── END TEMPORARY GRANDFATHERING ────────────────────────────────────
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('approved_at');
        });
    }
};
