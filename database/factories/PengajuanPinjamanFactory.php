<?php

namespace Database\Factories;

use App\Models\PengajuanPinjaman;
use App\Models\User;
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
    public function definition(): array
    {
        // Get random active anggota and paket
        $anggota = User::whereNotNull('nomor_anggota')
            ->where('isactive', '1')
            ->inRandomOrder()
            ->first();

        $paket = MasterPaketPinjaman::where('isactive', '1')
            ->inRandomOrder()
            ->first();

        // If no data exists, create them
        if (!$anggota) {
            $anggota = User::factory()->anggotaAktif()->create();
        }

        if (!$paket) {
            $paket = MasterPaketPinjaman::factory()->aktif()->create();
        }

        // Generate jumlah paket (1-40)
        $jumlahPaket = fake()->numberBetween(1, 40);

        // Calculate amounts (1 paket = Rp 500,000)
        $jumlahPinjaman = $jumlahPaket * 500000;

        // Generate tenor
        $tenorOptions = ['6 bulan', '10 bulan', '12 bulan'];
        $tenor = fake()->randomElement($tenorOptions);
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
        $status = fake()->randomElement($statusOptions);

        // Generate dates based on status
        $tanggalPengajuan = null;
        $tanggalApproval = null;
        $approvedBy = null;

        if (in_array($status, ['diajukan', 'review_admin', 'review_panitia', 'disetujui', 'ditolak'])) {
            $tanggalPengajuan = fake()->dateTimeBetween('-6 months', '-1 week');
        }

        if (in_array($status, ['disetujui', 'ditolak'])) {
            $baseDate = $tanggalPengajuan ?: fake()->dateTimeBetween('-3 months', '-1 week');
            $tanggalApproval = fake()->dateTimeBetween($baseDate, 'now');
            $approvedBy = fake()->randomElement(['kadmin', 'akredt', 'ketuum']);
        }

        return [
            'user_id' => $anggota->username, // Menggunakan username sesuai foreign key constraint
            'paket_pinjaman_id' => $paket->id,
            'tenor_pinjaman' => $tenor,
            'jumlah_paket_dipilih' => $jumlahPaket,
            'jumlah_pinjaman' => $jumlahPinjaman,
            'bunga_per_bulan' => $bungaPerBulan,
            'cicilan_per_bulan' => $cicilanPerBulan,
            'total_pembayaran' => $totalPembayaran,
            'tujuan_pinjaman' => fake()->randomElement($tujuanOptions),
            'jenis_pengajuan' => fake()->randomElement(['baru', 'top_up']),
            'status_pengajuan' => $status,
            'catatan_pengajuan' => fake()->optional(0.7)->sentence(),
            'catatan_approval' => $status === 'ditolak' ? fake()->sentence() : null,
            'tanggal_pengajuan' => $tanggalPengajuan?->format('Y-m-d H:i:s'),
            'tanggal_approval' => $tanggalApproval?->format('Y-m-d H:i:s'),
            'approved_by' => $approvedBy,
            'status_pencairan' => 'belum_cair',
            'isactive' => '1',
            'created_at' => now(),
            'updated_at' => now(),
            'user_create' => 'factory',
            'user_update' => 'factory'
        ];
    }

    /**
     * State untuk pengajuan yang disetujui
     */
    public function approved(): static
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
     * State untuk pengajuan yang pending
     */
    public function pending(): static
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
     * State untuk pengajuan yang ditolak
     */
    public function rejected(): static
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
     * State untuk pengajuan draft
     */
    public function draft(): static
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
     * State untuk pengajuan top up
     */
    public function topUp(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'jenis_pengajuan' => 'top_up',
                'isactive' => '1',
            ];
        });
    }

    /**
     * State untuk pinjaman kecil (1-5 paket)
     */
    public function smallLoan(): static
    {
        return $this->state(function (array $attributes) {
            $jumlahPaket = fake()->numberBetween(1, 5);
            $jumlahPinjaman = $jumlahPaket * 500000;

            $tenor = fake()->randomElement(['6 bulan', '12 bulan']);
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
     * State untuk pinjaman besar (20-40 paket)
     */
    public function largeLoan(): static
    {
        return $this->state(function (array $attributes) {
            $jumlahPaket = fake()->numberBetween(20, 40);
            $jumlahPinjaman = $jumlahPaket * 500000;

            $tenor = fake()->randomElement(['18 bulan', '24 bulan']);
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
     * State untuk user tertentu
     */
    public function forUser(User $user): static
    {
        return $this->state(fn (array $attributes) => [
            'user_id' => $user->username,
        ]);
    }
}
