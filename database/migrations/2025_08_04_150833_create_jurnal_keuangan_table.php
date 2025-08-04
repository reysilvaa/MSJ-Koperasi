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
        Schema::create('jurnal_keuangan', function (Blueprint $table) {
            $table->id();
            $table->string('nomor_jurnal', 20)->unique();
            $table->date('tanggal');
            $table->string('akun_kode', 10);
            $table->string('akun_nama', 100);
            $table->enum('jenis_akun', ['aset', 'kewajiban', 'modal', 'pendapatan', 'beban']);
            $table->decimal('debit', 15, 2)->default(0);
            $table->decimal('kredit', 15, 2)->default(0);
            $table->string('referensi_tabel', 50)->nullable();
            $table->bigInteger('referensi_id')->nullable();
            $table->text('deskripsi');
            $table->string('created_by', 50);
            $table->enum('status', ['draft', 'posted', 'reversed'])->default('draft');
            $table->datetime('tanggal_posting')->nullable();
            $table->string('posted_by')->nullable();
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
        Schema::dropIfExists('jurnal_keuangan');
    }
};
