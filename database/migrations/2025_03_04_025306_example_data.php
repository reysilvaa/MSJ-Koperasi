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
        Schema::create('example_data', function (Blueprint $table) {
            $table->string('idobat', 6);
            $table->char('jenis', 2);
            $table->string('nama', 100);
            $table->string('kemasan', 50);
            $table->float('harga', 10, 2);
            $table->string('image')->default('noimage.png');
            $table->date('expired')->useCurrent();
            $table->integer('min_stock')->default(0);
            $table->integer('stock')->default(0);
            $table->char('rules', 20)->nullable();
            $table->enum('isactive', [0, 1])->default(1); //1=active,0=not active
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();
            $table->string('user_create')->nullable();
            $table->string('user_update')->nullable();
            $table->primary(['idobat']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('example_data');
    }
};
