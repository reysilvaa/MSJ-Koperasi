<?php

namespace Database\Seeders;
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
        $koperasiMenus = ['KOP101', 'KOP102', 'KOP103', 'KOP201', 'KOP301', 'KOP302', 'KOP303', 'KOP401', 'KOP402', 'KOP403', 'KOP501'];
        
        DB::table('sys_auth')->whereIn('dmenu', $koperasiMenus)->delete();
        DB::table('sys_table')->whereIn('dmenu', $koperasiMenus)->delete();
        DB::table('sys_dmenu')->whereIn('dmenu', $koperasiMenus)->delete();
        
        $koperasiGmenus = ['KOP001', 'KOP002', 'KOP003', 'KOP004', 'KOP005'];
        DB::table('sys_gmenu')->whereIn('gmenu', $koperasiGmenus)->delete();

        // Insert sys_gmenu (Group Menu) - sesuai struktur yang benar
        $gmenus = [
            ['gmenu' => 'KOP001', 'name' => 'Master Data', 'icon' => 'fas fa-database', 'urut' => 1, 'isactive' => '1', 'created_at' => now(), 'updated_at' => now(), 'user_create' => 'seeder', 'user_update' => 'seeder'],
            ['gmenu' => 'KOP002', 'name' => 'Pinjaman', 'icon' => 'fas fa-money-bill-wave', 'urut' => 2, 'isactive' => '1', 'created_at' => now(), 'updated_at' => now(), 'user_create' => 'seeder', 'user_update' => 'seeder'],
            ['gmenu' => 'KOP004', 'name' => 'Keuangan', 'icon' => 'fas fa-coins', 'urut' => 4, 'isactive' => '1', 'created_at' => now(), 'updated_at' => now(), 'user_create' => 'seeder', 'user_update' => 'seeder'],
            ['gmenu' => 'KOP005', 'name' => 'Pengguna', 'icon' => 'fas fa-users-cog', 'urut' => 6, 'isactive' => '1', 'created_at' => now(), 'updated_at' => now(), 'user_create' => 'seeder', 'user_update' => 'seeder'],
        ];

        DB::table('sys_gmenu')->insert($gmenus);

        // Insert sys_dmenu (Detail Menu) - sesuai struktur yang benar
        $dmenus = [
            // KOP001 - Master Data
            ['gmenu' => 'KOP001', 'dmenu' => 'KOP101', 'js' => '0', 'name' => 'Data Anggota', 'layout' => 'master', 'url' => 'anggota', 'tabel' => 'mst_anggota', 'icon' => 'fas fa-user-friends', 'urut' => 1, 'isactive' => '1', 'created_at' => now(), 'updated_at' => now(), 'user_create' => 'seeder', 'user_update' => 'seeder'],
            ['gmenu' => 'KOP001', 'dmenu' => 'KOP102', 'js' => '0', 'name' => 'Paket Pinjaman', 'layout' => 'master', 'url' => 'paket', 'tabel' => 'mst_paket', 'icon' => 'fas fa-box', 'urut' => 2, 'isactive' => '1', 'created_at' => now(), 'updated_at' => now(), 'user_create' => 'seeder', 'user_update' => 'seeder'],
            ['gmenu' => 'KOP001', 'dmenu' => 'KOP103', 'js' => '0', 'name' => 'Periode Pencairan', 'layout' => 'manual', 'url' => 'periode', 'tabel' => 'mst_periode', 'icon' => 'fas fa-calendar-alt', 'urut' => 3, 'isactive' => '1', 'created_at' => now(), 'updated_at' => now(), 'user_create' => 'seeder', 'user_update' => 'seeder'],

            // KOP002 - Pinjaman
            ['gmenu' => 'KOP002', 'dmenu' => 'KOP201', 'js' => '0', 'name' => 'Piutang Pinjaman', 'layout' => 'master', 'url' => 'piutang', 'tabel' => 'trs_piutang', 'icon' => 'fas fa-file-invoice-dollar', 'urut' => 1, 'isactive' => '1', 'created_at' => now(), 'updated_at' => now(), 'user_create' => 'seeder', 'user_update' => 'seeder'],

            // KOP004 - Keuangan
            ['gmenu' => 'KOP004', 'dmenu' => 'KOP401', 'js' => '0', 'name' => 'Potongan', 'layout' => 'master', 'url' => 'potongan', 'tabel' => 'trs_potongan', 'icon' => 'fas fa-cut', 'urut' => 1, 'isactive' => '1', 'created_at' => now(), 'updated_at' => now(), 'user_create' => 'seeder', 'user_update' => 'seeder'],
            ['gmenu' => 'KOP004', 'dmenu' => 'KOP402', 'js' => '0', 'name' => 'Cicilan', 'layout' => 'master', 'url' => 'cicilan', 'tabel' => 'trs_cicilan', 'icon' => 'fas fa-hand-holding-usd', 'urut' => 2, 'isactive' => '1', 'created_at' => now(), 'updated_at' => now(), 'user_create' => 'seeder', 'user_update' => 'seeder'],
            ['gmenu' => 'KOP004', 'dmenu' => 'KOP403', 'js' => '0', 'name' => 'SHU', 'layout' => 'master', 'url' => 'shu', 'tabel' => 'trs_shu', 'icon' => 'fas fa-trophy', 'urut' => 3, 'isactive' => '1', 'created_at' => now(), 'updated_at' => now(), 'user_create' => 'seeder', 'user_update' => 'seeder'],

            // KOP005 - Pengguna
            ['gmenu' => 'KOP005', 'dmenu' => 'KOP501', 'js' => '0', 'name' => 'Users', 'layout' => 'master', 'url' => 'users', 'tabel' => 'users', 'icon' => 'fas fa-user', 'urut' => 1, 'isactive' => '1', 'created_at' => now(), 'updated_at' => now(), 'user_create' => 'seeder', 'user_update' => 'seeder'],
        ];

        DB::table('sys_dmenu')->insert($dmenus);
    }
}