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
        Schema::create('sumur', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bku_kontrak_id')->constrained('bku_kontrak')->cascadeOnDelete();
            $table->string('nama_sumur', 100)->unique();
            $table->string('desa', 100);
            $table->string('kecamatan', 100);
            $table->string('kabupaten', 100);
            $table->decimal('latitude', 12, 9);
            $table->decimal('longitude', 11, 8);
            $table->timestamps();

            $table->index('bku_kontrak_id', 'idx_sumur_bku_kontrak');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sumur');
    }
};
