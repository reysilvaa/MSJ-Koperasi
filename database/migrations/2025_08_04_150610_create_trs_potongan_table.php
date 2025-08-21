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
        Schema::create('trs_potongan', function (Blueprint $table) {
            $table->string('periode', 255);
            $table->string('nik', 255);
            $table->decimal('simpanan', 15, 2)->default(0);
            $table->decimal('cicilan_pinjaman', 15, 2)->default(0);
            $table->integer('potongan_ke')->default(1);
            $table->decimal('total_potongan', 15, 2);
            $table->text('keterangan')->nullable();
            $table->enum('isactive', ['0', '1'])->default('1');
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();
            $table->string('user_create', 255)->nullable();
            $table->string('user_update', 255)->nullable();
            
            // Composite primary key
            $table->primary(['periode', 'nik']);

            // Unique constraint
            $table->unique(['periode', 'nik', 'potongan_ke'], 'unique_potongan');
            
            // Foreign key constraint
            $table->foreign('nik')->references('nik')->on('mst_anggota')->onUpdate('cascade')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('trs_potongan');
    }
};
