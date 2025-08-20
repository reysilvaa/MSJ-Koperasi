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
        // Cek apakah user koperasi sudah ada
        if (DB::table('users')->where('username', 'admin_koperasi')->count() == 0) {
            
            // Insert User Anggota dengan data lengkap
            DB::table('users')->insert([
                'username' => 'anggota_koperasi',
                'firstname' => 'Anggota',
                'lastname' => 'Koperasi',
                'email' => 'anggota.koperasi@spunindo.com',
                'password' => bcrypt('anggota123'),
                'idroles' => 'anggot',
                // Data anggota terintegrasi
                'nomor_anggota' => 'A240001',
                'nik' => '3515123456789012',
                'nama_lengkap' => 'Anggota Koperasi',
                'no_hp' => '081234567890',
                'jenis_kelamin' => 'L',
                'tanggal_lahir' => '1990-01-01',
                'alamat' => 'Jl. Contoh No. 123, Sidoarjo, Jawa Timur',
                'jabatan' => 'Staff',
                'departemen' => 'IT',
                'gaji_pokok' => 8000000.00,
                'tanggal_bergabung' => '2024-01-01',
                'tanggal_aktif' => '2024-02-01',
                'simpanan_pokok' => 50000.00,
                'simpanan_wajib_bulanan' => 25000.00,
                'total_simpanan_wajib' => 200000.00, // 8 bulan x 25rb
                'total_simpanan_sukarela' => 0.00,
                'no_rekening' => '1234567890',
                'nama_bank' => 'BRI',
                'foto_ktp' => null,
                'keterangan' => 'Anggota aktif dengan status baik',
                'isactive' => 1,
                'created_at' => now(),
                'updated_at' => now(),
                'user_create' => 'seeder',
                'user_update' => 'seeder'
            ]);

            // Insert Admin Users (tanpa data anggota)
            $adminUsers = [
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
            ];

            foreach ($adminUsers as $user) {
                DB::table('users')->insert(array_merge($user, [
                    'isactive' => 1,
                    'created_at' => now(),
                    'updated_at' => now(),
                    'user_create' => 'seeder',
                    'user_update' => 'seeder'
                ]));
            }

            // ===== MASTER PAKET PINJAMAN =====
            DB::table('master_paket_pinjaman')->insert([
                [
                    'periode' => '2025-08',
                    'stock_limit' => 100,
                    'stock_terpakai' => 0,
                    'isactive' => 1,
                    'created_at' => now(),
                    'updated_at' => now(),
                    'user_create' => 'seeder',
                    'user_update' => 'seeder'
                ],
                [
                    'periode' => '2025-09',
                    'stock_limit' => 100,
                    'stock_terpakai' => 0,
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
