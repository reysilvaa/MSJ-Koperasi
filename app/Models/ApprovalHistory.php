<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use App\Helpers\Koperasi\Approval\ApprovalWorkflowHelper;

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

	public function pengajuan_pinjaman()
	{
		return $this->belongsTo(PengajuanPinjaman::class);
	}

	public function user()
	{
		return $this->belongsTo(User::class, 'user_create', 'username');
	}

	/**
	 * Delegate to ApprovalWorkflowHelper for approval level
	 * @deprecated Use ApprovalWorkflowHelper::getApprovalLevel() instead
	 */
	public static function getApprovalLevel($role)
	{
		return ApprovalWorkflowHelper::getApprovalLevel($role);
	}

	/**
	 * Delegate to ApprovalWorkflowHelper for existing approval check
	 * @deprecated Use ApprovalWorkflowHelper::hasExistingApproval() instead
	 */
	public static function hasExistingApproval($pengajuan_id, $username, $role)
	{
		return ApprovalWorkflowHelper::hasExistingApproval($pengajuan_id, $username, $role);
	}

	/**
	 * Delegate to ApprovalWorkflowHelper for workflow permissions
	 * @deprecated Use ApprovalWorkflowHelper::validateWorkflowPermissions() instead
	 */
	public static function validateWorkflowPermissions($currentStatus, $userRole)
	{
		return ApprovalWorkflowHelper::validateWorkflowPermissions($currentStatus, $userRole);
	}

	/**
	 * Delegate to ApprovalWorkflowHelper for next status
	 * @deprecated Use ApprovalWorkflowHelper::getNextStatus() instead
	 */
	public static function getNextStatus($userRole, $action)
	{
		return ApprovalWorkflowHelper::getNextStatus($userRole, $action);
	}

	/**
	 * Delegate to ApprovalWorkflowHelper for approval order
	 * @deprecated Use ApprovalWorkflowHelper::getApprovalOrder() instead
	 */
	public static function getApprovalOrder($userRole)
	{
		return ApprovalWorkflowHelper::getApprovalOrder($userRole);
	}

	/**
	 * Delegate to ApprovalWorkflowHelper for status description
	 * @deprecated Use ApprovalWorkflowHelper::getStatusDescription() instead
	 */
	public static function getStatusDescription($status)
	{
		return ApprovalWorkflowHelper::getStatusDescription($status);
	}

	/**
	 * Delegate to ApprovalWorkflowHelper for final status check
	 * @deprecated Use ApprovalWorkflowHelper::isFinalStatus() instead
	 */
	public static function isFinalStatus($status)
	{
		return ApprovalWorkflowHelper::isFinalStatus($status);
	}

	/**
	 * Delegate to ApprovalWorkflowHelper for next approver role
	 * @deprecated Use ApprovalWorkflowHelper::getNextApproverRole() instead
	 */
	public static function getNextApproverRole($currentStatus)
	{
		return ApprovalWorkflowHelper::getNextApproverRole($currentStatus);
	}

	/**
	 * Delegate to ApprovalWorkflowHelper for approval progress
	 * @deprecated Use ApprovalWorkflowHelper::getApprovalProgress() instead
	 */
	public static function getApprovalProgress($status)
	{
		return ApprovalWorkflowHelper::getApprovalProgress($status);
	}

	/**
	 * Delegate to ApprovalWorkflowHelper for approval history
	 * @deprecated Use ApprovalWorkflowHelper::getApprovalHistoryForPengajuan() instead
	 */
	public static function getApprovalHistoryForPengajuan($pengajuan_id)
	{
		return ApprovalWorkflowHelper::getApprovalHistoryForPengajuan($pengajuan_id);
	}

	/**
	 * Delegate to ApprovalWorkflowHelper for fully approved check
	 * @deprecated Use ApprovalWorkflowHelper::isFullyApproved() instead
	 */
	public static function isFullyApproved($pengajuan_id)
	{
		return ApprovalWorkflowHelper::isFullyApproved($pengajuan_id);
	}
}
