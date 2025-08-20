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
        // Tabel iuran anggota
        // Penyesuaian: gunakan bigIncrements dan foreignId untuk kompatibilitas dengan users.id (unsigned BIGINT)
        Schema::create('iuran', function (Blueprint $table) {
            $table->bigIncrements('id_iuran');
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->enum('jenis_iuran', ['wajib', 'pokok']);
            $table->decimal('iuran', 10, 2);
            $table->unsignedTinyInteger('bulan'); // 1-12
            $table->unsignedSmallInteger('tahun');
            $table->enum('isactive', [0, 1])->default(1);
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();
            $table->string('user_create')->nullable();
            $table->string('user_update')->nullable();
            $table->index(['user_id', 'tahun', 'bulan'], 'idx_iuran_user_tahun_bulan');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('iuran');
    }
};
