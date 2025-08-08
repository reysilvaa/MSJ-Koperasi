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

        // Ambil ID tenor dari master_tenor untuk mapping
        $tenor6Bulan = DB::table('master_tenor')->where('tenor_bulan', 6)->first()->id ?? 1;
        $tenor10Bulan = DB::table('master_tenor')->where('tenor_bulan', 10)->first()->id ?? 2;
        $tenor12Bulan = DB::table('master_tenor')->where('tenor_bulan', 12)->first()->id ?? 3;
        $tenor15Bulan = DB::table('master_tenor')->where('tenor_bulan', 15)->first()->id ?? 4;

        // Master paket untuk bulan ini
        $masterPaketData = [
            [
                'periode' => $currentPeriod,
                'nama_paket' => 'Paket Pinjaman ' . now()->format('F Y'),
                'deskripsi' => 'Paket pinjaman untuk periode ' . now()->format('F Y') . '. 1 unit = Rp 500.000',
                'bunga_per_bulan' => 1.00,
                'tenor_diizinkan' => json_encode([$tenor6Bulan, $tenor10Bulan, $tenor12Bulan, $tenor15Bulan]),
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
