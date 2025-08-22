<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Notifikasi
 * 
 * @property int $id
 * @property int $anggota_id
 * @property string $judul
 * @property string $pesan
 * @property string $jenis
 * @property string $kategori
 * @property string $status
 * @property Carbon|null $tanggal_baca
 * @property string|null $link_action
 * @property array|null $data_tambahan
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
class Notifikasi extends Model
{
	protected $table = 'notifikasi';

	protected $casts = [
		'anggota_id' => 'int',
		'tanggal_baca' => 'datetime',
		'data_tambahan' => 'json'
	];

	protected $fillable = [
		'anggota_id',
		'judul',
		'pesan',
		'jenis',
		'kategori',
		'status',
		'tanggal_baca',
		'link_action',
		'data_tambahan',
		'isactive',
		'user_create',
		'user_update'
	];

	public function anggotum()
	{
		return $this->belongsTo(Anggotum::class, 'anggota_id');
	}
}
