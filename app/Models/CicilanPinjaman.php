<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class CicilanPinjaman
 * 
 * @property int $id
 * @property int $pinjaman_id
 * @property int $angsuran_ke
 * @property Carbon $tanggal_jatuh_tempo
 * @property Carbon|null $tanggal_bayar
 * @property float $nominal_pokok
 * @property float $nominal_bunga
 * @property float $nominal_denda
 * @property float $total_bayar
 * @property float $nominal_dibayar
 * @property float $sisa_bayar
 * @property string $status
 * @property int $hari_terlambat
 * @property string|null $metode_pembayaran
 * @property string|null $nomor_transaksi
 * @property string|null $keterangan
 * @property string $isactive
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property string|null $user_create
 * @property string|null $user_update
 * 
 * @property Pinjaman $pinjaman
 *
 * @package App\Models
 */
class CicilanPinjaman extends Model
{
	protected $table = 'cicilan_pinjaman';

	protected $casts = [
		'pinjaman_id' => 'int',
		'angsuran_ke' => 'int',
		'tanggal_jatuh_tempo' => 'datetime',
		'tanggal_bayar' => 'datetime',
		'nominal_pokok' => 'float',
		'nominal_bunga' => 'float',
		'nominal_denda' => 'float',
		'total_bayar' => 'float',
		'nominal_dibayar' => 'float',
		'sisa_bayar' => 'float',
		'hari_terlambat' => 'int'
	];

	protected $fillable = [
		'pinjaman_id',
		'angsuran_ke',
		'tanggal_jatuh_tempo',
		'tanggal_bayar',
		'nominal_pokok',
		'nominal_bunga',
		'nominal_denda',
		'total_bayar',
		'nominal_dibayar',
		'sisa_bayar',
		'status',
		'hari_terlambat',
		'metode_pembayaran',
		'nomor_transaksi',
		'keterangan',
		'isactive',
		'user_create',
		'user_update'
	];

	public function pinjaman()
	{
		return $this->belongsTo(Pinjaman::class);
	}
}
