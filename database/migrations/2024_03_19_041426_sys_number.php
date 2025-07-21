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
        Schema::create('sys_number', function (Blueprint $table) {
            $table->char('periode', 4);
            $table->char('tipe', 3);
            $table->char('lastid', 10);
            $table->char('lastx', 3);
            $table->enum('isactive', [0, 1])->default(1); //1=active,0=not active
            $table->primary(['periode', 'tipe']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sys_number');
    }
};
