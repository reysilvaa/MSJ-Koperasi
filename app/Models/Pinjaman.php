<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * Class Pinjaman
 *
 * @property int $id
 * @property string $nomor_pinjaman
 * @property int $pengajuan_pinjaman_id
 * @property int $anggota_id
 * @property float $nominal_pinjaman
 * @property float $bunga_per_bulan
 * @property int $tenor_bulan
 * @property float $angsuran_pokok
 * @property float $angsuran_bunga
 * @property float $total_angsuran
 * @property Carbon $tanggal_pencairan
 * @property Carbon $tanggal_jatuh_tempo
 * @property Carbon $tanggal_angsuran_pertama
 * @property string $status
 * @property float $sisa_pokok
 * @property float $total_dibayar
 * @property int $angsuran_ke
 * @property string|null $keterangan
 * @property string $isactive
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property string|null $user_create
 * @property string|null $user_update
 *
 * @property Anggotum $anggotum
 * @property PengajuanPinjaman $pengajuan_pinjaman
 * @property Collection|CicilanPinjaman[] $cicilan_pinjamen
 *
 * @package App\Models
 */
class Pinjaman extends Model
{
    use HasFactory;
	protected $table = 'pinjaman';

	protected $casts = [
		'pengajuan_pinjaman_id' => 'int',
		'anggota_id' => 'int',
		'nominal_pinjaman' => 'float',
		'bunga_per_bulan' => 'float',
		'tenor_bulan' => 'int',
		'angsuran_pokok' => 'float',
		'angsuran_bunga' => 'float',
		'total_angsuran' => 'float',
		'tanggal_pencairan' => 'datetime',
		'tanggal_jatuh_tempo' => 'datetime',
		'tanggal_angsuran_pertama' => 'datetime',
		'sisa_pokok' => 'float',
		'total_dibayar' => 'float',
		'angsuran_ke' => 'int'
	];

	protected $fillable = [
		'nomor_pinjaman',
		'pengajuan_pinjaman_id',
		'anggota_id',
		'nominal_pinjaman',
		'bunga_per_bulan',
		'tenor_bulan',
		'angsuran_pokok',
		'angsuran_bunga',
		'total_angsuran',
		'tanggal_pencairan',
		'tanggal_jatuh_tempo',
		'tanggal_angsuran_pertama',
		'status',
		'sisa_pokok',
		'total_dibayar',
		'angsuran_ke',
		'keterangan',
		'isactive',
		'user_create',
		'user_update'
	];

	public function anggotum()
	{
		return $this->belongsTo(Anggotum::class, 'anggota_id');
	}

	public function pengajuan_pinjaman()
	{
		return $this->belongsTo(PengajuanPinjaman::class);
	}

	public function cicilan_pinjamen()
	{
		return $this->hasMany(CicilanPinjaman::class);
	}

}
