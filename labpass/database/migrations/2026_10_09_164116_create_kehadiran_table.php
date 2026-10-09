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
        Schema::create('kehadiran', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sesi_id')->constrained('sesi')->onDelete('cascade');
            $table->foreignId('mahasiswa_id')->constrained('mahasiswa')->onDelete('cascade');
            $table->string('token')->unique(); // ulid, unique
            $table->string('status'); // lulus, ditolak, menunggu_dispensasi, dispensasi_disetujui, dispensasi_ditolak
            $table->json('apd_terdeteksi')->nullable();
            $table->json('apd_kurang')->nullable();
            $table->string('snapshot_path')->nullable();
            $table->json('evaluasi_risiko')->nullable();
            $table->timestamp('jam_masuk')->nullable();
            $table->timestamp('jam_keluar')->nullable();
            $table->timestamps();
            
            // Unique constraint
            $table->unique(['sesi_id', 'mahasiswa_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kehadiran');
    }
};
