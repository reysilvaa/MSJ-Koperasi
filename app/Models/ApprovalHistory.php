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

	public function pengajuan_pinjaman()
	{
		return $this->belongsTo(PengajuanPinjaman::class);
	}
}
