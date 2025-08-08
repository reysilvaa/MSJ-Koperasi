<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class KoperasiDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Cek apakah data master tenor sudah ada
        if (DB::table('master_tenor')->count() == 0) {
            // Insert Master Tenor
        DB::table('master_tenor')->insert([
            [
                'tenor_bulan' => 6,
                'nama_tenor' => '6 Bulan',
                'deskripsi' => 'Tenor pendek 6 bulan dengan bunga flat 1%',
                'status' => 'aktif',
                'isactive' => 1,
                'created_at' => now(),
                'updated_at' => now(),
                'user_create' => 'seeder',
                'user_update' => 'seeder'
            ],
            [
                'tenor_bulan' => 10,
                'nama_tenor' => '10 Bulan',
                'deskripsi' => 'Tenor menengah 10 bulan dengan bunga flat 1%',
                'status' => 'aktif',
                'isactive' => 1,
                'created_at' => now(),
                'updated_at' => now(),
                'user_create' => 'seeder',
                'user_update' => 'seeder'
            ],
            [
                'tenor_bulan' => 12,
                'nama_tenor' => '12 Bulan',
                'deskripsi' => 'Tenor panjang 12 bulan dengan bunga flat 1%',
                'status' => 'aktif',
                'isactive' => 1,
                'created_at' => now(),
                'updated_at' => now(),
                'user_create' => 'seeder',
                'user_update' => 'seeder'
            ],
            [
                'tenor_bulan' => 15,
                'nama_tenor' => '15 Bulan',
                'deskripsi' => 'Tenor panjang 15 bulan dengan bunga flat 1%',
                'status' => 'aktif',
                'isactive' => 1,
                'created_at' => now(),
                'updated_at' => now(),
                'user_create' => 'seeder',
                'user_update' => 'seeder'
            ]
        ]);
        }

        // Cek apakah data paket pinjaman sudah ada
        if (DB::table('master_paket_pinjaman')->count() == 0) {
        // Insert Master Paket Pinjaman
        DB::table('master_paket_pinjaman')->insert([
            [
                'kode_paket' => 'PKT-005',
                'nama_paket' => 'Paket 5 Unit',
                'deskripsi' => 'Paket pinjaman 5 unit @ Rp 500.000 = Rp 2.500.000',
                'jumlah_paket' => 5,
                'nilai_per_paket' => 500000,
                'limit_minimum' => 2500000,
                'limit_maksimum' => 2500000,
                'bunga_per_bulan' => 1.00,
                'tenor_diizinkan' => json_encode([1, 2, 3]), // ID tenor 6, 10, 12 bulan
                'status' => 'aktif',
                'syarat_pengajuan' => json_encode([
                    'min_masa_kerja' => '6 bulan',
                    'gaji_minimal' => 3000000,
                    'dokumen' => ['KTP', 'Slip Gaji', 'Rekening Tabungan']
                ]),
                'isactive' => 1,
                'created_at' => now(),
                'updated_at' => now(),
                'user_create' => 'seeder',
                'user_update' => 'seeder'
            ],
            [
                'kode_paket' => 'PKT-010',
                'nama_paket' => 'Paket 10 Unit',
                'deskripsi' => 'Paket pinjaman 10 unit @ Rp 500.000 = Rp 5.000.000',
                'jumlah_paket' => 10,
                'nilai_per_paket' => 500000,
                'limit_minimum' => 5000000,
                'limit_maksimum' => 5000000,
                'bunga_per_bulan' => 1.00,
                'tenor_diizinkan' => json_encode([1, 2, 3]),
                'status' => 'aktif',
                'syarat_pengajuan' => json_encode([
                    'min_masa_kerja' => '12 bulan',
                    'gaji_minimal' => 5000000,
                    'dokumen' => ['KTP', 'Slip Gaji', 'Rekening Tabungan', 'NPWP']
                ]),
                'isactive' => 1,
                'created_at' => now(),
                'updated_at' => now(),
                'user_create' => 'seeder',
                'user_update' => 'seeder'
            ],
            [
                'kode_paket' => 'PKT-020',
                'nama_paket' => 'Paket 20 Unit',
                'deskripsi' => 'Paket pinjaman 20 unit @ Rp 500.000 = Rp 10.000.000',
                'jumlah_paket' => 20,
                'nilai_per_paket' => 500000,
                'limit_minimum' => 10000000,
                'limit_maksimum' => 10000000,
                'bunga_per_bulan' => 1.00,
                'tenor_diizinkan' => json_encode([1, 2, 3]),
                'status' => 'aktif',
                'syarat_pengajuan' => json_encode([
                    'min_masa_kerja' => '24 bulan',
                    'gaji_minimal' => 8000000,
                    'dokumen' => ['KTP', 'Slip Gaji', 'Rekening Tabungan', 'NPWP', 'Surat Referensi']
                ]),
                'isactive' => 1,
                'created_at' => now(),
                'updated_at' => now(),
                'user_create' => 'seeder',
                'user_update' => 'seeder'
            ],
            [
                'kode_paket' => 'PKT-040',
                'nama_paket' => 'Paket 40 Unit',
                'deskripsi' => 'Paket pinjaman 40 unit @ Rp 500.000 = Rp 20.000.000',
                'jumlah_paket' => 40,
                'nilai_per_paket' => 500000,
                'limit_minimum' => 20000000,
                'limit_maksimum' => 20000000,
                'bunga_per_bulan' => 1.00,
                'tenor_diizinkan' => json_encode([2, 3]), // Hanya 10 dan 12 bulan
                'status' => 'aktif',
                'syarat_pengajuan' => json_encode([
                    'min_masa_kerja' => '36 bulan',
                    'gaji_minimal' => 15000000,
                    'dokumen' => ['KTP', 'Slip Gaji', 'Rekening Tabungan', 'NPWP', 'Surat Referensi', 'Jaminan']
                ]),
                'isactive' => 1,
                'created_at' => now(),
                'updated_at' => now(),
                'user_create' => 'seeder',
                'user_update' => 'seeder'
            ]
        ]);
        }

        // Cek apakah data anggota sudah ada
        if (DB::table('anggota')->where('nomor_anggota', 'A240001')->count() == 0) {
        // Insert data anggota untuk user dengan role anggota
        DB::table('anggota')->insert([
            'nomor_anggota' => 'A240001',
            'nik' => '3515123456789012',
            'nama_lengkap' => 'Anggota Koperasi',
            'email' => 'anggota.koperasi@spunindo.com',
            'no_hp' => '081234567890',
            'jenis_kelamin' => 'L',
            'tanggal_lahir' => '1990-01-01',
            'alamat' => 'Jl. Contoh No. 123, Sidoarjo, Jawa Timur',
            'jabatan' => 'Staff',
            'departemen' => 'IT',
            'gaji_pokok' => 8000000.00,
            'tanggal_bergabung' => '2024-01-01',
            'tanggal_aktif' => '2024-02-01',
            'status_keanggotaan' => 'aktif',
            'simpanan_pokok' => 500000.00,
            'simpanan_wajib_bulanan' => 100000.00,
            'total_simpanan_wajib' => 800000.00, // 8 bulan x 100rb
            'total_simpanan_sukarela' => 0.00,
            'no_rekening' => '1234567890',
            'nama_bank' => 'BRI',
            'foto' => null,
            'keterangan' => 'Anggota aktif dengan status baik',
            'isactive' => 1,
            'created_at' => now(),
            'updated_at' => now(),
            'user_create' => 'seeder',
            'user_update' => 'seeder'
        ]);
        }

        // Cek apakah user koperasi sudah ada
        if (DB::table('users')->where('username', 'admin_koperasi')->count() == 0) {
        // Insert Users untuk setiap role
        DB::table('users')->insert([
            [
                'username' => 'anggota_koperasi',
                'firstname' => 'Anggota',
                'lastname' => 'Koperasi',
                'email' => 'anggota.koperasi@spunindo.com',
                'password' => bcrypt('anggota123'),
                'idroles' => 'anggot'
            ],
            [
                'username' => 'admin_koperasi',
                'firstname' => 'Ketua Admin',
                'lastname' => 'Koperasi',
                'email' => 'admin.koperasi@spunindo.com',
                'password' => bcrypt('admin123'),
                'idroles' => 'kadmin'
            ],
            [
                'username' => 'admin_kredit',
                'firstname' => 'Admin Kredit',
                'lastname' => 'Koperasi',
                'email' => 'admin.kredit@spunindo.com',
                'password' => bcrypt('kredit123'),
                'idroles' => 'akredt'
            ],
            [
                'username' => 'admin_transfer',
                'firstname' => 'Admin Transfer',
                'lastname' => 'Koperasi',
                'email' => 'admin.transfer@spunindo.com',
                'password' => bcrypt('transfer123'),
                'idroles' => 'atrans'
            ],
            [
                'username' => 'ketua_umum',
                'firstname' => 'Ketua Umum',
                'lastname' => 'Koperasi',
                'email' => 'ketua.umum@spunindo.com',
                'password' => bcrypt('ketua123'),
                'idroles' => 'ketuum'
            ]
        ]);
        }
    }
}
