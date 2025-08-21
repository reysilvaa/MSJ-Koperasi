<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class KoperasiDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Cek apakah data anggota sudah ada
        if (DB::table('anggota')->where('nik', '3515123456789012')->count() == 0) {
        // Insert data anggota untuk user dengan role anggota
        DB::table('anggota')->insert([
            'nik' => '3515123456789012',
            'nama_lengkap' => 'Anggota Koperasi',
            'email' => 'anggota.koperasi@spunindo.com',
            'no_hp' => '081234567890',
            'jenis_kelamin' => 'L',
            'tanggal_lahir' => '1990-01-01',
            'alamat' => 'Jl. Contoh No. 123, Sidoarjo, Jawa Timur',
            'jabatan' => 'Staff',
            'departemen' => 'IT',
            'gaji_pokok' => 8000000.00,
            'tanggal_bergabung' => '2024-01-01',
            'no_rekening' => '1234567890',
            'nama_bank' => 'BRI',
            'foto_ktp' => null,
            'keterangan' => 'Anggota aktif dengan status baik',
            'isactive' => 1, // Sample data - already activated
            'created_at' => now(),
            'updated_at' => now(),
            'user_create' => 'seeder',
            'user_update' => 'seeder'
        ]);
        }

        // Cek apakah user koperasi sudah ada
        if (DB::table('users')->where('username', 'admin_koperasi')->count() == 0) {
        // Insert Users untuk setiap role
        DB::table('users')->insert([
            [
                'username' => 'anggota_koperasi',
                'firstname' => 'Anggota',
                'lastname' => 'Koperasi',
                'email' => 'anggota.koperasi@spunindo.com',
                'password' => bcrypt('anggota123'),
                'idroles' => 'anggot'
            ],
            [
                'username' => 'admin_koperasi',
                'firstname' => 'Ketua Admin',
                'lastname' => 'Koperasi',
                'email' => 'admin.koperasi@spunindo.com',
                'password' => bcrypt('admin123'),
                'idroles' => 'kadmin'
            ],
            [
                'username' => 'admin_kredit',
                'firstname' => 'Admin Kredit',
                'lastname' => 'Koperasi',
                'email' => 'admin.kredit@spunindo.com',
                'password' => bcrypt('kredit123'),
                'idroles' => 'akredt'
            ],
            [
                'username' => 'admin_transfer',
                'firstname' => 'Admin Transfer',
                'lastname' => 'Koperasi',
                'email' => 'admin.transfer@spunindo.com',
                'password' => bcrypt('transfer123'),
                'idroles' => 'atrans'
            ],
            [
                'username' => 'ketua_umum',
                'firstname' => 'Ketua Umum',
                'lastname' => 'Koperasi',
                'email' => 'ketua.umum@spunindo.com',
                'password' => bcrypt('ketua123'),
                'idroles' => 'ketuum'
            ]
        ]);

        // ===== PERIODE PENCAIRAN =====
        // DB::table('periode_pencairan')->insert([
        //     [
        //         'tahun' => 2025,
        //         'bulan' => 8,
        //         'isactive' => 1,
        //         'created_at' => now(),
        //         'updated_at' => now(),
        //         'user_create' => 'seeder',
        //         'user_update' => 'seeder'
        //     ],
        //     [
        //         'tahun' => 2025,
        //         'bulan' => 9,
        //         'isactive' => 1,
        //         'created_at' => now(),
        //         'updated_at' => now(),
        //         'user_create' => 'seeder',
        //         'user_update' => 'seeder'
        //     ]
        // ]);
        }
    }
}
