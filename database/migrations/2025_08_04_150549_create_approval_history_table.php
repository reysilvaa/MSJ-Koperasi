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
        Schema::create('approval_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pengajuan_pinjaman_id')->constrained('pengajuan_pinjaman');
            $table->string('level_approval', 20);
            $table->enum('status_approval', ['pending', 'approved', 'rejected']);
            $table->text('catatan')->nullable();
            $table->datetime('tanggal_approval')->nullable();
            $table->integer('urutan');
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
        Schema::dropIfExists('approval_history');
    }
};
