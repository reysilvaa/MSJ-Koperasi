<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Iuran;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class KoperasiIuranSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * Seeder khusus untuk membuat data iuran menggunakan factory
     */
    public function run(): void
    {
        $this->command->info('💰 Creating Iuran Data using Factory...');

        // Hapus data lama jika ada
        DB::table('iuran')->truncate();

        // Ambil semua anggota aktif
        $anggota = User::whereNotNull('nomor_anggota')
            ->where('isactive', '1')
            ->get();

        if ($anggota->isEmpty()) {
            $this->command->info('   No active members found, creating sample members...');
            
            // Buat 6 anggota contoh menggunakan factory
            User::factory()
                ->count(6)
                ->anggotaAktif()
                ->create();

            // Refresh anggota setelah dibuat
            $anggota = User::whereNotNull('nomor_anggota')
                ->where('isactive', '1')
                ->get();
        }

        $this->command->info("   Creating iuran data for {$anggota->count()} members...");

        // 1. Buat simpanan pokok untuk setiap anggota menggunakan factory
        foreach ($anggota as $member) {
            Iuran::factory()
                ->pokok()
                ->forUser($member)
                ->create();
        }

        // 2. Buat simpanan wajib untuk tahun 2024 (data historis)
        $this->command->info('   Creating historical iuran wajib for 2024...');
        for ($bulan = 1; $bulan <= 12; $bulan++) {
            // Buat iuran wajib untuk sebagian anggota (simulasi tidak semua bayar)
            $jumlahBayar = fake()->numberBetween(
                (int)($anggota->count() * 0.8), // minimal 80% bayar
                $anggota->count() // maksimal semua bayar
            );

            Iuran::factory()
                ->count($jumlahBayar)
                ->wajib()
                ->tahun(2024)
                ->bulan($bulan)
                ->create();
        }

        // 3. Buat simpanan wajib untuk tahun 2025 (data current)
        $this->command->info('   Creating current year iuran wajib for 2025...');
        $currentMonth = now()->month;
        
        for ($bulan = 1; $bulan <= $currentMonth; $bulan++) {
            // Untuk bulan ini, simulasi belum semua bayar
            if ($bulan == $currentMonth) {
                $jumlahBayar = fake()->numberBetween(
                    (int)($anggota->count() * 0.6), // minimal 60% sudah bayar
                    (int)($anggota->count() * 0.9)  // maksimal 90% sudah bayar
                );
            } else {
                // Bulan sebelumnya, lebih banyak yang sudah bayar
                $jumlahBayar = fake()->numberBetween(
                    (int)($anggota->count() * 0.85),
                    $anggota->count()
                );
            }

            // Buat iuran wajib dengan user_id yang spesifik
            $selectedMembers = $anggota->random($jumlahBayar);
            foreach ($selectedMembers as $member) {
                Iuran::factory()
                    ->wajib()
                    ->tahun(2025)
                    ->bulan($bulan)
                    ->forUser($member)
                    ->create();
            }
        }

        // 4. Tambahan: Buat beberapa iuran wajib untuk bulan-bulan yang akan datang (simulasi pembayaran di muka)
        if ($currentMonth < 12) {
            $this->command->info('   Creating advance payments for future months...');
            for ($bulan = $currentMonth + 1; $bulan <= min($currentMonth + 3, 12); $bulan++) {
                $jumlahBayar = fake()->numberBetween(1, (int)($anggota->count() * 0.3)); // Sedikit yang bayar di muka
                
                $selectedMembers = $anggota->random($jumlahBayar);
                foreach ($selectedMembers as $member) {
                    Iuran::factory()
                        ->wajib()
                        ->tahun(2025)
                        ->bulan($bulan)
                        ->forUser($member)
                        ->create();
                }
            }
        }

        // Tampilkan statistik
        $this->displayStatistics();

        $this->command->info('✅ Iuran Data Created Successfully using Factory!');
    }

    /**
     * Tampilkan statistik iuran
     */
    private function displayStatistics()
    {
        $totalPokok = DB::table('iuran')->where('jenis_iuran', 'pokok')->sum('iuran');
        $totalWajib = DB::table('iuran')->where('jenis_iuran', 'wajib')->sum('iuran');
        $countPokok = DB::table('iuran')->where('jenis_iuran', 'pokok')->count();
        $countWajib = DB::table('iuran')->where('jenis_iuran', 'wajib')->count();

        $this->command->info('');
        $this->command->info('=== STATISTIK IURAN ===');
        $this->command->info("Simpanan Pokok: {$countPokok} transaksi, Total: Rp " . number_format($totalPokok, 0, ',', '.'));
        $this->command->info("Simpanan Wajib: {$countWajib} transaksi, Total: Rp " . number_format($totalWajib, 0, ',', '.'));
        $this->command->info("Grand Total: Rp " . number_format($totalPokok + $totalWajib, 0, ',', '.'));
        $this->command->info('========================');
    }
}