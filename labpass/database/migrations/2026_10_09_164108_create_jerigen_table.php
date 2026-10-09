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
        Schema::create('jerigen', function (Blueprint $table) {
            $table->id();
            $table->string('kode'); // misal "JR-A"
            $table->string('label');
            $table->string('warna'); // hex
            $table->string('jenis_limbah')->nullable();
            $table->text('keterangan')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('jerigen');
    }
};
