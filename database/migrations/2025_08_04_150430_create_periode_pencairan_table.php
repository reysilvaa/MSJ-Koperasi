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
        Schema::create('periode_pencairan', function (Blueprint $table) {
            $table->id();
            $table->string('nama_periode', 100); // "Pencairan Januari 2025"
            $table->date('tanggal_mulai');
            $table->date('tanggal_selesai');
            $table->date('tanggal_pencairan');
            $table->integer('maksimal_aplikasi')->default(0); // 0 = unlimited
            $table->decimal('total_dana_tersedia', 15, 2)->default(0);
            $table->decimal('total_dana_terpakai', 15, 2)->default(0);
            $table->enum('status', ['draft', 'aktif', 'selesai', 'ditutup'])->default('draft');
            $table->text('keterangan')->nullable();
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
        Schema::dropIfExists('periode_pencairan');
    }
};
