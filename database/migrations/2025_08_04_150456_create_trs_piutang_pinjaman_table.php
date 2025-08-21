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
        Schema::create('trs_piutang', function (Blueprint $table) {
            $table->string('nomor_pinjaman', 255)->primary();
            $table->string('nik', 255);
            $table->enum('status_approval', ['approve', 'pending', 'rejected'])->default('pending');
            $table->enum('level_approval', ['1', '2', '3', '0'])->default('0');
            $table->string('mst_paket_id', 255);
            $table->string('tenor_pinjaman', 50);
            $table->integer('jumlah_paket_dipilih');
            $table->decimal('nominal_pinjaman', 15, 2);
            $table->decimal('bunga_pinjaman', 15, 2);
            $table->decimal('total_pinjaman', 15, 2);
            $table->text('tujuan_pinjaman');
            $table->enum('jenis_pengajuan', ['baru', 'top_up'])->default('baru');
            $table->text('catatan_approval')->nullable();
            $table->dateTime('tanggal_pengajuan')->useCurrent();
            $table->dateTime('tanggal_approval')->nullable();
            $table->string('mst_periode_id', 255)->nullable();
            $table->enum('isactive', ['0', '1'])->default('1');
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();
            $table->string('user_create', 255)->nullable();
            $table->string('user_update', 255)->nullable();
            
            // Foreign key constraints
            $table->foreign('nik')->references('nik')->on('mst_anggota')->onUpdate('cascade')->onDelete('restrict');
            $table->foreign('mst_paket_id')->references('id')->on('mst_paket')->onUpdate('cascade')->onDelete('restrict');
            $table->foreign('mst_periode_id')->references('id')->on('mst_periode')->onUpdate('cascade')->onDelete('set null');
            
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('trs_piutang');
    }
};
