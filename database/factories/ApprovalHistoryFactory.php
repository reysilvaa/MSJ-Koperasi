<?php

namespace Database\Factories;

use App\Models\ApprovalHistory;
use App\Models\PengajuanPinjaman;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ApprovalHistory>
 */
class ApprovalHistoryFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = ApprovalHistory::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        // Get random pengajuan pinjaman
        $pengajuan = PengajuanPinjaman::inRandomOrder()->first();
        
        if (!$pengajuan) {
            $pengajuan = PengajuanPinjaman::factory()->create();
        }
        
        // Approval levels
        $levelApproval = fake()->randomElement([
            'admin_koperasi',
            'admin_kredit', 
            'ketua_umum',
            'admin_transfer'
        ]);
        
        // Status approval options (sesuai enum di database)
        $statusApproval = fake()->randomElement(['pending', 'approved', 'rejected']);
        
        // Generate approval notes based on status
        $notes = $this->generateNotesByStatus($statusApproval);
        
        return [
            'pengajuan_pinjaman_id' => $pengajuan->id,
            'level_approval' => $levelApproval,
            'status_approval' => $statusApproval,
            'catatan' => $notes,
            'tanggal_approval' => fake()->dateTimeBetween('-6 months', 'now')->format('Y-m-d H:i:s'),
            'urutan' => fake()->numberBetween(1, 5),
            'isactive' => '1',
            'created_at' => now(),
            'updated_at' => now(),
            'user_create' => 'factory',
            'user_update' => 'factory'
        ];
    }
    
    /**
     * Generate notes based on approval status
     */
    private function generateNotesByStatus($status)
    {
        $notesByStatus = [
            'pending' => [
                'Pengajuan diterima dan menunggu review',
                'Dokumen lengkap, siap untuk ditinjau',
                'Pengajuan masuk antrian review'
            ],
            'approved' => [
                'Pengajuan disetujui sesuai ketentuan',
                'Memenuhi syarat dan layak untuk dicairkan',
                'Disetujui dengan tenor dan jumlah sesuai pengajuan',
                'Pengajuan disetujui oleh panitia kredit'
            ],
            'rejected' => [
                'Tidak memenuhi syarat kelayakan kredit',
                'Dokumen tidak lengkap atau tidak valid',
                'Riwayat pembayaran kurang baik',
                'Jumlah pengajuan melebihi kemampuan bayar',
                'Tidak sesuai dengan ketentuan koperasi'
            ]
        ];
        
        $notes = $notesByStatus[$status] ?? ['Catatan approval'];
        return fake()->randomElement($notes);
    }
    
    /**
     * State untuk approval yang pending
     */
    public function pending(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'status_approval' => 'pending',
                'catatan' => 'Pengajuan diterima dan menunggu review',
                'tanggal_approval' => null, // Pending belum ada tanggal approval
                'urutan' => 1,
            ];
        });
    }
    
    /**
     * State untuk approval yang disetujui
     */
    public function approved(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'status_approval' => 'approved',
                'catatan' => 'Pengajuan disetujui sesuai ketentuan',
                'level_approval' => 'ketua_umum',
                'tanggal_approval' => fake()->dateTimeBetween('-1 month', 'now')->format('Y-m-d H:i:s'),
                'urutan' => fake()->numberBetween(2, 4),
            ];
        });
    }
    
    /**
     * State untuk approval yang ditolak
     */
    public function rejected(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'status_approval' => 'rejected',
                'catatan' => fake()->randomElement([
                    'Tidak memenuhi syarat kelayakan kredit',
                    'Dokumen tidak lengkap atau tidak valid',
                    'Riwayat pembayaran kurang baik'
                ]),
                'level_approval' => 'ketua_umum',
                'tanggal_approval' => fake()->dateTimeBetween('-1 month', 'now')->format('Y-m-d H:i:s'),
                'urutan' => fake()->numberBetween(2, 4),
            ];
        });
    }
    
    /**
     * State untuk pencairan (approved dengan level admin_transfer)
     */
    public function disbursed(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'status_approval' => 'approved',
                'catatan' => 'Dana telah dicairkan ke rekening anggota',
                'level_approval' => 'admin_transfer',
                'tanggal_approval' => fake()->dateTimeBetween('-1 week', 'now')->format('Y-m-d H:i:s'),
                'urutan' => 5, // Pencairan biasanya step terakhir
            ];
        });
    }

    /**
     * State untuk pengajuan tertentu
     */
    public function forPengajuan(PengajuanPinjaman $pengajuan): static
    {
        return $this->state(fn (array $attributes) => [
            'pengajuan_pinjaman_id' => $pengajuan->id,
        ]);
    }

    /**
     * State untuk level approval tertentu
     */
    public function byLevel(string $level): static
    {
        return $this->state(fn (array $attributes) => [
            'level_approval' => $level,
        ]);
    }
}