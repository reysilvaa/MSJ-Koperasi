<?php

namespace App\Helpers\Koperasi\Iuran;

use Illuminate\Support\Facades\DB;
use App\Helpers\Function_Helper;
use App\Models\User;
use App\Models\Iuran;

class IuranHelper
{
    /**
     * Validasi tahun untuk laporan iuran
     *
     * @param mixed $tahun
     * @return array ['valid' => bool, 'message' => string, 'tahun' => int]
     */
    public static function validateTahun($tahun)
    {
        $currentYear = date('Y');

        // Validasi format angka
        if (!is_numeric($tahun)) {
            return [
                'valid' => false,
                'message' => 'Tahun harus berupa angka',
                'tahun' => null
            ];
        }

        $tahun = intval($tahun);

        // Validasi tahun tidak boleh melebihi tahun saat ini
        if ($tahun > $currentYear) {
            return [
                'valid' => false,
                'message' => "Tahun tidak boleh melebihi tahun saat ini ({$currentYear})",
                'tahun' => null
            ];
        }

        // Validasi tahun minimum
        if ($tahun < 2000) {
            return [
                'valid' => false,
                'message' => 'Tahun tidak boleh kurang dari 2000',
                'tahun' => null
            ];
        }

        return [
            'valid' => true,
            'message' => 'Tahun valid',
            'tahun' => $tahun
        ];
    }

    /**
     * Mendapatkan data laporan iuran anggota per tahun (format tabel bulanan)
     *
     * @param int $tahun
     * @return array
     */
    public static function getIuranAnggotaData($tahun)
    {
        // Ambil semua anggota aktif
        $anggota = User::where('isactive', '1')
            ->whereNotNull('nomor_anggota')
            ->select('id', 'nomor_anggota', 'nama_lengkap')
            ->orderBy('nama_lengkap')
            ->get();

        // Ambil data iuran untuk tahun tersebut dengan grouping yang lebih efisien
        $iuranData = Iuran::where('tahun', $tahun)
            ->select('user_id', 'jenis_iuran', 'bulan', DB::raw('SUM(iuran) as total_iuran'))
            ->groupBy('user_id', 'jenis_iuran', 'bulan')
            ->get()
            ->groupBy(['user_id', 'bulan', 'jenis_iuran']);

        $result = [];
        $no = 1;

        foreach ($anggota as $member) {
            $memberData = [
                'no' => $no++,
                'user_id' => $member->id,
                'nomor_anggota' => $member->nomor_anggota,
                'nama_lengkap' => $member->nama_lengkap,
                'bulan' => []
            ];

            // Initialize 12 bulan dengan 0
            for ($bulan = 1; $bulan <= 12; $bulan++) {
                $memberData['bulan'][$bulan] = [
                    'wajib' => 0,
                    'pokok' => 0,
                    'total' => 0
                ];
            }

            // Isi data iuran yang ada
            if (isset($iuranData[$member->id])) {
                foreach ($iuranData[$member->id] as $bulan => $jenisData) {
                    foreach ($jenisData as $jenis => $iuranList) {
                        $total = $iuranList->sum('total_iuran');
                        $memberData['bulan'][$bulan][$jenis] = $total;
                    }

                    // Hitung total per bulan (wajib + pokok)
                    $memberData['bulan'][$bulan]['total'] =
                        $memberData['bulan'][$bulan]['wajib'] +
                        $memberData['bulan'][$bulan]['pokok'];
                }
            }

            // Hitung total saldo per anggota
            $memberData['total_saldo'] = 0;
            for ($bulan = 1; $bulan <= 12; $bulan++) {
                $memberData['total_saldo'] += $memberData['bulan'][$bulan]['total'];
            }

            $result[] = $memberData;
        }

        return $result;
    }

    /**
     * Mendapatkan ringkasan total per bulan dan per jenis
     *
     * @param int $tahun
     * @return array
     */
    public static function getIuranSummary($tahun)
    {
        // Ambil data iuran dengan grouping yang efisien
        $iuranSummary = Iuran::where('tahun', $tahun)
            ->select('bulan', 'jenis_iuran', DB::raw('SUM(iuran) as total'))
            ->groupBy('bulan', 'jenis_iuran')
            ->get()
            ->groupBy(['bulan', 'jenis_iuran']);

        // Initialize arrays
        $totalPerBulan = [];
        $totalSP = []; // Simpanan Pokok
        $totalSW = []; // Simpanan Wajib

        for ($bulan = 1; $bulan <= 12; $bulan++) {
            $totalSP[$bulan] = 0;
            $totalSW[$bulan] = 0;
            $totalPerBulan[$bulan] = 0;

            if (isset($iuranSummary[$bulan])) {
                if (isset($iuranSummary[$bulan]['pokok'])) {
                    $totalSP[$bulan] = $iuranSummary[$bulan]['pokok']->sum('total');
                }
                if (isset($iuranSummary[$bulan]['wajib'])) {
                    $totalSW[$bulan] = $iuranSummary[$bulan]['wajib']->sum('total');
                }
                $totalPerBulan[$bulan] = $totalSP[$bulan] + $totalSW[$bulan];
            }
        }

        return [
            'total_per_bulan' => $totalPerBulan,
            'total_sp' => $totalSP,
            'total_sw' => $totalSW,
            'grand_total' => array_sum($totalPerBulan),
            'grand_total_sp' => array_sum($totalSP),
            'grand_total_sw' => array_sum($totalSW)
        ];
    }

    /**
     * Memproses data laporan iuran anggota per tahun
     *
     * @param int $tahun
     * @return array
     */
    public static function processIuranReport($tahun)
    {
        try {
            // Debug data consistency
            $consistency = self::checkDataConsistency($tahun);

            // Dapatkan data iuran anggota per tahun
            $tableResult = self::getIuranAnggotaData($tahun);

            // Dapatkan ringkasan data
            $summary = self::getIuranSummary($tahun);

            // Log aktivitas (sistem internal koperasi)
            $functionHelper = new Function_Helper();
            $functionHelper->log_insert('V', 'KOP401', "Generate laporan iuran anggota tahun {$tahun}", '1');

            return [
                'success' => true,
                'data' => [
                    'table_result' => $tableResult,
                    'summary' => $summary,
                    'filter' => [
                        'tahun' => $tahun
                    ],
                    'title_menu' => 'Laporan Iuran Tahunan',
                    'total_records' => count($tableResult),
                    'debug_info' => $consistency
                ]
            ];

        } catch (\Exception $e) {
            // Catat error ke sistem internal
            $functionHelper = new Function_Helper();
            $functionHelper->log_insert('E', 'KOP401', "Error generate laporan iuran: " . $e->getMessage(), '0');

            return [
                'success' => false,
                'message' => 'Terjadi kesalahan saat memproses laporan: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Mendapatkan nama bulan dalam bahasa Indonesia
     *
     * @param int $bulan
     * @return string
     */
    public static function getNamaBulan($bulan)
    {
        $namaBulan = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
        ];

        return $namaBulan[$bulan] ?? 'Tidak Diketahui';
    }

    /**
     * Mendapatkan nama bulan singkat
     *
     * @param int $bulan
     * @return string
     */
    public static function getNamaBulanSingkat($bulan)
    {
        $namaBulan = [
            1 => 'Jan', 2 => 'Feb', 3 => 'Mar', 4 => 'Apr',
            5 => 'Mei', 6 => 'Jun', 7 => 'Jul', 8 => 'Agus',
            9 => 'Sept', 10 => 'Oct', 11 => 'Nov', 12 => 'Dec'
        ];

        return $namaBulan[$bulan] ?? 'N/A';
    }

    /**
     * Mendapatkan informasi periode saat ini
     *
     * @return array
     */
    public static function getCurrentPeriodInfo()
    {
        return [
            'current_year' => date('Y'),
            'current_month' => date('n'),
            'tahun_default' => date('Y')
        ];
    }

    /**
     * Format data untuk export (placeholder untuk pengembangan selanjutnya)
     *
     * @param int $tahun
     * @param int $bulan
     * @param string $jenis_iuran
     * @param string $exportType
     * @return array
     */
    public static function prepareExportData($tahun, $bulan, $jenis_iuran, $exportType)
    {
        return [
            'success' => false,
            'message' => 'Fitur export sedang dalam pengembangan',
            'redirect_back' => true
        ];
    }

    /**
     * Validasi data iuran sebelum disimpan
     *
     * @param array $data
     * @return array
     */
    public static function validateIuranData($data)
    {
        $errors = [];

        // Validasi user_id
        if (empty($data['user_id'])) {
            $errors[] = 'Anggota harus dipilih';
        } else {
            $user = User::where('id', $data['user_id'])
                ->where('isactive', '1')
                ->whereNotNull('nomor_anggota')
                ->first();

            if (!$user) {
                $errors[] = 'Anggota tidak valid atau tidak aktif';
            }
        }

        // Validasi jenis_iuran
        if (empty($data['jenis_iuran']) || !in_array($data['jenis_iuran'], ['wajib', 'pokok'])) {
            $errors[] = 'Jenis iuran harus dipilih (wajib atau pokok)';
        }

        // Validasi nominal
        if (empty($data['iuran']) || !is_numeric($data['iuran']) || $data['iuran'] <= 0) {
            $errors[] = 'Nominal iuran harus diisi dengan angka positif';
        }

        // Validasi bulan
        if (empty($data['bulan']) || !is_numeric($data['bulan']) || $data['bulan'] < 1 || $data['bulan'] > 12) {
            $errors[] = 'Bulan harus dipilih (1-12)';
        }

        // Validasi tahun
        if (empty($data['tahun']) || !is_numeric($data['tahun']) || $data['tahun'] < 2000 || $data['tahun'] > (date('Y') + 1)) {
            $errors[] = 'Tahun tidak valid';
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors
        ];
    }

    /**
     * Debug method untuk melihat data iuran yang tersedia
     *
     * @param int $tahun
     * @return array
     */
    public static function debugIuranData($tahun)
    {
        $debug = [
            'tahun' => $tahun,
            'total_anggota_aktif' => User::where('isactive', '1')->whereNotNull('nomor_anggota')->count(),
            'total_iuran_records' => Iuran::where('tahun', $tahun)->count(),
            'iuran_by_jenis' => [
                'wajib' => Iuran::where('tahun', $tahun)->where('jenis_iuran', 'wajib')->count(),
                'pokok' => Iuran::where('tahun', $tahun)->where('jenis_iuran', 'pokok')->count(),
            ],
            'iuran_by_bulan' => [],
            'sample_data' => Iuran::where('tahun', $tahun)->limit(5)->get()->toArray()
        ];

        // Count per bulan
        for ($bulan = 1; $bulan <= 12; $bulan++) {
            $debug['iuran_by_bulan'][$bulan] = [
                'total' => Iuran::where('tahun', $tahun)->where('bulan', $bulan)->count(),
                'wajib' => Iuran::where('tahun', $tahun)->where('bulan', $bulan)->where('jenis_iuran', 'wajib')->count(),
                'pokok' => Iuran::where('tahun', $tahun)->where('bulan', $bulan)->where('jenis_iuran', 'pokok')->count(),
            ];
        }

        return $debug;
    }

    /**
     * Method untuk mengecek konsistensi data
     *
     * @param int $tahun
     * @return array
     */
    public static function checkDataConsistency($tahun)
    {
        $issues = [];

        // Cek apakah ada anggota aktif
        $activeMembers = User::where('isactive', '1')->whereNotNull('nomor_anggota')->count();
        if ($activeMembers == 0) {
            $issues[] = 'Tidak ada anggota aktif yang ditemukan';
        }

        // Cek apakah ada data iuran untuk tahun tersebut
        $iuranCount = Iuran::where('tahun', $tahun)->count();
        if ($iuranCount == 0) {
            $issues[] = "Tidak ada data iuran untuk tahun {$tahun}";
        }

        // Cek apakah ada iuran wajib
        $iuranWajib = Iuran::where('tahun', $tahun)->where('jenis_iuran', 'wajib')->count();
        if ($iuranWajib == 0) {
            $issues[] = "Tidak ada data iuran wajib untuk tahun {$tahun}";
        }

        // Cek apakah ada iuran pokok
        $iuranPokok = Iuran::where('tahun', $tahun)->where('jenis_iuran', 'pokok')->count();
        if ($iuranPokok == 0) {
            $issues[] = "Tidak ada data iuran pokok untuk tahun {$tahun}";
        }

        return [
            'has_issues' => !empty($issues),
            'issues' => $issues,
            'stats' => [
                'active_members' => $activeMembers,
                'total_iuran' => $iuranCount,
                'iuran_wajib' => $iuranWajib,
                'iuran_pokok' => $iuranPokok
            ]
        ];
    }
}
