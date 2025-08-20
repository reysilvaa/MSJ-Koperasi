<?php

namespace App\Helpers\Koperasi\Iuran;

use Illuminate\Support\Facades\DB;
use App\Models\Anggotum;
use App\Helpers\Function_Helper;
use App\Models\User;

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
     * Mendapatkan data iuran anggota dengan breakdown bulanan
     * Terbatas pada periode saat ini saja
     *
     * @param int $tahun
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function getIuranAnggotaData($tahun)
    {
        $months = self::getMonthsMapping();
        $currentYear = date('Y');
        $currentMonth = date('n');

        $selectFields = [
            'nomor_anggota',
            'nama_lengkap as nama'
        ];

        // Generate kolom bulanan secara dinamis
        foreach ($months as $monthName => $monthNum) {
            $monthNumInt = intval($monthNum);

            // Hanya tampilkan bulan sampai bulan saat ini jika tahun saat ini
            if ($tahun == $currentYear && $monthNumInt > $currentMonth) {
                $selectFields[] = DB::raw("NULL as {$monthName}");
            } else {
                $selectFields[] = DB::raw(self::buildMonthlyContributionQuery($tahun, $monthNum, $monthName));
            }
        }

        // Tambahkan kalkulasi total saldo
        $selectFields[] = DB::raw(self::buildTotalSaldoQuery($tahun, $currentYear, $currentMonth));

        return User::select($selectFields)
            ->where('isactive', '1')
            ->where('tanggal_bergabung', '<=', "{$tahun}-12-31")
            ->orderBy('nomor_anggota')
            ->get();
    }

    /**
     * Mendapatkan data simpanan pokok per bulan (terbatas periode saat ini)
     *
     * @param int $tahun
     * @return array
     */
    public static function getSimpananPokokDataLimited($tahun)
    {
        $currentYear = date('Y');
        $currentMonth = date('n');

        $spData = User::getSimpananPokokByMonth($tahun);

        // Jika tahun saat ini, batasi sampai bulan saat ini saja
        if ($tahun == $currentYear) {
            $limitedSpData = [];
            for ($bulan = 1; $bulan <= $currentMonth; $bulan++) {
                $limitedSpData[$bulan] = $spData[$bulan] ?? 0;
            }
            return $limitedSpData;
        }

        return $spData;
    }

    /**
     * Mendapatkan data simpanan wajib per bulan (terbatas periode saat ini)
     *
     * @param int $tahun
     * @return array
     */
    public static function getSimpananWajibDataLimited($tahun)
    {
        $currentYear = date('Y');
        $currentMonth = date('n');

        $swData = User::getSimpananWajibByMonth($tahun);

        // Jika tahun saat ini, batasi sampai bulan saat ini saja
        if ($tahun == $currentYear) {
            $limitedSwData = [];
            for ($bulan = 1; $bulan <= $currentMonth; $bulan++) {
                $limitedSwData[$bulan] = $swData[$bulan] ?? 0;
            }
            return $limitedSwData;
        }

        return $swData;
    }

    /**
     * Memproses data laporan iuran anggota
     *
     * @param int $tahun
     * @return array
     */
    public static function processIuranReport($tahun)
    {
        $currentYear = date('Y');
        $currentMonth = date('n');

        try {
            // Dapatkan data iuran anggota
            $tableResult = self::getIuranAnggotaData($tahun);

            // Dapatkan data SP dan SW
            $spData = self::getSimpananPokokDataLimited($tahun);
            $swData = self::getSimpananWajibDataLimited($tahun);

            // Log aktivitas
            $functionHelper = new Function_Helper();
            $functionHelper->log_insert('V', 'KOP401', "Generate laporan iuran anggota tahun {$tahun}", '1');

            return [
                'success' => true,
                'data' => [
                    'table_result' => $tableResult,
                    'filter' => ['tahun' => $tahun],
                    'title_menu' => 'Laporan Iuran Bulanan',
                    'sp_data' => $spData,
                    'sw_data' => $swData,
                    'current_year' => $currentYear,
                    'current_month' => $currentMonth,
                    'max_month' => ($tahun == $currentYear) ? $currentMonth : 12
                ]
            ];

        } catch (\Exception $e) {
            // Log error
            $functionHelper = new Function_Helper();
            $functionHelper->log_insert('E', 'KOP401', "Error generate laporan iuran: " . $e->getMessage(), '0');

            return [
                'success' => false,
                'message' => 'Terjadi kesalahan saat memproses laporan: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Mendapatkan mapping bulan
     *
     * @return array
     */
    private static function getMonthsMapping()
    {
        return [
            'jan' => '01', 'feb' => '02', 'mar' => '03', 'apr' => '04',
            'mei' => '05', 'jun' => '06', 'jul' => '07', 'agu' => '08',
            'sep' => '09', 'okt' => '10', 'nov' => '11', 'des' => '12'
        ];
    }

    /**
     * Membangun query untuk kontribusi bulanan
     *
     * @param int $tahun
     * @param string $monthNum
     * @param string $monthName
     * @return string
     */
    private static function buildMonthlyContributionQuery($tahun, $monthNum, $monthName)
    {
        return "(CASE
            WHEN MONTH(tanggal_bergabung) = {$monthNum} AND YEAR(tanggal_bergabung) = {$tahun}
            THEN simpanan_pokok ELSE 0 END) +
            (CASE WHEN tanggal_bergabung < '{$tahun}-{$monthNum}-01'
            THEN simpanan_wajib_bulanan ELSE 0 END) as {$monthName}";
    }

    /**
     * Membangun query untuk total saldo
     *
     * @param int $tahun
     * @param int $currentYear
     * @param int $currentMonth
     * @return string
     */
    private static function buildTotalSaldoQuery($tahun, $currentYear, $currentMonth)
    {
        if ($tahun == $currentYear) {
            return "(CASE
                WHEN YEAR(tanggal_bergabung) = {$tahun} AND MONTH(tanggal_bergabung) <= {$currentMonth}
                THEN simpanan_pokok + (simpanan_wajib_bulanan * ({$currentMonth} - MONTH(tanggal_bergabung)))
                WHEN tanggal_bergabung < '{$tahun}-01-01'
                THEN simpanan_wajib_bulanan * {$currentMonth}
                ELSE 0 END) as total_saldo";
        } else {
            return "(CASE
                WHEN YEAR(tanggal_bergabung) = {$tahun}
                THEN simpanan_pokok + (simpanan_wajib_bulanan * (12 - MONTH(tanggal_bergabung)))
                WHEN tanggal_bergabung < '{$tahun}-01-01'
                THEN simpanan_wajib_bulanan * 12
                ELSE 0 END) as total_saldo";
        }
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
     * @param string $exportType
     * @return array
     */
    public static function prepareExportData($tahun, $exportType)
    {
        // Untuk saat ini, return info bahwa fitur sedang dikembangkan
        return [
            'success' => false,
            'message' => 'Fitur export sedang dalam pengembangan',
            'redirect_back' => true
        ];
    }
}
