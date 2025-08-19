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
	use HasFactory;
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

	/**
	 * Filter pengajuan based on user role and approval status
	 */
	public static function filterByRole($role, $username)
	{
		$query = self::with(['anggotum', 'master_paket_pinjaman', 'periode_pencairan'])
			->where('isactive', '1');

		switch ($role) {
			case 'kadmin': // Ketua Admin - review pertama
				return $query->where('status_pengajuan', 'diajukan')
					->whereNotExists(function($subquery) use ($username) {
						$subquery->select(DB::raw(1))
								 ->from('approval_history')
								 ->whereColumn('approval_history.pengajuan_pinjaman_id', 'pengajuan_pinjaman.id')
								 ->where('approval_history.user_create', $username)
								 ->where('approval_history.isactive', '1');
					});

			case 'akredt': // Admin Kredit - review kedua
				return $query->where('status_pengajuan', 'review_admin')
					->whereNotExists(function($subquery) use ($username) {
						$subquery->select(DB::raw(1))
								 ->from('approval_history')
								 ->whereColumn('approval_history.pengajuan_pinjaman_id', 'pengajuan_pinjaman.id')
								 ->where('approval_history.user_create', $username)
								 ->where('approval_history.isactive', '1');
					});

			case 'ketuum': // Ketua Umum - final approval
				return $query->where('status_pengajuan', 'review_panitia')
					->whereNotExists(function($subquery) use ($username) {
						$subquery->select(DB::raw(1))
								 ->from('approval_history')
								 ->whereColumn('approval_history.pengajuan_pinjaman_id', 'pengajuan_pinjaman.id')
								 ->where('approval_history.user_create', $username)
								 ->where('approval_history.isactive', '1');
					});

			default: // Default: show all pending approvals (for super admin)
				return $query->whereIn('status_pengajuan', ['diajukan', 'review_admin', 'review_panitia', 'review_ketua']);
		}
	}

	/**
	 * Get current username from session or user login data
	 */
	public static function getCurrentUsername($data)
	{
		return session('username') ?? $data['user_login']->username;
	}

	/**
	 * Get user role from login data
	 */
	public static function getUserRole($data)
	{
		return $data['user_login']->idroles;
	}

	/**
	 * Check if user has admin role that can bypass approval workflow
	 */
	public static function isAdminUser($data)
	{
		$userRole = self::getUserRole($data);
		$adminRoles = ['kadmin', 'akredt', 'atrans', 'ketuum'];
		return in_array($userRole, $adminRoles);
	}

	/**
	 * Business logic constants
	 */
	public const NILAI_PER_PAKET = 500000; // Rp 500.000 per paket
	public const BUNGA_PER_BULAN = 1.0; // Fixed 1% per bulan
	public const EDITABLE_STATUSES = ['draft', 'diajukan'];
	public const TENOR_OPTIONS = [
		'6 bulan' => 6,
		'10 bulan' => 10,
		'12 bulan' => 12,
	];

	/**
	 * Check if anggota has existing pending application
	 */
	public static function hasExistingPendingApplication($anggotaId, $excludeId = null)
	{
		$query = self::where('anggota_id', $anggotaId)
			->where('status_pengajuan', 'diajukan')
			->where('isactive', '1');

		if ($excludeId) {
			$query->where('id', '!=', $excludeId);
		}

		return $query->exists();
	}

	/**
	 * Check eligibility for top-up loan
	 * Fixed: Removed reference to non-existent 'status' column in cicilan_pinjaman table
	 */
	public static function checkTopUpEligibility($anggotaId)
	{
		return self::select(
				'pengajuan_pinjaman.id as pengajuan_id',
				'pinjaman.id as pinjaman_id',
				'pinjaman.tenor_bulan',
				DB::raw('COUNT(CASE WHEN cicilan_pinjaman.tanggal_bayar IS NOT NULL THEN cicilan_pinjaman.id END) as total_cicilan_lunas'),
				DB::raw('(pinjaman.tenor_bulan - COUNT(CASE WHEN cicilan_pinjaman.tanggal_bayar IS NOT NULL THEN cicilan_pinjaman.id END)) as sisa_cicilan')
			)
			->join('pinjaman', 'pengajuan_pinjaman.id', '=', 'pinjaman.pengajuan_pinjaman_id')
			->leftJoin('cicilan_pinjaman', 'pinjaman.id', '=', 'cicilan_pinjaman.pinjaman_id')
			->where('pengajuan_pinjaman.anggota_id', $anggotaId)
			->where('pengajuan_pinjaman.status_pengajuan', 'disetujui')
			->where('pinjaman.status', 'aktif')
			->where('pengajuan_pinjaman.isactive', '1')
			->where('pinjaman.isactive', '1')
			->groupBy('pengajuan_pinjaman.id', 'pinjaman.id', 'pinjaman.tenor_bulan')
			->having('sisa_cicilan', '<=', 2)
			->first();
	}

	/**
	 * Determine loan type based on eligibility
	 */
	public static function determineLoanType($anggotaId)
	{
		$eligibility = self::checkTopUpEligibility($anggotaId);
		return !empty($eligibility) ? 'top_up' : 'baru';
	}

	/**
	 * Calculate loan amounts
	 */
	public static function calculateLoanAmounts($jumlahPaket, $tenorBulan)
	{
		$jumlahPinjaman = $jumlahPaket * self::NILAI_PER_PAKET;
		$cicilanPokok = $jumlahPinjaman / $tenorBulan;
		$bungaFlat = $jumlahPinjaman * (self::BUNGA_PER_BULAN / 100);
		$cicilanPerBulan = $cicilanPokok + $bungaFlat;
		$totalPembayaran = $cicilanPerBulan * $tenorBulan;

		return [
			'jumlah_pinjaman' => $jumlahPinjaman,
			'bunga_per_bulan' => self::BUNGA_PER_BULAN,
			'cicilan_per_bulan' => $cicilanPerBulan,
			'total_pembayaran' => $totalPembayaran,
		];
	}

	/**
	 * Get tenor in months from string
	 */
	public static function getTenorBulan($tenorString)
	{
		return self::TENOR_OPTIONS[$tenorString] ?? 0;
	}

	/**
	 * Check if pengajuan is editable
	 */
	public function isEditable()
	{
		return in_array($this->status_pengajuan, self::EDITABLE_STATUSES);
	}

	/**
	 * Get validation rules for pengajuan
	 */
	public static function getValidationRules()
	{
		return [
			'anggota_id' => 'required|exists:anggota,id',
			'paket_pinjaman_id' => 'required|exists:master_paket_pinjaman,id',
			'jumlah_paket_dipilih' => 'required|integer|min:1',
			'tenor_pinjaman' => 'required|string|in:6 bulan,10 bulan,12 bulan',
			'tujuan_pinjaman' => 'required|string|max:500',
			'jenis_pengajuan' => 'required|in:baru,top_up',
			'periode_pencairan_id' => 'required|exists:periode_pencairan,id',
		];
	}

	/**
	 * Get statistics for pengajuan list
	 */
	public static function getStatistics($collection)
	{
		return [
			'total_pengajuan' => $collection->count(),
			'pending_approval' => $collection->where('status_pengajuan', 'diajukan')->count(),
			'approved' => $collection->where('status_pengajuan', 'disetujui')->count(),
			'rejected' => $collection->where('status_pengajuan', 'ditolak')->count(),
		];
	}

	/**
	 * Apply search filters to query
	 */
	public static function applySearchFilter($query, $search)
	{
		if (!empty($search)) {
			$query->where(function($q) use ($search) {
				$q->where('id', 'like', "%$search%")
				  ->orWhereHas('anggotum', function($qa) use ($search) {
					  $qa->where('nama_lengkap', 'like', "%$search%")
						->orWhere('nomor_anggota', 'like', "%$search%");
				  });
			});
		}
		return $query;
	}

	/**
	 * Apply authorization rules to query
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
}