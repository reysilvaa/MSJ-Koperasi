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

        // Define all menu items with their groups
        $menuItems = [
            // KOP001 - Master Data
            ['gmenu' => 'KOP001', 'dmenu' => 'KOP101'],
            ['gmenu' => 'KOP001', 'dmenu' => 'KOP102'],
            ['gmenu' => 'KOP001', 'dmenu' => 'KOP103'],

            // KOP002 - Pinjaman
            ['gmenu' => 'KOP002', 'dmenu' => 'KOP201'],
            ['gmenu' => 'KOP002', 'dmenu' => 'KOP202'],
            ['gmenu' => 'KOP002', 'dmenu' => 'KOP203'],

            // KOP003 - Pencairan
            ['gmenu' => 'KOP003', 'dmenu' => 'KOP301'],
            ['gmenu' => 'KOP003', 'dmenu' => 'KOP302'],

            // KOP004 - Keuangan
            ['gmenu' => 'KOP004', 'dmenu' => 'KOP401'],
            ['gmenu' => 'KOP004', 'dmenu' => 'KOP402'],
            ['gmenu' => 'KOP004', 'dmenu' => 'KOP403'],

            // KOP005 - Laporan
            ['gmenu' => 'KOP005', 'dmenu' => 'KOP501'],
            ['gmenu' => 'KOP005', 'dmenu' => 'KOP502'],
            ['gmenu' => 'KOP005', 'dmenu' => 'KOP503'],
            ['gmenu' => 'KOP005', 'dmenu' => 'KOP504'], // Neraca
            ['gmenu' => 'KOP005', 'dmenu' => 'KOP505'], // Laba Rugi
            ['gmenu' => 'KOP005', 'dmenu' => 'KOP506'], // Cash Flow
            ['gmenu' => 'KOP005', 'dmenu' => 'KOP507'], // SHU
            ['gmenu' => 'KOP005', 'dmenu' => 'KOP508'], // Jurnal Umum
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
            

            // Admin Kredit - Credit analysis and approval focus
            'akredt' => [
                'add' => '1', 'edit' => '1', 'delete' => '0', 'approval' => '1',
                'print' => '1', 'excel' => '1', 'pdf' => '1', 'value' => '1',
                'rules' => '0', 'isactive' => '1'
            ],

            // Admin Transfer - Transfer and disbursement focus
            'atrans' => [
                'add' => '1', 'edit' => '1', 'delete' => '0', 'approval' => '1',
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
                // Special permissions for specific menus based on activity diagram
                $menuPermissions = $permissions;

                // Anggota special rules
                if ($roleId === 'anggot') {
                    if (in_array($menu['dmenu'], ['KOP201'])) { // Pengajuan Pinjaman
                        $menuPermissions['add'] = '1'; // Anggota bisa mengajukan pinjaman
                        $menuPermissions['edit'] = '1'; // Edit pengajuan sendiri
                    }
                    if (in_array($menu['dmenu'], ['KOP101', 'KOP102', 'KOP103', 'KOP202', 'KOP301', 'KOP302'])) {
                        $menuPermissions['value'] = '0'; // No access to master data and admin functions
                        $menuPermissions['isactive'] = '0';
                    }
                }

                // Admin Kredit special rules (Fokus pada credit analysis)
                if ($roleId === 'akredt') {
                    if (in_array($menu['dmenu'], ['KOP202'])) { // Approval Pinjaman
                        $menuPermissions['approval'] = '1'; // Strong approval rights
                    }
                    if (in_array($menu['dmenu'], ['KOP301', 'KOP302', 'KOP401', 'KOP402'])) { // Limited access to transfer/finance
                        $menuPermissions['add'] = '0';
                        $menuPermissions['edit'] = '0';
                    }
                }

                // Admin Transfer special rules (Fokus pada pencairan dan transfer)
                if ($roleId === 'atrans') {
                    if (in_array($menu['dmenu'], ['KOP301', 'KOP302', 'KOP401', 'KOP402'])) { // Transfer & Finance
                        $menuPermissions['approval'] = '1'; // Strong approval for transfers
                    }
                    if (in_array($menu['dmenu'], ['KOP101', 'KOP102', 'KOP103', 'KOP202'])) { // Limited access to master & approval
                        $menuPermissions['add'] = '0';
                        $menuPermissions['delete'] = '0';
                    }
                }

                $authRecords[] = array_merge([
                    'idroles' => $roleId,
                    'gmenu' => $menu['gmenu'],
                    'dmenu' => $menu['dmenu']
                ], $menuPermissions);
            }
        }

        // Insert all records at once
        DB::table('sys_auth')->insert($authRecords);
    }
}
