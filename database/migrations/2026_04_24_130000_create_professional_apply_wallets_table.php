<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('professional_apply_wallets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->foreignId('current_plan_id')->nullable()->constrained('plans')->nullOnDelete();
            $table->integer('monthly_limit')->default(5);
            $table->integer('monthly_remaining')->default(5);
            $table->integer('extra_remaining')->default(0);
            $table->timestamp('period_start')->useCurrent();
            $table->timestamp('period_end')->useCurrent();
            $table->timestamp('last_reset_at')->nullable();
            $table->timestamps();

            $table->index('period_end');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('professional_apply_wallets');
    }
};
