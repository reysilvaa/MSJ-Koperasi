<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class tabel_rpt_seeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //tabel_menu        
        DB::table('sys_table')->where(['gmenu' => 'report', 'dmenu' => 'rpseed'])->delete();

        DB::table('sys_table')->insert([
            'gmenu' => 'report',
            'dmenu' => 'rpseed',
            'urut' => '1',
            'field' => 'query',
            'alias' => 'Query',
            'type' => 'text',
            'length' => '255',
            'validate' => 'max:255',
            'filter' => '1',
            'class' => 'lower',
            'query' => ""
        ]);
    }
}
