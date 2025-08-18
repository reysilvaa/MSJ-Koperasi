<?php

namespace Database\Factories;

use App\Models\Pinjaman;
use App\Models\PengajuanPinjaman;
use App\Models\Anggotum;
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
    public function definition()
    {
        // Get random approved pengajuan and anggota
        $pengajuan = PengajuanPinjaman::where('isactive', '1')->inRandomOrder()->first();
        $anggota = Anggotum::where('isactive', '1')->inRandomOrder()->first();
        
        $pengajuanId = $pengajuan ? $pengajuan->id : 1;
        $anggotaId = $anggota ? $anggota->id : 1;
        
        // Generate nomor pinjaman (format: PJM-YYYY-NNNN)
        static $counter = 1;
        $nomorPinjaman = 'PJM-' . date('Y') . '-' . str_pad($counter, 4, '0', STR_PAD_LEFT);
        $counter++;
        
        // Generate loan amounts
        $nominalPinjaman = $this->faker->numberBetween(500000, 20000000); // 1-40 paket
        $bungaPerBulan = 1.00; // 1% flat
        $tenorBulan = $this->faker->randomElement([6, 12, 18, 24]);
        
        // Calculate installments
        $angsuranPokok = $nominalPinjaman / $tenorBulan;
        $angsuranBunga = $nominalPinjaman * ($bungaPerBulan / 100);
        $totalAngsuran = $angsuranPokok + $angsuranBunga;
        
        // Generate dates
        $tanggalPencairan = $this->faker->dateTimeBetween('-2 years', 'now');
        $tanggalJatuhTempo = (clone $tanggalPencairan)->modify("+{$tenorBulan} months");
        $tanggalAngsuranPertama = (clone $tanggalPencairan)->modify('+1 month');
        
        // Generate payment progress
        $angsuranKe = $this->faker->numberBetween(0, $tenorBulan);
        $totalDibayar = $angsuranKe * $totalAngsuran;
        $sisaPokok = $nominalPinjaman - ($angsuranKe * $angsuranPokok);
        
        // Determine status based on payment progress
        $status = $this->determineStatus($angsuranKe, $tenorBulan, $tanggalJatuhTempo);
        
        return [
            'nomor_pinjaman' => $nomorPinjaman,
            'pengajuan_pinjaman_id' => $pengajuanId,
            'anggota_id' => $anggotaId,
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
            'keterangan' => $this->faker->optional(0.3)->sentence(),
            'isactive' => '1',
            'user_create' => 'system',
            'user_update' => 'system'
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
     * Indicate that the loan is active.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function active()
    {
        return $this->state(function (array $attributes) {
            $tenorBulan = $this->faker->randomElement([6, 12, 18, 24]);
            $angsuranKe = $this->faker->numberBetween(1, (int)($tenorBulan * 0.7));
            
            return [
                'status' => 'aktif',
                'tenor_bulan' => $tenorBulan,
                'angsuran_ke' => $angsuranKe,
                'tanggal_pencairan' => $this->faker->dateTimeBetween('-1 year', '-1 month')->format('Y-m-d'),
            ];
        });
    }
    
    /**
     * Indicate that the loan is fully paid.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function lunas()
    {
        return $this->state(function (array $attributes) {
            $tenorBulan = $this->faker->randomElement([6, 12, 18, 24]);
            $nominalPinjaman = $attributes['nominal_pinjaman'] ?? 5000000;
            $angsuranPokok = $nominalPinjaman / $tenorBulan;
            $totalAngsuran = $attributes['total_angsuran'] ?? ($angsuranPokok + ($nominalPinjaman * 0.01));
            
            return [
                'status' => 'lunas',
                'tenor_bulan' => $tenorBulan,
                'angsuran_ke' => $tenorBulan,
                'sisa_pokok' => 0,
                'total_dibayar' => $totalAngsuran * $tenorBulan,
                'tanggal_pencairan' => $this->faker->dateTimeBetween('-2 years', '-6 months')->format('Y-m-d'),
            ];
        });
    }
    
    /**
     * Indicate that the loan has payment issues.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function bermasalah()
    {
        return $this->state(function (array $attributes) {
            $tenorBulan = $this->faker->randomElement([6, 12, 18, 24]);
            $angsuranKe = $this->faker->numberBetween(0, (int)($tenorBulan * 0.5));
            
            // Set tanggal jatuh tempo sudah lewat
            $tanggalPencairan = $this->faker->dateTimeBetween('-3 years', '-1 year');
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
}
