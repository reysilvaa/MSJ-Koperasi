<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class KoperasiTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Delete existing data first
        DB::table('sys_table')->where(['gmenu' => 'KOP001', 'dmenu' => 'KOP101'])->delete();
        DB::table('sys_table')->where(['gmenu' => 'KOP001', 'dmenu' => 'KOP102'])->delete();
        DB::table('sys_table')->where(['gmenu' => 'KOP001', 'dmenu' => 'KOP103'])->delete();
        DB::table('sys_table')->where(['gmenu' => 'KOP001', 'dmenu' => 'KOP104'])->delete();
        DB::table('sys_table')->where(['gmenu' => 'KOP002', 'dmenu' => 'KOP201'])->delete();
        DB::table('sys_table')->where(['gmenu' => 'KOP002', 'dmenu' => 'KOP202'])->delete();
        DB::table('sys_table')->where(['gmenu' => 'KOP002', 'dmenu' => 'KOP203'])->delete();
        DB::table('sys_table')->where(['gmenu' => 'KOP002', 'dmenu' => 'KOP204'])->delete();
        DB::table('sys_table')->where(['gmenu' => 'KOP003', 'dmenu' => 'KOP301'])->delete();
        DB::table('sys_table')->where(['gmenu' => 'KOP003', 'dmenu' => 'KOP302'])->delete();
        DB::table('sys_table')->where(['gmenu' => 'KOP003', 'dmenu' => 'KOP303'])->delete();
        DB::table('sys_table')->where(['gmenu' => 'KOP003', 'dmenu' => 'KOP304'])->delete();
        DB::table('sys_table')->where(['gmenu' => 'KOP004', 'dmenu' => 'KOP403'])->delete();
        DB::table('sys_table')->where(['gmenu' => 'KOP004', 'dmenu' => 'KOP404'])->delete();

        // =========================
        // KOP001 - KOP101: Data Anggota
        // =========================
        DB::table('sys_table')->insert([
            'gmenu' => 'KOP001',
            'dmenu' => 'KOP101',
            'urut' => '1',
            'field' => 'id',
            'alias' => 'ID',
            'type' => 'primarykey',
            'length' => '11',
            'decimals' => '0',
            'default' => '',
            'validate' => '',
            'primary' => '1',
            'filter' => '0',
            'list' => '0',
            'show' => '0',
            'position' => '1'
        ]);

        DB::table('sys_table')->insert([
            'gmenu' => 'KOP001',
            'dmenu' => 'KOP101',
            'urut' => '2',
            'field' => 'nomor_anggota',
            'alias' => 'Nomor Anggota',
            'type' => 'text',
            'length' => '20',
            'decimals' => '0',
            'default' => '',
            'validate' => 'required|unique:anggota,nomor_anggota',
            'primary' => '0',
            'filter' => '1',
            'list' => '1',
            'show' => '1',
            'position' => '2'
        ]);

        DB::table('sys_table')->insert([
            'gmenu' => 'KOP001',
            'dmenu' => 'KOP101',
            'urut' => '3',
            'field' => 'nik',
            'alias' => 'NIK',
            'type' => 'text',
            'length' => '16',
            'decimals' => '0',
            'default' => '',
            'validate' => 'required|unique:anggota,nik',
            'primary' => '0',
            'filter' => '1',
            'list' => '1',
            'show' => '1',
            'position' => '2'
        ]);

        DB::table('sys_table')->insert([
            'gmenu' => 'KOP001',
            'dmenu' => 'KOP101',
            'urut' => '4',
            'field' => 'nama_lengkap',
            'alias' => 'Nama Lengkap',
            'type' => 'text',
            'length' => '100',
            'decimals' => '0',
            'default' => '',
            'validate' => 'required',
            'primary' => '0',
            'filter' => '1',
            'list' => '1',
            'show' => '1',
            'position' => '2'
        ]);

        DB::table('sys_table')->insert([
            'gmenu' => 'KOP001',
            'dmenu' => 'KOP101',
            'urut' => '5',
            'field' => 'email',
            'alias' => 'Email',
            'type' => 'email',
            'length' => '100',
            'decimals' => '0',
            'default' => '',
            'validate' => 'required|email|unique:anggota,email',
            'primary' => '0',
            'filter' => '1',
            'list' => '1',
            'show' => '1',
            'position' => '2'
        ]);

        DB::table('sys_table')->insert([
            'gmenu' => 'KOP001',
            'dmenu' => 'KOP101',
            'urut' => '6',
            'field' => 'no_hp',
            'alias' => 'No HP',
            'type' => 'text',
            'length' => '15',
            'decimals' => '0',
            'default' => '',
            'validate' => 'required',
            'primary' => '0',
            'filter' => '1',
            'list' => '1',
            'show' => '1',
            'position' => '2'
        ]);

        DB::table('sys_table')->insert([
            'gmenu' => 'KOP001',
            'dmenu' => 'KOP101',
            'urut' => '7',
            'field' => 'jenis_kelamin',
            'alias' => 'Jenis Kelamin',
            'type' => 'enum',
            'length' => '1',
            'decimals' => '0',
            'default' => '',
            'validate' => 'required',
            'primary' => '0',
            'filter' => '1',
            'list' => '1',
            'show' => '1',
            'query' => "select 'L' as value, 'Laki-laki' as name union select 'P' as value, 'Perempuan' as name",
            'position' => '2'
        ]);

        DB::table('sys_table')->insert([
            'gmenu' => 'KOP001',
            'dmenu' => 'KOP101',
            'urut' => '8',
            'field' => 'tanggal_lahir',
            'alias' => 'Tanggal Lahir',
            'type' => 'date',
            'length' => '10',
            'decimals' => '0',
            'default' => '',
            'validate' => 'required',
            'primary' => '0',
            'filter' => '1',
            'list' => '0',
            'show' => '1',
            'position' => '2'
        ]);

        DB::table('sys_table')->insert([
            'gmenu' => 'KOP001',
            'dmenu' => 'KOP101',
            'urut' => '9',
            'field' => 'alamat',
            'alias' => 'Alamat',
            'type' => 'text',
            'length' => '255',
            'decimals' => '0',
            'default' => '',
            'validate' => 'required',
            'primary' => '0',
            'filter' => '0',
            'list' => '0',
            'show' => '1',
            'position' => '2'
        ]);

        DB::table('sys_table')->insert([
            'gmenu' => 'KOP001',
            'dmenu' => 'KOP101',
            'urut' => '10',
            'field' => 'jabatan',
            'alias' => 'Jabatan',
            'type' => 'text',
            'length' => '50',
            'decimals' => '0',
            'default' => '',
            'validate' => 'required',
            'primary' => '0',
            'filter' => '1',
            'list' => '1',
            'show' => '1',
            'position' => '2'
        ]);

        DB::table('sys_table')->insert([
            'gmenu' => 'KOP001',
            'dmenu' => 'KOP101',
            'urut' => '11',
            'field' => 'departemen',
            'alias' => 'Departemen',
            'type' => 'text',
            'length' => '50',
            'decimals' => '0',
            'default' => '',
            'validate' => 'required',
            'primary' => '0',
            'filter' => '1',
            'list' => '1',
            'show' => '1',
            'position' => '2'
        ]);

        DB::table('sys_table')->insert([
            'gmenu' => 'KOP001',
            'dmenu' => 'KOP101',
            'urut' => '12',
            'field' => 'status_anggota',
            'alias' => 'Status Anggota',
            'type' => 'enum',
            'length' => '10',
            'decimals' => '0',
            'default' => 'aktif',
            'validate' => 'required',
            'primary' => '0',
            'filter' => '1',
            'list' => '1',
            'show' => '1',
            'query' => "select 'aktif' as value, 'Aktif' as name union select 'non_aktif' as value, 'Non Aktif' as name union select 'keluar' as value, 'Keluar' as name",
            'position' => '2'
        ]);

        DB::table('sys_table')->insert([
            'gmenu' => 'KOP001',
            'dmenu' => 'KOP101',
            'urut' => '13',
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
            'query' => "select value, name from sys_enum where idenum = 'isactive' and isactive = '1'"
        ]);

        // =========================
        // KOP001 - KOP102: Master Paket Pinjaman
        // =========================
        DB::table('sys_table')->insert([
            'gmenu' => 'KOP001',
            'dmenu' => 'KOP102',
            'urut' => '1',
            'field' => 'id',
            'alias' => 'ID',
            'type' => 'primarykey',
            'length' => '11',
            'decimals' => '0',
            'default' => '',
            'validate' => '',
            'primary' => '1',
            'filter' => '0',
            'list' => '0',
            'show' => '0',
            'position' => '2'
        ]);

        DB::table('sys_table')->insert([
            'gmenu' => 'KOP001',
            'dmenu' => 'KOP102',
            'urut' => '2',
            'field' => 'kode_paket',
            'alias' => 'Kode Paket',
            'type' => 'text',
            'length' => '10',
            'decimals' => '0',
            'default' => '',
            'validate' => 'required|unique:master_paket_pinjaman,kode_paket',
            'primary' => '0',
            'filter' => '1',
            'list' => '1',
            'show' => '1',
            'position' => '2'
        ]);

        DB::table('sys_table')->insert([
            'gmenu' => 'KOP001',
            'dmenu' => 'KOP102',
            'urut' => '3',
            'field' => 'nama_paket',
            'alias' => 'Nama Paket',
            'type' => 'text',
            'length' => '100',
            'decimals' => '0',
            'default' => '',
            'validate' => 'required',
            'primary' => '0',
            'filter' => '1',
            'list' => '1',
            'show' => '1',
            'position' => '2'
        ]);

        DB::table('sys_table')->insert([
            'gmenu' => 'KOP001',
            'dmenu' => 'KOP102',
            'urut' => '4',
            'field' => 'limit_minimum',
            'alias' => 'Limit Minimum',
            'type' => 'currency',
            'length' => '15',
            'decimals' => '2',
            'default' => '0',
            'validate' => 'required',
            'primary' => '0',
            'filter' => '1',
            'list' => '1',
            'show' => '1',
            'position' => '2'
        ]);

        DB::table('sys_table')->insert([
            'gmenu' => 'KOP001',
            'dmenu' => 'KOP102',
            'urut' => '5',
            'field' => 'limit_maksimum',
            'alias' => 'Limit Maksimum',
            'type' => 'currency',
            'length' => '15',
            'decimals' => '2',
            'default' => '0',
            'validate' => 'required',
            'primary' => '0',
            'filter' => '1',
            'list' => '1',
            'show' => '1',
            'position' => '2'
        ]);

        DB::table('sys_table')->insert([
            'gmenu' => 'KOP001',
            'dmenu' => 'KOP102',
            'urut' => '6',
            'field' => 'bunga_per_tahun',
            'alias' => 'Bunga per Tahun (%)',
            'type' => 'number',
            'length' => '5',
            'decimals' => '2',
            'default' => '0',
            'validate' => 'required',
            'primary' => '0',
            'filter' => '1',
            'list' => '1',
            'show' => '1',
            'position' => '2'
        ]);

        DB::table('sys_table')->insert([
            'gmenu' => 'KOP001',
            'dmenu' => 'KOP102',
            'urut' => '7',
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
            'query' => "select value, name from sys_enum where idenum = 'isactive' and isactive = '1'"
        ]);

        // =========================
        // KOP001 - KOP103: Master Tenor
        // =========================
        DB::table('sys_table')->insert([
            'gmenu' => 'KOP001',
            'dmenu' => 'KOP103',
            'urut' => '1',
            'field' => 'id',
            'alias' => 'ID',
            'type' => 'primarykey',
            'length' => '11',
            'decimals' => '0',
            'default' => '',
            'validate' => '',
            'primary' => '1',
            'filter' => '0',
            'list' => '0',
            'show' => '0',
            'position' => '2'
        ]);

        DB::table('sys_table')->insert([
            'gmenu' => 'KOP001',
            'dmenu' => 'KOP103',
            'urut' => '2',
            'field' => 'tenor_bulan',
            'alias' => 'Tenor (Bulan)',
            'type' => 'number',
            'length' => '3',
            'decimals' => '0',
            'default' => '',
            'validate' => 'required|unique:master_tenor,tenor_bulan',
            'primary' => '0',
            'filter' => '1',
            'list' => '1',
            'show' => '1',
            'position' => '2'
        ]);

        DB::table('sys_table')->insert([
            'gmenu' => 'KOP001',
            'dmenu' => 'KOP103',
            'urut' => '3',
            'field' => 'nama_tenor',
            'alias' => 'Nama Tenor',
            'type' => 'text',
            'length' => '50',
            'decimals' => '0',
            'default' => '',
            'validate' => 'required',
            'primary' => '0',
            'filter' => '1',
            'list' => '1',
            'show' => '1',
            'position' => '2'
        ]);

        // =========================
        // KOP002 - KOP201: Pengajuan Pinjaman
        // =========================
        DB::table('sys_table')->insert([
            'gmenu' => 'KOP002',
            'dmenu' => 'KOP201',
            'urut' => '1',
            'field' => 'id',
            'alias' => 'ID',
            'type' => 'primarykey',
            'length' => '11',
            'decimals' => '0',
            'default' => '',
            'validate' => '',
            'primary' => '1',
            'filter' => '0',
            'list' => '0',
            'show' => '0',
            'position' => '2'
        ]);

        DB::table('sys_table')->insert([
            'gmenu' => 'KOP002',
            'dmenu' => 'KOP201',
            'urut' => '2',
            'field' => 'nomor_pengajuan',
            'alias' => 'Nomor Pengajuan',
            'type' => 'text',
            'length' => '20',
            'decimals' => '0',
            'default' => '',
            'validate' => 'required',
            'primary' => '0',
            'generateid' => 'auto',
            'filter' => '1',
            'list' => '1',
            'show' => '1',
            'position' => '2'
        ]);

        DB::table('sys_table')->insert([
            'gmenu' => 'KOP002',
            'dmenu' => 'KOP201',
            'urut' => '3',
            'field' => 'anggota_id',
            'alias' => 'Anggota',
            'type' => 'enum',
            'length' => '11',
            'decimals' => '0',
            'default' => '',
            'validate' => 'required',
            'primary' => '0',
            'filter' => '1',
            'list' => '1',
            'show' => '1',
            'query' => "select id as value, concat(nomor_anggota, ' - ', nama_lengkap) as name from anggota where status_anggota = 'aktif' and isactive = '1'",
            'position' => '2'
        ]);

        DB::table('sys_table')->insert([
            'gmenu' => 'KOP002',
            'dmenu' => 'KOP201',
            'urut' => '4',
            'field' => 'master_paket_pinjaman_id',
            'alias' => 'Paket Pinjaman',
            'type' => 'enum',
            'length' => '11',
            'decimals' => '0',
            'default' => '',
            'validate' => 'required',
            'primary' => '0',
            'filter' => '1',
            'list' => '1',
            'show' => '1',
            'query' => "select id as value, concat(kode_paket, ' - ', nama_paket) as name from master_paket_pinjaman where status = 'aktif' and isactive = '1'",
            'position' => '2'
        ]);

        DB::table('sys_table')->insert([
            'gmenu' => 'KOP002',
            'dmenu' => 'KOP201',
            'urut' => '5',
            'field' => 'nominal_pengajuan',
            'alias' => 'Nominal Pengajuan',
            'type' => 'currency',
            'length' => '15',
            'decimals' => '2',
            'default' => '0',
            'validate' => 'required',
            'primary' => '0',
            'filter' => '1',
            'list' => '1',
            'show' => '1',
            'position' => '2'
        ]);

        DB::table('sys_table')->insert([
            'gmenu' => 'KOP002',
            'dmenu' => 'KOP201',
            'urut' => '6',
            'field' => 'status',
            'alias' => 'Status',
            'type' => 'enum',
            'length' => '10',
            'decimals' => '0',
            'default' => 'pending',
            'validate' => 'required',
            'primary' => '0',
            'filter' => '1',
            'list' => '1',
            'show' => '1',
            'query' => "select 'pending' as value, 'Pending' as name union select 'review' as value, 'Review' as name union select 'approved' as value, 'Approved' as name union select 'rejected' as value, 'Rejected' as name",
            'position' => '2'
        ]);

        // =========================
        // KOP004 - KOP403: Laporan Laba Rugi
        // =========================
        DB::table('sys_table')->insert([
            'gmenu' => 'KOP004',
            'dmenu' => 'KOP403',
            'urut' => '1',
            'field' => 'query',
            'alias' => 'Query Laba Rugi',
            'type' => 'report',
            'length' => '0',
            'decimals' => '0',
            'default' => '',
            'validate' => '',
            'primary' => '0',
            'filter' => '0',
            'list' => '0',
            'show' => '1',
            'query' => "SELECT 'PENDAPATAN' as kategori, 'Bunga Pinjaman' as akun, COALESCE(SUM(cp.nominal_bunga), 0) as nominal FROM cicilan_pinjaman cp WHERE YEAR(cp.tanggal_bayar) = :tahun AND cp.status = 'lunas' UNION ALL SELECT 'BEBAN' as kategori, 'Operasional' as akun, COALESCE(SUM(jk.debit), 0) as nominal FROM jurnal_keuangan jk WHERE YEAR(jk.tanggal) = :tahun AND jk.jenis_akun = 'beban' ORDER BY kategori, akun",
            'position' => '2'
        ]);

        DB::table('sys_table')->insert([
            'gmenu' => 'KOP004',
            'dmenu' => 'KOP403',
            'urut' => '2',
            'field' => 'tahun',
            'alias' => 'Tahun Laporan',
            'type' => 'number',
            'length' => '4',
            'decimals' => '0',
            'default' => date('Y'),
            'validate' => 'required',
            'primary' => '0',
            'filter' => '1',
            'list' => '0',
            'show' => '1',
            'position' => '2'
        ]);

        // =========================
        // KOP004 - KOP404: Laporan Neraca
        // =========================
        DB::table('sys_table')->insert([
            'gmenu' => 'KOP004',
            'dmenu' => 'KOP404',
            'urut' => '1',
            'field' => 'query',
            'alias' => 'Query Neraca',
            'type' => 'report',
            'length' => '0',
            'decimals' => '0',
            'default' => '',
            'validate' => '',
            'primary' => '0',
            'filter' => '0',
            'list' => '0',
            'show' => '1',
            'query' => "SELECT 'ASET' as kelompok, 'Kas dan Bank' as akun, COALESCE(SUM(CASE WHEN jk.jenis_akun = 'aset' THEN jk.debit - jk.kredit ELSE 0 END), 0) as nominal FROM jurnal_keuangan jk WHERE YEAR(jk.tanggal) <= :tahun UNION ALL SELECT 'ASET' as kelompok, 'Piutang Pinjaman' as akun, COALESCE(SUM(p.sisa_pokok), 0) as nominal FROM pinjaman p WHERE p.status = 'aktif' ORDER BY kelompok, akun",
            'position' => '2'
        ]);

        DB::table('sys_table')->insert([
            'gmenu' => 'KOP004',
            'dmenu' => 'KOP404',
            'urut' => '2',
            'field' => 'tahun',
            'alias' => 'Tahun Laporan',
            'type' => 'number',
            'length' => '4',
            'decimals' => '0',
            'default' => date('Y'),
            'validate' => 'required',
            'primary' => '0',
            'filter' => '1',
            'list' => '0',
            'show' => '1',
            'position' => '2'
        ]);
    }
}
