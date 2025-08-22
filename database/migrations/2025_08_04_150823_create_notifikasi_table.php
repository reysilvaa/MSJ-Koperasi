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
        Schema::create('notifikasi', function (Blueprint $table) {
            $table->id();
            $table->foreignId('anggota_id')->constrained('anggota');
            $table->string('judul', 100);
            $table->text('pesan');
            $table->enum('jenis', ['info', 'warning', 'success', 'error']);
            $table->enum('kategori', ['pinjaman', 'iuran', 'shu', 'sistem', 'reminder']);
            $table->enum('status', ['unread', 'read'])->default('unread');
            $table->datetime('tanggal_baca')->nullable();
            $table->string('link_action', 255)->nullable();
            $table->json('data_tambahan')->nullable();
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
        Schema::dropIfExists('notifikasi');
    }
};
