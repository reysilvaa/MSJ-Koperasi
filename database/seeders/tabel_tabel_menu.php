<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class tabel_tabel_menu extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('sys_table')->where(['gmenu' => 'system', 'dmenu' => 'tablex'])->delete();

        DB::table('sys_table')->insert([
            'gmenu' => 'system',
            'dmenu' => 'tablex',
            'urut' => '1',
            'field' => 'gmenu',
            'alias' => 'Group Menu',
            'type' => 'enum',
            'length' => '6',
            'decimals' => '0',
            'default' => '',
            'validate' => 'required|max:6|min:6|unique:sys_auth,gmenu',
            'primary' => '1',
            'filter' => '1',
            'list' => '1',
            'show' => '1',
            'query' => "select gmenu, name from sys_gmenu where isactive = '1'",
            'position' => '1'
        ]);

        DB::table('sys_table')->insert([
            'gmenu' => 'system',
            'dmenu' => 'tablex',
            'urut' => '2',
            'field' => 'dmenu',
            'alias' => 'Nama Menu',
            'type' => 'enum',
            'length' => '6',
            'decimals' => '0',
            'default' => '',
            'validate' => 'required|max:6|min:6|unique:sys_auth,dmenu',
            'primary' => '1',
            'filter' => '1',
            'list' => '1',
            'show' => '1',
            'sub' => 'gmenu',
            'query' => "select dmenu, name, gmenu from sys_dmenu where isactive = '1'",
            'position' => '1'
        ]);

        DB::table('sys_table')->insert([
            'gmenu' => 'system',
            'dmenu' => 'tablex',
            'urut' => '3',
            'field' => 'urut',
            'alias' => 'Urut',
            'type' => 'number',
            'length' => '50',
            'decimals' => '0',
            'default' => '',
            'validate' => 'required|numeric|between:1,50',
            'primary' => '1',
            'filter' => '1',
            'list' => '1',
            'show' => '1',
            'query' => '',
            'note' => 'Posisi urut kolom',
            'position' => '2'
        ]);

        DB::table('sys_table')->insert([
            'gmenu' => 'system',
            'dmenu' => 'tablex',
            'urut' => '4',
            'field' => 'field',
            'alias' => 'Field Kolom',
            'type' => 'string',
            'length' => '25',
            'decimals' => '0',
            'default' => '',
            'validate' => 'required|max:25|min:2',
            'primary' => '0',
            'filter' => '1',
            'list' => '1',
            'show' => '1',
            'query' => '',
            'note' => 'Nama Kolom Di Database',
            'position' => '2'
        ]);

        DB::table('sys_table')->insert([
            'gmenu' => 'system',
            'dmenu' => 'tablex',
            'urut' => '5',
            'field' => 'alias',
            'alias' => 'Alias Kolom',
            'type' => 'string',
            'length' => '50',
            'decimals' => '0',
            'default' => '',
            'validate' => 'required|max:50|min:2',
            'primary' => '0',
            'filter' => '1',
            'list' => '1',
            'show' => '1',
            'query' => '',
            'note' => 'Nama Kolom Yang Tampil Di User',
            'position' => '2'
        ]);

        DB::table('sys_table')->insert([
            'gmenu' => 'system',
            'dmenu' => 'tablex',
            'urut' => '6',
            'field' => 'type',
            'alias' => 'Type Kolom',
            'type' => 'enum',
            'length' => '50',
            'decimals' => '0',
            'default' => '',
            'validate' => 'required|max:50|min:2',
            'primary' => '0',
            'filter' => '1',
            'list' => '1',
            'show' => '1',
            'query' => "select value, name from sys_enum where idenum = 'type' and isactive = '1'",
            'note' => 'Tipe Kolom',
            'position' => '2'
        ]);

        DB::table('sys_table')->insert([
            'gmenu' => 'system',
            'dmenu' => 'tablex',
            'urut' => '7',
            'field' => 'length',
            'alias' => 'Length',
            'type' => 'number',
            'length' => '5000',
            'decimals' => '0',
            'default' => '',
            'validate' => 'nullable|numeric|between:0,5000',
            'primary' => '0',
            'filter' => '1',
            'list' => '1',
            'show' => '1',
            'query' => '',
            'note' => 'Panjang Karakter',
            'position' => '2'
        ]);

        DB::table('sys_table')->insert([
            'gmenu' => 'system',
            'dmenu' => 'tablex',
            'urut' => '8',
            'field' => 'default',
            'alias' => 'Default',
            'type' => 'string',
            'length' => '20',
            'decimals' => '0',
            'default' => '',
            'validate' => 'nullable|max:20|min:1',
            'primary' => '0',
            'filter' => '1',
            'list' => '1',
            'show' => '1',
            'query' => '',
            'note' => 'Default Value',
            'position' => '2'
        ]);

        DB::table('sys_table')->insert([
            'gmenu' => 'system',
            'dmenu' => 'tablex',
            'urut' => '9',
            'field' => 'validate',
            'alias' => 'Validasi',
            'type' => 'string',
            'length' => '100',
            'decimals' => '0',
            'default' => '',
            'validate' => 'required|max:100|min:2',
            'primary' => '0',
            'filter' => '1',
            'list' => '1',
            'show' => '1',
            'query' => '',
            'note' => 'Validasi Data',
            'position' => '2'
        ]);

        DB::table('sys_table')->insert([
            'gmenu' => 'system',
            'dmenu' => 'tablex',
            'urut' => '10',
            'field' => 'primary',
            'alias' => 'Primary Key',
            'type' => 'enum',
            'length' => '1',
            'decimals' => '0',
            'default' => '',
            'validate' => 'required|max:1|min:1',
            'primary' => '0',
            'filter' => '1',
            'list' => '1',
            'show' => '1',
            'query' => "select value, name from sys_enum where idenum = 'primary' and isactive = '1'",
            'note' => 'Sebagai Primary Key Atau Tidak. Jika Unique Maka Validasi Diisi "unique:tabel,kolom"',
            'position' => '2'
        ]);

        DB::table('sys_table')->insert([
            'gmenu' => 'system',
            'dmenu' => 'tablex',
            'urut' => '11',
            'field' => 'generateid',
            'alias' => 'Generate ID',
            'type' => 'string',
            'length' => '25',
            'decimals' => '0',
            'default' => '',
            'validate' => 'nullable|max:25|min:1',
            'primary' => '0',
            'filter' => '1',
            'list' => '1',
            'show' => '1',
            'query' => '',
            'note' => 'Kosongi Jika ID Diisi Manual Atau Tulis Nama Kolom Yg Dijadikan Acuan ID',
            'position' => '2'
        ]);

        DB::table('sys_table')->insert([
            'gmenu' => 'system',
            'dmenu' => 'tablex',
            'urut' => '12',
            'field' => 'filter',
            'alias' => 'Filter',
            'type' => 'enum',
            'length' => '1',
            'decimals' => '0',
            'default' => '',
            'validate' => 'required|max:1|min:1',
            'primary' => '0',
            'filter' => '1',
            'list' => '1',
            'show' => '1',
            'query' => "select value, name from sys_enum where idenum = 'questions' and isactive = '1'",
            'note' => 'Sebagai Filter Report Atau Bisa Edit Atau Tidak',
            'position' => '2'
        ]);

        DB::table('sys_table')->insert([
            'gmenu' => 'system',
            'dmenu' => 'tablex',
            'urut' => '13',
            'field' => 'list',
            'alias' => 'List',
            'type' => 'enum',
            'length' => '1',
            'decimals' => '0',
            'default' => '',
            'validate' => 'required|max:1|min:1',
            'primary' => '0',
            'filter' => '1',
            'list' => '1',
            'show' => '1',
            'query' => "select value, name from sys_enum where idenum = 'questions' and isactive = '1'",
            'note' => 'Apakah Ditampilkan Di List Atau Tidak',
            'position' => '2'
        ]);

        DB::table('sys_table')->insert([
            'gmenu' => 'system',
            'dmenu' => 'tablex',
            'urut' => '14',
            'field' => 'show',
            'alias' => 'Show',
            'type' => 'enum',
            'length' => '1',
            'decimals' => '0',
            'default' => '',
            'validate' => 'required|max:1|min:1',
            'primary' => '0',
            'filter' => '1',
            'list' => '1',
            'show' => '1',
            'query' => "select value, name from sys_enum where idenum = 'questions' and isactive = '1'",
            'note' => 'Apakah Ditampilkan Saat Menu Tambah Atau Tidak',
            'position' => '2'
        ]);

        DB::table('sys_table')->insert([
            'gmenu' => 'system',
            'dmenu' => 'tablex',
            'urut' => '15',
            'field' => 'query',
            'alias' => 'Query',
            'type' => 'text',
            'length' => '5000',
            'decimals' => '0',
            'default' => '',
            'validate' => 'nullable|max:5000|min:1',
            'primary' => '0',
            'filter' => '1',
            'list' => '0',
            'show' => '1',
            'query' => '',
            'note' => 'Tipe Enum Wajib Diisi Query, Jika Report Pastikan Query Dalam 1 Baris Dan Untuk Value Pada WHERE Harus Di Kasih ":" Pada Awal Kata',
            'position' => '2'
        ]);

        DB::table('sys_table')->insert([
            'gmenu' => 'system',
            'dmenu' => 'tablex',
            'urut' => '16',
            'field' => 'class',
            'alias' => 'Class',
            'type' => 'text',
            'length' => '255',
            'decimals' => '0',
            'default' => '',
            'validate' => 'nullable|max:255|min:1',
            'primary' => '0',
            'filter' => '1',
            'list' => '1',
            'show' => '1',
            'query' => '',
            'note' => 'Isi Class Tambahan Jika Ada',
            'position' => '2'
        ]);

        DB::table('sys_table')->insert([
            'gmenu' => 'system',
            'dmenu' => 'tablex',
            'urut' => '17',
            'field' => 'sub',
            'alias' => 'Sub',
            'type' => 'text',
            'length' => '255',
            'decimals' => '0',
            'default' => '',
            'validate' => 'nullable|max:255|min:1',
            'primary' => '0',
            'filter' => '1',
            'list' => '1',
            'show' => '1',
            'query' => '',
            'note' => 'Isi Nama Kolom Yg Di Jadikan Sub Header',
            'position' => '2'
        ]);

        DB::table('sys_table')->insert([
            'gmenu' => 'system',
            'dmenu' => 'tablex',
            'urut' => '18',
            'field' => 'link',
            'alias' => 'Link',
            'type' => 'string',
            'length' => '100',
            'decimals' => '0',
            'default' => '',
            'validate' => 'nullable|max:100|min:2',
            'primary' => '0',
            'filter' => '1',
            'list' => '1',
            'show' => '1',
            'query' => '',
            'note' => 'Isi Link Tujuan',
            'position' => '2'
        ]);

        DB::table('sys_table')->insert([
            'gmenu' => 'system',
            'dmenu' => 'tablex',
            'urut' => '19',
            'field' => 'note',
            'alias' => 'Catatan',
            'type' => 'text',
            'length' => '255',
            'decimals' => '0',
            'default' => '',
            'validate' => 'nullable|max:255|min:1',
            'primary' => '0',
            'filter' => '1',
            'list' => '1',
            'show' => '1',
            'query' => '',
            'note' => 'Isi Catatan Tambahan Jika Ada, Tulisan Ini Tampil Dibawah Form Input',
            'position' => '2'
        ]);

        DB::table('sys_table')->insert([
            'gmenu' => 'system',
            'dmenu' => 'tablex',
            'urut' => '20',
            'field' => 'position',
            'alias' => 'Posisi',
            'type' => 'enum',
            'length' => '1',
            'decimals' => '0',
            'default' => '0',
            'validate' => 'nullable',
            'primary' => '0',
            'filter' => '1',
            'list' => '1',
            'show' => '1',
            'query' => "select value, name from sys_enum where idenum = 'position' and isactive = '1'",
            'note' => 'Mengatur Posisi Kolom Sebagai Apa',
            'position' => '2'
        ]);

        DB::table('sys_table')->insert([
            'gmenu' => 'system',
            'dmenu' => 'tablex',
            'urut' => '21',
            'field' => 'isactive',
            'alias' => 'Status',
            'type' => 'enum',
            'length' => '1',
            'decimals' => '0',
            'default' => '1',
            'validate' => '',
            'primary' => '0',
            'filter' => '1',
            'list' => '1',
            'show' => '0',
            'query' => "select value, name from sys_enum where idenum = 'isactive' and isactive = '1'",
            'position' => '0'
        ]);
    }
}
