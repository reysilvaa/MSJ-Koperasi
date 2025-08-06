<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class KoperasiMenuSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Delete existing data first (urutan penting karena foreign key)
        DB::table('sys_auth')->where('gmenu', 'like', 'KOP%')->delete();
        DB::table('sys_table')->where('gmenu', 'like', 'KOP%')->delete();
        DB::table('sys_dmenu')->where('gmenu', 'like', 'KOP%')->delete();
        DB::table('sys_gmenu')->where('gmenu', 'like', 'KOP%')->delete();

        // Insert sys_gmenu (Group Menu)
        $gmenus = [
            ['gmenu' => 'KOP001', 'name' => 'Master Data', 'icon' => 'fas fa-database', 'urut' => 1, 'isactive' => '1', 'created_at' => now(), 'updated_at' => now(), 'user_create' => 'seeder', 'user_update' => 'seeder'],
            ['gmenu' => 'KOP002', 'name' => 'Pinjaman', 'icon' => 'fas fa-money-bill-wave', 'urut' => 2, 'isactive' => '1', 'created_at' => now(), 'updated_at' => now(), 'user_create' => 'seeder', 'user_update' => 'seeder'],
            ['gmenu' => 'KOP003', 'name' => 'Keuangan', 'icon' => 'fas fa-coins', 'urut' => 3, 'isactive' => '1', 'created_at' => now(), 'updated_at' => now(), 'user_create' => 'seeder', 'user_update' => 'seeder'],
            ['gmenu' => 'KOP004', 'name' => 'Laporan', 'icon' => 'fas fa-chart-line', 'urut' => 4, 'isactive' => '1', 'created_at' => now(), 'updated_at' => now(), 'user_create' => 'seeder', 'user_update' => 'seeder'],
            ['gmenu' => 'KOP005', 'name' => 'Sistem', 'icon' => 'fas fa-cogs', 'urut' => 5, 'isactive' => '1', 'created_at' => now(), 'updated_at' => now(), 'user_create' => 'seeder', 'user_update' => 'seeder'],
        ];

        DB::table('sys_gmenu')->insert($gmenus);

        // Insert sys_dmenu (Detail Menu)
        $dmenus = [
            // Master Data
            ['gmenu' => 'KOP001', 'dmenu' => 'KOP101', 'name' => 'Data Anggota', 'layout' => 'master', 'url' => 'KOP101', 'tabel' => 'anggota', 'urut' => 1, 'isactive' => '1', 'created_at' => now(), 'updated_at' => now(), 'user_create' => 'seeder', 'user_update' => 'seeder'],
            ['gmenu' => 'KOP001', 'dmenu' => 'KOP102', 'name' => 'Paket Pinjaman', 'layout' => 'master', 'url' => 'KOP102', 'tabel' => 'master_paket_pinjaman', 'urut' => 2, 'isactive' => '1', 'created_at' => now(), 'updated_at' => now(), 'user_create' => 'seeder', 'user_update' => 'seeder'],
            ['gmenu' => 'KOP001', 'dmenu' => 'KOP103', 'name' => 'Tenor Pinjaman', 'layout' => 'master', 'url' => 'KOP103', 'tabel' => 'master_tenor', 'urut' => 3, 'isactive' => '1', 'created_at' => now(), 'updated_at' => now(), 'user_create' => 'seeder', 'user_update' => 'seeder'],
            ['gmenu' => 'KOP001', 'dmenu' => 'KOP104', 'name' => 'Konfigurasi', 'layout' => 'master', 'url' => 'KOP104', 'tabel' => 'konfigurasi_koperasi', 'urut' => 4, 'isactive' => '1', 'created_at' => now(), 'updated_at' => now(), 'user_create' => 'seeder', 'user_update' => 'seeder'],

            // Pinjaman
            ['gmenu' => 'KOP002', 'dmenu' => 'KOP201', 'name' => 'Pengajuan Pinjaman', 'layout' => 'transc', 'url' => 'KOP201', 'tabel' => 'pengajuan_pinjaman', 'urut' => 1, 'isactive' => '1', 'created_at' => now(), 'updated_at' => now(), 'user_create' => 'seeder', 'user_update' => 'seeder'],
            ['gmenu' => 'KOP002', 'dmenu' => 'KOP202', 'name' => 'Approval Pinjaman', 'layout' => 'stdr', 'url' => 'KOP202', 'tabel' => 'approval_history', 'urut' => 2, 'isactive' => '1', 'created_at' => now(), 'updated_at' => now(), 'user_create' => 'seeder', 'user_update' => 'seeder'],
            ['gmenu' => 'KOP002', 'dmenu' => 'KOP203', 'name' => 'Data Pinjaman', 'layout' => 'stdr', 'url' => 'KOP203', 'tabel' => 'pinjaman', 'urut' => 3, 'isactive' => '1', 'created_at' => now(), 'updated_at' => now(), 'user_create' => 'seeder', 'user_update' => 'seeder'],
            ['gmenu' => 'KOP002', 'dmenu' => 'KOP204', 'name' => 'Pembayaran Cicilan', 'layout' => 'transc', 'url' => 'KOP204', 'tabel' => 'cicilan_pinjaman', 'urut' => 4, 'isactive' => '1', 'created_at' => now(), 'updated_at' => now(), 'user_create' => 'seeder', 'user_update' => 'seeder'],

            // Keuangan
            ['gmenu' => 'KOP003', 'dmenu' => 'KOP301', 'name' => 'Iuran Anggota', 'layout' => 'transc', 'url' => 'KOP301', 'tabel' => 'iuran_anggota', 'urut' => 1, 'isactive' => '1', 'created_at' => now(), 'updated_at' => now(), 'user_create' => 'seeder', 'user_update' => 'seeder'],
            ['gmenu' => 'KOP003', 'dmenu' => 'KOP302', 'name' => 'Transfer Dana', 'layout' => 'transc', 'url' => 'KOP302', 'tabel' => 'transfer_dana', 'urut' => 2, 'isactive' => '1', 'created_at' => now(), 'updated_at' => now(), 'user_create' => 'seeder', 'user_update' => 'seeder'],
            ['gmenu' => 'KOP003', 'dmenu' => 'KOP303', 'name' => 'SHU Anggota', 'layout' => 'stdr', 'url' => 'KOP303', 'tabel' => 'shu', 'urut' => 3, 'isactive' => '1', 'created_at' => now(), 'updated_at' => now(), 'user_create' => 'seeder', 'user_update' => 'seeder'],
            ['gmenu' => 'KOP003', 'dmenu' => 'KOP304', 'name' => 'Jurnal Keuangan', 'layout' => 'stdr', 'url' => 'KOP304', 'tabel' => 'jurnal_keuangan', 'urut' => 4, 'isactive' => '1', 'created_at' => now(), 'updated_at' => now(), 'user_create' => 'seeder', 'user_update' => 'seeder'],

            // Laporan
            ['gmenu' => 'KOP004', 'dmenu' => 'KOP401', 'name' => 'Laporan Pinjaman', 'layout' => 'report', 'url' => 'KOP401', 'tabel' => 'pinjaman', 'urut' => 1, 'isactive' => '1', 'created_at' => now(), 'updated_at' => now(), 'user_create' => 'seeder', 'user_update' => 'seeder'],
            ['gmenu' => 'KOP004', 'dmenu' => 'KOP402', 'name' => 'Laporan Iuran', 'layout' => 'report', 'url' => 'KOP402', 'tabel' => 'iuran_anggota', 'urut' => 2, 'isactive' => '1', 'created_at' => now(), 'updated_at' => now(), 'user_create' => 'seeder', 'user_update' => 'seeder'],
            ['gmenu' => 'KOP004', 'dmenu' => 'KOP403', 'name' => 'Laba Rugi', 'layout' => 'report', 'url' => 'KOP403', 'tabel' => 'jurnal_keuangan', 'urut' => 3, 'isactive' => '1', 'created_at' => now(), 'updated_at' => now(), 'user_create' => 'seeder', 'user_update' => 'seeder'],
            ['gmenu' => 'KOP004', 'dmenu' => 'KOP404', 'name' => 'Neraca', 'layout' => 'report', 'url' => 'KOP404', 'tabel' => 'jurnal_keuangan', 'urut' => 4, 'isactive' => '1', 'created_at' => now(), 'updated_at' => now(), 'user_create' => 'seeder', 'user_update' => 'seeder'],
            ['gmenu' => 'KOP004', 'dmenu' => 'KOP405', 'name' => 'Laporan SHU', 'layout' => 'report', 'url' => 'KOP405', 'tabel' => 'shu', 'urut' => 5, 'isactive' => '1', 'created_at' => now(), 'updated_at' => now(), 'user_create' => 'seeder', 'user_update' => 'seeder'],

            // Sistem
            ['gmenu' => 'KOP005', 'dmenu' => 'KOP501', 'name' => 'Notifikasi', 'layout' => 'stdr', 'url' => 'KOP501', 'tabel' => 'notifikasi', 'urut' => 1, 'isactive' => '1', 'created_at' => now(), 'updated_at' => now(), 'user_create' => 'seeder', 'user_update' => 'seeder'],
            ['gmenu' => 'KOP005', 'dmenu' => 'KOP502', 'name' => 'Dokumen Pengajuan', 'layout' => 'stdr', 'url' => 'KOP502', 'tabel' => 'dokumen_pengajuan', 'urut' => 2, 'isactive' => '1', 'created_at' => now(), 'updated_at' => now(), 'user_create' => 'seeder', 'user_update' => 'seeder'],
        ];

        DB::table('sys_dmenu')->insert($dmenus);
    }
}
