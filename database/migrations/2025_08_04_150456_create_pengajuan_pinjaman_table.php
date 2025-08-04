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
            $table->foreignId('anggota_id')->constrained('anggota');
            $table->foreignId('master_paket_pinjaman_id')->constrained('master_paket_pinjaman');
            $table->foreignId('master_tenor_id')->constrained('master_tenor');
            $table->decimal('nominal_pengajuan', 15, 2);
            $table->integer('tenor_bulan');
            $table->text('tujuan_pinjaman');
            $table->enum('status', ['pending', 'review', 'approved', 'rejected', 'cancelled'])->default('pending');
            $table->decimal('nominal_disetujui', 15, 2)->nullable();
            $table->text('catatan_pengajuan')->nullable();
            $table->date('tanggal_pengajuan');
            $table->date('tanggal_review')->nullable();
            $table->date('tanggal_approval')->nullable();
            $table->string('reviewed_by')->nullable();
            $table->string('approved_by')->nullable();
            $table->text('alasan_penolakan')->nullable();
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
        Schema::dropIfExists('pengajuan_pinjaman');
    }
};
