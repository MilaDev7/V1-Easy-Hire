<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('professionals', function (Blueprint $table) {
            // Rename city to location
            $table->renameColumn('city', 'location');
        });
    }

    public function down(): void
    {
        Schema::table('professionals', function (Blueprint $table) {
            // Rollback: Rename location back to city
            $table->renameColumn('location', 'city');
        });
    }
};