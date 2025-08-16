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
 * @property string $approver_name
 * @property string $approver_jabatan
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
		'approver_name',
		'approver_jabatan',
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
	 */
	public const STATUS_MAP = [
		'kadmin' => ['approve' => 'review_admin', 'reject' => 'ditolak'],
		'akredt' => ['approve' => 'review_panitia', 'reject' => 'ditolak'],
		'ketuum' => ['approve' => 'disetujui', 'reject' => 'ditolak'],
	];

	/**
	 * Valid workflow transitions
	 */
	public const VALID_WORKFLOW = [
		'diajukan' => ['kadmin'],
		'review_admin' => ['akredt'],
		'review_panitia' => ['ketuum'],
	];

	/**
	 * Approval order mapping
	 */
	public const APPROVAL_ORDER = [
		'kadmin' => 1,
		'akredt' => 2,
		'ketuum' => 3,
	];

	public function pengajuan_pinjaman()
	{
		return $this->belongsTo(PengajuanPinjaman::class);
	}

	public function user()
	{
		return $this->belongsTo(User::class, 'approver_name', 'username');
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
			->where('approver_name', $username)
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
}
