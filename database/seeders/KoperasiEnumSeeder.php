<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class KoperasiEnumSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Hapus enum lama untuk idenum terkait agar tidak duplikat
        DB::table('sys_enum')->whereIn('idenum', ['simpanan', 'shuprs', 'isbayar'])->delete();

        // Insert enum baru sesuai permintaan
        DB::table('sys_enum')->insert([
            // simpanan
            ['idenum' => 'simpanan', 'value' => '25000', 'name' => 'Wajib'],
            ['idenum' => 'simpanan', 'value' => '50000', 'name' => 'Pokok'],

            // shuprs (proporsi SHU)
            ['idenum' => 'shuprs', 'value' => '0.45', 'name' => 'Simpanan'],
            ['idenum' => 'shuprs', 'value' => '0.35', 'name' => 'Bunga'],

            // isbayar
            ['idenum' => 'isbayar', 'value' => '1', 'name' => 'Bayar'],
            ['idenum' => 'isbayar', 'value' => '0', 'name' => 'Belum Bayar'],
        ]);
    }
}
