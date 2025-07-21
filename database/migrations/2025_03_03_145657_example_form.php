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
        Schema::create('example_form', function (Blueprint $table) {
            $table->id();
            $table->string('upper', 20)->nullable();
            $table->string('lower', 20)->nullable();
            $table->string('notspace', 20)->nullable();
            $table->string('readonly', 20)->nullable();
            $table->string('char', 20)->nullable();
            $table->float('currency', 10, 2)->nullable();
            $table->date('date')->nullable();
            $table->string('email')->nullable();
            $table->string('enum')->nullable();
            $table->string('file')->nullable();
            $table->string('image')->default('noimage.png');
            $table->integer('number')->nullable();
            $table->string('password')->nullable();
            $table->string('search')->nullable();
            $table->string('string')->nullable();
            $table->text('text')->nullable();
            $table->string('customs')->nullable();
            $table->string('multiples')->nullable();
            // komponen tabel wajib ada
            $table->enum('isactive', [0, 1])->default(1); //1=active,0=not active
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
        Schema::dropIfExists('example_form');
    }
};
