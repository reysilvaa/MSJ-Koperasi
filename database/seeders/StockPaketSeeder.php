<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class StockPaketSeeder extends Seeder
{
    /**
     * Seed master_paket_pinjaman dengan stock management fields
     *
     * Business Rules:
     * 1. 1 paket = Rp 500.000 (fixed value)
     * 2. Stock management di master_paket_pinjaman langsung
     * 3. Stock direset setiap bulan (Auto Monthly Reset)
     */
    public function run(): void
    {
        // Clean up existing data with better criteria
        DB::table('master_paket_pinjaman')->whereIn('kode_paket', ['P5', 'P10', 'P20', 'P40'])->delete();

        // Ambil ID tenor dari master_tenor untuk mapping
        $tenor6Bulan = DB::table('master_tenor')->where('tenor_bulan', 6)->first()->id ?? 1;
        $tenor10Bulan = DB::table('master_tenor')->where('tenor_bulan', 10)->first()->id ?? 2;
        $tenor12Bulan = DB::table('master_tenor')->where('tenor_bulan', 12)->first()->id ?? 3;
        $tenor15Bulan = DB::table('master_tenor')->where('tenor_bulan', 15)->first()->id ?? 4;

        // Master paket data dengan stock management
        $masterPaketData = [
            [
                'kode_paket' => 'P5',
                'nama_paket' => 'Paket 5 Unit - 2.5 Juta',
                'deskripsi' => 'Pinjaman Rp 2.500.000 (5 x Rp 500.000)',
                'bunga_per_bulan' => 1.00,
                'tenor_diizinkan' => json_encode([$tenor6Bulan, $tenor10Bulan, $tenor12Bulan]),
                'stock_limit_bulanan' => 20,
                'stock_terpakai' => 0,
                'stock_reserved' => 0,
                'stock_tersedia' => 20,
                'alert_threshold' => 5,
                'reset_terakhir' => now()->format('Y-m-01'),
                'isactive' => 1,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'kode_paket' => 'P10',
                'nama_paket' => 'Paket 10 Unit - 5 Juta',
                'deskripsi' => 'Pinjaman Rp 5.000.000 (10 x Rp 500.000)',
                'bunga_per_bulan' => 1.00,
                'tenor_diizinkan' => json_encode([$tenor6Bulan, $tenor10Bulan, $tenor12Bulan]),
                'stock_limit_bulanan' => 15,
                'stock_terpakai' => 0,
                'stock_reserved' => 0,
                'stock_tersedia' => 15,
                'alert_threshold' => 3,
                'reset_terakhir' => now()->format('Y-m-01'),
                'isactive' => 1,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'kode_paket' => 'P20',
                'nama_paket' => 'Paket 20 Unit - 10 Juta',
                'deskripsi' => 'Pinjaman Rp 10.000.000 (20 x Rp 500.000)',
                'bunga_per_bulan' => 1.00,
                'tenor_diizinkan' => json_encode([$tenor10Bulan, $tenor12Bulan, $tenor15Bulan]),
                'stock_limit_bulanan' => 10,
                'stock_terpakai' => 0,
                'stock_reserved' => 0,
                'stock_tersedia' => 10,
                'alert_threshold' => 2,
                'reset_terakhir' => now()->format('Y-m-01'),
                'isactive' => 1,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'kode_paket' => 'P40',
                'nama_paket' => 'Paket 40 Unit - 20 Juta',
                'deskripsi' => 'Pinjaman Rp 20.000.000 (40 x Rp 500.000)',
                'bunga_per_bulan' => 1.00,
                'tenor_diizinkan' => json_encode([$tenor12Bulan, $tenor15Bulan]),
                'stock_limit_bulanan' => 5,
                'stock_terpakai' => 0,
                'stock_reserved' => 0,
                'stock_tersedia' => 5,
                'alert_threshold' => 1,
                'reset_terakhir' => now()->format('Y-m-01'),
                'isactive' => 1,
                'created_at' => now(),
                'updated_at' => now()
            ]
        ];

        // Insert master paket data
        DB::table('master_paket_pinjaman')->insert($masterPaketData);

        $this->command->info('✅ Master Paket Pinjaman dengan Stock Management berhasil di-seed');
        $this->command->info('📊 Paket Summary:');
        $this->command->info('   - P5 (2.5 Juta): 20 unit/bulan');
        $this->command->info('   - P10 (5 Juta): 15 unit/bulan');
        $this->command->info('   - P20 (10 Juta): 10 unit/bulan');
        $this->command->info('   - P40 (20 Juta): 5 unit/bulan');
        $this->command->info('� Logic: Nominal dihitung otomatis = unit x 500.000');
    }
}
