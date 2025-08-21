<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class KoperasiDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Pastikan user 'anggota_koperasi' tersedia terlebih dahulu
        $anggotaUser = DB::table('users')->where('username', 'anggota_koperasi')->first();
        if (!$anggotaUser) {
            $anggotaUserId = DB::table('users')->insertGetId([
                'username'   => 'anggota_koperasi',
                'firstname'  => 'Anggota',
                'lastname'   => 'Koperasi',
                'email'      => 'anggota.koperasi@spunindo.com',
                'password'   => bcrypt('anggota123'),
                'idroles'    => 'anggot',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $anggotaUser = DB::table('users')->where('id', $anggotaUserId)->first();
        }

        // Sinkronisasi 1 user 1 mst_anggota (berdasarkan migration terbaru)
        $exists = DB::table('mst_anggota')->where('user_id', $anggotaUser->id)->exists();
        if (!$exists) {
            DB::table('mst_anggota')->insert([
                'nik'                     => '3515123456789012',
                'user_id'                 => $anggotaUser->id,
                'nama_lengkap'            => 'Anggota Koperasi',
                'jenis_kelamin'           => 'L',
                'no_telp'                 => '081234567890',
                'alamat'                  => 'Jl. Contoh No. 123, Sidoarjo, Jawa Timur',
                'departemen'              => 'IT',
                'jabatan'                 => 'Staff',
                'tanggal_bergabung'       => '2024-01-01',
                'no_rekening'             => '1234567890',
                'nama_bank'               => 'BRI',
                'nama_pemilik_rekening'   => 'Anggota Koperasi',
                'foto_ktp'                => null,
                'isactive'                => '1',
                'created_at'              => now(),
                'updated_at'              => now(),
                'user_create'             => 'seeder',
                'user_update'             => 'seeder',
            ]);
        }

        // Buat akun pengguna lain jika belum ada (cek per-username untuk hindari duplikasi)
        $users = [
            [
                'username'  => 'admin_koperasi',
                'firstname' => 'Ketua Admin',
                'lastname'  => 'Koperasi',
                'email'     => 'admin.koperasi@spunindo.com',
                'password'  => 'admin123',
                'idroles'   => 'kadmin',
            ],
            [
                'username'  => 'admin_kredit',
                'firstname' => 'Admin Kredit',
                'lastname'  => 'Koperasi',
                'email'     => 'admin.kredit@spunindo.com',
                'password'  => 'kredit123',
                'idroles'   => 'akredt',
            ],
            [
                'username'  => 'admin_transfer',
                'firstname' => 'Admin Transfer',
                'lastname'  => 'Koperasi',
                'email'     => 'admin.transfer@spunindo.com',
                'password'  => 'transfer123',
                'idroles'   => 'atrans',
            ],
            [
                'username'  => 'ketua_umum',
                'firstname' => 'Ketua Umum',
                'lastname'  => 'Koperasi',
                'email'     => 'ketua.umum@spunindo.com',
                'password'  => 'ketua123',
                'idroles'   => 'ketuum',
            ],
        ];

        foreach ($users as $u) {
            $exists = DB::table('users')->where('username', $u['username'])->exists();
            if (!$exists) {
                DB::table('users')->insert([
                    'username'   => $u['username'],
                    'firstname'  => $u['firstname'],
                    'lastname'   => $u['lastname'],
                    'email'      => $u['email'],
                    'password'   => Hash::make($u['password']),
                    'idroles'    => $u['idroles'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        // Periode dapat di-seed terpisah jika dibutuhkan menggunakan mst_periode
        // DB::table('mst_periode')->insert([...])
    }
}
