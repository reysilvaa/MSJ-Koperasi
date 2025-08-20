<?php

namespace Database\Factories;

use App\Models\MasterPaketPinjaman;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\MasterPaketPinjaman>
 */
class MasterPaketPinjamanFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = MasterPaketPinjaman::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        // Generate periode (YYYY-MM format)
        $year = fake()->numberBetween(2024, 2026);
        $month = fake()->numberBetween(1, 12);
        $periode = sprintf('%04d-%02d', $year, $month);
        
        // Generate stock limit (50-200 paket)
        $stockLimit = fake()->numberBetween(50, 200);
        
        // Generate stock terpakai (0 sampai 80% dari limit)
        $stockTerpakai = fake()->numberBetween(0, (int)($stockLimit * 0.8));
        
        return [
            'periode' => $periode,
            'stock_limit' => $stockLimit,
            'stock_terpakai' => $stockTerpakai,
            'isactive' => fake()->randomElement(['0', '1']),
            'created_at' => now(),
            'updated_at' => now(),
            'user_create' => 'factory',
            'user_update' => 'factory'
        ];
    }
    
    /**
     * State untuk paket aktif dengan stock tinggi
     */
    public function activeWithStock(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'isactive' => '1',
                'stock_limit' => fake()->numberBetween(100, 200),
                'stock_terpakai' => fake()->numberBetween(0, 50),
            ];
        });
    }
    
    /**
     * State untuk paket yang hampir penuh
     */
    public function nearlyFull(): static
    {
        return $this->state(function (array $attributes) {
            $limit = fake()->numberBetween(80, 150);
            return [
                'isactive' => '1',
                'stock_limit' => $limit,
                'stock_terpakai' => fake()->numberBetween((int)($limit * 0.8), $limit),
            ];
        });
    }

    /**
     * State untuk periode tertentu
     */
    public function periode(string $periode): static
    {
        return $this->state(fn (array $attributes) => [
            'periode' => $periode,
        ]);
    }

    /**
     * State untuk paket aktif
     */
    public function aktif(): static
    {
        return $this->state(fn (array $attributes) => [
            'isactive' => '1',
        ]);
    }
}