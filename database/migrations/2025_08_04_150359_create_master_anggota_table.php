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
        Schema::create('mst_anggota', function (Blueprint $table) {
            $table->string('nik', 255)->unique();
            $table->foreignId('user_id')->unique()->constrained('users')->cascadeOnDelete();
            $table->string('nama_lengkap', 255)->nullable();
            $table->enum('jenis_kelamin', ['L', 'P'])->nullable();
            $table->string('no_telp', 255)->nullable();
            $table->string('alamat', 255)->nullable();
            $table->string('departemen', 255)->nullable();
            $table->string('jabatan', 255)->nullable();
            $table->date('tanggal_bergabung')->nullable();
            $table->string('no_rekening', 255)->nullable();
            $table->string('nama_bank', 255)->nullable();
            $table->string('nama_pemilik_rekening', 255)->nullable();
            $table->string('foto_ktp', 255)->nullable();
            $table->enum('isactive', [0, 1])->default(1);
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();
            $table->string('user_create', 255)->nullable();
            $table->string('user_update', 255)->nullable();
            
            // Index untuk optimasi
            $table->index('isactive', 'idx_mst_anggota_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mst_anggota');
    }
};
