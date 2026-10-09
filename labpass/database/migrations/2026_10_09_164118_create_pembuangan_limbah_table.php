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
        Schema::create('pembuangan_limbah', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kehadiran_id')->constrained('kehadiran')->onDelete('cascade');
            $table->foreignId('materi_limbah_id')->constrained('materi_limbah')->onDelete('cascade');
            $table->decimal('volume_ml', 10, 2)->nullable();
            $table->timestamp('dibuang_pada')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pembuangan_limbah');
    }
};
