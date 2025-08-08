<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class KoperasiTableSeeder extends Seeder
{
    public function run(): void
    {
        // Delete existing data - sesuai menu structure yang baru
        $menuToDelete = [
            ['gmenu' => 'KOP001', 'dmenu' => 'KOP101'],
            ['gmenu' => 'KOP001', 'dmenu' => 'KOP102'],
            ['gmenu' => 'KOP001', 'dmenu' => 'KOP103'],
            ['gmenu' => 'KOP002', 'dmenu' => 'KOP201'],
            ['gmenu' => 'KOP002', 'dmenu' => 'KOP202'],
            ['gmenu' => 'KOP002', 'dmenu' => 'KOP203'],
            ['gmenu' => 'KOP003', 'dmenu' => 'KOP301'],
            ['gmenu' => 'KOP003', 'dmenu' => 'KOP302'],
            ['gmenu' => 'KOP004', 'dmenu' => 'KOP401'],
            ['gmenu' => 'KOP004', 'dmenu' => 'KOP402'],
            ['gmenu' => 'KOP004', 'dmenu' => 'KOP403'],
            ['gmenu' => 'KOP005', 'dmenu' => 'KOP501'],
            ['gmenu' => 'KOP005', 'dmenu' => 'KOP502'],
            ['gmenu' => 'KOP005', 'dmenu' => 'KOP503'],
        ];

        foreach ($menuToDelete as $menu) {
            DB::table('sys_table')->where($menu)->delete();
        }

        // Define table configurations with compact structure
        $configs = [
            // KOP101 - Data Anggota
            'KOP101' => [
                'gmenu' => 'KOP001',
                'fields' => [
                    ['field' => 'id', 'alias' => 'ID', 'type' => 'primarykey', 'length' => '11', 'primary' => '1'],
                    ['field' => 'nomor_anggota', 'alias' => 'Nomor Anggota', 'type' => 'text', 'length' => '20', 'validate' => 'required|unique:anggota,nomor_anggota'],
                    ['field' => 'nik', 'alias' => 'NIK', 'type' => 'text', 'length' => '16', 'validate' => 'required|unique:anggota,nik'],
                    ['field' => 'nama_lengkap', 'alias' => 'Nama Lengkap', 'type' => 'text', 'length' => '100', 'validate' => 'required'],
                    ['field' => 'email', 'alias' => 'Email', 'type' => 'email', 'length' => '100', 'validate' => 'required|email|unique:anggota,email'],
                    ['field' => 'no_hp', 'alias' => 'No HP', 'type' => 'text', 'length' => '15', 'validate' => 'required'],
                    ['field' => 'jenis_kelamin', 'alias' => 'Jenis Kelamin', 'type' => 'enum', 'length' => '1', 'validate' => 'required', 'query' => "select 'L' as value, 'Laki-laki' as name union select 'P' as value, 'Perempuan' as name"],
                    ['field' => 'tanggal_lahir', 'alias' => 'Tanggal Lahir', 'type' => 'date', 'validate' => 'required'],
                    ['field' => 'alamat', 'alias' => 'Alamat', 'type' => 'text', 'length' => '255', 'validate' => 'required'],
                    ['field' => 'jabatan', 'alias' => 'Jabatan', 'type' => 'text', 'length' => '50', 'validate' => 'required'],
                    ['field' => 'departemen', 'alias' => 'Departemen', 'type' => 'text', 'length' => '50', 'validate' => 'required'],
                    ['field' => 'status_keanggotaan', 'alias' => 'Status Anggota', 'type' => 'enum', 'default' => 'aktif', 'validate' => 'required', 'query' => "select 'aktif' as value, 'Aktif' as name union select 'non_aktif' as value, 'Non Aktif' as name union select 'keluar' as value, 'Keluar' as name"],
                    $this->getActiveField()
                ]
            ],

            // KOP102 - Master Paket Pinjaman
            'KOP102' => [
                'gmenu' => 'KOP001',
                'fields' => [
                    ['field' => 'id', 'alias' => 'ID', 'type' => 'primarykey', 'length' => '11', 'primary' => '1'],
                    ['field' => 'kode_paket', 'alias' => 'Kode Paket', 'type' => 'text', 'length' => '10', 'validate' => 'required|unique:master_paket_pinjaman,kode_paket'],
                    ['field' => 'nama_paket', 'alias' => 'Nama Paket', 'type' => 'text', 'length' => '100', 'validate' => 'required'],
                    ['field' => 'limit_minimum', 'alias' => 'Limit Minimum', 'type' => 'currency', 'length' => '15', 'decimals' => '2', 'validate' => 'required'],
                    ['field' => 'limit_maksimum', 'alias' => 'Limit Maksimum', 'type' => 'currency', 'length' => '15', 'decimals' => '2', 'validate' => 'required'],
                    ['field' => 'bunga_per_bulan', 'alias' => 'Bunga per Bulan (%)', 'type' => 'number', 'length' => '5', 'decimals' => '2', 'validate' => 'required'],
                    $this->getActiveField()
                ]
            ],

            // KOP103 - Master Tenor
            'KOP103' => [
                'gmenu' => 'KOP001',
                'fields' => [
                    ['field' => 'id', 'alias' => 'ID', 'type' => 'primarykey', 'length' => '11', 'primary' => '1'],
                    ['field' => 'tenor_bulan', 'alias' => 'Tenor (Bulan)', 'type' => 'number', 'length' => '3', 'validate' => 'required|unique:master_tenor,tenor_bulan'],
                    ['field' => 'nama_tenor', 'alias' => 'Nama Tenor', 'type' => 'text', 'length' => '50', 'validate' => 'required']
                ]
            ],

            // KOP201 - Pengajuan Pinjaman
            'KOP201' => [
                'gmenu' => 'KOP002',
                'fields' => [
                    ['field' => 'id', 'alias' => 'ID', 'type' => 'primarykey', 'length' => '11', 'primary' => '1'],
                    ['field' => 'nomor_pengajuan', 'alias' => 'Nomor Pengajuan', 'type' => 'text', 'length' => '20', 'validate' => 'required', 'generateid' => 'auto'],
                    ['field' => 'anggota_id', 'alias' => 'Anggota', 'type' => 'enum', 'length' => '11', 'validate' => 'required', 'query' => "select id as value, concat(nomor_anggota, ' - ', nama_lengkap) as name from anggota where status_keanggotaan = 'aktif' and isactive = '1'"],
                    ['field' => 'master_paket_pinjaman_id', 'alias' => 'Paket Pinjaman', 'type' => 'enum', 'length' => '11', 'validate' => 'required', 'query' => "select id as value, concat(kode_paket, ' - ', nama_paket) as name from master_paket_pinjaman where status = 'aktif' and isactive = '1'"],
                    ['field' => 'nominal_pengajuan', 'alias' => 'Nominal Pengajuan', 'type' => 'currency', 'length' => '15', 'decimals' => '2', 'validate' => 'required'],
                    ['field' => 'status', 'alias' => 'Status', 'type' => 'enum', 'default' => 'pending', 'validate' => 'required', 'query' => "select 'pending' as value, 'Pending' as name union select 'review' as value, 'Review' as name union select 'approved' as value, 'Approved' as name union select 'rejected' as value, 'Rejected' as name"]
                ]
            ],

            // KOP202 - Approval Pinjaman
            'KOP202' => [
                'gmenu' => 'KOP002',
                'fields' => [
                    ['field' => 'id', 'alias' => 'ID', 'type' => 'primarykey', 'length' => '11', 'primary' => '1'],
                    ['field' => 'pengajuan_pinjaman_id', 'alias' => 'Pengajuan Pinjaman', 'type' => 'enum', 'length' => '11', 'validate' => 'required', 'query' => "select id as value, concat(nomor_pengajuan, ' - Rp ', format(nominal_pengajuan, 0)) as name from pengajuan_pinjaman where status = 'review' and isactive = '1'"],
                    ['field' => 'approver', 'alias' => 'Approver', 'type' => 'text', 'length' => '100', 'validate' => 'required'],
                    ['field' => 'status_approval', 'alias' => 'Status Approval', 'type' => 'enum', 'validate' => 'required', 'query' => "select 'approved' as value, 'Approved' as name union select 'rejected' as value, 'Rejected' as name"],
                    ['field' => 'catatan', 'alias' => 'Catatan', 'type' => 'text', 'length' => '500', 'filter' => '0', 'list' => '0'],
                    ['field' => 'tanggal_approval', 'alias' => 'Tanggal Approval', 'type' => 'date', 'validate' => 'required'],
                    $this->getActiveField()
                ]
            ],

            // KOP203 - Data Pinjaman Aktif
            'KOP203' => [
                'gmenu' => 'KOP002',
                'fields' => [
                    ['field' => 'id', 'alias' => 'ID', 'type' => 'primarykey', 'length' => '11', 'primary' => '1'],
                    ['field' => 'nomor_pinjaman', 'alias' => 'Nomor Pinjaman', 'type' => 'text', 'length' => '20', 'validate' => 'required'],
                    ['field' => 'anggota_id', 'alias' => 'Anggota', 'type' => 'enum', 'length' => '11', 'validate' => 'required', 'query' => "select id as value, concat(nomor_anggota, ' - ', nama_lengkap) as name from anggota where status_keanggotaan = 'aktif' and isactive = '1'"],
                    ['field' => 'pokok_pinjaman', 'alias' => 'Pokok Pinjaman', 'type' => 'currency', 'length' => '15', 'decimals' => '2', 'validate' => 'required'],
                    ['field' => 'sisa_pokok', 'alias' => 'Sisa Pokok', 'type' => 'currency', 'length' => '15', 'decimals' => '2', 'validate' => 'required'],
                    ['field' => 'bunga_per_bulan', 'alias' => 'Bunga per Bulan', 'type' => 'currency', 'length' => '15', 'decimals' => '2', 'validate' => 'required'],
                    ['field' => 'tenor_bulan', 'alias' => 'Tenor (Bulan)', 'type' => 'number', 'length' => '3', 'validate' => 'required'],
                    ['field' => 'cicilan_per_bulan', 'alias' => 'Cicilan per Bulan', 'type' => 'currency', 'length' => '15', 'decimals' => '2', 'validate' => 'required'],
                    ['field' => 'tanggal_cair', 'alias' => 'Tanggal Cair', 'type' => 'date', 'validate' => 'required'],
                    ['field' => 'tanggal_jatuh_tempo', 'alias' => 'Tanggal Jatuh Tempo', 'type' => 'date', 'validate' => 'required'],
                    ['field' => 'status', 'alias' => 'Status Pinjaman', 'type' => 'enum', 'default' => 'aktif', 'validate' => 'required', 'query' => "select 'aktif' as value, 'Aktif' as name union select 'lunas' as value, 'Lunas' as name union select 'macet' as value, 'Bermasalah' as name"],
                    $this->getActiveField()
                ]
            ],

            // KOP301 - Periode Pencairan
            'KOP301' => [
                'gmenu' => 'KOP003',
                'fields' => [
                    ['field' => 'id', 'alias' => 'ID', 'type' => 'primarykey', 'length' => '11', 'primary' => '1'],
                    ['field' => 'kode_periode', 'alias' => 'Kode Periode', 'type' => 'text', 'length' => '20', 'validate' => 'required|unique:periode_pencairan,kode_periode'],
                    ['field' => 'nama_periode', 'alias' => 'Nama Periode', 'type' => 'text', 'length' => '100', 'validate' => 'required'],
                    ['field' => 'tanggal_mulai', 'alias' => 'Tanggal Mulai', 'type' => 'date', 'validate' => 'required'],
                    ['field' => 'tanggal_selesai', 'alias' => 'Tanggal Selesai', 'type' => 'date', 'validate' => 'required'],
                    ['field' => 'status', 'alias' => 'Status', 'type' => 'enum', 'default' => 'draft', 'validate' => 'required', 'query' => "select 'draft' as value, 'Draft' as name union select 'aktif' as value, 'Aktif' as name union select 'selesai' as value, 'Selesai' as name"],
                    $this->getActiveField()
                ]
            ],

            // KOP302 - Proses Pencairan (system layout untuk workflow)
            'KOP302' => [
                'gmenu' => 'KOP003',
                'fields' => [
                    ['field' => 'id', 'alias' => 'ID', 'type' => 'primarykey', 'length' => '11', 'primary' => '1'],
                    ['field' => 'periode_pencairan_id', 'alias' => 'Periode Pencairan', 'type' => 'enum', 'length' => '11', 'validate' => 'required', 'query' => "select id as value, concat(kode_periode, ' - ', nama_periode) as name from periode_pencairan where status = 'aktif' and isactive = '1'"],
                    ['field' => 'pengajuan_pinjaman_id', 'alias' => 'Pengajuan Pinjaman', 'type' => 'enum', 'length' => '11', 'validate' => 'required', 'query' => "select id as value, concat(nomor_pengajuan, ' - Rp ', format(nominal_pengajuan, 0)) as name from pengajuan_pinjaman where status = 'approved' and isactive = '1'"],
                    ['field' => 'status_pencairan', 'alias' => 'Status Pencairan', 'type' => 'enum', 'default' => 'menunggu', 'validate' => 'required', 'query' => "select 'menunggu' as value, 'Menunggu' as name union select 'proses' as value, 'Proses' as name union select 'selesai' as value, 'Selesai' as name"],
                    ['field' => 'tanggal_pencairan', 'alias' => 'Tanggal Pencairan', 'type' => 'date'],
                    ['field' => 'catatan', 'alias' => 'Catatan', 'type' => 'text', 'length' => '500', 'filter' => '0', 'list' => '0']
                ]
            ],

            // KOP401 - Cicilan Anggota
            'KOP401' => [
                'gmenu' => 'KOP004',
                'fields' => [
                    ['field' => 'id', 'alias' => 'ID', 'type' => 'primarykey', 'length' => '11', 'primary' => '1'],
                    ['field' => 'nomor_transaksi', 'alias' => 'Nomor Transaksi', 'type' => 'text', 'length' => '20', 'validate' => 'required', 'generateid' => 'auto'],
                    ['field' => 'pinjaman_id', 'alias' => 'Pinjaman', 'type' => 'enum', 'length' => '11', 'validate' => 'required', 'query' => "select id as value, concat(nomor_pinjaman, ' - Rp ', format(cicilan_per_bulan, 0)) as name from pinjaman where status = 'aktif' and isactive = '1'"],
                    ['field' => 'cicilan_ke', 'alias' => 'Cicilan Ke-', 'type' => 'number', 'length' => '3', 'validate' => 'required'],
                    ['field' => 'nominal_pokok', 'alias' => 'Nominal Pokok', 'type' => 'currency', 'length' => '15', 'decimals' => '2', 'validate' => 'required'],
                    ['field' => 'nominal_bunga', 'alias' => 'Nominal Bunga', 'type' => 'currency', 'length' => '15', 'decimals' => '2', 'validate' => 'required'],
                    ['field' => 'denda', 'alias' => 'Denda', 'type' => 'currency', 'length' => '15', 'decimals' => '2', 'default' => '0'],
                    ['field' => 'total_bayar', 'alias' => 'Total Bayar', 'type' => 'currency', 'length' => '15', 'decimals' => '2', 'validate' => 'required'],
                    ['field' => 'tanggal_jatuh_tempo', 'alias' => 'Tanggal Jatuh Tempo', 'type' => 'date', 'validate' => 'required'],
                    ['field' => 'tanggal_bayar', 'alias' => 'Tanggal Bayar', 'type' => 'date'],
                    ['field' => 'status', 'alias' => 'Status Cicilan', 'type' => 'enum', 'default' => 'belum_bayar', 'validate' => 'required', 'query' => "select 'belum_bayar' as value, 'Belum Bayar' as name union select 'lunas' as value, 'Lunas' as name union select 'telat' as value, 'Telat' as name"],
                    $this->getActiveField()
                ]
            ],

            // KOP402 - Iuran Anggota
            'KOP402' => [
                'gmenu' => 'KOP004',
                'fields' => [
                    ['field' => 'id', 'alias' => 'ID', 'type' => 'primarykey', 'length' => '11', 'primary' => '1'],
                    ['field' => 'nomor_transaksi', 'alias' => 'Nomor Transaksi', 'type' => 'text', 'length' => '20', 'validate' => 'required', 'generateid' => 'auto'],
                    ['field' => 'anggota_id', 'alias' => 'Anggota', 'type' => 'enum', 'length' => '11', 'validate' => 'required', 'query' => "select id as value, concat(nomor_anggota, ' - ', nama_lengkap) as name from anggota where status_keanggotaan = 'aktif' and isactive = '1'"],
                    ['field' => 'jenis_iuran', 'alias' => 'Jenis Iuran', 'type' => 'enum', 'length' => '20', 'validate' => 'required', 'query' => "select 'simpanan_pokok' as value, 'Simpanan Pokok' as name union select 'simpanan_wajib' as value, 'Simpanan Wajib' as name union select 'simpanan_sukarela' as value, 'Simpanan Sukarela' as name"],
                    ['field' => 'nominal', 'alias' => 'Nominal', 'type' => 'currency', 'length' => '15', 'decimals' => '2', 'validate' => 'required'],
                    ['field' => 'tanggal_iuran', 'alias' => 'Tanggal Iuran', 'type' => 'date', 'validate' => 'required'],
                    ['field' => 'bulan_iuran', 'alias' => 'Bulan Iuran', 'type' => 'text', 'length' => '7', 'validate' => 'required'],
                    ['field' => 'keterangan', 'alias' => 'Keterangan', 'type' => 'text', 'length' => '255', 'filter' => '0', 'list' => '0'],
                    $this->getActiveField()
                ]
            ],

            // KOP403 - Notifikasi
            'KOP403' => [
                'gmenu' => 'KOP004',
                'fields' => [
                    ['field' => 'id', 'alias' => 'ID', 'type' => 'primarykey', 'length' => '11', 'primary' => '1'],
                    ['field' => 'judul', 'alias' => 'Judul Notifikasi', 'type' => 'text', 'length' => '100', 'validate' => 'required'],
                    ['field' => 'pesan', 'alias' => 'Pesan', 'type' => 'text', 'length' => '500', 'validate' => 'required', 'filter' => '0'],
                    ['field' => 'jenis', 'alias' => 'Jenis Notifikasi', 'type' => 'enum', 'length' => '20', 'validate' => 'required', 'query' => "select 'info' as value, 'Informasi' as name union select 'warning' as value, 'Peringatan' as name union select 'urgent' as value, 'Urgent' as name"],
                    ['field' => 'anggota_id', 'alias' => 'Target Anggota', 'type' => 'enum', 'length' => '11', 'query' => "select id as value, concat(nomor_anggota, ' - ', nama_lengkap) as name from anggota where status_keanggotaan = 'aktif' and isactive = '1' union select '0' as value, 'Semua Anggota' as name"],
                    ['field' => 'status_baca', 'alias' => 'Status Baca', 'type' => 'enum', 'default' => 'belum_dibaca', 'validate' => 'required', 'query' => "select 'belum_dibaca' as value, 'Belum Dibaca' as name union select 'sudah_dibaca' as value, 'Sudah Dibaca' as name"],
                    ['field' => 'tanggal_kirim', 'alias' => 'Tanggal Kirim', 'type' => 'datetime', 'validate' => 'required'],
                    $this->getActiveField()
                ]
            ],

            // KOP501 - Laporan Pinjaman
            'KOP501' => [
                'gmenu' => 'KOP005',
                'fields' => [
                    ['field' => 'query', 'alias' => 'Query Laporan Pinjaman', 'type' => 'report', 'length' => '0', 'filter' => '0', 'list' => '0', 'query' => "SELECT p.nomor_pinjaman, a.nama_lengkap, p.pokok_pinjaman, p.sisa_pokok, p.status, p.tanggal_cair FROM pinjaman p LEFT JOIN anggota a ON p.anggota_id = a.id WHERE (:status = '' OR p.status = :status) AND p.tanggal_cair BETWEEN :tanggal_dari AND :tanggal_sampai ORDER BY p.tanggal_cair DESC"],
                    ['field' => 'tanggal_dari', 'alias' => 'Tanggal Dari', 'type' => 'date', 'validate' => 'required', 'list' => '0'],
                    ['field' => 'tanggal_sampai', 'alias' => 'Tanggal Sampai', 'type' => 'date', 'validate' => 'required', 'list' => '0'],
                    ['field' => 'status', 'alias' => 'Status Pinjaman', 'type' => 'enum', 'query' => "select '' as value, 'Semua Status' as name union select 'aktif' as value, 'Aktif' as name union select 'lunas' as value, 'Lunas' as name", 'list' => '0']
                ]
            ],

            // KOP502 - Laporan Keuangan
            'KOP502' => [
                'gmenu' => 'KOP005',
                'fields' => [
                    ['field' => 'query', 'alias' => 'Query Laporan Keuangan', 'type' => 'report', 'length' => '0', 'filter' => '0', 'list' => '0', 'query' => "SELECT cp.nomor_transaksi, a.nama_lengkap, cp.nominal_pokok, cp.nominal_bunga, cp.total_bayar, cp.tanggal_bayar FROM cicilan_pinjaman cp LEFT JOIN pinjaman p ON cp.pinjaman_id = p.id LEFT JOIN anggota a ON p.anggota_id = a.id WHERE cp.status = 'lunas' AND cp.tanggal_bayar BETWEEN :tanggal_dari AND :tanggal_sampai ORDER BY cp.tanggal_bayar DESC"],
                    ['field' => 'tanggal_dari', 'alias' => 'Tanggal Dari', 'type' => 'date', 'validate' => 'required', 'list' => '0'],
                    ['field' => 'tanggal_sampai', 'alias' => 'Tanggal Sampai', 'type' => 'date', 'validate' => 'required', 'list' => '0']
                ]
            ],

            // KOP503 - Laporan Anggota
            'KOP503' => [
                'gmenu' => 'KOP005',
                'fields' => [
                    ['field' => 'query', 'alias' => 'Query Laporan Anggota', 'type' => 'report', 'length' => '0', 'filter' => '0', 'list' => '0', 'query' => "SELECT a.nomor_anggota, a.nama_lengkap, a.email, a.jabatan, a.departemen, a.status_keanggotaan, COUNT(p.id) as total_pinjaman, COALESCE(SUM(p.pokok_pinjaman), 0) as total_pinjaman_amount FROM anggota a LEFT JOIN pinjaman p ON a.id = p.anggota_id WHERE (:status = '' OR a.status_keanggotaan = :status) GROUP BY a.id ORDER BY a.nama_lengkap"],
                    ['field' => 'status', 'alias' => 'Status Anggota', 'type' => 'enum', 'query' => "select '' as value, 'Semua Status' as name union select 'aktif' as value, 'Aktif' as name union select 'non_aktif' as value, 'Non Aktif' as name union select 'keluar' as value, 'Keluar' as name", 'list' => '0']
                ]
            ]
        ];

        // Insert all configurations
        foreach ($configs as $dmenu => $config) {
            $this->insertTableConfig($dmenu, $config);
        }
    }

    private function insertTableConfig($dmenu, $config)
    {
        $urut = 1;
        foreach ($config['fields'] as $field) {
            $data = array_merge([
                'gmenu' => $config['gmenu'],
                'dmenu' => $dmenu,
                'urut' => $urut++,
                'decimals' => '0',
                'default' => '',
                'validate' => '',
                'primary' => '0',
                'filter' => '1',
                'list' => '1',
                'show' => '1',
                'position' => '2'
            ], $field);

            // Set defaults for primary key fields
            if ($field['type'] === 'primarykey') {
                $data = array_merge($data, [
                    'filter' => '0',
                    'list' => '0',
                    'show' => '0',
                    'position' => '1'
                ]);
            }

            DB::table('sys_table')->insert($data);
        }
    }

    private function getActiveField()
    {
        return [
            'field' => 'isactive',
            'alias' => 'Status',
            'type' => 'enum',
            'length' => '1',
            'default' => '1',
            'filter' => '1',
            'list' => '1',
            'show' => '0',
            'query' => "select value, name from sys_enum where idenum = 'isactive' and isactive = '1'"
        ];
    }
}
