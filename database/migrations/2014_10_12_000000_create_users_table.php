<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('username', 20)->unique();
            $table->string('firstname')->nullable();
            $table->string('lastname')->nullable();
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->string('address')->nullable();
            $table->string('city')->nullable();
            $table->string('country')->nullable();
            $table->string('postal')->nullable();
            $table->text('about')->nullable();
            $table->longText('idroles')->nullable();
            $table->string('image')->default('noimage.png');

            // Kolom dari tabel anggota yang diintegrasikan
            $table->string('nomor_anggota', 20)->nullable()->unique();
            $table->string('nik', 16)->nullable()->unique();
            $table->string('nama_lengkap', 100)->nullable();
            $table->string('no_hp', 15)->nullable();
            $table->enum('jenis_kelamin', ['L', 'P'])->nullable();
            $table->date('tanggal_lahir')->nullable();
            $table->text('alamat')->nullable();
            $table->string('jabatan', 50)->nullable();
            $table->string('departemen', 50)->nullable();
            $table->decimal('gaji_pokok', 15, 2)->nullable();
            $table->date('tanggal_bergabung')->nullable();
            $table->date('tanggal_aktif')->nullable();
            $table->string('no_rekening', 20)->nullable();
            $table->string('nama_bank', 50)->nullable();
            $table->string('foto_ktp')->nullable();
            $table->text('keterangan')->nullable();
            $table->enum('isactive', [0, 1])->default(1);
            $table->rememberToken();
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();
            $table->string('user_create')->nullable();
            $table->string('user_update')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('users');
    }
};
