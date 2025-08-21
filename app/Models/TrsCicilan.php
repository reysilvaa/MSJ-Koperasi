<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class TrsCicilan
 * 
 * @property string $nomor_pinjaman
 * @property string $nik
 * @property string $periode
 * @property int $angsuran_ke
 * @property Carbon $tanggal_jatuh_tempo
 * @property float $nominal_pokok
 * @property float $bunga_rp
 * @property float $total_angsuran
 * @property Carbon|null $tanggal_bayar
 * @property string $isbayar
 * @property float $total_bayar
 * @property string $isactive
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property string|null $user_create
 * @property string|null $user_update
 * 
 * @property MstAnggota $mst_anggota
 * @property TrsPiutang $trs_piutang
 *
 * @package App\Models
 */
class TrsCicilan extends Model
{
	protected $table = 'trs_cicilan';
	public $incrementing = false;

	protected $casts = [
		'angsuran_ke' => 'int',
		'tanggal_jatuh_tempo' => 'datetime',
		'nominal_pokok' => 'float',
		'bunga_rp' => 'float',
		'total_angsuran' => 'float',
		'tanggal_bayar' => 'datetime',
		'total_bayar' => 'float'
	];

	protected $fillable = [
		'angsuran_ke',
		'tanggal_jatuh_tempo',
		'nominal_pokok',
		'bunga_rp',
		'total_angsuran',
		'tanggal_bayar',
		'isbayar',
		'total_bayar',
		'isactive',
		'user_create',
		'user_update'
	];

	public function mst_anggota()
	{
		return $this->belongsTo(MstAnggota::class, 'nik');
	}

	public function trs_piutang()
	{
		return $this->belongsTo(TrsPiutang::class, 'nomor_pinjaman');
	}
}
