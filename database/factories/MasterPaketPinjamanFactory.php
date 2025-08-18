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
    public function definition()
    {
        // Generate periode (YYYY-MM format)
        $year = $this->faker->numberBetween(2024, 2026);
        $month = $this->faker->numberBetween(1, 12);
        $periode = sprintf('%04d-%02d', $year, $month);
        
        // Generate stock limit (50-200 paket)
        $stockLimit = $this->faker->numberBetween(50, 200);
        
        // Generate stock terpakai (0 sampai 80% dari limit)
        $stockTerpakai = $this->faker->numberBetween(0, (int)($stockLimit * 0.8));
        
        return [
            'periode' => $periode,
            'stock_limit' => $stockLimit,
            'stock_terpakai' => $stockTerpakai,
            'isactive' => $this->faker->randomElement(['0', '1']),
            'user_create' => 'system',
            'user_update' => 'system'
        ];
    }
    
    /**
     * Indicate that the paket is active with high stock.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function activeWithStock()
    {
        return $this->state(function (array $attributes) {
            return [
                'isactive' => '1',
                'stock_limit' => $this->faker->numberBetween(100, 200),
                'stock_terpakai' => $this->faker->numberBetween(0, 50),
            ];
        });
    }
    
    /**
     * Indicate that the paket is nearly full.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function nearlyFull()
    {
        return $this->state(function (array $attributes) {
            $limit = $this->faker->numberBetween(80, 150);
            return [
                'isactive' => '1',
                'stock_limit' => $limit,
                'stock_terpakai' => $this->faker->numberBetween((int)($limit * 0.8), $limit),
            ];
        });
    }
}
