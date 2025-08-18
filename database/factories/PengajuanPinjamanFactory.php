<?php

namespace Database\Factories;

use App\Models\PengajuanPinjaman;
use App\Models\Anggotum;
use App\Models\MasterPaketPinjaman;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\PengajuanPinjaman>
 */
class PengajuanPinjamanFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = PengajuanPinjaman::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        // Get random active anggota and paket
        $anggota = Anggotum::where('isactive', '1')->inRandomOrder()->first();
        $paket = MasterPaketPinjaman::where('isactive', '1')->inRandomOrder()->first();

        // If no data exists, create default IDs
        $anggotaId = $anggota ? $anggota->id : 1;
        $paketId = $paket ? $paket->id : 1;

        // Generate jumlah paket (1-40)
        $jumlahPaket = $this->faker->numberBetween(1, 40);

        // Calculate amounts (1 paket = Rp 500,000)
        $jumlahPinjaman = $jumlahPaket * 500000;

        // Generate tenor
        $tenorOptions = ['6 bulan', '10 bulan', '12 bulan'];
        $tenor = $this->faker->randomElement($tenorOptions);
        $tenorBulan = (int) explode(' ', $tenor)[0];

        // Bunga per bulan (1% flat)
        $bungaPerBulan = 1.00;

        // Calculate cicilan (flat 1% per month)
        $bungaRupiah = $jumlahPinjaman * ($bungaPerBulan / 100);
        $cicilanPokok = $jumlahPinjaman / $tenorBulan;
        $cicilanPerBulan = $cicilanPokok + $bungaRupiah;
        $totalPembayaran = $cicilanPerBulan * $tenorBulan;

        // Tujuan pinjaman options
        $tujuanOptions = [
            'Modal usaha kecil',
            'Renovasi rumah',
            'Biaya pendidikan anak',
            'Biaya kesehatan',
            'Pembelian kendaraan',
            'Modal dagang',
            'Biaya pernikahan',
            'Investasi peralatan',
            'Kebutuhan mendesak',
            'Pengembangan usaha'
        ];

        // Status pengajuan options
        $statusOptions = ['draft', 'diajukan', 'review_admin', 'review_panitia', 'disetujui', 'ditolak'];
        $status = $this->faker->randomElement($statusOptions);

        // Generate dates based on status
        $tanggalPengajuan = null;
        $tanggalApproval = null;
        $approvedBy = null;

        if (in_array($status, ['diajukan', 'review_admin', 'review_panitia', 'disetujui', 'ditolak'])) {
            $tanggalPengajuan = $this->faker->dateTimeBetween('-6 months', '-1 week');
        }

        if (in_array($status, ['disetujui', 'ditolak'])) {
            $baseDate = $tanggalPengajuan ?: $this->faker->dateTimeBetween('-3 months', '-1 week');
            $tanggalApproval = $this->faker->dateTimeBetween($baseDate, 'now');
            $approvedBy = $this->faker->randomElement(['kadmin', 'akredt', 'ketuum']);
        }

        return [
            'anggota_id' => $anggotaId,
            'paket_pinjaman_id' => $paketId,
            'tenor_pinjaman' => $tenor,
            'jumlah_paket_dipilih' => $jumlahPaket,
            'jumlah_pinjaman' => $jumlahPinjaman,
            'bunga_per_bulan' => $bungaPerBulan,
            'cicilan_per_bulan' => $cicilanPerBulan,
            'total_pembayaran' => $totalPembayaran,
            'tujuan_pinjaman' => $this->faker->randomElement($tujuanOptions),
            'jenis_pengajuan' => $this->faker->randomElement(['baru', 'top_up']),
            'status_pengajuan' => $status,
            'catatan_pengajuan' => $this->faker->optional(0.7)->sentence(),
            'catatan_approval' => $status === 'ditolak' ? $this->faker->sentence() : null,
            'tanggal_pengajuan' => $tanggalPengajuan?->format('Y-m-d H:i:s'),
            'tanggal_approval' => $tanggalApproval?->format('Y-m-d H:i:s'),
            'approved_by' => $approvedBy,
            'status_pencairan' => 'belum_cair',
            'isactive' => '1',
            'user_create' => 'system',
            'user_update' => 'system'
        ];
    }

    /**
     * Indicate that the pengajuan is approved.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function approved()
    {
        return $this->state(function (array $attributes) {
            return [
                'status_pengajuan' => 'disetujui',
                'tanggal_pengajuan' => fake()->dateTimeBetween('-3 months', '-1 month')->format('Y-m-d H:i:s'),
                'tanggal_approval' => fake()->dateTimeBetween('-1 month', 'now')->format('Y-m-d H:i:s'),
                'approved_by' => fake()->randomElement(['kadmin', 'akredt', 'ketuum']),
                'catatan_approval' => 'Pengajuan disetujui sesuai ketentuan',
            ];
        });
    }

    /**
     * Indicate that the pengajuan is pending.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function pending()
    {
        return $this->state(function (array $attributes) {
            return [
                'status_pengajuan' => fake()->randomElement(['diajukan', 'review_admin', 'review_panitia']),
                'tanggal_pengajuan' => fake()->dateTimeBetween('-1 month', '-1 day')->format('Y-m-d H:i:s'),
                'tanggal_approval' => null,
                'approved_by' => null,
                'catatan_approval' => null,
            ];
        });
    }

    /**
     * Indicate that the pengajuan is rejected.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function rejected()
    {
        return $this->state(function (array $attributes) {
            return [
                'status_pengajuan' => 'ditolak',
                'tanggal_pengajuan' => fake()->dateTimeBetween('-3 months', '-1 month')->format('Y-m-d H:i:s'),
                'tanggal_approval' => fake()->dateTimeBetween('-1 month', 'now')->format('Y-m-d H:i:s'),
                'approved_by' => fake()->randomElement(['kadmin', 'akredt', 'ketuum']),
                'catatan_approval' => fake()->randomElement([
                    'Tidak memenuhi syarat kelayakan kredit',
                    'Dokumen tidak lengkap',
                    'Riwayat pembayaran kurang baik'
                ]),
            ];
        });
    }

    /**
     * Indicate that the pengajuan is draft.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function draft()
    {
        return $this->state(function (array $attributes) {
            return [
                'status_pengajuan' => 'draft',
                'tanggal_pengajuan' => null,
                'tanggal_approval' => null,
                'approved_by' => null,
                'catatan_approval' => null,
            ];
        });
    }

    /**
     * Indicate that the pengajuan is for top up.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function topUp()
    {
        return $this->state(function (array $attributes) {
            return [
                'jenis_pengajuan' => 'top_up',
                'isactive' => '1',
            ];
        });
    }

    /**
     * Indicate small loan amount (1-5 paket).
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function smallLoan()
    {
        return $this->state(function (array $attributes) {
            $jumlahPaket = $this->faker->numberBetween(1, 5);
            $jumlahPinjaman = $jumlahPaket * 500000;

            $tenor = $this->faker->randomElement(['6 bulan', '12 bulan']);
            $tenorBulan = (int) explode(' ', $tenor)[0];

            $bungaPerBulan = $jumlahPinjaman * 0.01;
            $cicilanPokok = $jumlahPinjaman / $tenorBulan;
            $cicilanPerBulan = $cicilanPokok + $bungaPerBulan;
            $totalPembayaran = $cicilanPerBulan * $tenorBulan;

            return [
                'jumlah_paket_dipilih' => $jumlahPaket,
                'tenor_pinjaman' => $tenor,
                'jumlah_pinjaman' => $jumlahPinjaman,
                'cicilan_per_bulan' => $cicilanPerBulan,
                'total_pembayaran' => $totalPembayaran,
            ];
        });
    }

    /**
     * Indicate large loan amount (20-40 paket).
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function largeLoan()
    {
        return $this->state(function (array $attributes) {
            $jumlahPaket = $this->faker->numberBetween(20, 40);
            $jumlahPinjaman = $jumlahPaket * 500000;

            $tenor = $this->faker->randomElement(['18 bulan', '24 bulan']);
            $tenorBulan = (int) explode(' ', $tenor)[0];

            $bungaPerBulan = $jumlahPinjaman * 0.01;
            $cicilanPokok = $jumlahPinjaman / $tenorBulan;
            $cicilanPerBulan = $cicilanPokok + $bungaPerBulan;
            $totalPembayaran = $cicilanPerBulan * $tenorBulan;

            return [
                'jumlah_paket_dipilih' => $jumlahPaket,
                'tenor_pinjaman' => $tenor,
                'jumlah_pinjaman' => $jumlahPinjaman,
                'cicilan_per_bulan' => $cicilanPerBulan,
                'total_pembayaran' => $totalPembayaran,
            ];
        });
    }
}
