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
        Schema::create('shu', function (Blueprint $table) {
            $table->id();
            $table->year('tahun');
            $table->decimal('total_pendapatan', 15, 2);
            $table->decimal('total_beban', 15, 2);
            $table->decimal('laba_bersih', 15, 2);
            $table->decimal('cadangan_wajib', 15, 2);
            $table->decimal('cadangan_umum', 15, 2);
            $table->decimal('shu_anggota', 15, 2);
            $table->decimal('persentase_simpanan', 5, 2);
            $table->decimal('persentase_transaksi', 5, 2);
            $table->enum('status', ['draft', 'approved', 'distributed'])->default('draft');
            $table->date('tanggal_rapat');
            $table->date('tanggal_distribusi')->nullable();
            $table->text('keterangan')->nullable();
            $table->string('approved_by')->nullable();
            $table->enum('isactive', [0, 1])->default(1);
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();
            $table->string('user_create')->nullable();
            $table->string('user_update')->nullable();

            $table->unique('tahun');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shu');
    }
};
