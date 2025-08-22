<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Class MstPeriode
 * 
 * @property string $id
 * @property Carbon $tahun
 * @property int $bulan
 * @property string $isactive
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property string|null $user_create
 * @property string|null $user_update
 * 
 * @property Collection|TrsPiutang[] $trs_piutangs
 *
 * @package App\Models
 */
class MstPeriode extends Model
{
	protected $table = 'mst_periode';
	public $incrementing = false;

	protected $casts = [
		'tahun' => 'datetime',
		'bulan' => 'int'
	];

	protected $fillable = [
		'tahun',
		'bulan',
		'isactive',
		'user_create',
		'user_update'
	];

	public function trs_piutangs()
	{
		return $this->hasMany(TrsPiutang::class);
	}
}
