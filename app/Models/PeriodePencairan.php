<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Class PeriodePencairan
 *
 * @property int $id
 * @property int $tahun
 * @property int $bulan
 * @property string $isactive
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property string|null $user_create
 * @property string|null $user_update
 *
 * @property Collection|PengajuanPinjaman[] $pengajuan_pinjamen
 *
 * @package App\Models
 */
class PeriodePencairan extends Model
{
	protected $table = 'periode_pencairan';

	protected $casts = [
		'tahun' => 'int',
		'bulan' => 'int'
	];

	protected $fillable = [
		'tahun',
		'bulan',
		'isactive',
		'user_create',
		'user_update'
	];

	public function pengajuan_pinjamen()
	{
		return $this->hasMany(PengajuanPinjaman::class);
	}

	/**
	 * Get nama periode display (Januari 2025, Februari 2025, etc)
	 */
	public function getNamaPeriodeAttribute()
	{
		$bulan_names = [
			1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
			5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
			9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
		];

		return $bulan_names[$this->bulan] . ' ' . $this->tahun;
	}

	/**
	 * Auto generate periode untuk 1 tahun (Januari - Desember)
	 */
	public static function generateYearlyPeriods($tahun, $user_create = 'system')
	{
		$created_periods = [];

		for ($bulan = 1; $bulan <= 12; $bulan++) {
			// Check if already exists
			$exists = self::where('tahun', $tahun)
						  ->where('bulan', $bulan)
						  ->exists();

			if (!$exists) {
				$period = self::create([
					'tahun' => $tahun,
					'bulan' => $bulan,
					'isactive' => '1',
					'user_create' => $user_create,
					'user_update' => $user_create
				]);

				$created_periods[] = $period;
			}
		}

		return $created_periods;
	}
	/**
	 * Get statistics for periode list (following MSJ Framework pattern)
	 */
	public static function getStatistics($collection)
	{
		return [
			'total_periode' => $collection->count(),
			'periode_aktif' => $collection->where('isactive', '1')->count(),
			'periode_nonaktif' => $collection->where('isactive', '0')->count(),
			'tahun_terbaru' => $collection->max('tahun'),
		];
	}

	/**
	 * Apply search filters to query (following MSJ Framework pattern)
	 */
	public static function applySearchFilter($query, $search)
	{
		if (!empty($search)) {
			$query->where(function($q) use ($search) {
				$q->where('tahun', 'like', "%$search%")
				  ->orWhere('bulan', 'like', "%$search%");
			});
		}
		return $query;
	}

	/**
	 * Apply authorization rules to query (following MSJ Framework pattern)
	 */
	public static function applyAuthorizationRules($query, $authorize, $userRoles)
	{
		if ($authorize->rules == '1') {
			$query->where(function ($q) use ($userRoles) {
				foreach ($userRoles as $role) {
					$q->orWhereRaw("FIND_IN_SET(?, REPLACE(rules, ' ', ''))", [$role]);
				}
			});
		}
		return $query;
	}

	/**
	 * Get active list for dropdowns (following MSJ Framework pattern)
	 */
	public static function getActiveList()
	{
		return self::where('isactive', '1')
			->orderBy('tahun', 'desc')
			->orderBy('bulan', 'asc')
			->get();
	}
}
