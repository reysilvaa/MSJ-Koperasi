<?php

namespace App\Helpers\Koperasi\Approval;

use App\Models\ApprovalHistory;
use App\Models\PengajuanPinjaman;

/**
 * Helper class untuk fungsi-fungsi workflow approval yang dapat digunakan
 * oleh berbagai model dan controller terkait approval system
 */
class ApprovalWorkflowHelper
{
    /**
     * Role to level mapping for approval workflow
     */
    public const ROLE_LEVEL_MAP = [
        'kadmin' => 'Ketua Admin',
        'akredt' => 'Admin Kredit', 
        'ketuum' => 'Ketua Umum'
    ];

    /**
     * Status transition mapping for approval workflow
     * FIXED: Proper 3-level approval workflow
     */
    public const STATUS_MAP = [
        'kadmin' => ['approve' => 'review_admin', 'reject' => 'ditolak'],      // Level 1: kadmin -> review_admin
        'akredt' => ['approve' => 'review_panitia', 'reject' => 'ditolak'],    // Level 2: akredt -> review_panitia  
        'ketuum' => ['approve' => 'disetujui', 'reject' => 'ditolak'],         // Level 3: ketuum -> disetujui (FINAL)
    ];

    /**
     * Valid workflow transitions
     * FIXED: Proper workflow sequence
     */
    public const VALID_WORKFLOW = [
        'diajukan' => ['kadmin'],           // Status 'diajukan' hanya bisa di-approve oleh kadmin
        'review_admin' => ['akredt'],       // Status 'review_admin' hanya bisa di-approve oleh akredt
        'review_panitia' => ['ketuum'],     // Status 'review_panitia' hanya bisa di-approve oleh ketuum
    ];

    /**
     * Approval order mapping
     */
    public const APPROVAL_ORDER = [
        'kadmin' => 1,  // Level 1
        'akredt' => 2,  // Level 2
        'ketuum' => 3,  // Level 3 (Final)
    ];

    /**
     * Status descriptions for better understanding
     */
    public const STATUS_DESCRIPTIONS = [
        'draft' => 'Draft - Belum diajukan',
        'diajukan' => 'Diajukan - Menunggu review Ketua Admin',
        'review_admin' => 'Review Admin - Menunggu review Admin Kredit', 
        'review_panitia' => 'Review Panitia - Menunggu approval Ketua Umum',
        'disetujui' => 'Disetujui - Pengajuan telah disetujui semua level',
        'ditolak' => 'Ditolak - Pengajuan ditolak'
    ];

    /**
     * Get approval level for a given role
     */
    public static function getApprovalLevel($role)
    {
        return self::ROLE_LEVEL_MAP[$role] ?? $role;
    }

    /**
     * Check if user has already approved this application at their role level
     */
    public static function hasExistingApproval($pengajuan_id, $username, $role)
    {
        $level = self::getApprovalLevel($role);

        return ApprovalHistory::where('pengajuan_pinjaman_id', $pengajuan_id)
            ->where('user_create', $username)
            ->where('level_approval', $level)
            ->where('isactive', '1')
            ->exists();
    }

    /**
     * Validate workflow permissions for current user and status
     */
    public static function validateWorkflowPermissions($currentStatus, $userRole)
    {
        return isset(self::VALID_WORKFLOW[$currentStatus]) &&
               in_array($userRole, self::VALID_WORKFLOW[$currentStatus]);
    }

    /**
     * Get next status based on role and action
     */
    public static function getNextStatus($userRole, $action)
    {
        return self::STATUS_MAP[$userRole][$action] ?? 'ditolak';
    }

    /**
     * Get approval order for role
     */
    public static function getApprovalOrder($userRole)
    {
        return self::APPROVAL_ORDER[$userRole] ?? 0;
    }

    /**
     * Get status description
     */
    public static function getStatusDescription($status)
    {
        return self::STATUS_DESCRIPTIONS[$status] ?? $status;
    }

    /**
     * Check if status is final (approved or rejected)
     */
    public static function isFinalStatus($status)
    {
        return in_array($status, ['disetujui', 'ditolak']);
    }

    /**
     * Get next required approver role for current status
     */
    public static function getNextApproverRole($currentStatus)
    {
        $nextRoles = self::VALID_WORKFLOW[$currentStatus] ?? [];
        return !empty($nextRoles) ? $nextRoles[0] : null;
    }

    /**
     * Get approval progress percentage
     */
    public static function getApprovalProgress($status)
    {
        $progressMap = [
            'draft' => 0,
            'diajukan' => 25,
            'review_admin' => 50,
            'review_panitia' => 75,
            'disetujui' => 100,
            'ditolak' => 0
        ];

        return $progressMap[$status] ?? 0;
    }

    /**
     * Get all approval history for a pengajuan with proper ordering
     */
    public static function getApprovalHistoryForPengajuan($pengajuan_id)
    {
        return ApprovalHistory::where('pengajuan_pinjaman_id', $pengajuan_id)
            ->where('isactive', '1')
            ->orderBy('urutan', 'asc')
            ->orderBy('created_at', 'asc')
            ->get();
    }

    /**
     * Check if all required approvals are completed
     */
    public static function isFullyApproved($pengajuan_id)
    {
        $requiredLevels = ['Ketua Admin', 'Admin Kredit', 'Ketua Umum'];
        $approvedLevels = ApprovalHistory::where('pengajuan_pinjaman_id', $pengajuan_id)
            ->where('status_approval', 'approved')
            ->where('isactive', '1')
            ->pluck('level_approval')
            ->toArray();

        return count(array_intersect($requiredLevels, $approvedLevels)) === count($requiredLevels);
    }

    /**
     * Create approval history record
     */
    public static function createApprovalRecord($pengajuan_id, $userRole, $username, $action, $catatan = '')
    {
        $level = self::getApprovalLevel($userRole);
        $order = self::getApprovalOrder($userRole);

        return ApprovalHistory::create([
            'pengajuan_pinjaman_id' => $pengajuan_id,
            'level_approval' => $level,
            'status_approval' => $action,
            'catatan' => $catatan,
            'tanggal_approval' => now(),
            'urutan' => $order,
            'isactive' => '1',
            'user_create' => $username,
        ]);
    }

    /**
     * Process approval workflow
     */
    public static function processApprovalWorkflow($pengajuan_id, $userRole, $username, $action, $catatan = '')
    {
        // Validate permissions
        $pengajuan = PengajuanPinjaman::find($pengajuan_id);
        if (!$pengajuan) {
            return [
                'success' => false,
                'message' => 'Pengajuan tidak ditemukan'
            ];
        }

        if (!self::validateWorkflowPermissions($pengajuan->status_pengajuan, $userRole)) {
            return [
                'success' => false,
                'message' => 'Anda tidak memiliki akses untuk melakukan approval pada status ini'
            ];
        }

        // Check if user already approved
        if (self::hasExistingApproval($pengajuan_id, $username, $userRole)) {
            return [
                'success' => false,
                'message' => 'Anda sudah melakukan approval untuk pengajuan ini'
            ];
        }

        // Create approval record
        $approvalRecord = self::createApprovalRecord($pengajuan_id, $userRole, $username, $action, $catatan);

        // Update pengajuan status
        $nextStatus = self::getNextStatus($userRole, $action);
        $updateData = [
            'status_pengajuan' => $nextStatus,
            'user_update' => $username,
            'updated_at' => now(),
        ];

        // If final approval or rejection, set additional fields
        if (self::isFinalStatus($nextStatus)) {
            $updateData['tanggal_approval'] = now();
            $updateData['approved_by'] = $username;
            if ($action === 'reject') {
                $updateData['catatan_approval'] = $catatan;
            }
        }

        $pengajuan->update($updateData);

        return [
            'success' => true,
            'message' => 'Approval berhasil diproses',
            'approval_record' => $approvalRecord,
            'updated_pengajuan' => $pengajuan->fresh()
        ];
    }

    /**
     * Get approval summary for a pengajuan
     */
    public static function getApprovalSummary($pengajuan_id)
    {
        $pengajuan = PengajuanPinjaman::find($pengajuan_id);
        if (!$pengajuan) {
            return null;
        }

        $approvalHistory = self::getApprovalHistoryForPengajuan($pengajuan_id);
        $currentStatus = $pengajuan->status_pengajuan;
        $nextApprover = self::getNextApproverRole($currentStatus);
        $progress = self::getApprovalProgress($currentStatus);
        $isFinal = self::isFinalStatus($currentStatus);

        return [
            'pengajuan_id' => $pengajuan_id,
            'current_status' => $currentStatus,
            'status_description' => self::getStatusDescription($currentStatus),
            'next_approver_role' => $nextApprover,
            'next_approver_level' => $nextApprover ? self::getApprovalLevel($nextApprover) : null,
            'progress_percentage' => $progress,
            'is_final' => $isFinal,
            'is_fully_approved' => self::isFullyApproved($pengajuan_id),
            'approval_history' => $approvalHistory,
            'total_approvals' => $approvalHistory->count(),
        ];
    }
}