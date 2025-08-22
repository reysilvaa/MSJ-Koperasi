<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class TrsShu
 * 
 * @property int $id
 * @property string $periode
 * @property string $nik
 * @property float $simpanan_total
 * @property float $bunga_total
 * @property float $hasil_persen_simpanan
 * @property float $hasil_persen_bunga
 * @property float $total_shu
 * @property string $isactive
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property string|null $user_create
 * @property string|null $user_update
 * 
 * @property MstAnggota $mst_anggota
 *
 * @package App\Models
 */
class TrsShu extends Model
{
	protected $table = 'trs_shu';

	protected $casts = [
		'simpanan_total' => 'float',
		'bunga_total' => 'float',
		'hasil_persen_simpanan' => 'float',
		'hasil_persen_bunga' => 'float',
		'total_shu' => 'float'
	];

	protected $fillable = [
		'periode',
		'nik',
		'simpanan_total',
		'bunga_total',
		'hasil_persen_simpanan',
		'hasil_persen_bunga',
		'total_shu',
		'isactive',
		'user_create',
		'user_update'
	];

	public function mst_anggotum()
	{
		return $this->belongsTo(MstAnggota::class, 'nik');
	}
}
