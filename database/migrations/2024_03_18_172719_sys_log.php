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
        Schema::create('sys_log', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->date('date')->default(now());
            $table->string('username', 20);
            $table->char('tipe', 1);
            $table->string('dmenu');
            $table->string('description');
            $table->enum('status', [0, 1])->nullable(); //0=gagal,1=sukses
            $table->string('ipaddress');
            $table->string('useragent');
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sys_log');
    }
};
