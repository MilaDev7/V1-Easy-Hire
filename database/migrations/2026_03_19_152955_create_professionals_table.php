<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('professionals', function (Blueprint $table) {
            $table->id();

            // link to users table
            $table->foreignId('user_id')
                  ->constrained('users')
                  ->onDelete('cascade');

            $table->string('skill');
            $table->integer('experience'); // years
            $table->text('bio')->nullable();

            // extra info
            $table->integer('age')->nullable();
            $table->string('gender')->nullable();
            $table->string('city')->nullable();

            // images (NOT file system for now)
            $table->string('cv')->nullable();
            $table->string('certificate')->nullable();

            // rating system
            $table->float('average_rating')->default(0);
            $table->integer('total_reviews')->default(0);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('professionals');
    }
};