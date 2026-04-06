<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('plans', function (Blueprint $table) {
            $table->integer('direct_requests_limit')->nullable()->after('job_posts_limit');
        });

        Schema::table('subscriptions', function (Blueprint $table) {
            $table->integer('direct_requests_remaining')->nullable()->after('remaining_posts');
        });
    }

    public function down(): void
    {
        Schema::table('plans', function (Blueprint $table) {
            $table->dropColumn('direct_requests_limit');
        });

        Schema::table('subscriptions', function (Blueprint $table) {
            $table->dropColumn('direct_requests_remaining');
        });
    }
};
