<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasColumn('contracts', 'client_confirmed_at')) {
            Schema::table('contracts', function (Blueprint $table) {
                $table->dropColumn('client_confirmed_at');
            });
        }

        $driver = DB::connection()->getDriverName();

        if ($driver === 'mysql') {
            DB::statement("ALTER TABLE contracts MODIFY COLUMN status ENUM('active','pending_completion','completed','cancelled') NOT NULL DEFAULT 'active'");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $driver = DB::connection()->getDriverName();

        if ($driver === 'mysql') {
            DB::statement("ALTER TABLE contracts MODIFY COLUMN status ENUM('active','completed','cancelled') NOT NULL DEFAULT 'active'");
        }

        if (! Schema::hasColumn('contracts', 'client_confirmed_at')) {
            Schema::table('contracts', function (Blueprint $table) {
                $table->timestamp('client_confirmed_at')->nullable()->after('status');
            });
        }
    }
};
