<?php

namespace App\Helpers\Koperasi\Pengajuan;

use App\Models\PengajuanPinjaman;

/**
 * Helper class untuk fungsi-fungsi validasi dan business rules
 * terkait Pengajuan Pinjaman
 */
class PengajuanPinjamanValidationHelper
{
    /**
     * Business logic constants
     */
    public const EDITABLE_STATUSES = ['draft', 'diajukan'];

    /**
     * Check if anggota has existing pengajuan with specific statuses
     * This unified method handles both pending application checks and broader status validation
     */
    public static function hasExistingPengajuan($anggotaId, $excludeId = null, $statuses = null)
    {
        // Default statuses to check for existing pengajuan
        // If no statuses specified, check for all pending/review statuses
        if ($statuses === null) {
            $statuses = ['diajukan', 'review_admin', 'review_panitia', 'review_ketua'];
        }

        // Handle single status as string (for backward compatibility)
        if (is_string($statuses)) {
            $statuses = [$statuses];
        }

        $query = PengajuanPinjaman::where('anggota_id', $anggotaId)
            ->whereIn('status_pengajuan', $statuses)
            ->where('isactive', '1');

        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        return $query->exists();
    }

    /**
     * Get validation rules for pengajuan
     */
    public static function getValidationRules()
    {
        return [
            'anggota_id' => 'required|exists:anggota,id',
            'paket_pinjaman_id' => 'required|exists:master_paket_pinjaman,id',
            'jumlah_paket_dipilih' => 'required|integer|min:1',
            'tenor_pinjaman' => 'required|string|in:6 bulan,10 bulan,12 bulan',
            'tujuan_pinjaman' => 'required|string|max:500',
            'jenis_pengajuan' => 'required|in:baru,top_up',
            'periode_pencairan_id' => 'required|exists:periode_pencairan,id',
        ];
    }

    /**
     * Check if pengajuan is editable based on status
     */
    public static function isEditable($statusPengajuan)
    {
        return in_array($statusPengajuan, self::EDITABLE_STATUSES);
    }

    /**
     * Validate if pengajuan can be processed based on business rules
     */
    public static function canProcessPengajuan($pengajuan, $userRole = null)
    {
        // Basic validation - pengajuan must exist and be active
        if (!$pengajuan || $pengajuan->isactive !== '1') {
            return [
                'can_process' => false,
                'message' => 'Pengajuan tidak ditemukan atau tidak aktif'
            ];
        }

        // Check if pengajuan is in editable status
        if (!self::isEditable($pengajuan->status_pengajuan)) {
            return [
                'can_process' => false,
                'message' => 'Pengajuan tidak dapat diproses karena status sudah final'
            ];
        }

        // Role-based validation if user role is provided
        if ($userRole) {
            $allowedRoles = ['kadmin', 'akredt', 'ketuum', 'atrans'];
            if (!in_array($userRole, $allowedRoles)) {
                return [
                    'can_process' => false,
                    'message' => 'User tidak memiliki akses untuk memproses pengajuan'
                ];
            }
        }

        return [
            'can_process' => true,
            'message' => 'Pengajuan dapat diproses'
        ];
    }

    /**
     * Validate loan amount based on business rules
     */
    public static function validateLoanAmount($jumlahPaket, $anggotaId = null)
    {
        // Basic validation
        if ($jumlahPaket <= 0) {
            return [
                'is_valid' => false,
                'message' => 'Jumlah paket harus lebih dari 0'
            ];
        }

        // Maximum loan validation (example: max 20 paket)
        $maxPaket = 20;
        if ($jumlahPaket > $maxPaket) {
            return [
                'is_valid' => false,
                'message' => "Jumlah paket maksimal adalah {$maxPaket} paket"
            ];
        }

        // Additional validation based on member status could be added here
        // if ($anggotaId) {
        //     // Check member's loan history, credit score, etc.
        // }

        return [
            'is_valid' => true,
            'message' => 'Jumlah pinjaman valid'
        ];
    }

    /**
     * Validate tenor selection
     */
    public static function validateTenor($tenorPinjaman)
    {
        $validTenors = ['6 bulan', '10 bulan', '12 bulan'];

        if (!in_array($tenorPinjaman, $validTenors)) {
            return [
                'is_valid' => false,
                'message' => 'Tenor pinjaman tidak valid. Pilihan yang tersedia: ' . implode(', ', $validTenors)
            ];
        }

        return [
            'is_valid' => true,
            'message' => 'Tenor pinjaman valid'
        ];
    }
}
