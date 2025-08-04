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
        Schema::create('master_tenor', function (Blueprint $table) {
            $table->id();
            $table->integer('tenor_bulan')->unique();
            $table->string('nama_tenor', 50);
            $table->text('deskripsi')->nullable();
            $table->decimal('bunga_tambahan', 5, 2)->default(0);
            $table->enum('status', ['aktif', 'non_aktif'])->default('aktif');
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
        Schema::dropIfExists('master_tenor');
    }
};
