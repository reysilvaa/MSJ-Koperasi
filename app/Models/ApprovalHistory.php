<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class ApprovalHistory
 *
 * @property int $id
 * @property int $pengajuan_pinjaman_id
 * @property string $level_approval
 * @property string $status_approval
 * @property string|null $catatan
 * @property Carbon|null $tanggal_approval
 * @property int $urutan
 * @property string $isactive
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property string|null $user_create
 * @property string|null $user_update
 *
 * @property PengajuanPinjaman $pengajuan_pinjaman
 *
 * @package App\Models
 */
class ApprovalHistory extends Model
{
	protected $table = 'approval_history';

	protected $casts = [
		'pengajuan_pinjaman_id' => 'int',
		'tanggal_approval' => 'datetime',
		'urutan' => 'int'
	];

	protected $fillable = [
		'pengajuan_pinjaman_id',
		'level_approval',
		'status_approval',
		'catatan',
		'tanggal_approval',
		'urutan',
		'isactive',
		'user_create',
		'user_update'
	];

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

	public function pengajuan_pinjaman()
	{
		return $this->belongsTo(PengajuanPinjaman::class);
	}

	public function user()
	{
		return $this->belongsTo(User::class, 'user_create', 'username');
	}

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

		return self::where('pengajuan_pinjaman_id', $pengajuan_id)
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
		return self::where('pengajuan_pinjaman_id', $pengajuan_id)
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
		$approvedLevels = self::where('pengajuan_pinjaman_id', $pengajuan_id)
			->where('status_approval', 'approved')
			->where('isactive', '1')
			->pluck('level_approval')
			->toArray();

		return count(array_intersect($requiredLevels, $approvedLevels)) === count($requiredLevels);
	}
}