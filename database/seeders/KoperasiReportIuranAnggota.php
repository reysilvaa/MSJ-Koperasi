<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class KoperasiReportIuranAnggota extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Clear existing data for KOP401
        DB::table('sys_table')->where(['gmenu' => 'KOP004', 'dmenu' => 'KOP401'])->delete();

        // Query Report untuk Laporan Iuran Bulanan
        DB::table('sys_table')->insert([
            'gmenu' => 'KOP004',
            'dmenu' => 'KOP401',
            'urut' => 1,
            'field' => 'query',
            'alias' => 'Laporan Iuran Bulanan Query',
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
    a.nomor_anggota AS nomor_anggota,
    a.nama_lengkap AS nama,

    -- Januari
    (CASE
        WHEN MONTH(a.tanggal_bergabung) = 1 AND YEAR(a.tanggal_bergabung) = :tahun
            THEN a.simpanan_pokok
        ELSE 0
    END) +
    (CASE
        WHEN YEAR(a.tanggal_bergabung) < :tahun
            OR (YEAR(a.tanggal_bergabung) = :tahun AND MONTH(a.tanggal_bergabung) <= 1)
            THEN a.simpanan_wajib_bulanan
        ELSE 0
    END) AS jan,

    -- Februari
    (CASE
        WHEN MONTH(a.tanggal_bergabung) = 2 AND YEAR(a.tanggal_bergabung) = :tahun
            THEN a.simpanan_pokok
        ELSE 0
    END) +
    (CASE
        WHEN YEAR(a.tanggal_bergabung) < :tahun
            OR (YEAR(a.tanggal_bergabung) = :tahun AND MONTH(a.tanggal_bergabung) <= 2)
            THEN a.simpanan_wajib_bulanan
        ELSE 0
    END) AS feb,

    -- Maret
    (CASE
        WHEN MONTH(a.tanggal_bergabung) = 3 AND YEAR(a.tanggal_bergabung) = :tahun
            THEN a.simpanan_pokok
        ELSE 0
    END) +
    (CASE
        WHEN YEAR(a.tanggal_bergabung) < :tahun
            OR (YEAR(a.tanggal_bergabung) = :tahun AND MONTH(a.tanggal_bergabung) <= 3)
            THEN a.simpanan_wajib_bulanan
        ELSE 0
    END) AS mar,

    -- April
    (CASE
        WHEN MONTH(a.tanggal_bergabung) = 4 AND YEAR(a.tanggal_bergabung) = :tahun
            THEN a.simpanan_pokok
        ELSE 0
    END) +
    (CASE
        WHEN YEAR(a.tanggal_bergabung) < :tahun
            OR (YEAR(a.tanggal_bergabung) = :tahun AND MONTH(a.tanggal_bergabung) <= 4)
            THEN a.simpanan_wajib_bulanan
        ELSE 0
    END) AS apr,

    -- Mei
    (CASE
        WHEN MONTH(a.tanggal_bergabung) = 5 AND YEAR(a.tanggal_bergabung) = :tahun
            THEN a.simpanan_pokok
        ELSE 0
    END) +
    (CASE
        WHEN YEAR(a.tanggal_bergabung) < :tahun
            OR (YEAR(a.tanggal_bergabung) = :tahun AND MONTH(a.tanggal_bergabung) <= 5)
            THEN a.simpanan_wajib_bulanan
        ELSE 0
    END) AS mei,

    -- Juni
    (CASE
        WHEN MONTH(a.tanggal_bergabung) = 6 AND YEAR(a.tanggal_bergabung) = :tahun
            THEN a.simpanan_pokok
        ELSE 0
    END) +
    (CASE
        WHEN YEAR(a.tanggal_bergabung) < :tahun
            OR (YEAR(a.tanggal_bergabung) = :tahun AND MONTH(a.tanggal_bergabung) <= 6)
            THEN a.simpanan_wajib_bulanan
        ELSE 0
    END) AS jun,

    -- Juli
    (CASE
        WHEN MONTH(a.tanggal_bergabung) = 7 AND YEAR(a.tanggal_bergabung) = :tahun
            THEN a.simpanan_pokok
        ELSE 0
    END) +
    (CASE
        WHEN YEAR(a.tanggal_bergabung) < :tahun
            OR (YEAR(a.tanggal_bergabung) = :tahun AND MONTH(a.tanggal_bergabung) <= 7)
            THEN a.simpanan_wajib_bulanan
        ELSE 0
    END) AS jul,

    -- Agustus
    (CASE
        WHEN MONTH(a.tanggal_bergabung) = 8 AND YEAR(a.tanggal_bergabung) = :tahun
            THEN a.simpanan_pokok
        ELSE 0
    END) +
    (CASE
        WHEN YEAR(a.tanggal_bergabung) < :tahun
            OR (YEAR(a.tanggal_bergabung) = :tahun AND MONTH(a.tanggal_bergabung) <= 8)
            THEN a.simpanan_wajib_bulanan
        ELSE 0
    END) AS agu,

    -- September
    (CASE
        WHEN MONTH(a.tanggal_bergabung) = 9 AND YEAR(a.tanggal_bergabung) = :tahun
            THEN a.simpanan_pokok
        ELSE 0
    END) +
    (CASE
        WHEN YEAR(a.tanggal_bergabung) < :tahun
            OR (YEAR(a.tanggal_bergabung) = :tahun AND MONTH(a.tanggal_bergabung) <= 9)
            THEN a.simpanan_wajib_bulanan
        ELSE 0
    END) AS sep,

    -- Oktober
    (CASE
        WHEN MONTH(a.tanggal_bergabung) = 10 AND YEAR(a.tanggal_bergabung) = :tahun
            THEN a.simpanan_pokok
        ELSE 0
    END) +
    (CASE
        WHEN YEAR(a.tanggal_bergabung) < :tahun
            OR (YEAR(a.tanggal_bergabung) = :tahun AND MONTH(a.tanggal_bergabung) <= 10)
            THEN a.simpanan_wajib_bulanan
        ELSE 0
    END) AS okt,

    -- November
    (CASE
        WHEN MONTH(a.tanggal_bergabung) = 11 AND YEAR(a.tanggal_bergabung) = :tahun
            THEN a.simpanan_pokok
        ELSE 0
    END) +
    (CASE
        WHEN YEAR(a.tanggal_bergabung) < :tahun
            OR (YEAR(a.tanggal_bergabung) = :tahun AND MONTH(a.tanggal_bergabung) <= 11)
            THEN a.simpanan_wajib_bulanan
        ELSE 0
    END) AS nov,

    -- Desember
    (CASE
        WHEN MONTH(a.tanggal_bergabung) = 12 AND YEAR(a.tanggal_bergabung) = :tahun
            THEN a.simpanan_pokok
        ELSE 0
    END) +
    (CASE
        WHEN YEAR(a.tanggal_bergabung) < :tahun
            OR (YEAR(a.tanggal_bergabung) = :tahun AND MONTH(a.tanggal_bergabung) <= 12)
            THEN a.simpanan_wajib_bulanan
        ELSE 0
    END) AS des,

    -- Total saldo
    a.simpanan_pokok +
    (CASE
        WHEN YEAR(a.tanggal_bergabung) < :tahun
            THEN a.simpanan_wajib_bulanan * 12
        WHEN YEAR(a.tanggal_bergabung) = :tahun
            THEN a.simpanan_wajib_bulanan * (13 - MONTH(a.tanggal_bergabung))
        ELSE 0
    END) AS total_saldo

FROM anggota a
WHERE a.isactive = '1'
ORDER BY a.nomor_anggota",
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
            'gmenu' => 'KOP004',
            'dmenu' => 'KOP401',
            'urut' => 2,
            'field' => 'tahun',
            'alias' => 'Tahun Laporan',
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
            'note' => 'Masukkan tahun laporan (contoh: 2025)',
            'position' => '2',
            'isactive' => '1',
            'created_at' => now(),
            'updated_at' => now(),
            'user_create' => 'seeder',
            'user_update' => 'seeder'
        ]);

        echo "TableKOP401Seeder completed successfully.\n";
    }
}
