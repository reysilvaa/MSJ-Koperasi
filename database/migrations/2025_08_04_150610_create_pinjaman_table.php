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
        Schema::create('pinjaman', function (Blueprint $table) {
            $table->id();
            $table->string('nomor_pinjaman', 20)->unique();
            $table->foreignId('pengajuan_pinjaman_id')->constrained('pengajuan_pinjaman');
            $table->string('user_id', 20); // Relasi ke users.username
            $table->decimal('nominal_pinjaman', 15, 2);
            $table->decimal('bunga_per_bulan', 5, 2);
            $table->integer('tenor_bulan');
            $table->decimal('angsuran_pokok', 15, 2);
            $table->decimal('angsuran_bunga', 15, 2);
            $table->decimal('total_angsuran', 15, 2);
            $table->date('tanggal_pencairan');
            $table->date('tanggal_jatuh_tempo');
            $table->date('tanggal_angsuran_pertama');
            $table->enum('status', ['aktif', 'lunas', 'bermasalah', 'hapus_buku'])->default('aktif');
            $table->decimal('sisa_pokok', 15, 2);
            $table->decimal('total_dibayar', 15, 2)->default(0);
            $table->integer('angsuran_ke')->default(0);
            $table->text('keterangan')->nullable();
            $table->enum('isactive', [0, 1])->default(1);
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();
            $table->string('user_create')->nullable();
            $table->string('user_update')->nullable();
            
            // FOREIGN KEY CONSTRAINTS
            $table->foreign('user_id')->references('username')->on('users')->onDelete('cascade');
            
            // Index untuk performa
            $table->index('user_id');
            $table->index('status');
            $table->index('tanggal_pencairan');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pinjaman');
    }
};
