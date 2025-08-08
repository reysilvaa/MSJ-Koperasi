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
        Schema::create('pengajuan_pinjaman', function (Blueprint $table) {
            $table->id();
            $table->string('nomor_pengajuan', 20)->unique();

            // FOREIGN KEY REFERENCES
            $table->foreignId('anggota_id')->constrained('anggota');
            $table->unsignedBigInteger('paket_pinjaman_id');
            $table->unsignedBigInteger('tenor_id');

            // DETAIL PENGAJUAN
            $table->decimal('jumlah_pinjaman', 15, 2);
            $table->decimal('bunga_per_bulan', 5, 2); // dari master_paket_pinjaman
            $table->decimal('cicilan_per_bulan', 15, 2); // hitung otomatis
            $table->decimal('total_pembayaran', 15, 2); // hitung otomatis
            $table->text('tujuan_pinjaman');

            // KHUSUS TOP UP
            $table->unsignedBigInteger('pinjaman_asal_id')->nullable(); // untuk TOP UP
            $table->decimal('sisa_cicilan_lama', 15, 2)->nullable(); // untuk TOP UP
            $table->enum('jenis_pengajuan', ['baru', 'top_up'])->default('baru');

            // STATUS & APPROVAL (sesuai activity diagram)
            $table->enum('status_pengajuan', ['draft', 'diajukan', 'review_admin', 'review_panitia', 'review_ketua', 'disetujui', 'ditolak', 'dibatalkan'])->default('draft');
            $table->text('catatan_pengajuan')->nullable();
            $table->text('catatan_approval')->nullable();
            $table->dateTime('tanggal_pengajuan')->nullable();
            $table->dateTime('tanggal_approval')->nullable();
            $table->string('approved_by')->nullable();

            // PERIODE PENCAIRAN (sesuai business rule)
            $table->unsignedBigInteger('periode_pencairan_id')->nullable();
            $table->enum('status_pencairan', ['belum_cair', 'dalam_proses', 'sudah_cair'])->default('belum_cair');

            $table->enum('isactive', [0, 1])->default(1);
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();
            $table->string('user_create')->nullable();
            $table->string('user_update')->nullable();

            // FOREIGN KEY CONSTRAINTS
            $table->foreign('paket_pinjaman_id')->references('id')->on('master_paket_pinjaman')->onDelete('cascade');
            $table->foreign('tenor_id')->references('id')->on('master_tenor')->onDelete('cascade');
            $table->foreign('pinjaman_asal_id')->references('id')->on('pengajuan_pinjaman')->onDelete('cascade');
            $table->foreign('periode_pencairan_id')->references('id')->on('periode_pencairan')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pengajuan_pinjaman');
    }
};
