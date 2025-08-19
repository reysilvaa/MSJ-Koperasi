<?php

namespace App\Helpers\Koperasi\Pengajuan;

use Illuminate\Support\Facades\DB;
use App\Models\PengajuanPinjaman;

/**
 * Helper class untuk fungsi-fungsi perhitungan dan kalkulasi
 * terkait Pengajuan Pinjaman
 */
class PengajuanPinjamanCalculationHelper
{
    /**
     * Business logic constants
     */
    public const NILAI_PER_PAKET = 500000; // Rp 500.000 per paket
    public const BUNGA_PER_BULAN = 1.0; // Fixed 1% per bulan
    public const TENOR_OPTIONS = [
        '6 bulan' => 6,
        '10 bulan' => 10,
        '12 bulan' => 12,
    ];

    /**
     * Calculate loan amounts
     */
    public static function calculateLoanAmounts($jumlahPaket, $tenorBulan)
    {
        $jumlahPinjaman = $jumlahPaket * self::NILAI_PER_PAKET;
        $cicilanPokok = $jumlahPinjaman / $tenorBulan;
        $bungaFlat = $jumlahPinjaman * (self::BUNGA_PER_BULAN / 100);
        $cicilanPerBulan = $cicilanPokok + $bungaFlat;
        $totalPembayaran = $cicilanPerBulan * $tenorBulan;

        return [
            'jumlah_pinjaman' => $jumlahPinjaman,
            'bunga_per_bulan' => self::BUNGA_PER_BULAN,
            'cicilan_per_bulan' => $cicilanPerBulan,
            'total_pembayaran' => $totalPembayaran,
            'angsuran_pokok' => $cicilanPokok,
            'angsuran_bunga' => $bungaFlat,
        ];
    }

    /**
     * Get tenor in months from string
     */
    public static function getTenorBulan($tenorString)
    {
        return self::TENOR_OPTIONS[$tenorString] ?? 0;
    }

    /**
     * Get statistics for pengajuan list
     */
    public static function getStatistics($collection)
    {
        return [
            'total_pengajuan' => $collection->count(),
            'pending_approval' => $collection->where('status_pengajuan', 'diajukan')->count(),
            'approved' => $collection->where('status_pengajuan', 'disetujui')->count(),
            'rejected' => $collection->where('status_pengajuan', 'ditolak')->count(),
        ];
    }

    /**
     * Check eligibility for top-up loan
     * Fixed: Removed reference to non-existent 'status' column in cicilan_pinjaman table
     */
    public static function checkTopUpEligibility($anggotaId)
    {
        return PengajuanPinjaman::select(
                'pengajuan_pinjaman.id as pengajuan_id',
                'pinjaman.id as pinjaman_id',
                'pinjaman.tenor_bulan',
                DB::raw('COUNT(CASE WHEN cicilan_pinjaman.tanggal_bayar IS NOT NULL THEN cicilan_pinjaman.id END) as total_cicilan_lunas'),
                DB::raw('(pinjaman.tenor_bulan - COUNT(CASE WHEN cicilan_pinjaman.tanggal_bayar IS NOT NULL THEN cicilan_pinjaman.id END)) as sisa_cicilan')
            )
            ->join('pinjaman', 'pengajuan_pinjaman.id', '=', 'pinjaman.pengajuan_pinjaman_id')
            ->leftJoin('cicilan_pinjaman', 'pinjaman.id', '=', 'cicilan_pinjaman.pinjaman_id')
            ->where('pengajuan_pinjaman.anggota_id', $anggotaId)
            ->where('pengajuan_pinjaman.status_pengajuan', 'disetujui')
            ->where('pinjaman.status', 'aktif')
            ->where('pengajuan_pinjaman.isactive', '1')
            ->where('pinjaman.isactive', '1')
            ->groupBy('pengajuan_pinjaman.id', 'pinjaman.id', 'pinjaman.tenor_bulan')
            ->having('sisa_cicilan', '<=', 2)
            ->first();
    }

    /**
     * Determine loan type based on eligibility
     */
    public static function determineLoanType($anggotaId)
    {
        $eligibility = self::checkTopUpEligibility($anggotaId);
        return !empty($eligibility) ? 'top_up' : 'baru';
    }
}
