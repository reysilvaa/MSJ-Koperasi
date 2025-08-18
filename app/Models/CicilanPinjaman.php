<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

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
 * @property float $total_bayar
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
	use HasFactory;
	protected $table = 'cicilan_pinjaman';

	protected $casts = [
		'pinjaman_id' => 'int',
		'angsuran_ke' => 'int',
		'tanggal_jatuh_tempo' => 'datetime',
		'tanggal_bayar' => 'datetime',
		'nominal_pokok' => 'float',
		'nominal_bunga' => 'float',
		'total_bayar' => 'float'
	];

	protected $fillable = [
		'pinjaman_id',
		'angsuran_ke',
		'tanggal_jatuh_tempo',
		'tanggal_bayar',
		'nominal_pokok',
		'nominal_bunga',
		'total_bayar',
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
