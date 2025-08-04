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
        Schema::create('transfer_dana', function (Blueprint $table) {
            $table->id();
            $table->string('nomor_transfer', 20)->unique();
            $table->foreignId('anggota_pengirim_id')->constrained('anggota');
            $table->foreignId('anggota_penerima_id')->constrained('anggota');
            $table->decimal('nominal', 15, 2);
            $table->decimal('biaya_transfer', 15, 2)->default(0);
            $table->enum('jenis_transfer', ['simpanan', 'cash', 'cicilan']);
            $table->date('tanggal_transfer');
            $table->enum('status', ['pending', 'berhasil', 'gagal', 'dibatalkan'])->default('pending');
            $table->text('keterangan')->nullable();
            $table->string('approved_by')->nullable();
            $table->datetime('tanggal_approval')->nullable();
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
        Schema::dropIfExists('transfer_dana');
    }
};
