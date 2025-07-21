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
        Schema::create('example_data_by_rule', function (Blueprint $table) {
            $table->id();
            $table->string('nama', 100);
            $table->longText('rules')->nullable(); //kolom wajib jika flag rules aktif di authorize
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
        Schema::dropIfExists('example_data_by_rule');
    }
};
