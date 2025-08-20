<?php

namespace Database\Factories;

use App\Models\Pinjaman;
use App\Models\PengajuanPinjaman;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Pinjaman>
 */
class PinjamanFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Pinjaman::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        // Get random approved pengajuan and anggota
        $pengajuan = PengajuanPinjaman::where('isactive', '1')
            ->where('status_pengajuan', 'disetujui')
            ->inRandomOrder()
            ->first();
        
        $anggota = User::whereNotNull('nomor_anggota')
            ->where('isactive', '1')
            ->inRandomOrder()
            ->first();
        
        // Create if not exists
        if (!$pengajuan) {
            $pengajuan = PengajuanPinjaman::factory()->approved()->create();
        }
        
        if (!$anggota) {
            $anggota = User::factory()->anggotaAktif()->create();
        }
        
        // Generate nomor pinjaman (format: PJM-YYYY-NNNN)
        static $counter = 1;
        $nomorPinjaman = 'PJM-' . date('Y') . '-' . str_pad($counter, 4, '0', STR_PAD_LEFT);
        $counter++;
        
        // Generate loan amounts
        $nominalPinjaman = fake()->numberBetween(500000, 20000000); // 1-40 paket
        $bungaPerBulan = 1.00; // 1% flat
        $tenorBulan = fake()->randomElement([6, 12, 18, 24]);
        
        // Calculate installments
        $angsuranPokok = $nominalPinjaman / $tenorBulan;
        $angsuranBunga = $nominalPinjaman * ($bungaPerBulan / 100);
        $totalAngsuran = $angsuranPokok + $angsuranBunga;
        
        // Generate dates
        $tanggalPencairan = fake()->dateTimeBetween('-2 years', 'now');
        $tanggalJatuhTempo = (clone $tanggalPencairan)->modify("+{$tenorBulan} months");
        $tanggalAngsuranPertama = (clone $tanggalPencairan)->modify('+1 month');
        
        // Generate payment progress
        $angsuranKe = fake()->numberBetween(0, $tenorBulan);
        $totalDibayar = $angsuranKe * $totalAngsuran;
        $sisaPokok = $nominalPinjaman - ($angsuranKe * $angsuranPokok);
        
        // Determine status based on payment progress
        $status = $this->determineStatus($angsuranKe, $tenorBulan, $tanggalJatuhTempo);
        
        return [
            'nomor_pinjaman' => $nomorPinjaman,
            'pengajuan_pinjaman_id' => $pengajuan->id,
            'user_id' => $anggota->username, // Menggunakan username sesuai foreign key constraint
            'nominal_pinjaman' => $nominalPinjaman,
            'bunga_per_bulan' => $bungaPerBulan,
            'tenor_bulan' => $tenorBulan,
            'angsuran_pokok' => $angsuranPokok,
            'angsuran_bunga' => $angsuranBunga,
            'total_angsuran' => $totalAngsuran,
            'tanggal_pencairan' => $tanggalPencairan->format('Y-m-d'),
            'tanggal_jatuh_tempo' => $tanggalJatuhTempo->format('Y-m-d'),
            'tanggal_angsuran_pertama' => $tanggalAngsuranPertama->format('Y-m-d'),
            'status' => $status,
            'sisa_pokok' => max(0, $sisaPokok),
            'total_dibayar' => $totalDibayar,
            'angsuran_ke' => $angsuranKe,
            'keterangan' => fake()->optional(0.3)->sentence(),
            'isactive' => '1',
            'created_at' => now(),
            'updated_at' => now(),
            'user_create' => 'factory',
            'user_update' => 'factory'
        ];
    }
    
    /**
     * Determine loan status based on payment progress
     */
    private function determineStatus($angsuranKe, $tenorBulan, $tanggalJatuhTempo)
    {
        if ($angsuranKe >= $tenorBulan) {
            return 'lunas';
        }
        
        if ($tanggalJatuhTempo < now() && $angsuranKe < $tenorBulan) {
            return 'bermasalah';
        }
        
        return 'aktif';
    }
    
    /**
     * State untuk pinjaman aktif
     */
    public function active(): static
    {
        return $this->state(function (array $attributes) {
            $tenorBulan = fake()->randomElement([6, 12, 18, 24]);
            $angsuranKe = fake()->numberBetween(1, (int)($tenorBulan * 0.7));
            
            return [
                'status' => 'aktif',
                'tenor_bulan' => $tenorBulan,
                'angsuran_ke' => $angsuranKe,
                'tanggal_pencairan' => fake()->dateTimeBetween('-1 year', '-1 month')->format('Y-m-d'),
            ];
        });
    }
    
    /**
     * State untuk pinjaman lunas
     */
    public function lunas(): static
    {
        return $this->state(function (array $attributes) {
            $tenorBulan = fake()->randomElement([6, 12, 18, 24]);
            $nominalPinjaman = $attributes['nominal_pinjaman'] ?? 5000000;
            $angsuranPokok = $nominalPinjaman / $tenorBulan;
            $totalAngsuran = $attributes['total_angsuran'] ?? ($angsuranPokok + ($nominalPinjaman * 0.01));
            
            return [
                'status' => 'lunas',
                'tenor_bulan' => $tenorBulan,
                'angsuran_ke' => $tenorBulan,
                'sisa_pokok' => 0,
                'total_dibayar' => $totalAngsuran * $tenorBulan,
                'tanggal_pencairan' => fake()->dateTimeBetween('-2 years', '-6 months')->format('Y-m-d'),
            ];
        });
    }
    
    /**
     * State untuk pinjaman bermasalah
     */
    public function bermasalah(): static
    {
        return $this->state(function (array $attributes) {
            $tenorBulan = fake()->randomElement([6, 12, 18, 24]);
            $angsuranKe = fake()->numberBetween(0, (int)($tenorBulan * 0.5));
            
            // Set tanggal jatuh tempo sudah lewat
            $tanggalPencairan = fake()->dateTimeBetween('-3 years', '-1 year');
            $tanggalJatuhTempo = (clone $tanggalPencairan)->modify("+{$tenorBulan} months");
            
            return [
                'status' => 'bermasalah',
                'tenor_bulan' => $tenorBulan,
                'angsuran_ke' => $angsuranKe,
                'tanggal_pencairan' => $tanggalPencairan->format('Y-m-d'),
                'tanggal_jatuh_tempo' => $tanggalJatuhTempo->format('Y-m-d'),
                'keterangan' => 'Terlambat pembayaran angsuran',
            ];
        });
    }

    /**
     * State untuk user tertentu
     */
    public function forUser(User $user): static
    {
        return $this->state(fn (array $attributes) => [
            'user_id' => $user->username,
        ]);
    }

    /**
     * State untuk pengajuan tertentu
     */
    public function forPengajuan(PengajuanPinjaman $pengajuan): static
    {
        return $this->state(fn (array $attributes) => [
            'pengajuan_pinjaman_id' => $pengajuan->id,
        ]);
    }
}