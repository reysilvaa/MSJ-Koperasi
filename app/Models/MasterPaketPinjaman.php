<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Class MasterPaketPinjaman
 *
 * @property int $id
 * @property string $periode
 * @property float $bunga_per_bulan
 * @property int $stock_limit
 * @property int $stock_terpakai
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
class MasterPaketPinjaman extends Model
{
	protected $table = 'master_paket_pinjaman';

	protected $casts = [
		'bunga_per_bulan' => 'float',
		'stock_limit' => 'int',
		'stock_terpakai' => 'int'
	];

	protected $fillable = [
		'periode',
		'bunga_per_bulan',
		'stock_limit',
		'stock_terpakai',
		'isactive',
		'user_create',
		'user_update'
	];

	public function pengajuan_pinjamen()
	{
		return $this->hasMany(PengajuanPinjaman::class, 'paket_pinjaman_id');
	}

	/**
	 * Get active paket list for forms
	 */
	public static function getActiveList()
	{
		return self::where('isactive', '1')
			->select('id', 'periode', 'stock_limit', 'stock_terpakai')
			->get();
	}

	/**
	 * Update stock usage (for tracking only, no validation)
	 */
	public function updateStockUsage($amount, $operation = 'increment')
	{
		if ($operation === 'increment') {
			$this->increment('stock_terpakai', $amount);
		} else {
			$this->decrement('stock_terpakai', $amount);
		}
	}

	/**
	 * Get available stock (for display only)
	 */
	public function getAvailableStockAttribute()
	{
		return max(0, $this->stock_limit - $this->stock_terpakai);
	}

	/**
	 * Check if stock is available (for display only, no blocking)
	 */
	public function hasAvailableStock($requestedAmount)
	{
		return $this->available_stock >= $requestedAmount;
	}
}
