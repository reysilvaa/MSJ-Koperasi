<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Class MstAnggota
 * 
 * @property string $nik
 * @property int $user_id
 * @property string|null $nama_lengkap
 * @property string|null $jenis_kelamin
 * @property string|null $no_telp
 * @property string|null $alamat
 * @property string|null $departemen
 * @property string|null $jabatan
 * @property Carbon|null $tanggal_bergabung
 * @property string|null $no_rekening
 * @property string|null $nama_bank
 * @property string|null $nama_pemilik_rekening
 * @property string|null $foto_ktp
 * @property string $isactive
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property string|null $user_create
 * @property string|null $user_update
 * 
 * @property User $user
 * @property Collection|TrsCicilan[] $trs_cicilans
 * @property Collection|TrsPiutang[] $trs_piutangs
 * @property Collection|TrsPotongan[] $trs_potongans
 * @property Collection|TrsShu[] $trs_shus
 *
 * @package App\Models
 */
class MstAnggota extends Model
{
	protected $table = 'mst_anggota';
	protected $primaryKey = 'nik';
	public $incrementing = false;

	protected $casts = [
		'user_id' => 'int',
		'tanggal_bergabung' => 'datetime'
	];

	protected $fillable = [
		'user_id',
		'nama_lengkap',
		'jenis_kelamin',
		'no_telp',
		'alamat',
		'departemen',
		'jabatan',
		'tanggal_bergabung',
		'no_rekening',
		'nama_bank',
		'nama_pemilik_rekening',
		'foto_ktp',
		'isactive',
		'user_create',
		'user_update'
	];

	public function user()
	{
		return $this->belongsTo(User::class);
	}

	public function trs_cicilans()
	{
		return $this->hasMany(TrsCicilan::class, 'nik');
	}

	public function trs_piutangs()
	{
		return $this->hasMany(TrsPiutang::class, 'nik');
	}

	public function trs_potongans()
	{
		return $this->hasMany(TrsPotongan::class, 'nik');
	}

	public function trs_shus()
	{
		return $this->hasMany(TrsShu::class, 'nik');
	}
}
