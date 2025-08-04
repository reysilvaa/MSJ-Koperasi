<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class KoperasiAuthSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Delete existing data first
        DB::table('sys_auth')->where('gmenu', 'like', 'KOP%')->delete();

        // Use existing 'admins' role from DatabaseSeeder
        // No need to create new role, use the existing one

        // Auth untuk KOP001 - KOP101: Data Anggota
        DB::table('sys_auth')->insert([
            'idroles' => 'admins',
            'gmenu' => 'KOP001',
            'dmenu' => 'KOP101',
            'add' => '1',
            'edit' => '1',  // edit = 0 sesuai permintaan
            'delete' => '1',
            'approval' => '1',
            'print' => '1',
            'excel' => '1',
            'pdf' => '1',
            'value' => '1',
            'rules' => '0', // rules = 0 sesuai permintaan
            'isactive' => '1'
        ]);

        // Auth untuk KOP001 - KOP102: Master Paket Pinjaman
        DB::table('sys_auth')->insert([
            'idroles' => 'admins',
            'gmenu' => 'KOP001',
            'dmenu' => 'KOP102',
            'add' => '1',
            'edit' => '1',
            'delete' => '1',
            'approval' => '1',
            'print' => '1',
            'excel' => '1',
            'pdf' => '1',
            'value' => '1',
            'rules' => '0',
            'isactive' => '1'
        ]);

        // Auth untuk KOP001 - KOP103: Master Tenor
        DB::table('sys_auth')->insert([
            'idroles' => 'admins',
            'gmenu' => 'KOP001',
            'dmenu' => 'KOP103',
            'add' => '1',
            'edit' => '1',
            'delete' => '1',
            'approval' => '1',
            'print' => '1',
            'excel' => '1',
            'pdf' => '1',
            'value' => '1',
            'rules' => '0',
            'isactive' => '1'
        ]);

        // Auth untuk KOP001 - KOP104: Konfigurasi Koperasi
        DB::table('sys_auth')->insert([
            'idroles' => 'admins',
            'gmenu' => 'KOP001',
            'dmenu' => 'KOP104',
            'add' => '1',
            'edit' => '1',
            'delete' => '1',
            'approval' => '1',
            'print' => '1',
            'excel' => '1',
            'pdf' => '1',
            'value' => '1',
            'rules' => '0',
            'isactive' => '1'
        ]);

        // Auth untuk KOP002 - KOP201: Pengajuan Pinjaman
        DB::table('sys_auth')->insert([
            'idroles' => 'admins',
            'gmenu' => 'KOP002',
            'dmenu' => 'KOP201',
            'add' => '1',
            'edit' => '1',
            'delete' => '1',
            'approval' => '1',
            'print' => '1',
            'excel' => '1',
            'pdf' => '1',
            'value' => '1',
            'rules' => '0',
            'isactive' => '1'
        ]);

        // Auth untuk KOP002 - KOP202: Approval Pinjaman
        DB::table('sys_auth')->insert([
            'idroles' => 'admins',
            'gmenu' => 'KOP002',
            'dmenu' => 'KOP202',
            'add' => '1',
            'edit' => '1',
            'delete' => '1',
            'approval' => '1',
            'print' => '1',
            'excel' => '1',
            'pdf' => '1',
            'value' => '1',
            'rules' => '0',
            'isactive' => '1'
        ]);

        // Auth untuk KOP002 - KOP203: Data Pinjaman
        DB::table('sys_auth')->insert([
            'idroles' => 'admins',
            'gmenu' => 'KOP002',
            'dmenu' => 'KOP203',
            'add' => '1',
            'edit' => '1',
            'delete' => '1',
            'approval' => '1',
            'print' => '1',
            'excel' => '1',
            'pdf' => '1',
            'value' => '1',
            'rules' => '0',
            'isactive' => '1'
        ]);

        // Auth untuk KOP002 - KOP204: Cicilan Pinjaman
        DB::table('sys_auth')->insert([
            'idroles' => 'admins',
            'gmenu' => 'KOP002',
            'dmenu' => 'KOP204',
            'add' => '1',
            'edit' => '1',
            'delete' => '1',
            'approval' => '1',
            'print' => '1',
            'excel' => '1',
            'pdf' => '1',
            'value' => '1',
            'rules' => '0',
            'isactive' => '1'
        ]);

        // Auth untuk KOP003 - KOP301: Iuran Anggota
        DB::table('sys_auth')->insert([
            'idroles' => 'admins',
            'gmenu' => 'KOP003',
            'dmenu' => 'KOP301',
            'add' => '1',
            'edit' => '1',
            'delete' => '1',
            'approval' => '1',
            'print' => '1',
            'excel' => '1',
            'pdf' => '1',
            'value' => '1',
            'rules' => '0',
            'isactive' => '1'
        ]);

        // Auth untuk KOP003 - KOP302: Transfer Dana
        DB::table('sys_auth')->insert([
            'idroles' => 'admins',
            'gmenu' => 'KOP003',
            'dmenu' => 'KOP302',
            'add' => '1',
            'edit' => '1',
            'delete' => '1',
            'approval' => '1',
            'print' => '1',
            'excel' => '1',
            'pdf' => '1',
            'value' => '1',
            'rules' => '0',
            'isactive' => '1'
        ]);

        // Auth untuk KOP003 - KOP303: SHU
        DB::table('sys_auth')->insert([
            'idroles' => 'admins',
            'gmenu' => 'KOP003',
            'dmenu' => 'KOP303',
            'add' => '1',
            'edit' => '1',
            'delete' => '1',
            'approval' => '1',
            'print' => '1',
            'excel' => '1',
            'pdf' => '1',
            'value' => '1',
            'rules' => '0',
            'isactive' => '1'
        ]);

        // Auth untuk KOP003 - KOP304: Jurnal Keuangan
        DB::table('sys_auth')->insert([
            'idroles' => 'admins',
            'gmenu' => 'KOP003',
            'dmenu' => 'KOP304',
            'add' => '1',
            'edit' => '1',
            'delete' => '1',
            'approval' => '1',
            'print' => '1',
            'excel' => '1',
            'pdf' => '1',
            'value' => '1',
            'rules' => '0',
            'isactive' => '1'
        ]);

        // Auth untuk KOP004 - KOP401: Dashboard
        DB::table('sys_auth')->insert([
            'idroles' => 'admins',
            'gmenu' => 'KOP004',
            'dmenu' => 'KOP401',
            'add' => '1',
            'edit' => '1',
            'delete' => '1',
            'approval' => '1',
            'print' => '1',
            'excel' => '1',
            'pdf' => '1',
            'value' => '1',
            'rules' => '0',
            'isactive' => '1'
        ]);

        // Auth untuk KOP004 - KOP402: Monitoring
        DB::table('sys_auth')->insert([
            'idroles' => 'admins',
            'gmenu' => 'KOP004',
            'dmenu' => 'KOP402',
            'add' => '1',
            'edit' => '1',
            'delete' => '1',
            'approval' => '1',
            'print' => '1',
            'excel' => '1',
            'pdf' => '1',
            'value' => '1',
            'rules' => '0',
            'isactive' => '1'
        ]);

        // Auth untuk KOP004 - KOP403: Laporan Laba Rugi
        DB::table('sys_auth')->insert([
            'idroles' => 'admins',
            'gmenu' => 'KOP004',
            'dmenu' => 'KOP403',
            'add' => '1',
            'edit' => '1',
            'delete' => '1',
            'approval' => '1',
            'print' => '1',
            'excel' => '1',
            'pdf' => '1',
            'value' => '1',
            'rules' => '0',
            'isactive' => '1'
        ]);

        // Auth untuk KOP004 - KOP404: Laporan Neraca
        DB::table('sys_auth')->insert([
            'idroles' => 'admins',
            'gmenu' => 'KOP004',
            'dmenu' => 'KOP404',
            'add' => '1',
            'edit' => '1',
            'delete' => '1',
            'approval' => '1',
            'print' => '1',
            'excel' => '1',
            'pdf' => '1',
            'value' => '1',
            'rules' => '0',
            'isactive' => '1'
        ]);
    }
}
