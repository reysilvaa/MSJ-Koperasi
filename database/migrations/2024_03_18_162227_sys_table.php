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
        Schema::create('sys_table', function (Blueprint $table) {
            $table->char('gmenu', 6);
            $table->char('dmenu', 6);
            $table->integer('urut');
            $table->string('field', 25)->nullable();
            $table->string('alias', 50)->nullable();
            $table->string('type', 50)->nullable();
            $table->bigInteger('length')->nullable();
            $table->enum('decimals', [0, 1, 2, 3])->default(0);
            $table->string('default', 20)->nullable();
            $table->string('validate', 100)->nullable();
            $table->enum('primary', [0, 1, 2])->default(0); //1=active,0=not active
            $table->string('generateid', 25)->nullable(); //generate id
            $table->enum('filter', [0, 1])->default(0); //1=active,0=not active
            $table->enum('list', [0, 1])->default(1); //1=active,0=not active
            $table->enum('show', [0, 1])->default(1); //1=active,0=not active
            $table->longText('query')->nullable(); //query report
            $table->string('class', 255)->nullable();
            $table->string('sub', 255)->nullable();
            $table->string('link', 50)->nullable();
            $table->string('note', 255)->nullable();
            $table->enum('position', [0, 1, 2, 3, 4])->default(0); //0=standard,1=header,2=detail,3=left,4=right
            $table->enum('isactive', [0, 1])->default(1); //1=active,0=not active
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();
            $table->string('user_create')->nullable();
            $table->string('user_update')->nullable();
            $table->foreign('gmenu')->references('gmenu')->on('sys_gmenu');
            $table->foreign('dmenu')->references('dmenu')->on('sys_dmenu');
            $table->primary(['gmenu', 'dmenu', 'urut']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sys_table');
    }
};
