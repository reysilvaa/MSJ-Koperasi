<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\DB;

/**
 * Class Anggotum
 *
 * @property int $id
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
 * @property float $simpanan_wajib_bulanan
 * @property float $total_simpanan_wajib
 * @property float $total_simpanan_sukarela
 * @property string|null $no_rekening
 * @property string|null $nama_bank
 * @property string|null $foto_ktp
 * @property string|null $keterangan
 * @property string $isactive
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property string|null $user_create
 * @property string|null $user_update
 *

 * @property Collection|Notifikasi[] $notifikasis
 * @property Collection|PengajuanPinjaman[] $pengajuan_pinjamen
 * @property Collection|Pinjaman[] $pinjaman
 *
 * @package App\Models
 */
class Anggotum extends Model
{
	use HasFactory;
	protected $table = 'anggota';

	protected $casts = [
		'tanggal_lahir' => 'datetime',
		'gaji_pokok' => 'float',
		'tanggal_bergabung' => 'datetime',
	];

	protected $fillable = [
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
		'no_rekening',
		'nama_bank',
		'foto_ktp',
		'keterangan',
		'isactive',
		'user_create',
		'user_update'
	];

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

	/**
	 * Get the user that owns the anggota.
	 */
	public function user()
	{
		return $this->belongsTo(User::class, 'nik', 'username');
	}

	/**
	 * Get active anggota list for forms
	 */
	public static function getActiveList()
	{
		return self::where('isactive', '1')
			->select('id', 'nik', 'nama_lengkap')
			->get();
	}

	/**
	 * Find anggota by user credentials
	 */
	public static function findByUserCredentials($email, $username)
	{
		return self::where('email', $email)
			->orWhere('user_create', $username)
			->where('isactive', '1')
			->select('id', 'nik', 'nama_lengkap')
			->first();
	}

	/**
	 * Check if user is regular member (anggota biasa)
	 */
	public static function isRegularMember($userRole)
	{
		return strpos($userRole, 'anggot') !== false;
	}

	/**
	 * Get Simpanan Pokok summary by month for specific year
	 */
	public static function getSimpananPokokByMonth($tahun)
	{
		$result = self::selectRaw('MONTH(tanggal_bergabung) as bulan_bergabung, SUM(simpanan_pokok) as total_sp')
			->where('isactive', '1')
			->whereYear('tanggal_bergabung', $tahun)
			->groupByRaw('MONTH(tanggal_bergabung)')
			->get();

		return $result->pluck('total_sp', 'bulan_bergabung')->toArray();
	}

	/**
	 * Get Simpanan Wajib summary by month for specific year
	 */
	public static function getSimpananWajibByMonth($tahun)
	{
		$swData = [];
		
		for ($bulan = 1; $bulan <= 12; $bulan++) {
			$bulanFormatted = str_pad($bulan, 2, '0', STR_PAD_LEFT);
			
			$swTotal = self::where('isactive', '1')
				->where('tanggal_bergabung', '<', "{$tahun}-{$bulanFormatted}-01")
				->sum('simpanan_wajib_bulanan');
				
			$swData[$bulan] = $swTotal;
		}

		return $swData;
	}
}