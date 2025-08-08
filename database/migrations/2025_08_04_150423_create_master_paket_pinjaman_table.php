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

            // NILAI PAKET FIXED: 1 paket = Rp 500.000 (tidak perlu field)
            // JUMLAH PAKET: Sudah diwakili stock_limit_bulanan

            // BUNGA FLAT 1% PER BULAN
            $table->decimal('bunga_per_bulan', 5, 2)->default(1.00); // 1% per bulan

            // TENOR YANG DIIZINKAN (JSON array of tenor IDs)
            $table->json('tenor_diizinkan'); // [6,10,12] atau [1,2,3]

            // STOCK MANAGEMENT (sesuai Activity Diagram 07)
            $table->integer('stock_limit_bulanan')->default(50); // Limit stock per bulan
            $table->integer('stock_terpakai')->default(0); // Stock yang sudah digunakan bulan ini
            $table->integer('stock_reserved')->default(0); // Stock yang direserve untuk pending
            $table->integer('stock_tersedia')->default(50); // Auto calculated: limit - terpakai - reserved
            $table->integer('alert_threshold')->default(10); // Alert jika stock <= threshold
            $table->date('reset_terakhir')->nullable(); // Tanggal terakhir reset stock bulanan

            // Hapus syarat_pengajuan karena terlalu kompleks untuk JSON
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
