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
        Schema::create('transaction_list', function (Blueprint $table) {
            $table->id();
            $table->string('idtrans', 50);
            $table->date('posting');
            $table->string('sloc', 4)->nullable();
            $table->integer('item')->nullable();
            $table->string('material', 100);
            $table->string('batch', 20);
            $table->float('length', 10, 2);
            $table->float('width', 10, 2);
            $table->float('gsm', 10, 2);
            $table->float('weight', 10, 2);
            $table->float('qty', 10, 2);
            $table->string('uom', 5);
            $table->string('color', 20);
            $table->enum('tipe', ['I', 'O'])->default('I'); //I=Input, O=Output
            $table->timestamp('created_at')->useCurrent();
            $table->string('user_create')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transaction_list');
    }
};
