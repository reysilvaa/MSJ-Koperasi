<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class IuranAnggotum
 * 
 * @property int $id
 * @property int $anggota_id
 * @property string $jenis_iuran
 * @property Carbon $tahun
 * @property int $bulan
 * @property float $nominal
 * @property Carbon|null $tanggal_bayar
 * @property string $status
 * @property string|null $metode_pembayaran
 * @property string|null $nomor_transaksi
 * @property string|null $keterangan
 * @property string $isactive
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property string|null $user_create
 * @property string|null $user_update
 * 
 * @property Anggotum $anggotum
 *
 * @package App\Models
 */
class IuranAnggotum extends Model
{
	protected $table = 'iuran_anggota';

	protected $casts = [
		'anggota_id' => 'int',
		'tahun' => 'datetime',
		'bulan' => 'int',
		'nominal' => 'float',
		'tanggal_bayar' => 'datetime'
	];

	protected $fillable = [
		'anggota_id',
		'jenis_iuran',
		'tahun',
		'bulan',
		'nominal',
		'tanggal_bayar',
		'status',
		'metode_pembayaran',
		'nomor_transaksi',
		'keterangan',
		'isactive',
		'user_create',
		'user_update'
	];

	public function anggotum()
	{
		return $this->belongsTo(Anggotum::class, 'anggota_id');
	}
}
