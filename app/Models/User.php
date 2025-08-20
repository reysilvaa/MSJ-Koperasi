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
        'simpanan_pokok',
        'simpanan_wajib_bulanan',
        'total_simpanan_wajib',
        'total_simpanan_sukarela',
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
        'simpanan_pokok' => 'decimal:2',
        'simpanan_wajib_bulanan' => 'decimal:2',
        'total_simpanan_wajib' => 'decimal:2',
        'total_simpanan_sukarela' => 'decimal:2',
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
     * Method untuk mendapatkan data simpanan pokok berdasarkan bulan
     */
    public static function getSimpananPokokByMonth($tahun)
    {
        $result = self::selectRaw('MONTH(tanggal_bergabung) as bulan_bergabung, SUM(simpanan_pokok) as total_sp')
            ->where('isactive', '1')
            ->whereNotNull('nomor_anggota')
            ->whereYear('tanggal_bergabung', $tahun)
            ->groupByRaw('MONTH(tanggal_bergabung)')
            ->get();

        return $result->pluck('total_sp', 'bulan_bergabung')->toArray();
    }

    /**
     * Method untuk mendapatkan data simpanan wajib berdasarkan bulan
     */
    public static function getSimpananWajibByMonth($tahun)
    {
        $swData = [];
        
        for ($bulan = 1; $bulan <= 12; $bulan++) {
            $bulanFormatted = str_pad($bulan, 2, '0', STR_PAD_LEFT);
            
            $swTotal = self::where('isactive', '1')
                ->whereNotNull('nomor_anggota')
                ->where('tanggal_bergabung', '<', "{$tahun}-{$bulanFormatted}-01")
                ->sum('simpanan_wajib_bulanan');
                
            $swData[$bulan] = $swTotal;
        }

        return $swData;
    }
}