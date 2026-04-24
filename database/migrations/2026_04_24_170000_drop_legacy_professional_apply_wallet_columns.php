<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('professional_apply_wallets', function (Blueprint $table) {
            $table->dropIndex(['period_end']);
            $table->dropColumn([
                'monthly_remaining',
                'extra_remaining',
                'period_start',
                'period_end',
                'last_reset_at',
            ]);
        });
    }

    public function down(): void
    {
        Schema::table('professional_apply_wallets', function (Blueprint $table) {
            $table->integer('monthly_remaining')->default(5)->after('remaining_applies');
            $table->integer('extra_remaining')->default(0)->after('monthly_remaining');
            $table->timestamp('period_start')->useCurrent()->after('extra_remaining');
            $table->timestamp('period_end')->useCurrent()->after('period_start');
            $table->timestamp('last_reset_at')->nullable()->after('period_end');
            $table->index('period_end');
        });
    }
};
