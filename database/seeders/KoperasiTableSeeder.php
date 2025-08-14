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
            // Laporan Keuangan Baru
            ['gmenu' => 'KOP005', 'dmenu' => 'KOP504'],
            ['gmenu' => 'KOP005', 'dmenu' => 'KOP505'],
            ['gmenu' => 'KOP005', 'dmenu' => 'KOP506'],
            ['gmenu' => 'KOP005', 'dmenu' => 'KOP507'],
            ['gmenu' => 'KOP005', 'dmenu' => 'KOP508'],
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
                    ['field' => 'periode', 'alias' => 'Periode', 'type' => 'text', 'length' => '7', 'validate' => 'required', 'default' => date('Y-m'), 'note' => 'Format: 2025-08'],
                    ['field' => 'bunga_per_bulan', 'alias' => 'Bunga per Bulan (%)', 'type' => 'number', 'length' => '5', 'decimals' => '2', 'default' => '1.00', 'validate' => 'required'],

                    // Stock Management Fields (Sederhana)
                    ['field' => 'stock_limit', 'alias' => 'Stock Limit', 'type' => 'number', 'length' => '11', 'validate' => 'required', 'default' => '100'],
                    ['field' => 'stock_terpakai', 'alias' => 'Stock Terpakai', 'type' => 'number', 'length' => '11', 'default' => '0', 'list' => '1', 'show' => '0'],

                    $this->getActiveField()
                ]
            ],

            // KOP201 - Pengajuan Pinjaman (sesuai Activity Diagram 02)
            'KOP201' => [
                'gmenu' => 'KOP002',
                'fields' => [
                    ['field' => 'anggota_id', 'alias' => 'Anggota', 'type' => 'enum', 'length' => '11', 'validate' => 'required', 'query' => "select id as value, concat(nomor_anggota, ' - ', nama_lengkap) as name from anggota where status_keanggotaan = 'aktif' and isactive = '1'"],

                    // Sistem Berbasis Paket (sesuai business logic)
                    ['field' => 'paket_pinjaman_id', 'alias' => 'Paket Pinjaman', 'type' => 'enum', 'length' => '11', 'validate' => 'required', 'query' => "select id as value, concat('Paket ', periode, ' (', bunga_per_bulan, '% per bulan)') as name from master_paket_pinjaman where isactive = '1'"],
                    ['field' => 'jumlah_paket_dipilih', 'alias' => 'Jumlah Paket', 'type' => 'number', 'length' => '3', 'validate' => 'required|min:1|max:40', 'default' => '1', 'note' => '1 paket = Rp 500.000 (min: 1, max: 40 paket)'],
                    ['field' => 'tenor_pinjaman', 'alias' => 'Tenor Pinjaman', 'type' => 'enum', 'length' => '50', 'validate' => 'required', 'query' => "select '6 bulan' as value, '6 bulan' as name union select '12 bulan' as value, '12 bulan' as name union select '18 bulan' as value, '18 bulan' as name union select '24 bulan' as value, '24 bulan' as name"],

                    // Auto-Calculate Fields (calculated by system)
                    ['field' => 'jumlah_pinjaman', 'alias' => 'Jumlah Pinjaman (Auto)', 'type' => 'currency', 'length' => '15', 'decimals' => '2', 'list' => '1', 'show' => '0', 'filter' => '0', 'note' => 'Auto calculated: jumlah_paket_dipilih × 500.000'],
                    ['field' => 'bunga_per_bulan', 'alias' => 'Bunga per Bulan (%)', 'type' => 'number', 'length' => '5', 'decimals' => '2', 'default' => '1.00', 'list' => '1', 'show' => '0'],
                    ['field' => 'cicilan_per_bulan', 'alias' => 'Cicilan per Bulan (Auto)', 'type' => 'currency', 'length' => '15', 'decimals' => '2', 'list' => '1', 'show' => '0', 'filter' => '0', 'note' => 'Auto calculated berdasarkan tenor'],
                    ['field' => 'total_pembayaran', 'alias' => 'Total Pembayaran (Auto)', 'type' => 'currency', 'length' => '15', 'decimals' => '2', 'list' => '1', 'show' => '0', 'filter' => '0', 'note' => 'Auto calculated: cicilan × tenor'],

                    // Pengajuan Details
                    ['field' => 'tujuan_pinjaman', 'alias' => 'Tujuan Pinjaman', 'type' => 'text', 'length' => '500', 'validate' => 'required'],
                    ['field' => 'jenis_pengajuan', 'alias' => 'Jenis Pengajuan', 'type' => 'enum', 'default' => 'baru', 'query' => "select 'baru' as value, 'Pinjaman Baru' as name union select 'top_up' as value, 'Top Up' as name"],
                    ['field' => 'status_pengajuan', 'alias' => 'Status', 'type' => 'enum', 'default' => 'draft', 'validate' => 'required', 'show' => '0', 'query' => "select 'draft' as value, 'Draft' as name union select 'diajukan' as value, 'Diajukan' as name union select 'review_admin' as value, 'Review Admin' as name union select 'review_panitia' as value, 'Review Panitia' as name union select 'review_ketua' as value, 'Review Ketua' as name union select 'disetujui' as value, 'Disetujui' as name union select 'ditolak' as value, 'Ditolak' as name"],
                    ['field' => 'catatan_pengajuan', 'alias' => 'Catatan Pengajuan', 'type' => 'text', 'length' => '500', 'filter' => '0'],
                    $this->getActiveField()
                ]
            ],

            // KOP202 - Approval Pinjaman
            'KOP202' => [
                'gmenu' => 'KOP002',
                'fields' => [
                    ['field' => 'id', 'alias' => 'ID', 'type' => 'primarykey', 'length' => '11', 'primary' => '1'],
                    ['field' => 'pengajuan_pinjaman_id', 'alias' => 'Pengajuan Pinjaman', 'type' => 'enum', 'length' => '11', 'validate' => 'required', 'query' => "select id as value, concat('ID ', id, ' - Rp ', format(jumlah_pinjaman, 0)) as name from pengajuan_pinjaman where status_pengajuan = 'review_panitia' and isactive = '1'"],
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
                    ['field' => 'nama_periode', 'alias' => 'Nama Periode', 'type' => 'text', 'length' => '100', 'validate' => 'required'],
                    ['field' => 'tanggal_mulai', 'alias' => 'Tanggal Mulai', 'type' => 'date', 'validate' => 'required'],
                    ['field' => 'tanggal_selesai', 'alias' => 'Tanggal Selesai', 'type' => 'date', 'validate' => 'required'],
                    ['field' => 'tanggal_pencairan', 'alias' => 'Tanggal Pencairan', 'type' => 'date', 'validate' => 'required'],
                    ['field' => 'maksimal_aplikasi', 'alias' => 'Maksimal Aplikasi', 'type' => 'number', 'length' => '11', 'default' => '0', 'note' => '0 = unlimited'],
                    ['field' => 'total_dana_tersedia', 'alias' => 'Total Dana Tersedia', 'type' => 'number', 'length' => '15', 'decimals' => '2', 'default' => '0.00'],
                    ['field' => 'total_dana_terpakai', 'alias' => 'Total Dana Terpakai', 'type' => 'number', 'length' => '15', 'decimals' => '2', 'default' => '0.00', 'show' => '0'],
                    ['field' => 'keterangan', 'alias' => 'Keterangan', 'type' => 'text', 'length' => '500'],
                    $this->getActiveField()
                ]
            ],

            // KOP302 - Proses Pencairan (system layout untuk workflow)
            'KOP302' => [
                'gmenu' => 'KOP003',
                'fields' => [
                    ['field' => 'id', 'alias' => 'ID', 'type' => 'primarykey', 'length' => '11', 'primary' => '1'],
                    ['field' => 'periode_pencairan_id', 'alias' => 'Periode Pencairan', 'type' => 'enum', 'length' => '11', 'validate' => 'required', 'query' => "select id as value, nama_periode as name from periode_pencairan where isactive = '1'"],
                    ['field' => 'pengajuan_pinjaman_id', 'alias' => 'Pengajuan Pinjaman', 'type' => 'enum', 'length' => '11', 'validate' => 'required', 'query' => "select id as value, concat('ID ', id, ' - Rp ', format(jumlah_pinjaman, 0)) as name from pengajuan_pinjaman where status_pengajuan = 'disetujui' and isactive = '1'"],
                    ['field' => 'status_pencairan', 'alias' => 'Status Pencairan', 'type' => 'enum', 'default' => 'menunggu', 'validate' => 'required', 'show' => '0', 'query' => "select 'menunggu' as value, 'Menunggu' as name union select 'proses' as value, 'Proses' as name union select 'selesai' as value, 'Selesai' as name"],
                    ['field' => 'tanggal_pencairan', 'alias' => 'Tanggal Pencairan', 'type' => 'date', 'show' => '0'],
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
                    ['field' => 'tanggal_bayar', 'alias' => 'Tanggal Bayar', 'type' => 'date', 'show' => '0'],
                    ['field' => 'status', 'alias' => 'Status Cicilan', 'type' => 'enum', 'default' => 'belum_bayar', 'validate' => 'required', 'show' => '0', 'query' => "select 'belum_bayar' as value, 'Belum Bayar' as name union select 'lunas' as value, 'Lunas' as name union select 'telat' as value, 'Telat' as name"],
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
            ],

            // KOP504 - Laporan Neraca
            'KOP504' => [
                'gmenu' => 'KOP005',
                'fields' => [
                    ['field' => 'query', 'alias' => 'Query Neraca', 'type' => 'report', 'length' => '0', 'filter' => '0', 'list' => '0', 'query' => "SELECT 'AKTIVA' as kategori, 'Kas' as akun, COALESCE(SUM(cp.total_bayar), 0) as nominal FROM cicilan_pinjaman cp WHERE cp.status = 'lunas' UNION SELECT 'AKTIVA' as kategori, 'Piutang Anggota' as akun, COALESCE(SUM(p.sisa_pokok), 0) as nominal FROM pinjaman p WHERE p.status = 'aktif' UNION SELECT 'PASIVA' as kategori, 'Modal Simpanan Pokok' as akun, COALESCE(SUM(a.simpanan_pokok), 0) as nominal FROM anggota a UNION SELECT 'PASIVA' as kategori, 'Modal Simpanan Wajib' as akun, COALESCE(SUM(a.simpanan_wajib), 0) as nominal FROM anggota a UNION SELECT 'PASIVA' as kategori, 'SHU Ditahan' as akun, COALESCE(SUM(cp.nominal_bunga * 0.25), 0) as nominal FROM cicilan_pinjaman cp WHERE cp.status = 'lunas' ORDER BY kategori, akun"],
                    ['field' => 'tanggal_laporan', 'alias' => 'Tanggal Laporan', 'type' => 'date', 'validate' => 'required', 'list' => '0']
                ]
            ],

            // KOP505 - Laporan Laba Rugi
            'KOP505' => [
                'gmenu' => 'KOP005',
                'fields' => [
                    ['field' => 'query', 'alias' => 'Query Laba Rugi', 'type' => 'report', 'length' => '0', 'filter' => '0', 'list' => '0', 'query' => "SELECT 'PENDAPATAN' as kategori, 'Pendapatan Bunga Pinjaman' as akun, COALESCE(SUM(cp.nominal_bunga), 0) as nominal FROM cicilan_pinjaman cp WHERE cp.status = 'lunas' AND cp.tanggal_bayar BETWEEN :tanggal_dari AND :tanggal_sampai UNION SELECT 'BEBAN' as kategori, 'Beban Operasional' as akun, COALESCE(SUM(cp.nominal_bunga * 0.10), 0) as nominal FROM cicilan_pinjaman cp WHERE cp.status = 'lunas' AND cp.tanggal_bayar BETWEEN :tanggal_dari AND :tanggal_sampai UNION SELECT 'BEBAN' as kategori, 'Beban Administrasi' as akun, COALESCE(COUNT(cp.id) * 5000, 0) as nominal FROM cicilan_pinjaman cp WHERE cp.status = 'lunas' AND cp.tanggal_bayar BETWEEN :tanggal_dari AND :tanggal_sampai UNION SELECT 'LABA' as kategori, 'Laba Bersih' as akun, COALESCE(SUM(cp.nominal_bunga * 0.85), 0) as nominal FROM cicilan_pinjaman cp WHERE cp.status = 'lunas' AND cp.tanggal_bayar BETWEEN :tanggal_dari AND :tanggal_sampai ORDER BY kategori, akun"],
                    ['field' => 'tanggal_dari', 'alias' => 'Tanggal Dari', 'type' => 'date', 'validate' => 'required', 'list' => '0'],
                    ['field' => 'tanggal_sampai', 'alias' => 'Tanggal Sampai', 'type' => 'date', 'validate' => 'required', 'list' => '0']
                ]
            ],

            // KOP506 - Laporan Cash Flow
            'KOP506' => [
                'gmenu' => 'KOP005',
                'fields' => [
                    ['field' => 'query', 'alias' => 'Query Cash Flow', 'type' => 'report', 'length' => '0', 'filter' => '0', 'list' => '0', 'query' => "SELECT 'ARUS KAS OPERASI' as kategori, 'Penerimaan Cicilan Pinjaman' as aktivitas, COALESCE(SUM(cp.total_bayar), 0) as nominal FROM cicilan_pinjaman cp WHERE cp.status = 'lunas' AND cp.tanggal_bayar BETWEEN :tanggal_dari AND :tanggal_sampai UNION SELECT 'ARUS KAS INVESTASI' as kategori, 'Pencairan Pinjaman Baru' as aktivitas, COALESCE(-SUM(p.pokok_pinjaman), 0) as nominal FROM pinjaman p WHERE p.status = 'aktif' AND p.tanggal_cair BETWEEN :tanggal_dari AND :tanggal_sampai UNION SELECT 'ARUS KAS FINANCING' as kategori, 'Penerimaan Simpanan Anggota' as aktivitas, COALESCE(SUM(a.simpanan_pokok + a.simpanan_wajib), 0) as nominal FROM anggota a WHERE a.created_at BETWEEN :tanggal_dari AND :tanggal_sampai UNION SELECT 'ARUS KAS FINANCING' as kategori, 'Pembayaran SHU' as aktivitas, COALESCE(-SUM(cp.nominal_bunga * 0.75), 0) as nominal FROM cicilan_pinjaman cp WHERE cp.status = 'lunas' AND cp.tanggal_bayar BETWEEN :tanggal_dari AND :tanggal_sampai ORDER BY kategori, aktivitas"],
                    ['field' => 'tanggal_dari', 'alias' => 'Tanggal Dari', 'type' => 'date', 'validate' => 'required', 'list' => '0'],
                    ['field' => 'tanggal_sampai', 'alias' => 'Tanggal Sampai', 'type' => 'date', 'validate' => 'required', 'list' => '0']
                ]
            ],

            // KOP507 - Laporan SHU
            'KOP507' => [
                'gmenu' => 'KOP005',
                'fields' => [
                    ['field' => 'query', 'alias' => 'Query SHU', 'type' => 'report', 'length' => '0', 'filter' => '0', 'list' => '0', 'query' => "SELECT a.nomor_anggota, a.nama_lengkap, COALESCE(SUM(cp.nominal_bunga * 0.45), 0) as jasa_modal, COALESCE(SUM(cp.nominal_bunga * 0.30), 0) as jasa_usaha, COALESCE(SUM(cp.nominal_bunga * 0.75), 0) as total_shu, CASE WHEN EXISTS(SELECT 1 FROM pinjaman p2 WHERE p2.anggota_id = a.id AND p2.status = 'aktif' AND p2.sisa_pokok > 0) THEN 0 ELSE COALESCE(SUM(cp.nominal_bunga * 0.75), 0) END as shu_diterima FROM anggota a LEFT JOIN pinjaman p ON a.id = p.anggota_id LEFT JOIN cicilan_pinjaman cp ON p.id = cp.pinjaman_id WHERE cp.status = 'lunas' AND YEAR(cp.tanggal_bayar) = :tahun GROUP BY a.id, a.nomor_anggota, a.nama_lengkap ORDER BY a.nama_lengkap"],
                    ['field' => 'tahun', 'alias' => 'Tahun SHU', 'type' => 'number', 'validate' => 'required', 'list' => '0']
                ]
            ],

            // KOP508 - Laporan Jurnal Umum
            'KOP508' => [
                'gmenu' => 'KOP005',
                'fields' => [
                    ['field' => 'query', 'alias' => 'Query Jurnal Umum', 'type' => 'report', 'length' => '0', 'filter' => '0', 'list' => '0', 'query' => "SELECT cp.tanggal_bayar as tanggal, CONCAT('Penerimaan Cicilan - ', a.nama_lengkap) as keterangan, 'Kas' as debet, 'Piutang Anggota' as kredit, cp.nominal_pokok as nominal FROM cicilan_pinjaman cp LEFT JOIN pinjaman p ON cp.pinjaman_id = p.id LEFT JOIN anggota a ON p.anggota_id = a.id WHERE cp.status = 'lunas' AND cp.tanggal_bayar BETWEEN :tanggal_dari AND :tanggal_sampai UNION SELECT cp.tanggal_bayar as tanggal, CONCAT('Pendapatan Bunga - ', a.nama_lengkap) as keterangan, 'Kas' as debet, 'Pendapatan Bunga' as kredit, cp.nominal_bunga as nominal FROM cicilan_pinjaman cp LEFT JOIN pinjaman p ON cp.pinjaman_id = p.id LEFT JOIN anggota a ON p.anggota_id = a.id WHERE cp.status = 'lunas' AND cp.tanggal_bayar BETWEEN :tanggal_dari AND :tanggal_sampai UNION SELECT p.tanggal_cair as tanggal, CONCAT('Pencairan Pinjaman - ', a.nama_lengkap) as keterangan, 'Piutang Anggota' as debet, 'Kas' as kredit, p.pokok_pinjaman as nominal FROM pinjaman p LEFT JOIN anggota a ON p.anggota_id = a.id WHERE p.status IN ('aktif', 'lunas') AND p.tanggal_cair BETWEEN :tanggal_dari AND :tanggal_sampai ORDER BY tanggal, keterangan"],
                    ['field' => 'tanggal_dari', 'alias' => 'Tanggal Dari', 'type' => 'date', 'validate' => 'required', 'list' => '0'],
                    ['field' => 'tanggal_sampai', 'alias' => 'Tanggal Sampai', 'type' => 'date', 'validate' => 'required', 'list' => '0']
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
