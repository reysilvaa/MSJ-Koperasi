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
        Schema::table('sys_dmenu', function (Blueprint $table) {
            $table->string('where')->after('tabel')->nullable(); // Contoh menambahkan kolom string
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sys_dmenu', function (Blueprint $table) {
            $table->dropColumn('where');
        });
    }
};
