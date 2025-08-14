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
        if (DB::table('anggota')->where('nomor_anggota', 'A240001')->count() == 0) {
        // Insert data anggota untuk user dengan role anggota
        DB::table('anggota')->insert([
            'nomor_anggota' => 'A240001',
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
            'tanggal_aktif' => '2024-02-01',
            'status_keanggotaan' => 'aktif',
            'simpanan_pokok' => 500000.00,
            'simpanan_wajib_bulanan' => 100000.00,
            'total_simpanan_wajib' => 800000.00, // 8 bulan x 100rb
            'total_simpanan_sukarela' => 0.00,
            'no_rekening' => '1234567890',
            'nama_bank' => 'BRI',
            'foto' => null,
            'keterangan' => 'Anggota aktif dengan status baik',
            'isactive' => 1,
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
        DB::table('periode_pencairan')->insert([
            [
                'nama_periode' => 'Pencairan Agustus 2025 - Periode 1',
                'tanggal_mulai' => '2025-08-01',
                'tanggal_selesai' => '2025-08-15',
                'tanggal_pencairan' => '2025-08-10',
                'maksimal_aplikasi' => 50,
                'total_dana_tersedia' => 500000000.00, // 500 juta
                'total_dana_terpakai' => 0.00,
                'keterangan' => 'Periode pencairan pertama bulan Agustus 2025',
                'isactive' => 1,
                'created_at' => now(),
                'updated_at' => now(),
                'user_create' => 'seeder',
                'user_update' => 'seeder'
            ],
            [
                'nama_periode' => 'Pencairan Agustus 2025 - Periode 2',
                'tanggal_mulai' => '2025-08-16',
                'tanggal_selesai' => '2025-08-31',
                'tanggal_pencairan' => '2025-08-25',
                'maksimal_aplikasi' => 30,
                'total_dana_tersedia' => 300000000.00, // 300 juta
                'total_dana_terpakai' => 0.00,
                'keterangan' => 'Periode pencairan kedua bulan Agustus 2025',
                'isactive' => 1,
                'created_at' => now(),
                'updated_at' => now(),
                'user_create' => 'seeder',
                'user_update' => 'seeder'
            ]
        ]);
        }
    }
}
