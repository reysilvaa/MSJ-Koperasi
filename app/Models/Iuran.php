<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Iuran extends Model
{
    use HasFactory;

    /**
     * Nama tabel yang digunakan oleh model ini
     */
    protected $table = 'iuran';

    /**
     * Primary key untuk tabel ini
     */
    protected $primaryKey = 'id_iuran';

    /**
     * Kolom yang dapat diisi secara mass assignment
     */
    protected $fillable = [
        'user_id',
        'jenis_iuran',
        'iuran',
        'bulan',
        'tahun'
    ];

    /**
     * Casting tipe data untuk kolom tertentu
     */
    protected $casts = [
        'iuran' => 'decimal:2',
        'bulan' => 'integer',
        'tahun' => 'integer',
        'jenis_iuran' => 'string'
    ];

    /**
     * Relasi ke model User (belongsTo)
     * Setiap iuran dimiliki oleh satu user
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    /**
     * Scope untuk filter berdasarkan tahun
     */
    public function scopeByTahun($query, $tahun)
    {
        return $query->where('tahun', $tahun);
    }

    /**
     * Scope untuk filter berdasarkan bulan
     */
    public function scopeByBulan($query, $bulan)
    {
        return $query->where('bulan', $bulan);
    }

    /**
     * Scope untuk filter berdasarkan jenis iuran
     */
    public function scopeByJenis($query, $jenis)
    {
        return $query->where('jenis_iuran', $jenis);
    }

    /**
     * Scope untuk filter berdasarkan user
     */
    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Accessor untuk mendapatkan nama bulan dalam bahasa Indonesia
     */
    public function getNamaBulanAttribute()
    {
        $namaBulan = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
        ];

        return $namaBulan[$this->bulan] ?? 'Tidak Diketahui';
    }

    /**
     * Accessor untuk format iuran dalam rupiah
     */
    public function getIuranFormatAttribute()
    {
        return 'Rp ' . number_format($this->iuran, 0, ',', '.');
    }

    /**
     * Accessor untuk mendapatkan periode (bulan-tahun)
     */
    public function getPeriodeAttribute()
    {
        return $this->nama_bulan . ' ' . $this->tahun;
    }
}
