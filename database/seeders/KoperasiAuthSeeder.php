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
        // Delete existing data first - hapus sys_auth dulu karena foreign key
        DB::table('sys_auth')->where('idroles', 'anggot')->delete();
        DB::table('sys_auth')->where('idroles', 'kadmin')->delete();
        DB::table('sys_auth')->where('idroles', 'akredt')->delete();
        DB::table('sys_auth')->where('idroles', 'atrans')->delete();
        DB::table('sys_auth')->where('idroles', 'ketuum')->delete();
        DB::table('sys_auth')->where('gmenu', 'like', 'KOP%')->delete();
        DB::table('sys_roles')->whereIn('idroles', ['anggot', 'kadmin', 'akredt', 'atrans', 'ketuum'])->delete();

        // Insert sys_roles first - sesuai dengan activity diagram (5 roles)
        DB::table('sys_roles')->insert([
            [
                'idroles' => 'anggot',
                'name' => 'Anggota',
                'description' => 'Anggota Koperasi - Pengajuan Pinjaman dan Kelola Iuran'
            ],
            [
                'idroles' => 'kadmin',
                'name' => 'Ketua Admin',
                'description' => 'Ketua Admin/Admin Koperasi - Verifikasi, Kelola Data, Master Periode'
            ],
            [
                'idroles' => 'akredt',
                'name' => 'Admin Kredit',
                'description' => 'Ketua Panitia Kredit - Review Pengajuan, Analisis Kelayakan, Credit Scoring'
            ],
            [
                'idroles' => 'atrans',
                'name' => 'Admin Transfer',
                'description' => 'Admin Transfer - Proses Transfer Dana, Banking Integration, Pencairan'
            ],
            [
                'idroles' => 'ketuum',
                'name' => 'Ketua Umum',
                'description' => 'Ketua Umum - Final Approval, Dashboard Eksekutif, SHU, Strategic Planning'
            ]
        ]);

        // Dashboard authorization - semua role koperasi bisa akses dashboard
        DB::table('sys_auth')->insert([
            [
                'idroles' => 'anggot',
                'gmenu' => 'blankx',
                'dmenu' => 'dashbr',
                'add' => '0',
                'edit' => '0',
                'delete' => '0'
            ],
            [
                'idroles' => 'kadmin',
                'gmenu' => 'blankx',
                'dmenu' => 'dashbr',
                'add' => '1',
                'edit' => '1',
                'delete' => '1'
            ],
            [
                'idroles' => 'akredt',
                'gmenu' => 'blankx',
                'dmenu' => 'dashbr',
                'add' => '1',
                'edit' => '1',
                'delete' => '0'
            ],
            [
                'idroles' => 'atrans',
                'gmenu' => 'blankx',
                'dmenu' => 'dashbr',
                'add' => '1',
                'edit' => '1',
                'delete' => '0'
            ],
            [
                'idroles' => 'ketuum',
                'gmenu' => 'blankx',
                'dmenu' => 'dashbr',
                'add' => '1',
                'edit' => '1',
                'delete' => '1'
            ]
        ]);

        // Define menu items in scope (berdasarkan migration yang tersedia)
        $menuItems = [
            // Master Data
            ['gmenu' => 'KOP001', 'dmenu' => 'KOP101'], // mst_anggota
            ['gmenu' => 'KOP001', 'dmenu' => 'KOP102'], // mst_paket
            ['gmenu' => 'KOP001', 'dmenu' => 'KOP103'], // mst_periode

            // Pinjaman
            ['gmenu' => 'KOP002', 'dmenu' => 'KOP201'], // trs_piutang

            // Keuangan
            ['gmenu' => 'KOP004', 'dmenu' => 'KOP401'], // trs_potongan
            ['gmenu' => 'KOP004', 'dmenu' => 'KOP402'], // trs_cicilan
            ['gmenu' => 'KOP004', 'dmenu' => 'KOP403'], // trs_shu

            // Pengguna (users)
            ['gmenu' => 'KOP005', 'dmenu' => 'KOP501'], // users
        ];

                // Authorization configurations based on activity diagram roles (5 roles)
        $authConfigs = [
            // Anggota - Limited access, mainly view and pengajuan pinjaman
            'anggot' => [
                'add' => '0', 'edit' => '0', 'delete' => '0', 'approval' => '0',
                'print' => '1', 'excel' => '0', 'pdf' => '1', 'value' => '1',
                'rules' => '0', 'isactive' => '1'
            ],

            // Ketua Admin - Full access to all modules
            'kadmin' => [
                'add' => '1', 'edit' => '1', 'delete' => '1', 'approval' => '1',
                'print' => '1', 'excel' => '1', 'pdf' => '1', 'value' => '1',
                'rules' => '0', 'isactive' => '1'
            ],


            // Admin Kredit - Credit analysis and approval focus (NO ADD/EDIT/DELETE)
            'akredt' => [
                'add' => '0', 'edit' => '0', 'delete' => '0', 'approval' => '1',
                'print' => '1', 'excel' => '1', 'pdf' => '1', 'value' => '1',
                'rules' => '0', 'isactive' => '1'
            ],

            // Admin Transfer - Transfer and disbursement focus (NO ADD/EDIT/DELETE)
            'atrans' => [
                'add' => '0', 'edit' => '0', 'delete' => '0', 'approval' => '1',
                'print' => '1', 'excel' => '1', 'pdf' => '1', 'value' => '1',
                'rules' => '0', 'isactive' => '1'
            ],

            // Ketua Umum - Strategic and final approval
            'ketuum' => [
                'add' => '1', 'edit' => '1', 'delete' => '1', 'approval' => '1',
                'print' => '1', 'excel' => '1', 'pdf' => '1', 'value' => '1',
                'rules' => '0', 'isactive' => '1'
            ]
        ];

        // Create authorization records for all roles and menu combinations
        $authRecords = [];

        foreach ($authConfigs as $roleId => $permissions) {
            foreach ($menuItems as $menu) {
                $authRecords[] = array_merge([
                    'idroles' => $roleId,
                    'gmenu' => $menu['gmenu'],
                    'dmenu' => $menu['dmenu']
                ], $permissions);
            }
        }

        // Insert all records at once
        DB::table('sys_auth')->insert($authRecords);
    }
}
