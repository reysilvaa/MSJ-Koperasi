<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Class PengajuanPinjaman
 *
 * @property int $id
 * @property string $nomor_pengajuan
 * @property int $anggota_id
 * @property int $paket_pinjaman_id
 * @property int $jumlah_paket_dipilih
 * @property string $tenor_pinjaman
 * @property float $jumlah_pinjaman
 * @property float $bunga_per_bulan
 * @property float $cicilan_per_bulan
 * @property float $total_pembayaran
 * @property string $tujuan_pinjaman
 * @property int|null $pinjaman_asal_id
 * @property float|null $sisa_cicilan_lama
 * @property string $jenis_pengajuan
 * @property string $status_pengajuan
 * @property string|null $catatan_pengajuan
 * @property string|null $catatan_approval
 * @property Carbon|null $tanggal_pengajuan
 * @property Carbon|null $tanggal_approval
 * @property string|null $approved_by
 * @property int|null $periode_pencairan_id
 * @property string $status_pencairan
 * @property string $isactive
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property string|null $user_create
 * @property string|null $user_update
 *
 * @property Anggotum $anggotum
 * @property MasterPaketPinjaman $master_paket_pinjaman
 * @property PeriodePencairan|null $periode_pencairan
 * @property PengajuanPinjaman|null $pengajuan_pinjaman
 * @property Collection|ApprovalHistory[] $approval_histories
 * @property Collection|PengajuanPinjaman[] $pengajuan_pinjamen
 * @property Collection|Pinjaman[] $pinjaman
 *
 * @package App\Models
 */
class PengajuanPinjaman extends Model
{
	protected $table = 'pengajuan_pinjaman';

	protected $casts = [
		'anggota_id' => 'int',
		'paket_pinjaman_id' => 'int',
		'jumlah_pinjaman' => 'float',
		'bunga_per_bulan' => 'float',
		'cicilan_per_bulan' => 'float',
		'total_pembayaran' => 'float',
		'pinjaman_asal_id' => 'int',
		'sisa_cicilan_lama' => 'float',
		'tanggal_pengajuan' => 'datetime',
		'tanggal_approval' => 'datetime',
		'periode_pencairan_id' => 'int'
	];

	protected $fillable = [
		'anggota_id',
		'paket_pinjaman_id',
		'jumlah_paket_dipilih',
		'tenor_pinjaman',
		'jumlah_pinjaman',
		'bunga_per_bulan',
		'cicilan_per_bulan',
		'total_pembayaran',
		'tujuan_pinjaman',
		'pinjaman_asal_id',
		'sisa_cicilan_lama',
		'jenis_pengajuan',
		'status_pengajuan',
		'catatan_pengajuan',
		'catatan_approval',
		'tanggal_pengajuan',
		'tanggal_approval',
		'approved_by',
		'periode_pencairan_id',
		'status_pencairan',
		'isactive',
		'user_create',
		'user_update'
	];

	public function anggota()
	{
		return $this->belongsTo(Anggotum::class, 'anggota_id');
	}

	public function anggotum()
	{
		return $this->belongsTo(Anggotum::class, 'anggota_id');
	}

	public function paketPinjaman()
	{
		return $this->belongsTo(MasterPaketPinjaman::class, 'paket_pinjaman_id');
	}

	public function master_paket_pinjaman()
	{
		return $this->belongsTo(MasterPaketPinjaman::class, 'paket_pinjaman_id');
	}

	public function periodePencairan()
	{
		return $this->belongsTo(PeriodePencairan::class, 'periode_pencairan_id');
	}

	public function periode_pencairan()
	{
		return $this->belongsTo(PeriodePencairan::class, 'periode_pencairan_id');
	}

	public function pengajuan_pinjaman()
	{
		return $this->belongsTo(PengajuanPinjaman::class, 'pinjaman_asal_id');
	}

	public function approval_histories()
	{
		return $this->hasMany(ApprovalHistory::class);
	}

	public function pengajuan_pinjamen()
	{
		return $this->hasMany(PengajuanPinjaman::class, 'pinjaman_asal_id');
	}

	public function pinjaman()
	{
		return $this->hasMany(Pinjaman::class);
	}
}
