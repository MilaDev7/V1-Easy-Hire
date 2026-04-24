<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('professional_apply_wallets', function (Blueprint $table) {
            $table->integer('remaining_applies')->default(0)->after('monthly_limit');
        });

        DB::statement('UPDATE professional_apply_wallets SET remaining_applies = GREATEST(COALESCE(monthly_remaining, 0), 0) + GREATEST(COALESCE(extra_remaining, 0), 0)');
    }

    public function down(): void
    {
        Schema::table('professional_apply_wallets', function (Blueprint $table) {
            $table->dropColumn('remaining_applies');
        });
    }
};
