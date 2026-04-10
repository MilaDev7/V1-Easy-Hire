<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('direct_requests', function (Blueprint $table) {
            $table->timestamp('expires_at')->nullable()->after('status')->index();
        });

        $driver = DB::getDriverName();

        if ($driver === 'mysql') {
            DB::statement("ALTER TABLE direct_requests MODIFY status ENUM('pending','accepted','rejected','expired') NOT NULL DEFAULT 'pending'");
        } elseif ($driver === 'pgsql') {
            DB::statement("ALTER TYPE direct_requests_status ADD VALUE IF NOT EXISTS 'expired'");
        }

        if ($driver === 'mysql') {
            DB::table('direct_requests')
                ->whereNull('expires_at')
                ->update([
                    'expires_at' => DB::raw('DATE_ADD(created_at, INTERVAL 48 HOUR)'),
                ]);
        } elseif ($driver === 'pgsql') {
            DB::table('direct_requests')
                ->whereNull('expires_at')
                ->update([
                    'expires_at' => DB::raw("created_at + interval '48 hours'"),
                ]);
        }
    }

    public function down(): void
    {
        $driver = DB::getDriverName();

        if ($driver === 'mysql') {
            DB::statement("ALTER TABLE direct_requests MODIFY status ENUM('pending','accepted','rejected') NOT NULL DEFAULT 'pending'");
        }

        Schema::table('direct_requests', function (Blueprint $table) {
            $table->dropColumn('expires_at');
        });
    }
};
