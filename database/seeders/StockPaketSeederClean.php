<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class StockPaketSeeder extends Seeder
{
    /**
     * Seed master_paket_pinjaman dengan periode bulanan
     *
     * Business Rules:
     * 1. 1 unit = Rp 500.000 (fixed value)
     * 2. Paket berdasarkan periode bulanan (YYYY-MM)
     * 3. Pengajuan hanya bisa di bulan aktif
     */
    public function run(): void
    {
        // Clean up existing data for current month
        $currentPeriod = now()->format('Y-m');
        DB::table('master_paket_pinjaman')->where('periode', $currentPeriod)->delete();

        // Master paket untuk bulan ini
        $masterPaketData = [
            [
                'periode' => $currentPeriod,
                'bunga_per_bulan' => 1.00,
                'stock_limit' => 100, // Total 100 unit untuk bulan ini
                'stock_terpakai' => 0,
                'isactive' => 1,
                'created_at' => now(),
                'updated_at' => now()
            ]
        ];

        // Insert master paket data
        DB::table('master_paket_pinjaman')->insert($masterPaketData);

        $this->command->info('✅ Paket Pinjaman periode ' . $currentPeriod . ' berhasil di-seed');
        $this->command->info('📊 Periode: ' . now()->format('F Y'));
        $this->command->info('📦 Stock Limit: 100 unit');
        $this->command->info('💡 Logic: 1 unit = Rp 500.000');
        $this->command->info('⚠️ Pengajuan hanya bisa di bulan aktif');
    }
}
