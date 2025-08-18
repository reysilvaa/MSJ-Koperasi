<?php

namespace Database\Factories;

use App\Models\CicilanPinjaman;
use App\Models\Pinjaman;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\CicilanPinjaman>
 */
class CicilanPinjamanFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = CicilanPinjaman::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        // Get random pinjaman - ensure it exists
        $pinjaman = Pinjaman::where('isactive', '1')->inRandomOrder()->first();

        // If no pinjaman exists, create one first
        if (!$pinjaman) {
            $pinjaman = Pinjaman::factory()->create();
        }

        $pinjamanId = $pinjaman->id;

        // Generate cicilan ke (1-24)
        $cicilanKe = $this->faker->numberBetween(1, 24);

        // Generate amounts based on typical loan structure
        $nominalPokok = $this->faker->numberBetween(200000, 1000000);
        $nominalBunga = $this->faker->numberBetween(50000, 200000);
        $totalCicilan = $nominalPokok + $nominalBunga;

        // Generate payment dates - ensure proper range
        $tanggalJatuhTempo = $this->faker->dateTimeBetween('-2 years', '+6 months');

        // Determine if paid and when
        $isPaid = $this->faker->boolean(70); // 70% chance of being paid
        $tanggalBayar = null;
        $metodePembayaran = null;
        $keterangan = null;

        if ($isPaid && $tanggalJatuhTempo <= now()) {
            // Only set payment date if due date is in the past
            $tanggalBayar = $this->faker->dateTimeBetween($tanggalJatuhTempo, 'now');
            $metodePembayaran = $this->faker->randomElement([
                'transfer_bank',
                'tunai',
                'potong_gaji',
                'auto_debit'
            ]);
            $keterangan = 'Pembayaran tepat waktu';
        } else {
            // Check if overdue
            if ($tanggalJatuhTempo < now()) {
                $keterangan = 'Belum dibayar, perlu tindak lanjut';
            } else {
                $keterangan = 'Belum jatuh tempo';
            }
        }

        return [
            'pinjaman_id' => $pinjamanId,
            'angsuran_ke' => $cicilanKe,
            'tanggal_jatuh_tempo' => $tanggalJatuhTempo->format('Y-m-d'),
            'tanggal_bayar' => $tanggalBayar ? $tanggalBayar->format('Y-m-d') : null,
            'nominal_pokok' => $nominalPokok,
            'nominal_bunga' => $nominalBunga,
            'total_bayar' => $totalCicilan,
            'metode_pembayaran' => $metodePembayaran,
            'nomor_transaksi' => $isPaid ? 'TRX-' . $this->faker->numerify('########') : null,
            'keterangan' => $keterangan,
            'isactive' => '1',
            'user_create' => 'system',
            'user_update' => 'system'
        ];
    }

    /**
     * Indicate that the cicilan is paid on time.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function paidOnTime()
    {
        return $this->state(function (array $attributes) {
            $tanggalJatuhTempo = $this->faker->dateTimeBetween('-1 year', '-1 month');
            $tanggalBayar = $this->faker->dateTimeBetween($tanggalJatuhTempo, (clone $tanggalJatuhTempo)->modify('+3 days'));

            return [
                'tanggal_jatuh_tempo' => $tanggalJatuhTempo->format('Y-m-d'),
                'tanggal_bayar' => $tanggalBayar->format('Y-m-d'),
                'metode_pembayaran' => $this->faker->randomElement(['transfer_bank', 'potong_gaji']),
                'nomor_transaksi' => 'TRX-' . $this->faker->numerify('########'),
                'keterangan' => 'Pembayaran tepat waktu',
            ];
        });
    }

    /**
     * Indicate that the cicilan is overdue.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function overdue()
    {
        return $this->state(function (array $attributes) {
            $tanggalJatuhTempo = $this->faker->dateTimeBetween('-6 months', '-1 week');
            $hariTerlambat = now()->diffInDays($tanggalJatuhTempo);

            return [
                'tanggal_jatuh_tempo' => $tanggalJatuhTempo->format('Y-m-d'),
                'tanggal_bayar' => null,
                'metode_pembayaran' => null,
                'nomor_transaksi' => null,
                'keterangan' => "Terlambat {$hariTerlambat} hari",
            ];
        });
    }

    /**
     * Indicate that the cicilan is paid.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function paid()
    {
        return $this->state(function (array $attributes) {
            $tanggalJatuhTempo = $this->faker->dateTimeBetween('-3 months', '-1 week');
            // Ensure payment date is after due date but not in future
            $maxPaymentDate = min(time(), $tanggalJatuhTempo->getTimestamp() + (30 * 24 * 60 * 60)); // max 30 days after due
            $tanggalBayar = $this->faker->dateTimeBetween($tanggalJatuhTempo, date('Y-m-d', $maxPaymentDate));

            return [
                'tanggal_jatuh_tempo' => $tanggalJatuhTempo->format('Y-m-d'),
                'tanggal_bayar' => $tanggalBayar->format('Y-m-d'),
                'metode_pembayaran' => $this->faker->randomElement(['transfer_bank', 'tunai', 'potong_gaji']),
                'nomor_transaksi' => 'TRX-' . $this->faker->numerify('########'),
                'keterangan' => 'Pembayaran lunas',
            ];
        });
    }

    /**
     * Indicate upcoming payment (due soon).
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function upcoming()
    {
        return $this->state(function (array $attributes) {
            $tanggalJatuhTempo = $this->faker->dateTimeBetween('now', '+1 month');

            return [
                'tanggal_jatuh_tempo' => $tanggalJatuhTempo->format('Y-m-d'),
                'tanggal_bayar' => null,
                'metode_pembayaran' => null,
                'nomor_transaksi' => null,
                'keterangan' => 'Cicilan akan jatuh tempo',
            ];
        });
    }
}
