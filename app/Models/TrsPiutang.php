<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Class TrsPiutang
 * 
 * @property string $nomor_pinjaman
 * @property string $nik
 * @property string $status_approval
 * @property string $level_approval
 * @property string $mst_paket_id
 * @property string $tenor_pinjaman
 * @property int $jumlah_paket_dipilih
 * @property float $nominal_pinjaman
 * @property float $bunga_pinjaman
 * @property float $total_pinjaman
 * @property string $tujuan_pinjaman
 * @property string $jenis_pengajuan
 * @property string|null $catatan_approval
 * @property Carbon $tanggal_pengajuan
 * @property Carbon|null $tanggal_approval
 * @property string|null $mst_periode_id
 * @property string $isactive
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property string|null $user_create
 * @property string|null $user_update
 * 
 * @property MstPaket $mst_paket
 * @property MstPeriode|null $mst_periode
 * @property MstAnggotum $mst_anggotum
 * @property Collection|TrsCicilan[] $trs_cicilans
 *
 * @package App\Models
 */
class TrsPiutang extends Model
{
	protected $table = 'trs_piutang';
	protected $primaryKey = 'nomor_pinjaman';
	public $incrementing = false;

	protected $casts = [
		'jumlah_paket_dipilih' => 'int',
		'nominal_pinjaman' => 'float',
		'bunga_pinjaman' => 'float',
		'total_pinjaman' => 'float',
		'tanggal_pengajuan' => 'datetime',
		'tanggal_approval' => 'datetime'
	];

	protected $fillable = [
		'nik',
		'status_approval',
		'level_approval',
		'mst_paket_id',
		'tenor_pinjaman',
		'jumlah_paket_dipilih',
		'nominal_pinjaman',
		'bunga_pinjaman',
		'total_pinjaman',
		'tujuan_pinjaman',
		'jenis_pengajuan',
		'catatan_approval',
		'tanggal_pengajuan',
		'tanggal_approval',
		'mst_periode_id',
		'isactive',
		'user_create',
		'user_update'
	];

	public function mst_paket()
	{
		return $this->belongsTo(MstPaket::class);
	}

	public function mst_periode()
	{
		return $this->belongsTo(MstPeriode::class);
	}

	public function mst_anggotum()
	{
		return $this->belongsTo(MstAnggotum::class, 'nik');
	}

	public function trs_cicilans()
	{
		return $this->hasMany(TrsCicilan::class, 'nomor_pinjaman');
	}
}
