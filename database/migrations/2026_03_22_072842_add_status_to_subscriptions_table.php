<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('subscriptions', function (Blueprint $table) {
            // Adds the status column with a default value of 'active'
            // Putting it 'after' ends_at makes the table look organized
            $table->string('status')->default('active')->after('remaining_posts');
        });
    }

    public function down(): void
    {
        Schema::table('subscriptions', function (Blueprint $table) {
            // Removes the column if you rollback the migration
            $table->dropColumn('status');
        });
    }
};