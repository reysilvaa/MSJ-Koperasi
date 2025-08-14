<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Class PeriodePencairan
 * 
 * @property int $id
 * @property string $nama_periode
 * @property Carbon $tanggal_mulai
 * @property Carbon $tanggal_selesai
 * @property Carbon $tanggal_pencairan
 * @property int $maksimal_aplikasi
 * @property float $total_dana_tersedia
 * @property float $total_dana_terpakai
 * @property string|null $keterangan
 * @property string $isactive
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property string|null $user_create
 * @property string|null $user_update
 * 
 * @property Collection|PengajuanPinjaman[] $pengajuan_pinjamen
 *
 * @package App\Models
 */
class PeriodePencairan extends Model
{
	protected $table = 'periode_pencairan';

	protected $casts = [
		'tanggal_mulai' => 'datetime',
		'tanggal_selesai' => 'datetime',
		'tanggal_pencairan' => 'datetime',
		'maksimal_aplikasi' => 'int',
		'total_dana_tersedia' => 'float',
		'total_dana_terpakai' => 'float'
	];

	protected $fillable = [
		'nama_periode',
		'tanggal_mulai',
		'tanggal_selesai',
		'tanggal_pencairan',
		'maksimal_aplikasi',
		'total_dana_tersedia',
		'total_dana_terpakai',
		'keterangan',
		'isactive',
		'user_create',
		'user_update'
	];

	public function pengajuan_pinjamen()
	{
		return $this->hasMany(PengajuanPinjaman::class);
	}
}
