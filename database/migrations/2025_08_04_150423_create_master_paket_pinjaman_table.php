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
        Schema::create('master_paket_pinjaman', function (Blueprint $table) {
            $table->id();
            $table->string('periode', 7); // Format: 2025-08 (tahun-bulan)
            $table->decimal('bunga_per_bulan', 5, 2)->default(1.00); // 1% per bulan
            $table->integer('stock_limit')->default(100); // Total limit untuk bulan ini
            $table->integer('stock_terpakai')->default(0); // Yang sudah digunakan

            $table->enum('isactive', [0, 1])->default(1);
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();
            $table->string('user_create')->nullable();
            $table->string('user_update')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('master_paket_pinjaman');
    }
};
