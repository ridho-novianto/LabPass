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
        Schema::create('token_log', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kehadiran_id')->nullable()->constrained('kehadiran')->onDelete('set null');
            $table->string('jenis'); // evaluasi_risiko
            $table->integer('token_masuk');
            $table->integer('token_keluar');
            $table->boolean('dari_cache');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('token_log');
    }
};
