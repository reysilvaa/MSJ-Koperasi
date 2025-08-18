<?php

namespace Database\Factories;

use App\Models\Anggotum;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Anggotum>
 */
class AnggotumFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Anggotum::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        // Indonesian names
        $maleNames = [
            'Ahmad Rizki', 'Budi Santoso', 'Candra Wijaya', 'Dedi Kurniawan', 'Eko Prasetyo',
            'Fajar Nugroho', 'Gunawan Setiawan', 'Hendra Saputra', 'Indra Permana', 'Joko Widodo',
            'Krisna Mahendra', 'Lukman Hakim', 'Muhammad Iqbal', 'Nur Hidayat', 'Oki Setiawan',
            'Putra Ramadhan', 'Qori Ananda', 'Rizal Fauzi', 'Sandi Pratama', 'Taufik Rahman',
            'Umar Bakri', 'Vino Mahardika', 'Wahyu Nugraha', 'Yoga Pratama', 'Zaki Maulana'
        ];

        $femaleNames = [
            'Ayu Lestari', 'Bella Sari', 'Citra Dewi', 'Dina Kartika', 'Eka Putri',
            'Fitri Handayani', 'Gita Permata', 'Hani Rahayu', 'Indah Sari', 'Jihan Amelia',
            'Kirana Putri', 'Lina Marlina', 'Maya Sari', 'Nita Anggraini', 'Olivia Damayanti',
            'Putri Maharani', 'Qonita Zahara', 'Rina Susanti', 'Sari Wulandari', 'Tika Rahmawati',
            'Umi Kalsum', 'Vera Novita', 'Winda Sari', 'Yuni Astuti', 'Zahra Amalia'
        ];

        // Indonesian cities for addresses
        $cities = [
            'Jakarta', 'Surabaya', 'Bandung', 'Medan', 'Semarang', 'Makassar', 'Palembang',
            'Tangerang', 'Depok', 'Bekasi', 'Bogor', 'Batam', 'Pekanbaru', 'Bandar Lampung',
            'Malang', 'Padang', 'Denpasar', 'Samarinda', 'Tasikmalaya', 'Serang', 'Magelang',
            'Cilegon', 'Balikpapan', 'Jambi', 'Sukabumi', 'Cirebon', 'Mataram', 'Jayapura',
            'Kupang', 'Manado', 'Ambon', 'Banda Aceh', 'Banjarmasin', 'Pontianak', 'Kendari'
        ];

        $departments = [
            'IT', 'Finance', 'HR', 'Marketing', 'Operations', 'Production', 'Quality Control',
            'Purchasing', 'Sales', 'Customer Service', 'R&D', 'Legal', 'Admin', 'Security',
            'Maintenance', 'Logistics', 'Planning', 'Engineering', 'Accounting', 'Training'
        ];

        $positions = [
            'Staff', 'Senior Staff', 'Supervisor', 'Assistant Manager', 'Manager',
            'Senior Manager', 'Coordinator', 'Team Leader', 'Specialist', 'Analyst',
            'Officer', 'Executive', 'Administrator', 'Technician', 'Operator'
        ];

        $banks = [
            'BCA', 'BRI', 'BNI', 'Mandiri', 'CIMB Niaga', 'Danamon', 'Permata',
            'OCBC NISP', 'Maybank', 'Panin', 'BTN', 'Mega', 'Bukopin', 'Sinarmas'
        ];

        // Generate gender first to determine name
        $gender = $this->faker->randomElement(['L', 'P']);
        $name = $gender === 'L'
            ? $this->faker->randomElement($maleNames)
            : $this->faker->randomElement($femaleNames);

        // Generate unique nomor_anggota using microtime
        $nomorAnggota = 'A' . date('y') . str_pad(substr(microtime(true) * 10000, -4), 4, '0', STR_PAD_LEFT);

        // Generate realistic Indonesian NIK (16 digits) - unique
        $nik = $this->faker->unique()->numerify('35##############');

        // Generate email based on name with unique suffix
        $emailName = strtolower(str_replace(' ', '.', $name));
        $email = $emailName . '.' . $this->faker->unique()->randomNumber(4) . '@spunindo.com';

        // Generate phone number
        $phoneNumber = '08' . $this->faker->numerify('##########');

        // Generate birth date (age 25-55)
        $birthDate = $this->faker->dateTimeBetween('-55 years', '-25 years');

        // Generate address
        $city = $this->faker->randomElement($cities);
        $address = 'Jl. ' . $this->faker->streetName . ' No. ' . $this->faker->buildingNumber . ', ' . $city . ', Indonesia';

        // Generate job details
        $department = $this->faker->randomElement($departments);
        $position = $this->faker->randomElement($positions);

        // Generate salary (3-15 million)
        $salary = $this->faker->numberBetween(3000000, 15000000);

        // Generate join date (last 5 years)
        $joinDate = $this->faker->dateTimeBetween('-5 years', '-1 month');

        // Generate active date (after join date, ensure it's not in future)
        $maxActiveDate = min(time(), $joinDate->getTimestamp() + (365 * 24 * 60 * 60)); // max 1 year after join
        $activeDate = $this->faker->dateTimeBetween($joinDate, date('Y-m-d', $maxActiveDate));

        // Generate bank details
        $bank = $this->faker->randomElement($banks);
        $accountNumber = $this->faker->numerify('##########');

        // Generate savings amounts
        $totalSimpananWajib = $this->faker->numberBetween(100000, 2000000);
        $totalSimpananSukarela = $this->faker->numberBetween(0, 5000000);

        // Generate foto_ktp filename
        $fotoKtp = 'ktp_' . strtolower(str_replace(' ', '_', $name)) . '_' . $this->faker->randomNumber(4) . '.jpg';

        return [
            'nomor_anggota' => $nomorAnggota,
            'nik' => $nik,
            'nama_lengkap' => $name,
            'email' => $email,
            'no_hp' => $phoneNumber,
            'jenis_kelamin' => $gender,
            'tanggal_lahir' => $birthDate->format('Y-m-d'),
            'alamat' => $address,
            'jabatan' => $position,
            'departemen' => $department,
            'gaji_pokok' => $salary,
            'tanggal_bergabung' => $joinDate->format('Y-m-d'),
            'tanggal_aktif' => $activeDate->format('Y-m-d'),
            'simpanan_pokok' => 50000.00,
            'simpanan_wajib_bulanan' => 25000.00,
            'total_simpanan_wajib' => $totalSimpananWajib,
            'total_simpanan_sukarela' => $totalSimpananSukarela,
            'no_rekening' => $accountNumber,
            'nama_bank' => $bank,
            'foto_ktp' => $fotoKtp,
            'keterangan' => $this->faker->optional(0.3)->sentence(),
            'isactive' => $this->faker->randomElement(['0', '1']),
            'user_create' => 'system',
            'user_update' => 'system'
        ];
    }

    /**
     * Indicate that the anggota is active.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function active()
    {
        return $this->state(function (array $attributes) {
            return [
                'isactive' => '1',
                'tanggal_aktif' => now()->format('Y-m-d'),
            ];
        });
    }

    /**
     * Indicate that the anggota is inactive.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function inactive()
    {
        return $this->state(function (array $attributes) {
            return [
                'isactive' => '0',
                'tanggal_aktif' => null,
            ];
        });
    }
}
