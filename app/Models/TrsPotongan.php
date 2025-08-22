<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class TrsPotongan
 * 
 * @property string $periode
 * @property string $nik
 * @property float $simpanan
 * @property float $cicilan_pinjaman
 * @property int $potongan_ke
 * @property float $total_potongan
 * @property string|null $keterangan
 * @property string $isactive
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property string|null $user_create
 * @property string|null $user_update
 * 
 * @property MstAnggotum $mst_anggotum
 *
 * @package App\Models
 */
class TrsPotongan extends Model
{
	protected $table = 'trs_potongan';
	public $incrementing = false;

	protected $casts = [
		'simpanan' => 'float',
		'cicilan_pinjaman' => 'float',
		'potongan_ke' => 'int',
		'total_potongan' => 'float'
	];

	protected $fillable = [
		'simpanan',
		'cicilan_pinjaman',
		'potongan_ke',
		'total_potongan',
		'keterangan',
		'isactive',
		'user_create',
		'user_update'
	];

	public function mst_anggotum()
	{
		return $this->belongsTo(MstAnggotum::class, 'nik');
	}
}
