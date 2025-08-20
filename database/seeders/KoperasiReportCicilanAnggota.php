<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class KoperasiReportCicilanAnggota extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Clear existing data for KOP203
        DB::table('sys_table')->where(['gmenu' => 'KOP002', 'dmenu' => 'KOP203'])->delete();

        // Query Report untuk Cicilan Anggota
        DB::table('sys_table')->insert([
            'gmenu' => 'KOP002',
            'dmenu' => 'KOP203',
            'urut' => 1,
            'field' => 'query',
            'alias' => 'Cicilan Anggota Query',
            'type' => 'report',
            'length' => 0,
            'decimals' => '0',
            'default' => '',
            'validate' => '',
            'primary' => '0',
            'generateid' => '',
            'filter' => '0',
            'list' => '1',
            'show' => '1',
            'query' => "SELECT
                u.nomor_anggota,
                u.nama_lengkap,
                p.nomor_pinjaman,
                cp.angsuran_ke,
                cp.tanggal_jatuh_tempo,
                cp.tanggal_bayar,
                MONTH(cp.tanggal_jatuh_tempo) as bulan,
                MONTHNAME(cp.tanggal_jatuh_tempo) as nama_bulan,
                YEAR(cp.tanggal_jatuh_tempo) as tahun,
                cp.nominal_pokok,
                cp.nominal_bunga,
                cp.total_bayar,
                cp.metode_pembayaran,
                cp.nomor_transaksi,
                cp.keterangan,
                CASE
                    WHEN cp.tanggal_bayar IS NOT NULL THEN 'Lunas'
                    WHEN cp.tanggal_jatuh_tempo < CURDATE() THEN 'Terlambat'
                    ELSE 'Belum Bayar'
                END as status_pembayaran,
                CASE
                    WHEN cp.tanggal_bayar IS NOT NULL THEN DATEDIFF(cp.tanggal_bayar, cp.tanggal_jatuh_tempo)
                    WHEN cp.tanggal_jatuh_tempo < CURDATE() THEN DATEDIFF(CURDATE(), cp.tanggal_jatuh_tempo)
                    ELSE 0
                END as hari_keterlambatan
            FROM cicilan_pinjaman cp
            INNER JOIN pinjaman p ON cp.pinjaman_id = p.id
            INNER JOIN users u ON p.anggota_id = u.id
            WHERE p.isactive = '1'
                AND cp.isactive = '1'
                AND u.nomor_anggota IS NOT NULL
                AND YEAR(cp.tanggal_jatuh_tempo) = :tahun
            ORDER BY u.nama_lengkap, cp.tanggal_jatuh_tempo, cp.angsuran_ke",
            'class' => '',
            'sub' => '',
            'link' => '',
            'note' => '',
            'position' => '1',
            'isactive' => '1',
            'created_at' => now(),
            'updated_at' => now(),
            'user_create' => 'seeder',
            'user_update' => 'seeder'
        ]);

        // Filter Tahun - input tahun biasa
        DB::table('sys_table')->insert([
            'gmenu' => 'KOP002',
            'dmenu' => 'KOP203',
            'urut' => 2,
            'field' => 'tahun',
            'alias' => 'Tahun',
            'type' => 'number',
            'length' => 4,
            'decimals' => '0',
            'default' => date('Y'),
            'validate' => 'required|numeric|min:2020|max:2030',
            'primary' => '0',
            'generateid' => '',
            'filter' => '1',
            'list' => '0',
            'show' => '0',
            'query' => '',
            'class' => '',
            'sub' => '',
            'link' => '',
            'note' => 'Masukkan tahun (contoh: 2024)',
            'position' => '2',
            'isactive' => '1',
            'created_at' => now(),
            'updated_at' => now(),
            'user_create' => 'seeder',
            'user_update' => 'seeder'
        ]);

        echo "TableKOP203Seeder completed successfully.\n";
    }
}
