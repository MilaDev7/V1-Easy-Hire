<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
  public function up(): void
{
    Schema::create('reports', function (Blueprint $table) {
        $table->id();

        $table->foreignId('contract_id')->constrained()->onDelete('cascade');

        $table->foreignId('reporter_id')->constrained('users')->onDelete('cascade');
        $table->foreignId('reported_id')->constrained('users')->onDelete('cascade');

        $table->text('reason');
        $table->timestamps();

        // ❌ prevent duplicate report
        $table->unique(['contract_id', 'reporter_id']);
    });
}
};
