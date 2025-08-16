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
        Schema::create('anggota', function (Blueprint $table) {
            $table->id();
            $table->string('nomor_anggota', 20)->unique();
            $table->string('nik', 16)->unique();
            $table->string('nama_lengkap', 100);
            $table->string('email', 100)->unique();
            $table->string('no_hp', 15);
            $table->enum('jenis_kelamin', ['L', 'P']);
            $table->date('tanggal_lahir');
            $table->text('alamat');
            $table->string('jabatan', 50);
            $table->string('departemen', 50);
            $table->decimal('gaji_pokok', 15, 2);
            $table->date('tanggal_bergabung');
            $table->date('tanggal_aktif')->nullable();
            $table->decimal('simpanan_pokok', 15, 2)->default(50000.00);
            $table->decimal('simpanan_wajib_bulanan', 15, 2)->default(25000.00);
            $table->decimal('total_simpanan_wajib', 15, 2)->default(0);
            $table->decimal('total_simpanan_sukarela', 15, 2)->default(0);
            $table->string('no_rekening', 20)->nullable();
            $table->string('nama_bank', 50)->nullable();
            $table->string('foto')->nullable();
            $table->text('keterangan')->nullable();
            $table->enum('isactive', [0, 1])->default(0); 
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
        Schema::dropIfExists('anggota');
    }
};
