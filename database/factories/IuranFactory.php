<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Iuran;
use App\Models\User;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Iuran>
 */
class IuranFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Iuran::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        // Ambil user yang memiliki nomor_anggota (anggota aktif)
        $user = User::whereNotNull('nomor_anggota')
            ->where('isactive', '1')
            ->inRandomOrder()
            ->first();

        if (!$user) {
            // Jika tidak ada user anggota, buat user dummy
            $user = User::factory()->anggotaAktif()->create();
        }

        $jenis_iuran = fake()->randomElement(['wajib', 'pokok']);
        $tahun = fake()->numberBetween(2023, 2025);
        $bulan = fake()->numberBetween(1, 12);

        // Nominal berdasarkan jenis iuran
        if ($jenis_iuran === 'pokok') {
            // Simpanan pokok biasanya lebih besar dan dibayar sekali
            $nominal = fake()->randomElement([50000, 100000, 150000, 200000]);
        } else {
            // Simpanan wajib bulanan
            $nominal = fake()->randomElement([25000, 30000, 35000, 40000, 50000]);
        }

        return [
            'user_id' => $user->id,
            'jenis_iuran' => $jenis_iuran,
            'iuran' => $nominal,
            'bulan' => $bulan,
            'tahun' => $tahun,
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }

    /**
     * State untuk iuran wajib
     */
    public function wajib(): static
    {
        return $this->state(fn (array $attributes) => [
            'jenis_iuran' => 'wajib',
            'iuran' => 25000,
        ]);
    }

    /**
     * State untuk iuran pokok
     */
    public function pokok(): static
    {
        return $this->state(fn (array $attributes) => [
            'jenis_iuran' => 'pokok',
            'iuran' => 50000,
        ]);
    }

    /**
     * State untuk tahun tertentu
     */
    public function tahun(int $tahun): static
    {
        return $this->state(fn (array $attributes) => [
            'tahun' => $tahun,
        ]);
    }

    /**
     * State untuk bulan tertentu
     */
    public function bulan(int $bulan): static
    {
        return $this->state(fn (array $attributes) => [
            'bulan' => $bulan,
        ]);
    }

    /**
     * State untuk user tertentu
     */
    public function forUser(User $user): static
    {
        return $this->state(fn (array $attributes) => [
            'user_id' => $user->id,
        ]);
    }
}
