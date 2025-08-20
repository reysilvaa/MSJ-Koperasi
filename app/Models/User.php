<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */

    protected $table = 'users';
    protected $primaryKey = 'username';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'username',
        'firstname',
        'lastname',
        'email',
        'password',
        'address',
        'city',
        'country',
        'postal',
        'about',
        'idroles',
        'image',
        // Kolom dari migrasi anggota
        'nomor_anggota',
        'nik',
        'nama_lengkap',
        'no_hp',
        'jenis_kelamin',
        'tanggal_lahir',
        'alamat',
        'jabatan',
        'departemen',
        'gaji_pokok',
        'tanggal_bergabung',
        'tanggal_aktif',
        'no_rekening',
        'nama_bank',
        'foto_ktp',
        'keterangan',
        'isactive',
        'user_create',
        'user_update'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'tanggal_lahir' => 'date',
        'tanggal_bergabung' => 'date',
        'tanggal_aktif' => 'date',
        'gaji_pokok' => 'decimal:2',
    ];

    /**
     * Always encrypt the password when it is updated.
     *
     * @param $value
     * @return string
     */
    public function setPasswordAttribute($value)
    {
        $this->attributes['password'] = bcrypt($value);
    }

    /**
     * Relasi ke tabel pinjaman
     */
    public function pinjaman()
    {
        return $this->hasMany(Pinjaman::class, 'user_id', 'username');
    }

    /**
     * Relasi ke tabel pengajuan_pinjaman
     */
    public function pengajuan_pinjamen()
    {
        return $this->hasMany(PengajuanPinjaman::class, 'user_id', 'username');
    }

    /**
     * Relasi ke tabel notifikasi
     */
    public function notifikasis()
    {
        return $this->hasMany(Notifikasi::class, 'user_id', 'username');
    }

    /**
     * Relasi ke tabel iuran (hasMany)
     * Setiap user memiliki banyak iuran
     */
    public function iurans()
    {
        return $this->hasMany(Iuran::class, 'user_id', 'id');
    }

    /**
     * Method untuk mendapatkan daftar anggota aktif (untuk dropdown/select)
     * FIXED: Mengatasi masalah username = 0 atau kosong
     */
    public static function getActiveAnggotaList()
    {
        return self::where('isactive', '1')
            ->whereNotNull('nomor_anggota')
            ->whereNotNull('username')
            ->where('username', '!=', '')
            ->where('username', '!=', '0')
            ->select('username', 'nomor_anggota', 'nama_lengkap')
            ->get();
    }

    /**
     * Method untuk cek apakah user adalah anggota biasa
     */
    public static function isRegularMember($userRole)
    {
        return strpos($userRole, 'anggot') !== false;
    }

    /**
     * Method untuk mendapatkan total iuran pokok user
     */
    public function getTotalIuranPokok()
    {
        return $this->iurans()->where('jenis_iuran', 'pokok')->sum('iuran');
    }

    /**
     * Method untuk mendapatkan total iuran wajib user berdasarkan tahun
     */
    public function getTotalIuranWajib($tahun = null)
    {
        $query = $this->iurans()->where('jenis_iuran', 'wajib');

        if ($tahun) {
            $query->where('tahun', $tahun);
        }

        return $query->sum('iuran');
    }

    /**
     * Method untuk mendapatkan iuran user berdasarkan bulan dan tahun
     */
    public function getIuranByPeriode($bulan, $tahun)
    {
        return $this->iurans()
            ->where('bulan', $bulan)
            ->where('tahun', $tahun)
            ->get();
    }

    /**
     * Method untuk cek apakah user sudah bayar iuran pada periode tertentu
     */
    public function hasIuranOnPeriode($jenis, $bulan, $tahun)
    {
        return $this->iurans()
            ->where('jenis_iuran', $jenis)
            ->where('bulan', $bulan)
            ->where('tahun', $tahun)
            ->exists();
    }
}
