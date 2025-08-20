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

class KoperasiDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * Seeder terpusat untuk data koperasi - bisa membuat data minimal atau lengkap
     */
    public function run(): void
    {
        $this->command->info('🏢 Starting Koperasi Data Seeding...');

        // 1. Buat data dasar (admin users dan paket pinjaman minimal)
        $this->createBasicData();

        // 2. Tanya apakah ingin membuat data dummy lengkap
        if ($this->command->confirm('Apakah Anda ingin membuat data dummy lengkap untuk testing?', false)) {
            $this->createDummyData();
        }

        $this->command->info('✅ Koperasi Data Seeding Completed!');
    }

    /**
     * Membuat data dasar koperasi (admin users dan paket pinjaman minimal)
     */
    private function createBasicData()
    {
        $this->command->info('📋 Creating Basic Koperasi Data...');

        // Cek apakah admin users sudah ada
        if (DB::table('users')->where('username', 'admin_koperasi')->count() == 0) {

            // Buat satu anggota contoh
            $this->command->info('   Creating sample anggota...');
            User::factory()
                ->anggotaAktif()
                ->create([
                    'username' => 'anggota_koperasi',
                    'firstname' => 'Anggota',
                    'lastname' => 'Koperasi',
                    'email' => 'anggota.koperasi@spunindo.com',
                    'password' => bcrypt('anggota123'), // Plain text, akan di-hash oleh mutator
                    'nomor_anggota' => 'A240001',
                    'nik' => '3515123456789012',
                    'nama_lengkap' => 'Anggota Koperasi',
                    'user_create' => 'seeder',
                    'user_update' => 'seeder'
                ]);

            // Buat admin users
            $this->command->info('   Creating admin users...');
            $adminUsers = [
                [
                    'username' => 'admin_koperasi',
                    'firstname' => 'Ketua Admin',
                    'lastname' => 'Koperasi',
                    'email' => 'admin.koperasi@spunindo.com',
                    'password' => 'admin123',
                    'idroles' => 'kadmin'
                ],
                [
                    'username' => 'admin_kredit',
                    'firstname' => 'Admin Kredit',
                    'lastname' => 'Koperasi',
                    'email' => 'admin.kredit@spunindo.com',
                    'password' => 'kredit123',
                    'idroles' => 'akredt'
                ],
                [
                    'username' => 'admin_transfer',
                    'firstname' => 'Admin Transfer',
                    'lastname' => 'Koperasi',
                    'email' => 'admin.transfer@spunindo.com',
                    'password' => 'transfer123',
                    'idroles' => 'atrans'
                ],
                [
                    'username' => 'ketua_umum',
                    'firstname' => 'Ketua Umum',
                    'lastname' => 'Koperasi',
                    'email' => 'ketua.umum@spunindo.com',
                    'password' => 'ketua123',
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

            // Buat master paket pinjaman untuk bulan ini dan beberapa bulan ke depan
            $this->command->info('   Creating master paket pinjaman...');
            $currentDate = now();
            for ($i = 0; $i < 6; $i++) {
                $periode = $currentDate->copy()->addMonths($i)->format('Y-m');
                MasterPaketPinjaman::factory()
                    ->periode($periode)
                    ->activeWithStock()
                    ->create([
                        'stock_limit' => 100,
                        'stock_terpakai' => 0,
                        'user_create' => 'seeder',
                        'user_update' => 'seeder'
                    ]);
            }

            $this->command->info('✅ Basic Koperasi Data Created Successfully!');
        } else {
            $this->command->info('⚠️  Basic Koperasi Data already exists, skipping...');
        }
    }

    /**
     * Membuat data dummy lengkap untuk testing
     */
    private function createDummyData()
    {
        $this->command->info('🚀 Creating Dummy Data for Testing...');

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

        $this->command->info('✅ Dummy Data Creation Completed!');
    }

    private function createMasterPaketPinjaman()
    {
        $this->command->info('📦 Creating Master Paket Pinjaman...');

        // Create paket for 2024-2026 (36 months) using factory
        $periods = [];
        for ($year = 2024; $year <= 2026; $year++) {
            for ($month = 1; $month <= 12; $month++) {
                $periode = sprintf('%04d-%02d', $year, $month);
                // Skip jika sudah ada
                if (MasterPaketPinjaman::where('periode', $periode)->count() == 0) {
                    $periods[] = $periode;
                }
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
}
