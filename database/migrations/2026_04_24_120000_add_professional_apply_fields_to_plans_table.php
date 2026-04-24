<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('plans', function (Blueprint $table) {
            if (! Schema::hasColumn('plans', 'plan_scope')) {
                $table->string('plan_scope')->default('client')->after('price');
            }
            if (! Schema::hasColumn('plans', 'apply_limit_monthly')) {
                $table->integer('apply_limit_monthly')->default(0)->after('job_posts_limit');
            }
            if (! Schema::hasColumn('plans', 'extra_apply_quantity')) {
                $table->integer('extra_apply_quantity')->default(0)->after('apply_limit_monthly');
            }
            if (! Schema::hasColumn('plans', 'is_active')) {
                $table->boolean('is_active')->default(true)->after('extra_apply_quantity');
            }
        });

        DB::table('plans')->whereNull('plan_scope')->update(['plan_scope' => 'client']);
    }

    public function down(): void
    {
        Schema::table('plans', function (Blueprint $table) {
            if (Schema::hasColumn('plans', 'is_active')) {
                $table->dropColumn('is_active');
            }
            if (Schema::hasColumn('plans', 'extra_apply_quantity')) {
                $table->dropColumn('extra_apply_quantity');
            }
            if (Schema::hasColumn('plans', 'apply_limit_monthly')) {
                $table->dropColumn('apply_limit_monthly');
            }
            if (Schema::hasColumn('plans', 'plan_scope')) {
                $table->dropColumn('plan_scope');
            }
        });
    }
};

