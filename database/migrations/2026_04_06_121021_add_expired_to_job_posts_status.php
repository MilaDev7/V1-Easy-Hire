<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            return;
        }

        DB::statement("ALTER TABLE job_posts MODIFY COLUMN status ENUM('open', 'assigned', 'completed', 'cancelled', 'expired') DEFAULT 'open'");
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            return;
        }

        DB::statement("ALTER TABLE job_posts MODIFY COLUMN status ENUM('open', 'assigned', 'completed', 'cancelled') DEFAULT 'open'");
    }
};
