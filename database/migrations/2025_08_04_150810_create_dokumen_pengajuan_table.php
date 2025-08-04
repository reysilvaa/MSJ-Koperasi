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
        Schema::create('dokumen_pengajuan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pengajuan_pinjaman_id')->constrained('pengajuan_pinjaman');
            $table->string('nama_dokumen', 100);
            $table->string('jenis_dokumen', 50);
            $table->string('file_path', 255);
            $table->string('file_name', 100);
            $table->string('file_extension', 10);
            $table->bigInteger('file_size');
            $table->enum('status_verifikasi', ['pending', 'verified', 'rejected'])->default('pending');
            $table->string('verified_by')->nullable();
            $table->datetime('tanggal_verifikasi')->nullable();
            $table->text('catatan_verifikasi')->nullable();
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
        Schema::dropIfExists('dokumen_pengajuan');
    }
};
