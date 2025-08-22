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
        Schema::create('trs_shu', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('periode', 255);
            $table->string('nik', 255);
            $table->decimal('simpanan_total', 15, 2)->default(0);
            $table->decimal('bunga_total', 15, 2)->default(0);
            $table->decimal('hasil_persen_simpanan', 5, 2)->default(40.00);
            $table->decimal('hasil_persen_bunga', 5, 2)->default(35.00);
            $table->decimal('total_shu', 15, 2);
            $table->enum('isactive', ['0', '1'])->default('1');
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();
            $table->string('user_create', 255)->nullable();
            $table->string('user_update', 255)->nullable();
            
            // Unique constraint
            $table->unique(['periode', 'nik'], 'unique_shu');
            
            // Foreign key constraint
            $table->foreign('nik')->references('nik')->on('mst_anggota')->onUpdate('cascade')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('trs_shu');
    }
};
