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
                    ['field' => 'nik', 'alias' => 'NIK', 'type' => 'string', 'length' => '16', 'validate' => 'required|unique:mst_anggota,nik', 'primary' => '1', 'position' => '3'],
                    ['field' => 'user_id', 'alias' => 'User', 'type' => 'enum', 'length' => '11', 'validate' => 'nullable|unique:mst_anggota,user_id', 'query' => "select id as value, email as name from users", 'position' => '4'],
                    ['field' => 'nama_lengkap', 'alias' => 'Nama Lengkap', 'type' => 'string', 'length' => '100', 'validate' => 'required', 'position' => '3'],
                    ['field' => 'jenis_kelamin', 'alias' => 'Jenis Kelamin', 'type' => 'enum', 'length' => '1', 'validate' => 'required', 'query' => "select 'L' as value, 'Laki-laki' as name union select 'P' as value, 'Perempuan' as name", 'position' => '3'],
                    ['field' => 'no_telp', 'alias' => 'No Telp', 'type' => 'string', 'length' => '15', 'validate' => 'nullable', 'position' => '3'],
                    ['field' => 'alamat', 'alias' => 'Alamat', 'type' => 'text', 'length' => '255', 'validate' => 'nullable', 'position' => '3'],
                    ['field' => 'tanggal_bergabung', 'alias' => 'Tanggal Bergabung', 'type' => 'date', 'validate' => 'nullable', 'position' => '3'],
                    ['field' => 'departemen', 'alias' => 'Departemen', 'type' => 'string', 'length' => '50', 'validate' => 'nullable', 'position' => '4'],
                    ['field' => 'jabatan', 'alias' => 'Jabatan', 'type' => 'string', 'length' => '50', 'validate' => 'nullable', 'position' => '4'],
                    ['field' => 'no_rekening', 'alias' => 'No Rekening', 'type' => 'string', 'length' => '20', 'validate' => 'nullable', 'position' => '4'],
                    ['field' => 'nama_bank', 'alias' => 'Nama Bank', 'type' => 'string', 'length' => '50', 'validate' => 'nullable', 'position' => '4'],
                    ['field' => 'nama_pemilik_rekening', 'alias' => 'Nama Pemilik Rekening', 'type' => 'string', 'length' => '100', 'validate' => 'nullable', 'position' => '4'],
                    ['field' => 'foto_ktp', 'alias' => 'Foto KTP', 'type' => 'image', 'length' => '2048', 'validate' => 'nullable|mimes:png,PNG,jpg,JPG,jpeg,JPEG|file|max:2048', 'default' => 'noimage.png', 'position' => '4'],
                    $this->getActiveField()
                ]
            ],

            // KOP102 - Master Paket Pinjaman
            'KOP102' => [
                'gmenu' => 'KOP001',
                'fields' => [
                    ['field' => 'id', 'alias' => 'ID', 'type' => 'primarykey', 'length' => '11', 'primary' => '1'],
                    ['field' => 'periode', 'alias' => 'Periode', 'type' => 'text', 'length' => '7', 'validate' => 'required', 'default' => date('Y-m'), 'note' => 'Format: 2025-08'],

                    // Stock Management Fields (Sederhana)
                    ['field' => 'stock_limit', 'alias' => 'Stock Limit', 'type' => 'number', 'length' => '11', 'validate' => 'required', 'default' => '100'],
                    ['field' => 'stock_terpakai', 'alias' => 'Stock Terpakai', 'type' => 'number', 'length' => '11', 'default' => '0', 'list' => '1', 'show' => '0'],

                    $this->getActiveField()
                ]
            ],

        // KOP103 - Periode Pencairan
            'KOP103' => [
                'gmenu' => 'KOP001',
                'fields' => [
                    ['field' => 'id', 'alias' => 'ID', 'type' => 'primarykey', 'length' => '25', 'primary' => '1'],
                    ['field' => 'tahun', 'alias' => 'Tahun', 'type' => 'number', 'length' => '4', 'validate' => 'required|digits:4|integer|min:2000|max:2100'],
                    ['field' => 'bulan', 'alias' => 'Bulan', 'type' => 'number', 'length' => '2', 'validate' => 'required|integer|min:1|max:12'],
                    $this->getActiveField()
                ]
            ],

            // KOP201 - Piutang Pinjaman
            'KOP201' => [
                'gmenu' => 'KOP002',
                'fields' => [
                    ['field' => 'nomor_pinjaman', 'alias' => 'Nomor Pinjaman', 'type' => 'text', 'length' => '20', 'validate' => 'required|unique:trs_piutang,nomor_pinjaman'],
                    ['field' => 'nik', 'alias' => 'NIK Anggota', 'type' => 'enum', 'length' => '16', 'validate' => 'required', 'query' => "select nik as value, concat(nik, ' - ', nama_lengkap) as name from mst_anggota where isactive = '1'"],
                    ['field' => 'status_approval', 'alias' => 'Status Approval', 'type' => 'enum', 'length' => '10', 'validate' => 'required', 'query' => "select 'approve' as value, 'Disetujui' as name union select 'pending' as value, 'Menunggu' as name union select 'rejected' as value, 'Ditolak' as name"],
                    ['field' => 'level_approval', 'alias' => 'Level Approval', 'type' => 'enum', 'length' => '1', 'validate' => 'required', 'query' => "select '0' as value, 'None' as name union select '1' as value, 'Level 1' as name union select '2' as value, 'Level 2' as name union select '3' as value, 'Level 3' as name"],
                    ['field' => 'mst_paket_id', 'alias' => 'Paket Pinjaman', 'type' => 'enum', 'length' => '11', 'validate' => 'required', 'query' => "select id as value, nama_paket as name from mst_paket where isactive = '1'"],
                    ['field' => 'tenor_pinjaman', 'alias' => 'Tenor Pinjaman', 'type' => 'enum', 'length' => '50', 'validate' => 'required', 'query' => "select '6 bulan' as value, '6 bulan' as name union select '12 bulan' as value, '12 bulan' as name union select '18 bulan' as value, '18 bulan' as name union select '24 bulan' as value, '24 bulan' as name"],
                    ['field' => 'jumlah_paket_dipilih', 'alias' => 'Jumlah Paket', 'type' => 'number', 'length' => '3', 'validate' => 'required|min:1'],
                    ['field' => 'nominal_pinjaman', 'alias' => 'Nominal Pinjaman', 'type' => 'currency', 'length' => '15', 'decimals' => '2', 'validate' => 'required'],
                    ['field' => 'bunga_pinjaman', 'alias' => 'Bunga Pinjaman', 'type' => 'currency', 'length' => '15', 'decimals' => '2', 'validate' => 'required'],
                    ['field' => 'total_pinjaman', 'alias' => 'Total Pinjaman', 'type' => 'currency', 'length' => '15', 'decimals' => '2', 'validate' => 'required'],
                    ['field' => 'tujuan_pinjaman', 'alias' => 'Tujuan Pinjaman', 'type' => 'text', 'length' => '500', 'validate' => 'required'],
                    ['field' => 'jenis_pengajuan', 'alias' => 'Jenis Pengajuan', 'type' => 'enum', 'length' => '10', 'validate' => 'required', 'query' => "select 'baru' as value, 'Pinjaman Baru' as name union select 'top_up' as value, 'Top Up' as name"],
                    ['field' => 'catatan_approval', 'alias' => 'Catatan Approval', 'type' => 'text', 'length' => '500', 'validate' => 'nullable'],
                    ['field' => 'tanggal_pengajuan', 'alias' => 'Tanggal Pengajuan', 'type' => 'datetime', 'length' => '0', 'validate' => 'required'],
                    ['field' => 'tanggal_approval', 'alias' => 'Tanggal Approval', 'type' => 'datetime', 'length' => '0', 'validate' => 'nullable'],
                    ['field' => 'mst_periode_id', 'alias' => 'Periode Pencairan', 'type' => 'enum', 'length' => '11', 'validate' => 'nullable', 'query' => "select id as value, concat(tahun, '-', lpad(bulan, 2, '0')) as name from mst_periode where isactive = '1'"],
                    $this->getActiveField()
                ]
            ],

            // KOP401 - Potongan
            'KOP401' => [
                'gmenu' => 'KOP004',
                'fields' => [
                    ['field' => 'periode', 'alias' => 'Periode', 'type' => 'text', 'length' => '7', 'validate' => 'required'],
                    ['field' => 'nik', 'alias' => 'NIK Anggota', 'type' => 'enum', 'length' => '16', 'validate' => 'required', 'query' => "select nik as value, concat(nik, ' - ', nama_lengkap) as name from mst_anggota where isactive = '1'"],
                    ['field' => 'potongan_ke', 'alias' => 'Potongan Ke', 'type' => 'number', 'length' => '3', 'default' => '1', 'validate' => 'required|min:1'],
                    ['field' => 'simpanan', 'alias' => 'Simpanan', 'type' => 'currency', 'length' => '15', 'decimals' => '2', 'default' => '0'],
                    ['field' => 'cicilan_pinjaman', 'alias' => 'Cicilan Pinjaman', 'type' => 'currency', 'length' => '15', 'decimals' => '2', 'default' => '0'],
                    ['field' => 'total_potongan', 'alias' => 'Total Potongan', 'type' => 'currency', 'length' => '15', 'decimals' => '2', 'validate' => 'required'],
                    ['field' => 'keterangan', 'alias' => 'Keterangan', 'type' => 'text', 'length' => '255', 'validate' => 'nullable'],
                    $this->getActiveField()
                ]
            ],

            // KOP402 - Cicilan
            'KOP402' => [
                'gmenu' => 'KOP004',
                'fields' => [
                    ['field' => 'nomor_pinjaman', 'alias' => 'Nomor Pinjaman', 'type' => 'enum', 'length' => '20', 'validate' => 'required', 'query' => "select nomor_pinjaman as value, nomor_pinjaman as name from trs_piutang"],
                    ['field' => 'nik', 'alias' => 'NIK Anggota', 'type' => 'enum', 'length' => '16', 'validate' => 'required', 'query' => "select nik as value, concat(nik, ' - ', nama_lengkap) as name from mst_anggota where isactive = '1'"],
                    ['field' => 'periode', 'alias' => 'Periode', 'type' => 'text', 'length' => '7', 'validate' => 'required'],
                    ['field' => 'angsuran_ke', 'alias' => 'Angsuran Ke', 'type' => 'number', 'length' => '3', 'validate' => 'required|min:1'],
                    ['field' => 'tanggal_jatuh_tempo', 'alias' => 'Tgl Jatuh Tempo', 'type' => 'date', 'validate' => 'required'],
                    ['field' => 'nominal_pokok', 'alias' => 'Nominal Pokok', 'type' => 'currency', 'length' => '15', 'decimals' => '2', 'validate' => 'required'],
                    ['field' => 'bunga_rp', 'alias' => 'Bunga (Rp)', 'type' => 'currency', 'length' => '15', 'decimals' => '2', 'validate' => 'required'],
                    ['field' => 'total_angsuran', 'alias' => 'Total Angsuran', 'type' => 'currency', 'length' => '15', 'decimals' => '2', 'validate' => 'required'],
                    ['field' => 'tanggal_bayar', 'alias' => 'Tanggal Bayar', 'type' => 'datetime', 'length' => '0', 'validate' => 'nullable'],
                    ['field' => 'isbayar', 'alias' => 'Status Bayar', 'type' => 'enum', 'length' => '1', 'default' => '0', 'validate' => 'required', 'query' => "select value as value, name as name from sys_enum where idenum = 'isbayar' and isactive = '1'"],
                    ['field' => 'total_bayar', 'alias' => 'Total Bayar', 'type' => 'currency', 'length' => '15', 'decimals' => '2', 'default' => '0'],
                    $this->getActiveField()
                ]
            ],

            // KOP403 - SHU
            'KOP403' => [
                'gmenu' => 'KOP004',
                'fields' => [
                    ['field' => 'id', 'alias' => 'ID', 'type' => 'primarykey', 'length' => '11', 'primary' => '1'],
                    ['field' => 'periode', 'alias' => 'Periode', 'type' => 'text', 'length' => '7', 'validate' => 'required'],
                    ['field' => 'nik', 'alias' => 'NIK Anggota', 'type' => 'enum', 'length' => '16', 'validate' => 'required', 'query' => "select nik as value, concat(nik, ' - ', nama_lengkap) as name from mst_anggota where isactive = '1'"],
                    ['field' => 'simpanan_total', 'alias' => 'Total Simpanan', 'type' => 'currency', 'length' => '15', 'decimals' => '2', 'default' => '0'],
                    ['field' => 'bunga_total', 'alias' => 'Total Bunga', 'type' => 'currency', 'length' => '15', 'decimals' => '2', 'default' => '0'],
                    ['field' => 'hasil_persen_simpanan', 'alias' => '% Simpanan', 'type' => 'number', 'length' => '5', 'validate' => 'numeric|min:0|max:100'],
                    ['field' => 'hasil_persen_bunga', 'alias' => '% Bunga', 'type' => 'number', 'length' => '5', 'validate' => 'numeric|min:0|max:100'],
                    ['field' => 'total_shu', 'alias' => 'Total SHU', 'type' => 'currency', 'length' => '15', 'decimals' => '2', 'validate' => 'required'],
                    $this->getActiveField()
                ]
            ],

            // KOP601 - Users
            'KOP601' => [
                'gmenu' => 'KOP006',
                'fields' => [
                    ['field' => 'id', 'alias' => 'ID', 'type' => 'primarykey', 'length' => '11', 'primary' => '1'],
                    ['field' => 'username', 'alias' => 'Username', 'type' => 'text', 'length' => '20', 'validate' => 'required|unique:users,username'],
                    ['field' => 'firstname', 'alias' => 'Nama Depan', 'type' => 'text', 'length' => '50', 'validate' => 'nullable'],
                    ['field' => 'lastname', 'alias' => 'Nama Belakang', 'type' => 'text', 'length' => '50', 'validate' => 'nullable'],
                    ['field' => 'email', 'alias' => 'Email', 'type' => 'email', 'length' => '100', 'validate' => 'required|email|unique:users,email'],
                    ['field' => 'password', 'alias' => 'Password', 'type' => 'password', 'length' => '100', 'validate' => 'required'],
                    ['field' => 'idroles', 'alias' => 'Role', 'type' => 'enum', 'length' => '50', 'validate' => 'required', 'query' => "select idroles as value, name as name from sys_roles"],
                    ['field' => 'image', 'alias' => 'Foto', 'type' => 'image', 'length' => '2048', 'default' => 'noimage.png', 'validate' => 'nullable|mimes:png,PNG,jpg,JPG,jpeg,JPEG|file|max:2048'],
                    $this->getActiveField()
                ]
            ],

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
