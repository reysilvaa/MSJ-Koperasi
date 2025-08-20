<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\MasterPaketPinjaman;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class KoperasiDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * Seeder ini untuk membuat data dasar koperasi (admin users dan paket pinjaman minimal)
     */
    public function run(): void
    {
        $this->command->info('🏢 Creating Basic Koperasi Data...');

        // Cek apakah admin users sudah ada
        if (DB::table('users')->where('username', 'admin_koperasi')->count() == 0) {
            
            // Buat satu anggota contoh menggunakan factory
            $this->command->info('   Creating sample anggota...');
            User::factory()
                ->anggotaAktif()
                ->create([
                    'username' => 'anggota_koperasi',
                    'firstname' => 'Anggota',
                    'lastname' => 'Koperasi',
                    'email' => 'anggota.koperasi@spunindo.com',
                    'password' => bcrypt('anggota123'),
                    'nomor_anggota' => 'A240001',
                    'nik' => '3515123456789012',
                    'nama_lengkap' => 'Anggota Koperasi',
                    'user_create' => 'seeder',
                    'user_update' => 'seeder'
                ]);

            // Buat admin users menggunakan factory
            $this->command->info('   Creating admin users...');
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

            foreach ($adminUsers as $userData) {
                User::factory()
                    ->admin()
                    ->create(array_merge($userData, [
                        'user_create' => 'seeder',
                        'user_update' => 'seeder'
                    ]));
            }

            // Buat master paket pinjaman menggunakan factory
            $this->command->info('   Creating master paket pinjaman...');
            MasterPaketPinjaman::factory()
                ->periode('2025-08')
                ->activeWithStock()
                ->create([
                    'stock_limit' => 100,
                    'stock_terpakai' => 0,
                    'user_create' => 'seeder',
                    'user_update' => 'seeder'
                ]);

            MasterPaketPinjaman::factory()
                ->periode('2025-09')
                ->activeWithStock()
                ->create([
                    'stock_limit' => 100,
                    'stock_terpakai' => 0,
                    'user_create' => 'seeder',
                    'user_update' => 'seeder'
                ]);

            $this->command->info('✅ Basic Koperasi Data Created Successfully!');
        } else {
            $this->command->info('⚠️  Basic Koperasi Data already exists, skipping...');
        }
    }
}
