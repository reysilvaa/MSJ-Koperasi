<?php

namespace App\Helpers\Koperasi\Pengajuan;

use App\Models\ApprovalHistory;
use App\Models\Pinjaman;
use App\Helpers\Koperasi\Pengajuan\PengajuanPinjamanCalculationHelper;
use App\Helpers\Koperasi\Approval\ApprovalWorkflowHelper;

/**
 * Helper class untuk fungsi-fungsi workflow approval dan pemrosesan
 * terkait Pengajuan Pinjaman
 */
class PengajuanPinjamanWorkflowHelper
{
    /**
     * Create approval history records based on user role and bypass level
     * Menggunakan ApprovalWorkflowHelper untuk menghindari redundansi
     */
    public static function createApprovalHistory($pengajuan, $userRole, $currentUsername)
    {
        switch ($userRole) {
            case 'kadmin': // Ketua Admin - auto-approve their own level
                ApprovalWorkflowHelper::createApprovalRecord(
                    $pengajuan->id,
                    $userRole,
                    $currentUsername,
                    'approved',
                    'Auto-approved by Admin Koperasi (level 1)'
                );
                break;

            case 'ketuum': // Ketua Umum - bypass all levels (1, 2, 3)
                // Level 1 - Ketua Admin (bypassed)
                ApprovalWorkflowHelper::createApprovalRecord(
                    $pengajuan->id,
                    'kadmin',
                    $currentUsername,
                    'approved',
                    'Auto-approved by Ketua Umum (bypass level 1)'
                );

                // Level 2 - Admin Kredit (bypassed)
                ApprovalWorkflowHelper::createApprovalRecord(
                    $pengajuan->id,
                    'akredt',
                    $currentUsername,
                    'approved',
                    'Auto-approved by Ketua Umum (bypass level 2)'
                );

                // Level 3 - Ketua Umum (their own level)
                ApprovalWorkflowHelper::createApprovalRecord(
                    $pengajuan->id,
                    'ketuum',
                    $currentUsername,
                    'approved',
                    'Approved by Ketua Umum during submission'
                );
                break;
        }
    }

    /**
     * Create active loan record for approved pengajuan
     */
    public static function createActiveLoan($pengajuan, $jumlahPaket, $tenorPinjaman, $formatHelper, $currentUsername)
    {
        // Use existing method to get tenor in months
        $tenorBulan = PengajuanPinjamanCalculationHelper::getTenorBulan($tenorPinjaman);

        // Use existing calculateLoanAmounts method to get all calculations
        $calculations = PengajuanPinjamanCalculationHelper::calculateLoanAmounts($jumlahPaket, $tenorBulan);

        // Calculate dates
        $tanggalPencairan = now();
        $tanggalJatuhTempo = now()->addMonths($tenorBulan);
        $tanggalAngsuranPertama = now()->addMonth();

        return Pinjaman::create([
            'nomor_pinjaman' => $formatHelper->IDFormat('KOP301'),
            'pengajuan_pinjaman_id' => $pengajuan->id,
            'anggota_id' => $pengajuan->anggota_id,
            'nominal_pinjaman' => $calculations['jumlah_pinjaman'],
            'bunga_per_bulan' => $calculations['bunga_per_bulan'],
            'tenor_bulan' => $tenorBulan,
            'angsuran_pokok' => $calculations['angsuran_pokok'],
            'angsuran_bunga' => $calculations['angsuran_bunga'],
            'total_angsuran' => $calculations['cicilan_per_bulan'],
            'tanggal_pencairan' => $tanggalPencairan,
            'tanggal_jatuh_tempo' => $tanggalJatuhTempo,
            'tanggal_angsuran_pertama' => $tanggalAngsuranPertama,
            'status' => 'aktif',
            'sisa_pokok' => $calculations['jumlah_pinjaman'],
            'total_dibayar' => 0,
            'angsuran_ke' => 0,
            'isactive' => '1',
            'user_create' => $currentUsername,
        ]);
    }

    /**
     * Process pengajuan creation with proper workflow
     * Menggunakan ApprovalWorkflowHelper untuk workflow yang konsisten
     */
    public static function processAfterCreation($pengajuan, $userRole, $currentUsername, $jumlahPaket = null, $tenorPinjaman = null, $formatHelper = null)
    {
        // Create approval history based on user role
        self::createApprovalHistory($pengajuan, $userRole, $currentUsername);

        // Special handling for Ketua Umum - complete approval workflow
        if ($userRole === 'ketuum') {
            // Get final approved status using ApprovalWorkflowHelper
            $finalStatus = ApprovalWorkflowHelper::getNextStatus('ketuum', 'approve');

            // Update status to approved immediately
            $pengajuan->update([
                'status_pengajuan' => $finalStatus,
                'tanggal_approval' => now(),
                'approved_by' => $currentUsername,
            ]);

            // Create active loan if parameters are provided
            if ($jumlahPaket && $tenorPinjaman && $formatHelper) {
                self::createActiveLoan($pengajuan, $jumlahPaket, $tenorPinjaman, $formatHelper, $currentUsername);
            }

            return true; // Indicates complete bypass was processed
        }

        // For other roles, update to next status in workflow
        if ($userRole === 'kadmin') {
            $nextStatus = ApprovalWorkflowHelper::getNextStatus('kadmin', 'approve');
            $pengajuan->update([
                'status_pengajuan' => $nextStatus,
                'user_update' => $currentUsername,
                'updated_at' => now(),
            ]);
        }

        return false; // Indicates normal or partial bypass workflow
    }

    /**
     * Get next approval status based on current status
     * @deprecated Use ApprovalWorkflowHelper::getNextStatus() instead
     */
    public static function getNextApprovalStatus($currentStatus)
    {
        // Map current status to next status using ApprovalWorkflowHelper
        $nextApproverRole = ApprovalWorkflowHelper::getNextApproverRole($currentStatus);
        if (!$nextApproverRole) {
            return $currentStatus; // No next status available
        }

        // Use the workflow helper to get next status
        return ApprovalWorkflowHelper::getNextStatus($nextApproverRole, 'approve');
    }

    /**
     * Get approval level based on user role
     * @deprecated Use ApprovalWorkflowHelper::getApprovalOrder() instead
     */
    public static function getApprovalLevel($userRole)
    {
        return ApprovalWorkflowHelper::getApprovalOrder($userRole);
    }

    /**
     * Check if user can approve at current pengajuan status
     * @deprecated Use ApprovalWorkflowHelper::validateWorkflowPermissions() instead
     */
    public static function canApprove($userRole, $pengajuanStatus)
    {
        return ApprovalWorkflowHelper::validateWorkflowPermissions($pengajuanStatus, $userRole);
    }

    /**
     * Process approval action using ApprovalWorkflowHelper
     */
    public static function processApproval($pengajuan, $action, $userRole, $currentUsername, $catatan = '')
    {
        // Use the centralized approval workflow helper
        return ApprovalWorkflowHelper::processApprovalWorkflow(
            $pengajuan->id,
            $userRole,
            $currentUsername,
            $action,
            $catatan
        );
    }

    /**
     * Get pengajuan approval summary using ApprovalWorkflowHelper
     */
    public static function getPengajuanApprovalSummary($pengajuan_id)
    {
        return ApprovalWorkflowHelper::getApprovalSummary($pengajuan_id);
    }

    /**
     * Check if pengajuan is fully approved using ApprovalWorkflowHelper
     */
    public static function isPengajuanFullyApproved($pengajuan_id)
    {
        return ApprovalWorkflowHelper::isFullyApproved($pengajuan_id);
    }

    /**
     * Get approval progress for pengajuan using ApprovalWorkflowHelper
     */
    public static function getPengajuanApprovalProgress($status)
    {
        return ApprovalWorkflowHelper::getApprovalProgress($status);
    }

    /**
     * Check if user has existing approval for pengajuan using ApprovalWorkflowHelper
     */
    public static function hasUserApprovedPengajuan($pengajuan_id, $username, $role)
    {
        return ApprovalWorkflowHelper::hasExistingApproval($pengajuan_id, $username, $role);
    }

    /**
     * Validate if user can approve pengajuan at current status using ApprovalWorkflowHelper
     */
    public static function canUserApprovePengajuan($currentStatus, $userRole)
    {
        return ApprovalWorkflowHelper::validateWorkflowPermissions($currentStatus, $userRole);
    }

    /**
     * Get next approver role for pengajuan using ApprovalWorkflowHelper
     */
    public static function getNextApproverForPengajuan($currentStatus)
    {
        return ApprovalWorkflowHelper::getNextApproverRole($currentStatus);
    }

    /**
     * Check if pengajuan status is final using ApprovalWorkflowHelper
     */
    public static function isPengajuanStatusFinal($status)
    {
        return ApprovalWorkflowHelper::isFinalStatus($status);
    }

    /**
     * Get status description for pengajuan using ApprovalWorkflowHelper
     */
    public static function getPengajuanStatusDescription($status)
    {
        return ApprovalWorkflowHelper::getStatusDescription($status);
    }

    /**
     * Get approval history for pengajuan using ApprovalWorkflowHelper
     */
    public static function getPengajuanApprovalHistory($pengajuan_id)
    {
        return ApprovalWorkflowHelper::getApprovalHistoryForPengajuan($pengajuan_id);
    }
}
