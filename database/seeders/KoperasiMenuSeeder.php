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

        // Insert sys_gmenu (Group Menu) - sesuai activity diagram
        $gmenus = [
            ['gmenu' => 'KOP001', 'name' => 'Master Data', 'icon' => 'fas fa-database', 'urut' => 1, 'isactive' => '1', 'created_at' => now(), 'updated_at' => now(), 'user_create' => 'seeder', 'user_update' => 'seeder'],
            ['gmenu' => 'KOP002', 'name' => 'Pinjaman', 'icon' => 'fas fa-money-bill-wave', 'urut' => 2, 'isactive' => '1', 'created_at' => now(), 'updated_at' => now(), 'user_create' => 'seeder', 'user_update' => 'seeder'],
            ['gmenu' => 'KOP003', 'name' => 'Pencairan', 'icon' => 'fas fa-hand-holding-usd', 'urut' => 3, 'isactive' => '1', 'created_at' => now(), 'updated_at' => now(), 'user_create' => 'seeder', 'user_update' => 'seeder'],
            ['gmenu' => 'KOP004', 'name' => 'Keuangan', 'icon' => 'fas fa-coins', 'urut' => 4, 'isactive' => '1', 'created_at' => now(), 'updated_at' => now(), 'user_create' => 'seeder', 'user_update' => 'seeder'],
            ['gmenu' => 'KOP005', 'name' => 'Laporan', 'icon' => 'fas fa-chart-line', 'urut' => 5, 'isactive' => '1', 'created_at' => now(), 'updated_at' => now(), 'user_create' => 'seeder', 'user_update' => 'seeder'],
        ];

        DB::table('sys_gmenu')->insert($gmenus);

        // Insert sys_dmenu (Detail Menu) - sesuai business process
        $dmenus = [
            // KOP001 - Master Data
            ['gmenu' => 'KOP001', 'dmenu' => 'KOP101', 'name' => 'Data Anggota', 'layout' => 'master', 'url' => 'anggota', 'tabel' => 'anggota', 'icon' => 'fas fa-user-friends', 'urut' => 1, 'isactive' => '1', 'created_at' => now(), 'updated_at' => now(), 'user_create' => 'seeder', 'user_update' => 'seeder'],
            ['gmenu' => 'KOP001', 'dmenu' => 'KOP102', 'name' => 'Paket Pinjaman', 'layout' => 'master', 'url' => 'paket-pinjaman', 'tabel' => 'master_paket_pinjaman', 'icon' => 'fas fa-box', 'urut' => 2, 'isactive' => '1', 'created_at' => now(), 'updated_at' => now(), 'user_create' => 'seeder', 'user_update' => 'seeder'],
            ['gmenu' => 'KOP001', 'dmenu' => 'KOP103', 'name' => 'Tenor Pinjaman', 'layout' => 'master', 'url' => 'tenor', 'tabel' => 'master_tenor', 'icon' => 'fas fa-clock', 'urut' => 3, 'isactive' => '1', 'created_at' => now(), 'updated_at' => now(), 'user_create' => 'seeder', 'user_update' => 'seeder'],

            // KOP002 - Pinjaman (sesuai activity diagram 02)
            ['gmenu' => 'KOP002', 'dmenu' => 'KOP201', 'name' => 'Pengajuan Pinjaman', 'layout' => 'master', 'url' => 'pengajuan-pinjaman', 'tabel' => 'pengajuan_pinjaman', 'icon' => 'fas fa-file-invoice-dollar', 'urut' => 1, 'isactive' => '1', 'created_at' => now(), 'updated_at' => now(), 'user_create' => 'seeder', 'user_update' => 'seeder'],
            ['gmenu' => 'KOP002', 'dmenu' => 'KOP202', 'name' => 'Approval Pinjaman', 'layout' => 'system', 'url' => 'approval-pinjaman', 'tabel' => 'pengajuan_pinjaman', 'icon' => 'fas fa-check-circle', 'urut' => 2, 'isactive' => '1', 'created_at' => now(), 'updated_at' => now(), 'user_create' => 'seeder', 'user_update' => 'seeder'],
            ['gmenu' => 'KOP002', 'dmenu' => 'KOP203', 'name' => 'Data Pinjaman Aktif', 'layout' => 'master', 'url' => 'pinjaman', 'tabel' => 'pinjaman', 'icon' => 'fas fa-money-check-alt', 'urut' => 3, 'isactive' => '1', 'created_at' => now(), 'updated_at' => now(), 'user_create' => 'seeder', 'user_update' => 'seeder'],

            // KOP003 - Pencairan (sesuai activity diagram 04)
            ['gmenu' => 'KOP003', 'dmenu' => 'KOP301', 'name' => 'Periode Pencairan', 'layout' => 'master', 'url' => 'periode-pencairan', 'tabel' => 'periode_pencairan', 'icon' => 'fas fa-calendar-alt', 'urut' => 1, 'isactive' => '1', 'created_at' => now(), 'updated_at' => now(), 'user_create' => 'seeder', 'user_update' => 'seeder'],
            ['gmenu' => 'KOP003', 'dmenu' => 'KOP302', 'name' => 'Proses Pencairan', 'layout' => 'system', 'url' => 'proses-pencairan', 'tabel' => 'pengajuan_pinjaman', 'icon' => 'fas fa-hand-holding-usd', 'urut' => 2, 'isactive' => '1', 'created_at' => now(), 'updated_at' => now(), 'user_create' => 'seeder', 'user_update' => 'seeder'],

            // KOP004 - Keuangan (sesuai activity diagram 05)
            ['gmenu' => 'KOP004', 'dmenu' => 'KOP401', 'name' => 'Cicilan Anggota', 'layout' => 'master', 'url' => 'cicilan', 'tabel' => 'cicilan_pinjaman', 'icon' => 'fas fa-credit-card', 'urut' => 1, 'isactive' => '1', 'created_at' => now(), 'updated_at' => now(), 'user_create' => 'seeder', 'user_update' => 'seeder'],
            ['gmenu' => 'KOP004', 'dmenu' => 'KOP402', 'name' => 'Iuran Anggota', 'layout' => 'master', 'url' => 'iuran', 'tabel' => 'iuran_anggota', 'icon' => 'fas fa-piggy-bank', 'urut' => 2, 'isactive' => '1', 'created_at' => now(), 'updated_at' => now(), 'user_create' => 'seeder', 'user_update' => 'seeder'],
            ['gmenu' => 'KOP004', 'dmenu' => 'KOP403', 'name' => 'Notifikasi', 'layout' => 'master', 'url' => 'notifikasi', 'tabel' => 'notifikasi', 'icon' => 'fas fa-bell', 'urut' => 3, 'isactive' => '1', 'created_at' => now(), 'updated_at' => now(), 'user_create' => 'seeder', 'user_update' => 'seeder'],

            // KOP005 - Laporan (sesuai kebutuhan reporting dan activity diagrams)
            ['gmenu' => 'KOP005', 'dmenu' => 'KOP501', 'name' => 'Laporan Pinjaman', 'layout' => 'report', 'url' => 'laporan-pinjaman', 'tabel' => 'pinjaman', 'icon' => 'fas fa-file-alt', 'urut' => 1, 'isactive' => '1', 'created_at' => now(), 'updated_at' => now(), 'user_create' => 'seeder', 'user_update' => 'seeder'],
            ['gmenu' => 'KOP005', 'dmenu' => 'KOP502', 'name' => 'Laporan Keuangan', 'layout' => 'report', 'url' => 'laporan-keuangan', 'tabel' => 'cicilan_pinjaman', 'icon' => 'fas fa-file-invoice', 'urut' => 2, 'isactive' => '1', 'created_at' => now(), 'updated_at' => now(), 'user_create' => 'seeder', 'user_update' => 'seeder'],
            ['gmenu' => 'KOP005', 'dmenu' => 'KOP503', 'name' => 'Laporan Anggota', 'layout' => 'report', 'url' => 'laporan-anggota', 'tabel' => 'anggota', 'icon' => 'fas fa-users', 'urut' => 3, 'isactive' => '1', 'created_at' => now(), 'updated_at' => now(), 'user_create' => 'seeder', 'user_update' => 'seeder'],

            // Laporan Keuangan Sesuai Activity Diagrams 09 & 11
            ['gmenu' => 'KOP005', 'dmenu' => 'KOP504', 'name' => 'Neraca (Balance Sheet)', 'layout' => 'report', 'url' => 'laporan-neraca', 'tabel' => 'anggota', 'icon' => 'fas fa-balance-scale', 'urut' => 4, 'isactive' => '1', 'created_at' => now(), 'updated_at' => now(), 'user_create' => 'seeder', 'user_update' => 'seeder'],
            ['gmenu' => 'KOP005', 'dmenu' => 'KOP505', 'name' => 'Laporan Laba/Rugi', 'layout' => 'report', 'url' => 'laporan-laba-rugi', 'tabel' => 'cicilan_pinjaman', 'icon' => 'fas fa-chart-pie', 'urut' => 5, 'isactive' => '1', 'created_at' => now(), 'updated_at' => now(), 'user_create' => 'seeder', 'user_update' => 'seeder'],
            ['gmenu' => 'KOP005', 'dmenu' => 'KOP506', 'name' => 'Cash Flow Statement', 'layout' => 'report', 'url' => 'laporan-cash-flow', 'tabel' => 'cicilan_pinjaman', 'icon' => 'fas fa-exchange-alt', 'urut' => 6, 'isactive' => '1', 'created_at' => now(), 'updated_at' => now(), 'user_create' => 'seeder', 'user_update' => 'seeder'],
            ['gmenu' => 'KOP005', 'dmenu' => 'KOP507', 'name' => 'Laporan SHU Tahunan', 'layout' => 'report', 'url' => 'laporan-shu', 'tabel' => 'anggota', 'icon' => 'fas fa-trophy', 'urut' => 7, 'isactive' => '1', 'created_at' => now(), 'updated_at' => now(), 'user_create' => 'seeder', 'user_update' => 'seeder'],
            ['gmenu' => 'KOP005', 'dmenu' => 'KOP508', 'name' => 'Jurnal Umum', 'layout' => 'report', 'url' => 'laporan-jurnal', 'tabel' => 'cicilan_pinjaman', 'icon' => 'fas fa-book', 'urut' => 8, 'isactive' => '1', 'created_at' => now(), 'updated_at' => now(), 'user_create' => 'seeder', 'user_update' => 'seeder'],
        ];

        DB::table('sys_dmenu')->insert($dmenus);
    }
}
