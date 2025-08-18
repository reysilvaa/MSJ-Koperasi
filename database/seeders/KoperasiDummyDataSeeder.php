<?php

namespace Database\Seeders;

use App\Models\Anggotum;
use App\Models\CicilanPinjaman;
use App\Models\MasterPaketPinjaman;
use App\Models\PengajuanPinjaman;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class KoperasiDummyDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('🚀 Starting Koperasi Dummy Data Generation...');

        // 1. Create Master Paket Pinjaman (2024-2026)
        $this->createMasterPaketPinjaman();

        // 2. Create Anggota (50 members)
        $this->createAnggota();

        // 3. Create Pengajuan Pinjaman (30 applications)
        $this->createPengajuanPinjaman();

        // 4. Create Users for each role
        $this->createUsers();

        $this->command->info('✅ Koperasi Dummy Data Generation Completed!');
    }

    private function createMasterPaketPinjaman()
    {
        $this->command->info('📦 Creating Master Paket Pinjaman...');

        // Create paket for 2024-2026 (36 months)
        $periods = [];
        for ($year = 2024; $year <= 2026; $year++) {
            for ($month = 1; $month <= 12; $month++) {
                $periods[] = sprintf('%04d-%02d', $year, $month);
            }
        }

        foreach ($periods as $periode) {
            MasterPaketPinjaman::create([
                'periode' => $periode,
                'stock_limit' => fake()->numberBetween(50, 200),
                'stock_terpakai' => fake()->numberBetween(0, 30),
                'isactive' => '1',
                'user_create' => 'seeder',
                'user_update' => 'seeder'
            ]);
        }

        $this->command->info("   ✓ Created " . count($periods) . " paket pinjaman periods");
    }

    private function createAnggota()
    {
        $this->command->info('👥 Creating Anggota...');

        // Create 40 active members
        Anggotum::factory()
            ->count(40)
            ->active()
            ->create();

        // Create 10 inactive members
        Anggotum::factory()
            ->count(10)
            ->inactive()
            ->create();

        $this->command->info('   ✓ Created 50 anggota (40 active, 10 inactive)');
    }

    private function createPengajuanPinjaman()
    {
        $this->command->info('📋 Creating Pengajuan Pinjaman...');

        // Create 20 approved applications
        PengajuanPinjaman::factory()
            ->count(20)
            ->approved()
            ->create();

        // Create 5 pending applications
        PengajuanPinjaman::factory()
            ->count(5)
            ->pending()
            ->create();

        // Create 3 rejected applications
        PengajuanPinjaman::factory()
            ->count(3)
            ->rejected()
            ->create();

        // Create 2 draft applications
        PengajuanPinjaman::factory()
            ->count(2)
            ->draft()
            ->create();

        $this->command->info('   ✓ Created 30 pengajuan pinjaman (20 approved, 5 pending, 3 rejected, 2 draft)');

        // Create pinjaman first, then cicilan
        $this->createPinjaman();
        $this->createCicilanPinjaman();

        $this->command->info('   ✓ Created pinjaman and cicilan pinjaman');
    }

    private function createPinjaman()
    {
        $this->command->info('💰 Creating Pinjaman...');

        // Create 20 active loans
        \App\Models\Pinjaman::factory()
            ->count(20)
            ->create();

        $this->command->info('   ✓ Created 20 pinjaman records');
    }

    private function createCicilanPinjaman()
    {
        $this->command->info('💰 Creating Cicilan Pinjaman...');

        // Create 50 paid cicilan
        CicilanPinjaman::factory()
            ->count(50)
            ->paid()
            ->create();

        // Create 20 overdue cicilan
        CicilanPinjaman::factory()
            ->count(20)
            ->overdue()
            ->create();

        // Create 30 upcoming cicilan
        CicilanPinjaman::factory()
            ->count(30)
            ->upcoming()
            ->create();

        $this->command->info('   ✓ Created 100 cicilan pinjaman (50 paid, 20 overdue, 30 upcoming)');
    }

    private function createUsers()
    {
        $this->command->info('👤 Creating Additional Users...');

        // Check if users already exist
        $existingUsers = DB::table('users')->whereIn('username', [
            'admin_koperasi', 'anggota_koperasi', 'admin_kredit', 'admin_transfer', 'ketua_umum'
        ])->pluck('username')->toArray();

        $usersToCreate = [
            [
                'username' => 'admin_koperasi',
                'firstname' => 'Ketua Admin',
                'lastname' => 'Koperasi',
                'email' => 'admin.koperasi@spunindo.com',
                'password' => bcrypt('admin123'),
                'idroles' => 'kadmin',
                'isactive' => '1'
            ],
            [
                'username' => 'anggota_koperasi',
                'firstname' => 'Anggota',
                'lastname' => 'Koperasi',
                'email' => 'anggota.koperasi@spunindo.com',
                'password' => bcrypt('anggota123'),
                'idroles' => 'anggot',
                'isactive' => '1'
            ],
            [
                'username' => 'admin_kredit',
                'firstname' => 'Admin Kredit',
                'lastname' => 'Koperasi',
                'email' => 'admin.kredit@spunindo.com',
                'password' => bcrypt('kredit123'),
                'idroles' => 'akredt',
                'isactive' => '1'
            ],
            [
                'username' => 'admin_transfer',
                'firstname' => 'Admin Transfer',
                'lastname' => 'Koperasi',
                'email' => 'admin.transfer@spunindo.com',
                'password' => bcrypt('transfer123'),
                'idroles' => 'atrans',
                'isactive' => '1'
            ],
            [
                'username' => 'ketua_umum',
                'firstname' => 'Ketua Umum',
                'lastname' => 'Koperasi',
                'email' => 'ketua.umum@spunindo.com',
                'password' => bcrypt('ketua123'),
                'idroles' => 'ketuum',
                'isactive' => '1'
            ]
        ];

        $createdCount = 0;
        foreach ($usersToCreate as $userData) {
            if (!in_array($userData['username'], $existingUsers)) {
                DB::table('users')->insert(array_merge($userData, [
                    'created_at' => now(),
                    'updated_at' => now()
                ]));
                $createdCount++;
            }
        }

        $this->command->info("   ✓ Created {$createdCount} new users (skipped existing ones)");
    }
}
