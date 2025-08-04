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
            $table->string('kode_paket', 10)->unique();
            $table->string('nama_paket', 100);
            $table->text('deskripsi')->nullable();
            $table->decimal('limit_minimum', 15, 2);
            $table->decimal('limit_maksimum', 15, 2);
            $table->decimal('bunga_per_tahun', 5, 2);
            $table->integer('tenor_minimum');
            $table->integer('tenor_maksimum');
            $table->decimal('biaya_admin', 15, 2)->default(0);
            $table->decimal('denda_per_hari', 15, 2)->default(0);
            $table->enum('status', ['aktif', 'non_aktif'])->default('aktif');
            $table->json('syarat_pengajuan')->nullable();
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
