<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $needsProfilePhoto = ! Schema::hasColumn('professionals', 'profile_photo');
        $needsIdCard = ! Schema::hasColumn('professionals', 'id_card');

        if (! $needsProfilePhoto && ! $needsIdCard) {
            return;
        }

        Schema::table('professionals', function (Blueprint $table) use ($needsProfilePhoto, $needsIdCard) {
            if ($needsProfilePhoto) {
                $table->string('profile_photo')->nullable()->after('certificate');
            }

            if ($needsIdCard) {
                $table->string('id_card')->nullable()->after('profile_photo');
            }
        });
    }

    public function down(): void
    {
        $hasProfilePhoto = Schema::hasColumn('professionals', 'profile_photo');
        $hasIdCard = Schema::hasColumn('professionals', 'id_card');

        if (! $hasProfilePhoto && ! $hasIdCard) {
            return;
        }

        Schema::table('professionals', function (Blueprint $table) use ($hasProfilePhoto, $hasIdCard) {
            if ($hasIdCard) {
                $table->dropColumn('id_card');
            }

            if ($hasProfilePhoto) {
                $table->dropColumn('profile_photo');
            }
        });
    }
};
