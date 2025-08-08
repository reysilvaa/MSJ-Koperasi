<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class KoperasiTableSeeder extends Seeder
{
    public function run(): void
    {
        // Delete existing data
        $menuToDelete = [
            ['gmenu' => 'KOP001', 'dmenu' => 'KOP101'],
            ['gmenu' => 'KOP001', 'dmenu' => 'KOP102'],
            ['gmenu' => 'KOP001', 'dmenu' => 'KOP103'],
            ['gmenu' => 'KOP001', 'dmenu' => 'KOP104'],
            ['gmenu' => 'KOP002', 'dmenu' => 'KOP201'],
            ['gmenu' => 'KOP002', 'dmenu' => 'KOP202'],
            ['gmenu' => 'KOP002', 'dmenu' => 'KOP203'],
            ['gmenu' => 'KOP002', 'dmenu' => 'KOP204'],
            ['gmenu' => 'KOP003', 'dmenu' => 'KOP301'],
            ['gmenu' => 'KOP003', 'dmenu' => 'KOP302'],
            ['gmenu' => 'KOP003', 'dmenu' => 'KOP303'],
            ['gmenu' => 'KOP003', 'dmenu' => 'KOP304'],
            ['gmenu' => 'KOP004', 'dmenu' => 'KOP403'],
            ['gmenu' => 'KOP004', 'dmenu' => 'KOP404'],
            ['gmenu' => 'KOP005', 'dmenu' => 'KOP501'],
            ['gmenu' => 'KOP005', 'dmenu' => 'KOP502'],
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
                    ['field' => 'status_anggota', 'alias' => 'Status Anggota', 'type' => 'enum', 'default' => 'aktif', 'validate' => 'required', 'query' => "select 'aktif' as value, 'Aktif' as name union select 'non_aktif' as value, 'Non Aktif' as name union select 'keluar' as value, 'Keluar' as name"],
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
                    ['field' => 'bunga_per_tahun', 'alias' => 'Bunga per Tahun (%)', 'type' => 'number', 'length' => '5', 'decimals' => '2', 'validate' => 'required'],
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

            // KOP104 - Konfigurasi Koperasi
            'KOP104' => [
                'gmenu' => 'KOP001',
                'fields' => [
                    ['field' => 'id', 'alias' => 'ID', 'type' => 'primarykey', 'length' => '11', 'primary' => '1'],
                    ['field' => 'nama_koperasi', 'alias' => 'Nama Koperasi', 'type' => 'text', 'length' => '100', 'validate' => 'required'],
                    ['field' => 'alamat', 'alias' => 'Alamat', 'type' => 'text', 'length' => '255', 'validate' => 'required'],
                    ['field' => 'no_telepon', 'alias' => 'No Telepon', 'type' => 'text', 'length' => '20', 'validate' => 'required'],
                    ['field' => 'email', 'alias' => 'Email', 'type' => 'email', 'length' => '100', 'validate' => 'required|email'],
                    ['field' => 'bagi_hasil_simpanan', 'alias' => 'Bagi Hasil Simpanan (%)', 'type' => 'number', 'length' => '5', 'decimals' => '2', 'validate' => 'required'],
                    ['field' => 'denda_telat_cicilan', 'alias' => 'Denda Telat Cicilan (%)', 'type' => 'number', 'length' => '5', 'decimals' => '2', 'validate' => 'required'],
                    $this->getActiveField()
                ]
            ],

            // KOP201 - Pengajuan Pinjaman
            'KOP201' => [
                'gmenu' => 'KOP002',
                'fields' => [
                    ['field' => 'id', 'alias' => 'ID', 'type' => 'primarykey', 'length' => '11', 'primary' => '1'],
                    ['field' => 'nomor_pengajuan', 'alias' => 'Nomor Pengajuan', 'type' => 'text', 'length' => '20', 'validate' => 'required', 'generateid' => 'auto'],
                    ['field' => 'anggota_id', 'alias' => 'Anggota', 'type' => 'enum', 'length' => '11', 'validate' => 'required', 'query' => "select id as value, concat(nomor_anggota, ' - ', nama_lengkap) as name from anggota where status_anggota = 'aktif' and isactive = '1'"],
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

            // KOP203 - Data Pinjaman
            'KOP203' => [
                'gmenu' => 'KOP002',
                'fields' => [
                    ['field' => 'id', 'alias' => 'ID', 'type' => 'primarykey', 'length' => '11', 'primary' => '1'],
                    ['field' => 'nomor_pinjaman', 'alias' => 'Nomor Pinjaman', 'type' => 'text', 'length' => '20', 'validate' => 'required'],
                    ['field' => 'anggota_id', 'alias' => 'Anggota', 'type' => 'enum', 'length' => '11', 'validate' => 'required', 'query' => "select id as value, concat(nomor_anggota, ' - ', nama_lengkap) as name from anggota where status_anggota = 'aktif' and isactive = '1'"],
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

            // KOP204 - Penarikan Anggota
            'KOP204' => [
                'gmenu' => 'KOP002',
                'fields' => [
                    ['field' => 'id', 'alias' => 'ID', 'type' => 'primarykey', 'length' => '11', 'primary' => '1'],
                    ['field' => 'nomor_penarikan', 'alias' => 'Nomor Penarikan', 'type' => 'text', 'length' => '20', 'validate' => 'required', 'generateid' => 'auto'],
                    ['field' => 'anggota_id', 'alias' => 'Anggota', 'type' => 'enum', 'length' => '11', 'validate' => 'required', 'query' => "select id as value, concat(nomor_anggota, ' - ', nama_lengkap) as name from anggota where status_anggota = 'aktif' and isactive = '1'"],
                    ['field' => 'jenis_penarikan', 'alias' => 'Jenis Penarikan', 'type' => 'enum', 'length' => '20', 'validate' => 'required', 'query' => "select 'simpanan_sukarela' as value, 'Simpanan Sukarela' as name union select 'pencairan_sebagian' as value, 'Pencairan Sebagian' as name union select 'pencairan_total' as value, 'Pencairan Total' as name"],
                    ['field' => 'nominal', 'alias' => 'Nominal Penarikan', 'type' => 'currency', 'length' => '15', 'decimals' => '2', 'validate' => 'required'],
                    ['field' => 'tanggal_penarikan', 'alias' => 'Tanggal Penarikan', 'type' => 'date', 'validate' => 'required'],
                    ['field' => 'status_penarikan', 'alias' => 'Status', 'type' => 'enum', 'default' => 'pending', 'validate' => 'required', 'query' => "select 'pending' as value, 'Pending' as name union select 'approved' as value, 'Disetujui' as name union select 'rejected' as value, 'Ditolak' as name"]
                ]
            ],

            // KOP301 - Iuran Anggota
            'KOP301' => [
                'gmenu' => 'KOP003',
                'fields' => [
                    ['field' => 'id', 'alias' => 'ID', 'type' => 'primarykey', 'length' => '11', 'primary' => '1'],
                    ['field' => 'nomor_transaksi', 'alias' => 'Nomor Transaksi', 'type' => 'text', 'length' => '20', 'validate' => 'required', 'generateid' => 'auto'],
                    ['field' => 'anggota_id', 'alias' => 'Anggota', 'type' => 'enum', 'length' => '11', 'validate' => 'required', 'query' => "select id as value, concat(nomor_anggota, ' - ', nama_lengkap) as name from anggota where status_anggota = 'aktif' and isactive = '1'"],
                    ['field' => 'jenis_iuran', 'alias' => 'Jenis Iuran', 'type' => 'enum', 'length' => '20', 'validate' => 'required', 'query' => "select 'simpanan_pokok' as value, 'Simpanan Pokok' as name union select 'simpanan_wajib' as value, 'Simpanan Wajib' as name union select 'simpanan_sukarela' as value, 'Simpanan Sukarela' as name"],
                    ['field' => 'nominal', 'alias' => 'Nominal', 'type' => 'currency', 'length' => '15', 'decimals' => '2', 'validate' => 'required'],
                    ['field' => 'tanggal_iuran', 'alias' => 'Tanggal Iuran', 'type' => 'date', 'validate' => 'required']
                ]
            ],

            // KOP302 - Transfer Dana
            'KOP302' => [
                'gmenu' => 'KOP003',
                'fields' => [
                    ['field' => 'id', 'alias' => 'ID', 'type' => 'primarykey', 'length' => '11', 'primary' => '1'],
                    ['field' => 'nomor_transfer', 'alias' => 'Nomor Transfer', 'type' => 'text', 'length' => '20', 'validate' => 'required', 'generateid' => 'auto'],
                    ['field' => 'anggota_pengirim_id', 'alias' => 'Anggota Pengirim', 'type' => 'enum', 'length' => '11', 'validate' => 'required', 'query' => "select id as value, concat(nomor_anggota, ' - ', nama_lengkap) as name from anggota where status_anggota = 'aktif' and isactive = '1'"],
                    ['field' => 'anggota_penerima_id', 'alias' => 'Anggota Penerima', 'type' => 'enum', 'length' => '11', 'validate' => 'required', 'query' => "select id as value, concat(nomor_anggota, ' - ', nama_lengkap) as name from anggota where status_anggota = 'aktif' and isactive = '1'"],
                    ['field' => 'nominal', 'alias' => 'Nominal Transfer', 'type' => 'currency', 'length' => '15', 'decimals' => '2', 'validate' => 'required']
                ]
            ],

            // KOP303 - SHU Anggota
            'KOP303' => [
                'gmenu' => 'KOP003',
                'fields' => [
                    ['field' => 'id', 'alias' => 'ID', 'type' => 'primarykey', 'length' => '11', 'primary' => '1'],
                    ['field' => 'tahun_buku', 'alias' => 'Tahun Buku', 'type' => 'number', 'length' => '4', 'validate' => 'required'],
                    ['field' => 'anggota_id', 'alias' => 'Anggota', 'type' => 'enum', 'length' => '11', 'validate' => 'required', 'query' => "select id as value, concat(nomor_anggota, ' - ', nama_lengkap) as name from anggota where status_anggota = 'aktif' and isactive = '1'"],
                    ['field' => 'total_simpanan', 'alias' => 'Total Simpanan', 'type' => 'currency', 'length' => '15', 'decimals' => '2', 'validate' => 'required'],
                    ['field' => 'total_transaksi', 'alias' => 'Total Transaksi', 'type' => 'currency', 'length' => '15', 'decimals' => '2', 'validate' => 'required'],
                    ['field' => 'persentase_simpanan', 'alias' => 'Persentase dari Simpanan (%)', 'type' => 'number', 'length' => '5', 'decimals' => '2', 'validate' => 'required'],
                    ['field' => 'persentase_transaksi', 'alias' => 'Persentase dari Transaksi (%)', 'type' => 'number', 'length' => '5', 'decimals' => '2', 'validate' => 'required'],
                    ['field' => 'shu_simpanan', 'alias' => 'SHU dari Simpanan', 'type' => 'currency', 'length' => '15', 'decimals' => '2', 'validate' => 'required'],
                    ['field' => 'shu_transaksi', 'alias' => 'SHU dari Transaksi', 'type' => 'currency', 'length' => '15', 'decimals' => '2', 'validate' => 'required'],
                    ['field' => 'total_shu', 'alias' => 'Total SHU', 'type' => 'currency', 'length' => '15', 'decimals' => '2', 'validate' => 'required'],
                    ['field' => 'status_pembayaran', 'alias' => 'Status Pembayaran', 'type' => 'enum', 'default' => 'belum_dibayar', 'validate' => 'required', 'query' => "select 'belum_dibayar' as value, 'Belum Dibayar' as name union select 'sudah_dibayar' as value, 'Sudah Dibayar' as name"],
                    ['field' => 'tanggal_pembayaran', 'alias' => 'Tanggal Pembayaran', 'type' => 'date'],
                    $this->getActiveField()
                ]
            ],

            // KOP304 - Jurnal Keuangan
            'KOP304' => [
                'gmenu' => 'KOP003',
                'fields' => [
                    ['field' => 'id', 'alias' => 'ID', 'type' => 'primarykey', 'length' => '11', 'primary' => '1'],
                    ['field' => 'nomor_jurnal', 'alias' => 'Nomor Jurnal', 'type' => 'text', 'length' => '20', 'validate' => 'required', 'generateid' => 'auto'],
                    ['field' => 'tanggal', 'alias' => 'Tanggal', 'type' => 'date', 'validate' => 'required'],
                    ['field' => 'keterangan', 'alias' => 'Keterangan', 'type' => 'text', 'length' => '255', 'validate' => 'required'],
                    ['field' => 'jenis_akun', 'alias' => 'Jenis Akun', 'type' => 'enum', 'length' => '20', 'validate' => 'required', 'query' => "select 'aset' as value, 'Aset' as name union select 'kewajiban' as value, 'Kewajiban' as name union select 'modal' as value, 'Modal' as name union select 'pendapatan' as value, 'Pendapatan' as name union select 'beban' as value, 'Beban' as name"],
                    ['field' => 'nama_akun', 'alias' => 'Nama Akun', 'type' => 'text', 'length' => '100', 'validate' => 'required'],
                    ['field' => 'debit', 'alias' => 'Debit', 'type' => 'currency', 'length' => '15', 'decimals' => '2', 'default' => '0'],
                    ['field' => 'kredit', 'alias' => 'Kredit', 'type' => 'currency', 'length' => '15', 'decimals' => '2', 'default' => '0'],
                    ['field' => 'referensi_id', 'alias' => 'Referensi ID', 'type' => 'text', 'length' => '50', 'filter' => '0'],
                    ['field' => 'referensi_tabel', 'alias' => 'Referensi Tabel', 'type' => 'text', 'length' => '50', 'filter' => '0'],
                    $this->getActiveField()
                ]
            ],

            // KOP403 - Laporan Laba Rugi
            'KOP403' => [
                'gmenu' => 'KOP004',
                'fields' => [
                    ['field' => 'query', 'alias' => 'Query Laba Rugi', 'type' => 'report', 'length' => '0', 'filter' => '0', 'list' => '0', 'query' => "SELECT 'PENDAPATAN' as kategori, 'Bunga Pinjaman' as akun, COALESCE(SUM(cp.nominal_bunga), 0) as nominal FROM cicilan_pinjaman cp WHERE YEAR(cp.tanggal_bayar) = :tahun AND cp.status = 'lunas' UNION ALL SELECT 'BEBAN' as kategori, 'Operasional' as akun, COALESCE(SUM(jk.debit), 0) as nominal FROM jurnal_keuangan jk WHERE YEAR(jk.tanggal) = :tahun AND jk.jenis_akun = 'beban' ORDER BY kategori, akun"],
                    ['field' => 'tahun', 'alias' => 'Tahun Laporan', 'type' => 'number', 'length' => '4', 'default' => date('Y'), 'validate' => 'required', 'list' => '0']
                ]
            ],

            // KOP404 - Laporan Neraca
            'KOP404' => [
                'gmenu' => 'KOP004',
                'fields' => [
                    ['field' => 'query', 'alias' => 'Query Neraca', 'type' => 'report', 'length' => '0', 'filter' => '0', 'list' => '0', 'query' => "SELECT 'ASET' as kelompok, 'Kas dan Bank' as akun, COALESCE(SUM(CASE WHEN jk.jenis_akun = 'aset' THEN jk.debit - jk.kredit ELSE 0 END), 0) as nominal FROM jurnal_keuangan jk WHERE YEAR(jk.tanggal) <= :tahun UNION ALL SELECT 'ASET' as kelompok, 'Piutang Pinjaman' as akun, COALESCE(SUM(p.sisa_pokok), 0) as nominal FROM pinjaman p WHERE p.status = 'aktif' ORDER BY kelompok, akun"],
                    ['field' => 'tahun', 'alias' => 'Tahun Laporan', 'type' => 'number', 'length' => '4', 'default' => date('Y'), 'validate' => 'required', 'list' => '0']
                ]
            ],

            // KOP501 - Notifikasi
            'KOP501' => [
                'gmenu' => 'KOP005',
                'fields' => [
                    ['field' => 'id', 'alias' => 'ID', 'type' => 'primarykey', 'length' => '11', 'primary' => '1'],
                    ['field' => 'judul', 'alias' => 'Judul Notifikasi', 'type' => 'text', 'length' => '100', 'validate' => 'required'],
                    ['field' => 'pesan', 'alias' => 'Pesan', 'type' => 'text', 'length' => '500', 'validate' => 'required', 'filter' => '0'],
                    ['field' => 'jenis', 'alias' => 'Jenis Notifikasi', 'type' => 'enum', 'length' => '20', 'validate' => 'required', 'query' => "select 'info' as value, 'Informasi' as name union select 'warning' as value, 'Peringatan' as name union select 'urgent' as value, 'Urgent' as name"],
                    ['field' => 'anggota_id', 'alias' => 'Target Anggota', 'type' => 'enum', 'length' => '11', 'query' => "select id as value, concat(nomor_anggota, ' - ', nama_lengkap) as name from anggota where status_anggota = 'aktif' and isactive = '1' union select '0' as value, 'Semua Anggota' as name"]
                ]
            ],

            // KOP502 - Laporan
            'KOP502' => [
                'gmenu' => 'KOP005',
                'fields' => [
                    ['field' => 'id', 'alias' => 'ID', 'type' => 'primarykey', 'length' => '11', 'primary' => '1'],
                    ['field' => 'nama_laporan', 'alias' => 'Nama Laporan', 'type' => 'text', 'length' => '100', 'validate' => 'required'],
                    ['field' => 'jenis_laporan', 'alias' => 'Jenis Laporan', 'type' => 'enum', 'length' => '30', 'validate' => 'required', 'query' => "select 'keuangan' as value, 'Laporan Keuangan' as name union select 'anggota' as value, 'Laporan Anggota' as name union select 'pinjaman' as value, 'Laporan Pinjaman' as name union select 'simpanan' as value, 'Laporan Simpanan' as name"],
                    ['field' => 'tanggal_dari', 'alias' => 'Tanggal Dari', 'type' => 'date', 'validate' => 'required'],
                    ['field' => 'tanggal_sampai', 'alias' => 'Tanggal Sampai', 'type' => 'date', 'validate' => 'required']
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
