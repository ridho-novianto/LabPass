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
        // 1. Add index on sesi_id first so foreign key has an index
        Schema::table('kehadiran', function (Blueprint $table) {
            $table->index('sesi_id', 'kehadiran_sesi_id_index');
        });

        // 2. Now drop the unique composite index
        Schema::table('kehadiran', function (Blueprint $table) {
            $table->dropUnique('kehadiran_sesi_id_mahasiswa_id_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('kehadiran', function (Blueprint $table) {
            $table->unique(['sesi_id', 'mahasiswa_id'], 'kehadiran_sesi_id_mahasiswa_id_unique');
        });

        Schema::table('kehadiran', function (Blueprint $table) {
            $table->dropIndex('kehadiran_sesi_id_index');
        });
    }
};
