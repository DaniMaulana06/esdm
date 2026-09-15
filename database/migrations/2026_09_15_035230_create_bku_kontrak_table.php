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
        Schema::create('bku_kontrak', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bku_id')->constrained('bku')->cascadeOnDelete();
            $table->foreignId('kontrak_id')->constrained('kontrak')->cascadeOnDelete();
            $table->integer('jumlah_sumur')->default(0);
            $table->timestamps();

            $table->unique(['bku_id', 'kontrak_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bku_kontrak');
    }
};
