<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('mst_periode', function (Blueprint $table) {
            $table->string('id', 255)->primary();
            $table->year('tahun');
            $table->tinyInteger('bulan')->unsigned();
            $table->enum('isactive', ['0', '1'])->default('1');
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();
            $table->string('user_create', 255)->nullable();
            $table->string('user_update', 255)->nullable();

            // Unique constraint untuk tahun dan bulan
            $table->unique(['tahun', 'bulan'], 'unique_periode');
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mst_periode');
    }
};
