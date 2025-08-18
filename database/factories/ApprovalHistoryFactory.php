<?php

namespace Database\Factories;

use App\Models\ApprovalHistory;
use App\Models\PengajuanPinjaman;
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
    public function definition()
    {
        // Get random pengajuan pinjaman
        $pengajuan = PengajuanPinjaman::inRandomOrder()->first();
        $pengajuanId = $pengajuan ? $pengajuan->id : 1;
        
        // Approval workflow stages
        $statusOptions = [
            'pending_review',
            'review_panitia', 
            'approved',
            'rejected',
            'pencairan'
        ];
        
        // Approver names
        $approvers = [
            'Ketua Koperasi',
            'Sekretaris Koperasi',
            'Bendahara Koperasi',
            'Ketua Panitia Kredit',
            'Anggota Panitia Kredit',
            'Manager Koperasi',
            'Admin Koperasi'
        ];
        
        // Generate approval notes based on status
        $status = $this->faker->randomElement($statusOptions);
        $notes = $this->generateNotesByStatus($status);
        
        return [
            'pengajuan_pinjaman_id' => $pengajuanId,
            'approver' => $this->faker->randomElement($approvers),
            'status' => $status,
            'catatan' => $notes,
            'tanggal_approval' => $this->faker->dateTimeBetween('-6 months', 'now')->format('Y-m-d'),
            'isactive' => '1',
            'user_create' => 'system',
            'user_update' => 'system'
        ];
    }
    
    /**
     * Generate notes based on approval status
     */
    private function generateNotesByStatus($status)
    {
        $notesByStatus = [
            'pending_review' => [
                'Pengajuan diterima dan menunggu review',
                'Dokumen lengkap, siap untuk ditinjau',
                'Pengajuan masuk antrian review'
            ],
            'review_panitia' => [
                'Sedang dalam tahap review panitia kredit',
                'Panitia sedang mengevaluasi kelayakan',
                'Dalam proses verifikasi dokumen'
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
            ],
            'pencairan' => [
                'Dana telah dicairkan ke rekening anggota',
                'Pencairan berhasil dilakukan',
                'Dana sudah ditransfer sesuai persetujuan'
            ]
        ];
        
        $notes = $notesByStatus[$status] ?? ['Catatan approval'];
        return $this->faker->randomElement($notes);
    }
    
    /**
     * Indicate that the approval is pending.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function pending()
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => 'pending_review',
                'catatan' => 'Pengajuan diterima dan menunggu review',
                'tanggal_approval' => now()->format('Y-m-d'),
            ];
        });
    }
    
    /**
     * Indicate that the approval is approved.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function approved()
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => 'approved',
                'catatan' => 'Pengajuan disetujui sesuai ketentuan',
                'approver' => 'Ketua Panitia Kredit',
            ];
        });
    }
    
    /**
     * Indicate that the approval is rejected.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function rejected()
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => 'rejected',
                'catatan' => $this->faker->randomElement([
                    'Tidak memenuhi syarat kelayakan kredit',
                    'Dokumen tidak lengkap atau tidak valid',
                    'Riwayat pembayaran kurang baik'
                ]),
                'approver' => 'Ketua Panitia Kredit',
            ];
        });
    }
    
    /**
     * Indicate that the loan has been disbursed.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function disbursed()
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => 'pencairan',
                'catatan' => 'Dana telah dicairkan ke rekening anggota',
                'approver' => 'Bendahara Koperasi',
            ];
        });
    }
}
