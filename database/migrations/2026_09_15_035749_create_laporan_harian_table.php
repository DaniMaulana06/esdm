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
        Schema::create('laporan_harian', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bku_kontrak_id')->constrained('bku_kontrak')->cascadeOnDelete();
            $table->date('tanggal');
            $table->decimal('total_produksi', 15,2)->default(0.00);
            $table->decimal('total_lifting',15,2)->default(0);
            $table->text('keterangan')->nullable();
            $table->timestamps();

            $table->unique(['bku_kontrak_id','tanggal'], 'unique_bku_tanggal');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('laporan_harian');
    }
};
