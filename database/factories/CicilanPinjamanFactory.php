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
    public function definition(): array
    {
        // Get random pinjaman - ensure it exists
        $pinjaman = Pinjaman::where('isactive', '1')->inRandomOrder()->first();

        // If no pinjaman exists, create one first
        if (!$pinjaman) {
            $pinjaman = Pinjaman::factory()->create();
        }

        // Generate cicilan ke (1-24)
        $cicilanKe = fake()->numberBetween(1, 24);

        // Generate amounts based on typical loan structure
        $nominalPokok = fake()->numberBetween(200000, 1000000);
        $nominalBunga = fake()->numberBetween(50000, 200000);
        $totalCicilan = $nominalPokok + $nominalBunga;

        // Generate payment dates - ensure proper range
        $tanggalJatuhTempo = fake()->dateTimeBetween('-2 years', '+6 months');

        // Determine if paid and when
        $isPaid = fake()->boolean(70); // 70% chance of being paid
        $tanggalBayar = null;
        $metodePembayaran = null;
        $keterangan = null;

        if ($isPaid && $tanggalJatuhTempo <= now()) {
            // Only set payment date if due date is in the past
            $tanggalBayar = fake()->dateTimeBetween($tanggalJatuhTempo, 'now');
            $metodePembayaran = fake()->randomElement([
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
            'pinjaman_id' => $pinjaman->id,
            'angsuran_ke' => $cicilanKe,
            'tanggal_jatuh_tempo' => $tanggalJatuhTempo->format('Y-m-d'),
            'tanggal_bayar' => $tanggalBayar ? $tanggalBayar->format('Y-m-d') : null,
            'nominal_pokok' => $nominalPokok,
            'nominal_bunga' => $nominalBunga,
            'total_bayar' => $totalCicilan,
            'metode_pembayaran' => $metodePembayaran,
            'nomor_transaksi' => $isPaid ? 'TRX-' . fake()->numerify('########') : null,
            'keterangan' => $keterangan,
            'isactive' => '1',
            'created_at' => now(),
            'updated_at' => now(),
            'user_create' => 'factory',
            'user_update' => 'factory'
        ];
    }

    /**
     * State untuk cicilan yang dibayar tepat waktu
     */
    public function paidOnTime(): static
    {
        return $this->state(function (array $attributes) {
            $tanggalJatuhTempo = fake()->dateTimeBetween('-1 year', '-1 month');
            $tanggalBayar = fake()->dateTimeBetween($tanggalJatuhTempo, (clone $tanggalJatuhTempo)->modify('+3 days'));

            return [
                'tanggal_jatuh_tempo' => $tanggalJatuhTempo->format('Y-m-d'),
                'tanggal_bayar' => $tanggalBayar->format('Y-m-d'),
                'metode_pembayaran' => fake()->randomElement(['transfer_bank', 'potong_gaji']),
                'nomor_transaksi' => 'TRX-' . fake()->numerify('########'),
                'keterangan' => 'Pembayaran tepat waktu',
            ];
        });
    }

    /**
     * State untuk cicilan yang terlambat
     */
    public function overdue(): static
    {
        return $this->state(function (array $attributes) {
            $tanggalJatuhTempo = fake()->dateTimeBetween('-6 months', '-1 week');
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
     * State untuk cicilan yang sudah dibayar
     */
    public function paid(): static
    {
        return $this->state(function (array $attributes) {
            $tanggalJatuhTempo = fake()->dateTimeBetween('-3 months', '-1 week');
            // Ensure payment date is after due date but not in future
            $maxPaymentDate = min(time(), $tanggalJatuhTempo->getTimestamp() + (30 * 24 * 60 * 60)); // max 30 days after due
            $tanggalBayar = fake()->dateTimeBetween($tanggalJatuhTempo, date('Y-m-d', $maxPaymentDate));

            return [
                'tanggal_jatuh_tempo' => $tanggalJatuhTempo->format('Y-m-d'),
                'tanggal_bayar' => $tanggalBayar->format('Y-m-d'),
                'metode_pembayaran' => fake()->randomElement(['transfer_bank', 'tunai', 'potong_gaji']),
                'nomor_transaksi' => 'TRX-' . fake()->numerify('########'),
                'keterangan' => 'Pembayaran lunas',
            ];
        });
    }

    /**
     * State untuk cicilan yang akan jatuh tempo
     */
    public function upcoming(): static
    {
        return $this->state(function (array $attributes) {
            $tanggalJatuhTempo = fake()->dateTimeBetween('now', '+1 month');

            return [
                'tanggal_jatuh_tempo' => $tanggalJatuhTempo->format('Y-m-d'),
                'tanggal_bayar' => null,
                'metode_pembayaran' => null,
                'nomor_transaksi' => null,
                'keterangan' => 'Cicilan akan jatuh tempo',
            ];
        });
    }

    /**
     * State untuk pinjaman tertentu
     */
    public function forPinjaman(Pinjaman $pinjaman): static
    {
        return $this->state(fn (array $attributes) => [
            'pinjaman_id' => $pinjaman->id,
        ]);
    }

    /**
     * State untuk angsuran ke tertentu
     */
    public function angsuranKe(int $angsuranKe): static
    {
        return $this->state(fn (array $attributes) => [
            'angsuran_ke' => $angsuranKe,
        ]);
    }
}