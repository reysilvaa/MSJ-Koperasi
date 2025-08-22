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
        Schema::create('trs_cicilan', function (Blueprint $table) {
            $table->string('nomor_pinjaman', 255);
            $table->string('nik', 255);
            $table->string('periode', 255);
            $table->integer('angsuran_ke');
            $table->date('tanggal_jatuh_tempo');
            $table->decimal('nominal_pokok', 15, 2);
            $table->decimal('bunga_rp', 15, 2);
            $table->decimal('total_angsuran', 15, 2);
            $table->dateTime('tanggal_bayar')->nullable();
            $table->enum('isbayar', ['0', '1'])->default('0');
            $table->decimal('total_bayar', 15, 2)->default(0);
            $table->enum('isactive', ['0', '1'])->default('1');
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();
            $table->string('user_create', 255)->nullable();
            $table->string('user_update', 255)->nullable();

            // Composite Primary Key
            $table->primary(['nomor_pinjaman', 'nik', 'periode']);

            // Unique constraint
            $table->unique(['nomor_pinjaman', 'angsuran_ke'], 'unique_cicilan');
            
            // Foreign key constraints
            $table->foreign('nomor_pinjaman')->references('nomor_pinjaman')->on('trs_piutang')->onUpdate('cascade')->onDelete('cascade');
            $table->foreign('nik')->references('nik')->on('mst_anggota')->onUpdate('cascade')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('trs_cicilan');
    }
};
