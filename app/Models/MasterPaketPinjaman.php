<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Class MasterPaketPinjaman
 *
 * @property int $id
 * @property string $periode
 * @property float $bunga_per_bulan
 * @property int $stock_limit
 * @property int $stock_terpakai
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
class MasterPaketPinjaman extends Model
{
	protected $table = 'master_paket_pinjaman';

	protected $casts = [
		'bunga_per_bulan' => 'float',
		'stock_limit' => 'int',
		'stock_terpakai' => 'int'
	];

	protected $fillable = [
		'periode',
		'bunga_per_bulan',
		'stock_limit',
		'stock_terpakai',
		'isactive',
		'user_create',
		'user_update'
	];

	public function pengajuan_pinjamen()
	{
		return $this->hasMany(PengajuanPinjaman::class, 'paket_pinjaman_id');
	}
}
