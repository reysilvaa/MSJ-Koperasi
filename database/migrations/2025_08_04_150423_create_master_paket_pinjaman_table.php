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

            // SISTEM PAKET: 1 paket = Rp 500.000
            $table->integer('jumlah_paket'); // 5, 10, 20, 40
            $table->decimal('nilai_per_paket', 15, 2)->default(500000); // Rp 500.000
            $table->decimal('limit_minimum', 15, 2); // jumlah_paket * 500000
            $table->decimal('limit_maksimum', 15, 2); // jumlah_paket * 500000

            // BUNGA FLAT 1% PER BULAN
            $table->decimal('bunga_per_bulan', 5, 2)->default(1.00); // 1% per bulan

            // TENOR YANG DIIZINKAN (JSON array of tenor IDs)
            $table->json('tenor_diizinkan'); // [6,10,12] atau [1,2,3]

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
