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
        Schema::table('users', function (Blueprint $table) {
            $table->string('role', 30)->default('operator_bku')->after('password'); // 'staf_esdm', 'operator_bku', 'admin'
            $table->foreignId('bku_id')->nullable()->after('role')->constrained('bku')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['bku_id']);
            $table->dropColumn(['role', 'bku_id']);
        });
    }
};
