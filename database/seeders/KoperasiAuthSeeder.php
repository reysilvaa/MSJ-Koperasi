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

        // Define all menu items with their groups
        $menuItems = [
            // KOP001 - Master Data
            ['gmenu' => 'KOP001', 'dmenu' => 'KOP101'],
            ['gmenu' => 'KOP001', 'dmenu' => 'KOP102'],
            ['gmenu' => 'KOP001', 'dmenu' => 'KOP103'],
            ['gmenu' => 'KOP001', 'dmenu' => 'KOP104'],

            // KOP002 - Pinjaman
            ['gmenu' => 'KOP002', 'dmenu' => 'KOP201'],
            ['gmenu' => 'KOP002', 'dmenu' => 'KOP202'],
            ['gmenu' => 'KOP002', 'dmenu' => 'KOP203'],
            ['gmenu' => 'KOP002', 'dmenu' => 'KOP204'],

            // KOP003 - Keuangan
            ['gmenu' => 'KOP003', 'dmenu' => 'KOP301'],
            ['gmenu' => 'KOP003', 'dmenu' => 'KOP302'],
            ['gmenu' => 'KOP003', 'dmenu' => 'KOP303'],
            ['gmenu' => 'KOP003', 'dmenu' => 'KOP304'],

            // KOP004 - Laporan
            ['gmenu' => 'KOP004', 'dmenu' => 'KOP401'],
            ['gmenu' => 'KOP004', 'dmenu' => 'KOP402'],
            ['gmenu' => 'KOP004', 'dmenu' => 'KOP403'],
            ['gmenu' => 'KOP004', 'dmenu' => 'KOP404'],
            ['gmenu' => 'KOP004', 'dmenu' => 'KOP405'],

            // KOP005 - Sistem
            ['gmenu' => 'KOP005', 'dmenu' => 'KOP501'],
            ['gmenu' => 'KOP005', 'dmenu' => 'KOP502'],
        ];

        // Default authorization template
        $authTemplate = [
            'idroles' => 'admins',
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
        ];

        // Create authorization records for all menu items
        $authRecords = [];
        foreach ($menuItems as $menu) {
            $authRecords[] = array_merge($authTemplate, $menu);
        }

        // Insert all records at once
        DB::table('sys_auth')->insert($authRecords);
    }
}
