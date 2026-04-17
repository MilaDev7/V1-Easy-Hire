<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Keep full history: normalize legacy values in place.
        DB::table('reports')
            ->whereNull('action_taken')
            ->orWhere('action_taken', '')
            ->orWhere('action_taken', 'no_action')
            ->update(['action_taken' => 'none']);

        DB::table('reports')
            ->whereNotIn('action_taken', ['none', 'warning', 'suspend_user', 'cancel_contract'])
            ->update(['action_taken' => 'none']);

        DB::table('reports')
            ->whereNotIn('status', ['pending', 'resolved'])
            ->update(['status' => 'pending']);

        if (DB::getDriverName() !== 'sqlite') {
            DB::statement("ALTER TABLE reports MODIFY COLUMN status ENUM('pending','resolved') NOT NULL DEFAULT 'pending'");
            DB::statement("ALTER TABLE reports MODIFY COLUMN action_taken ENUM('none','warning','suspend_user','cancel_contract') NOT NULL DEFAULT 'none'");
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() !== 'sqlite') {
            DB::statement("ALTER TABLE reports MODIFY COLUMN action_taken VARCHAR(255) NULL");
            DB::statement("ALTER TABLE reports MODIFY COLUMN status ENUM('pending','resolved') NOT NULL DEFAULT 'pending'");
        }
    }
};
