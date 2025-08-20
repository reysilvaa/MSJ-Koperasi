<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Iuran;
use App\Models\CicilanPinjaman;
use App\Models\MasterPaketPinjaman;
use App\Models\PengajuanPinjaman;
use App\Models\Pinjaman;
use App\Models\ApprovalHistory;
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

        // 2. Create Users/Anggota (50 members)
        $this->createUsersAnggota();

        // 3. Create Iuran data
        $this->createIuranData();

        // 4. Create Pengajuan Pinjaman (30 applications)
        $this->createPengajuanPinjaman();

        // 5. Create Approval History
        $this->createApprovalHistory();

        // 6. Create Users for each role
        $this->createAdminUsers();

        $this->command->info('✅ Koperasi Dummy Data Generation Completed!');
    }

    private function createMasterPaketPinjaman()
    {
        $this->command->info('📦 Creating Master Paket Pinjaman...');

        // Create paket for 2024-2026 (36 months) using factory
        $periods = [];
        for ($year = 2024; $year <= 2026; $year++) {
            for ($month = 1; $month <= 12; $month++) {
                $periods[] = sprintf('%04d-%02d', $year, $month);
            }
        }

        // Create active pakets with good stock
        foreach ($periods as $periode) {
            MasterPaketPinjaman::factory()
                ->periode($periode)
                ->activeWithStock()
                ->create();
        }

        // Create some nearly full pakets for variety
        MasterPaketPinjaman::factory()
            ->count(5)
            ->nearlyFull()
            ->create();

        $this->command->info("   ✓ Created " . (count($periods) + 5) . " paket pinjaman periods");
    }

    private function createUsersAnggota()
    {
        $this->command->info('👥 Creating Users/Anggota...');

        // Create 40 active anggota
        User::factory()
            ->count(40)
            ->anggotaAktif()
            ->create();

        // Create 10 inactive anggota
        User::factory()
            ->count(10)
            ->anggotaAktif()
            ->nonAktif()
            ->create();

        $this->command->info('   ✓ Created 50 users/anggota (40 active, 10 inactive)');
    }

    private function createIuranData()
    {
        $this->command->info('💰 Creating Iuran Data...');

        // Create iuran wajib untuk tahun 2023-2024
        for ($tahun = 2023; $tahun <= 2024; $tahun++) {
            for ($bulan = 1; $bulan <= 12; $bulan++) {
                // Skip future months for current year
                if ($tahun == 2024 && $bulan > now()->month) {
                    continue;
                }

                // Create iuran wajib for random anggota
                Iuran::factory()
                    ->count(fake()->numberBetween(25, 35))
                    ->wajib()
                    ->tahun($tahun)
                    ->bulan($bulan)
                    ->create();
            }
        }

        // Create iuran pokok (one-time payment for each anggota)
        $activeUsers = User::whereNotNull('nomor_anggota')
            ->where('isactive', '1')
            ->get();

        foreach ($activeUsers as $user) {
            Iuran::factory()
                ->pokok()
                ->forUser($user)
                ->create();
        }

        $this->command->info('   ✓ Created iuran data (wajib & pokok) for 2023-2024');
    }

    private function createPengajuanPinjaman()
    {
        $this->command->info('📋 Creating Pengajuan Pinjaman...');

        // Create 15 approved applications (regular)
        PengajuanPinjaman::factory()
            ->count(15)
            ->approved()
            ->create();

        // Create 5 approved small loans
        PengajuanPinjaman::factory()
            ->count(5)
            ->approved()
            ->smallLoan()
            ->create();

        // Create 3 approved large loans
        PengajuanPinjaman::factory()
            ->count(3)
            ->approved()
            ->largeLoan()
            ->create();

        // Create 2 approved top-up loans
        PengajuanPinjaman::factory()
            ->count(2)
            ->approved()
            ->topUp()
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

        $this->command->info('   ✓ Created 35 pengajuan pinjaman (25 approved, 5 pending, 3 rejected, 2 draft)');

        // Create pinjaman first, then cicilan
        $this->createPinjaman();
        $this->createCicilanPinjaman();

        $this->command->info('   ✓ Created pinjaman and cicilan pinjaman');
    }

    private function createPinjaman()
    {
        $this->command->info('💰 Creating Pinjaman...');

        // Create 15 active loans
        Pinjaman::factory()
            ->count(15)
            ->active()
            ->create();

        // Create 3 completed/lunas loans
        Pinjaman::factory()
            ->count(3)
            ->lunas()
            ->create();

        // Create 2 problematic loans
        Pinjaman::factory()
            ->count(2)
            ->bermasalah()
            ->create();

        $this->command->info('   ✓ Created 20 pinjaman records (15 active, 3 lunas, 2 bermasalah)');
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

    private function createApprovalHistory()
    {
        $this->command->info('📋 Creating Approval History...');

        // Get all pengajuan pinjaman that have been processed
        $processedPengajuan = PengajuanPinjaman::whereIn('status_pengajuan', [
            'disetujui', 'ditolak', 'review_admin', 'review_panitia'
        ])->get();

        foreach ($processedPengajuan as $pengajuan) {
            // Create approval history based on status
            switch ($pengajuan->status_pengajuan) {
                case 'disetujui':
                    // Create multiple approval steps for approved applications
                    ApprovalHistory::factory()
                        ->forPengajuan($pengajuan)
                        ->pending()
                        ->create();
                    
                    ApprovalHistory::factory()
                        ->forPengajuan($pengajuan)
                        ->approved()
                        ->create();
                    
                    // Some approved loans also have disbursement history
                    if (fake()->boolean(70)) {
                        ApprovalHistory::factory()
                            ->forPengajuan($pengajuan)
                            ->disbursed()
                            ->create();
                    }
                    break;

                case 'ditolak':
                    ApprovalHistory::factory()
                        ->forPengajuan($pengajuan)
                        ->pending()
                        ->create();
                    
                    ApprovalHistory::factory()
                        ->forPengajuan($pengajuan)
                        ->rejected()
                        ->create();
                    break;

                case 'review_admin':
                case 'review_panitia':
                    ApprovalHistory::factory()
                        ->forPengajuan($pengajuan)
                        ->pending()
                        ->create();
                    break;
            }
        }

        // Create some additional random approval histories
        ApprovalHistory::factory()
            ->count(15)
            ->create();

        $this->command->info('   ✓ Created approval history for processed pengajuan pinjaman');
    }

    private function createAdminUsers()
    {
        $this->command->info('👤 Creating Admin Users...');

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
                'idroles' => 'kadmin'
            ],
            [
                'username' => 'anggota_koperasi',
                'firstname' => 'Anggota',
                'lastname' => 'Koperasi',
                'email' => 'anggota.koperasi@spunindo.com',
                'password' => bcrypt('anggota123'),
                'idroles' => 'anggot'
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

        $createdCount = 0;
        foreach ($usersToCreate as $userData) {
            if (!in_array($userData['username'], $existingUsers)) {
                // Tentukan factory state berdasarkan role
                if ($userData['idroles'] === 'anggot') {
                    User::factory()
                        ->anggotaAktif()
                        ->create(array_merge($userData, [
                            'user_create' => 'seeder',
                            'user_update' => 'seeder'
                        ]));
                } else {
                    User::factory()
                        ->admin()
                        ->create(array_merge($userData, [
                            'user_create' => 'seeder',
                            'user_update' => 'seeder'
                        ]));
                }
                $createdCount++;
            }
        }

        $this->command->info("   ✓ Created {$createdCount} new admin users (skipped existing ones)");
    }
}
