<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

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
	use HasFactory;
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
}
