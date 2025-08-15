<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Anggotum
 *
 * @property int $id
 * @property string $nomor_anggota
 * @property string $nik
 * @property string $nama_lengkap
 * @property string $email
 * @property string $no_hp
 * @property string $jenis_kelamin
 * @property Carbon $tanggal_lahir
 * @property string $alamat
 * @property string $jabatan
 * @property string $departemen
 * @property float $gaji_pokok
 * @property Carbon $tanggal_bergabung
 * @property Carbon|null $tanggal_aktif
 * @property string $status_keanggotaan
 * @property float $simpanan_pokok
 * @property float $simpanan_wajib_bulanan
 * @property float $total_simpanan_wajib
 * @property float $total_simpanan_sukarela
 * @property string|null $no_rekening
 * @property string|null $nama_bank
 * @property string|null $foto
 * @property string|null $keterangan
 * @property string $isactive
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property string|null $user_create
 * @property string|null $user_update
 *
 * @property Collection|IuranAnggotum[] $iuran_anggota
 * @property Collection|Notifikasi[] $notifikasis
 * @property Collection|PengajuanPinjaman[] $pengajuan_pinjamen
 * @property Collection|Pinjaman[] $pinjaman
 *
 * @package App\Models
 */
class Anggotum extends Model
{
	protected $table = 'anggota';

	protected $casts = [
		'tanggal_lahir' => 'datetime',
		'gaji_pokok' => 'float',
		'tanggal_bergabung' => 'datetime',
		'tanggal_aktif' => 'datetime',
		'simpanan_pokok' => 'float',
		'simpanan_wajib_bulanan' => 'float',
		'total_simpanan_wajib' => 'float',
		'total_simpanan_sukarela' => 'float'
	];

	protected $fillable = [
		'nomor_anggota',
		'nik',
		'nama_lengkap',
		'email',
		'no_hp',
		'jenis_kelamin',
		'tanggal_lahir',
		'alamat',
		'jabatan',
		'departemen',
		'gaji_pokok',
		'tanggal_bergabung',
		'tanggal_aktif',
		'status_keanggotaan',
		'simpanan_pokok',
		'simpanan_wajib_bulanan',
		'total_simpanan_wajib',
		'total_simpanan_sukarela',
		'no_rekening',
		'nama_bank',
		'foto',
		'keterangan',
		'isactive',
		'user_create',
		'user_update'
	];

	public function iuran_anggota()
	{
		return $this->hasMany(IuranAnggotum::class, 'anggota_id');
	}

	public function notifikasis()
	{
		return $this->hasMany(Notifikasi::class, 'anggota_id');
	}

	public function pengajuan_pinjamen()
	{
		return $this->hasMany(PengajuanPinjaman::class, 'anggota_id');
	}

	public function pinjaman()
	{
		return $this->hasMany(Pinjaman::class, 'anggota_id');
	}
}
