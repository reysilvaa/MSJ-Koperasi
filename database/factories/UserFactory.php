<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use App\Models\User;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = User::class;

    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $firstName = fake()->firstName();
        $lastName = fake()->lastName();
        
        // Generate username yang lebih pendek (maksimal 20 karakter)
        $baseUsername = strtolower(substr($firstName, 0, 4) . substr($lastName, 0, 4));
        $username = $baseUsername . fake()->numberBetween(100, 999);
        
        // Pastikan username tidak lebih dari 20 karakter
        if (strlen($username) > 20) {
            $username = substr($username, 0, 17) . fake()->numberBetween(10, 99);
        }

        return [
            // Data login
            'username' => $username,
            'firstname' => $firstName,
            'lastname' => $lastName,
            'email' => fake()->unique()->safeEmail(),
            'password' => static::$password ??= Hash::make('password123'),
            'idroles' => 'anggot', // Default role anggota

            // Data anggota koperasi
            'nomor_anggota' => 'A' . str_pad(fake()->unique()->numberBetween(1, 9999), 6, '0', STR_PAD_LEFT),
            'nik' => fake()->unique()->numerify('35##############'), // NIK Jawa Timur
            'nama_lengkap' => $firstName . ' ' . $lastName,
            'no_hp' => '085232152313',
            'jenis_kelamin' => fake()->randomElement(['L', 'P']),
            'tanggal_lahir' => fake()->date('Y-m-d', '2000-01-01'),
            'alamat' => fake()->address(),
            'jabatan' => fake()->randomElement(['Staff', 'Supervisor', 'Manager', 'Koordinator']),
            'departemen' => fake()->randomElement(['IT', 'Finance', 'HRD', 'Marketing', 'Operasional', 'Admin']),
            'gaji_pokok' => fake()->randomFloat(2, 5000000, 15000000), // 5-15 juta
            'tanggal_bergabung' => fake()->dateTimeBetween('-2 years', 'now'),
            'tanggal_aktif' => fake()->dateTimeBetween('-2 years', 'now'),
            'no_rekening' => fake()->bankAccountNumber(),
            'nama_bank' => fake()->randomElement(['BRI', 'BCA', 'Mandiri', 'BNI', 'BTN']),
            'foto_ktp' => null,
            'keterangan' => fake()->optional()->sentence(),
            // System fields
            'isactive' => '1',
            'remember_token' => Str::random(10),
            'created_at' => now(),
            'updated_at' => now(),
            'user_create' => 'factory',
            'user_update' => 'factory',
        ];
    }

    /**
     * State untuk anggota aktif
     */
    public function anggotaAktif(): static
    {
        return $this->state(fn (array $attributes) => [
            'idroles' => 'anggot',
            'isactive' => '1',
            'nomor_anggota' => 'A' . str_pad(fake()->unique()->numberBetween(1, 9999), 6, '0', STR_PAD_LEFT),
        ]);
    }

    /**
     * State untuk admin
     */
    public function admin(): static
    {
        return $this->state(fn (array $attributes) => [
            'idroles' => 'kadmin',
            'nomor_anggota' => null,
            'nik' => null,
            'gaji_pokok' => null,
            'tanggal_bergabung' => null,
            'tanggal_aktif' => null,
            'no_rekening' => null,
            'nama_bank' => null,
        ]);
    }

    /**
     * State untuk anggota tidak aktif
     */
    public function nonAktif(): static
    {
        return $this->state(fn (array $attributes) => [
            'isactive' => '0',
        ]);
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }
}
