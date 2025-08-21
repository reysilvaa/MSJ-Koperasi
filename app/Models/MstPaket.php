<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Class MstPaket
 * 
 * @property string $id
 * @property string $nama_paket
 * @property int $stock_limit
 * @property int $stock_terpakai
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
class MstPaket extends Model
{
	protected $table = 'mst_paket';
	public $incrementing = false;

	protected $casts = [
		'stock_limit' => 'int',
		'stock_terpakai' => 'int'
	];

	protected $fillable = [
		'nama_paket',
		'stock_limit',
		'stock_terpakai',
		'isactive',
		'user_create',
		'user_update'
	];

	public function trs_piutangs()
	{
		return $this->hasMany(TrsPiutang::class);
	}
}
