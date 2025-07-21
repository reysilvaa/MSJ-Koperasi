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
        Schema::create('sys_dmenu', function (Blueprint $table) {
            $table->char('dmenu', 6);
            $table->char('gmenu', 6);
            $table->integer('urut');
            $table->string('name', 25)->nullable();
            $table->string('icon', 50)->nullable();
            $table->string('url', 50)->nullable();
            $table->string('tabel', 50)->nullable();
            $table->char('layout', 6)->default('master'); //set template on gmenu
            $table->char('sub', 6)->nullable(); //set submenu
            $table->enum('show', [0, 1])->default(1); //1=active,0=not active
            $table->enum('js', [0, 1])->default(0); //1=active,0=not active
            $table->enum('isactive', [0, 1])->default(1); //1=active,0=not active
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();
            $table->string('user_create')->nullable();
            $table->string('user_update')->nullable();
            $table->foreign('gmenu')->references('gmenu')->on('sys_gmenu');
            $table->primary(['dmenu']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sys_dmenu');
    }
};
