<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Helpers\Function_Helper;
use App\Models\Anggotum;

class IuranAnggotaController extends Controller
{
    /**
     * Display the filter form for iuran anggota report
     */
    public function index($data)
    {
        // Function helper
        $syslog = new Function_Helper;

        // Export handling
        if (request()->has('export') || request()->has('pdf')) {
            $exportType = request()->input('export');
            return $this->exportData($data['dmenu'], $exportType, request());
        }

        // Set default data for filter form
        $data['tahun_default'] = date('Y');

        return view($data['url'], $data);
    }

    /**
     * Process the report and show results using Eloquent ORM
     */
    public function store($data)
    {
        $request = request();

        // Get filter parameters
        $tahun = $request->input('tahun', date('Y'));

        // Get current date info for validation
        $currentYear = date('Y');
        $currentMonth = date('n');

        // Comprehensive year validation
        if (!is_numeric($tahun)) {
            return redirect()->back()->withErrors(['tahun' => 'Tahun harus berupa angka']);
        }
        
        $tahun = intval($tahun); // Convert to integer
        
        if ($tahun > $currentYear) {
            return redirect()->back()->withErrors(['tahun' => "Tahun tidak boleh melebihi tahun saat ini ({$currentYear})"]);
        }
        
        if ($tahun < 2000) {
            return redirect()->back()->withErrors(['tahun' => 'Tahun tidak boleh kurang dari 2000']);
        }

        // Check for export requests
        if ($request->has('export')) {
            return $this->exportData($tahun, $request->get('export'));
        }

        try {
            // Get iuran anggota data using Eloquent with calculated monthly contributions
            $data['table_result'] = $this->getIuranAnggotaData($tahun);
            $data['filter'] = ['tahun' => $tahun];
            $data['title_menu'] = 'Laporan Iuran Bulanan';

            // Get SP and SW data using model methods - limited to current period
            $data['sp_data'] = $this->getSimpananPokokDataLimited($tahun);
            $data['sw_data'] = $this->getSimpananWajibDataLimited($tahun);

            // Pass current period info to view
            $data['current_year'] = $currentYear;
            $data['current_month'] = $currentMonth;
            $data['max_month'] = ($tahun == $currentYear) ? $currentMonth : 12;

            // Log activity
            $syslog = new Function_Helper;
            $syslog->log_insert('V', 'KOP401', "Generate laporan iuran anggota tahun {$tahun}", '1');

            // Return result view
            return view('KOP004.iuranAnggota.result', $data);

        } catch (\Exception $e) {
            // Log error
            $syslog = new Function_Helper;
            $syslog->log_insert('E', 'KOP401', "Error generate laporan iuran", '0');

            return redirect()->back()->withErrors(['error' => 'Terjadi kesalahan saat memproses laporan: ' . $e->getMessage()]);
        }
    }

    /**
     * Get iuran anggota data with monthly breakdown using Eloquent
     * Limited to current month and year only
     */
    private function getIuranAnggotaData($tahun)
    {
        $months = [
            'jan' => '01', 'feb' => '02', 'mar' => '03', 'apr' => '04',
            'mei' => '05', 'jun' => '06', 'jul' => '07', 'agu' => '08',
            'sep' => '09', 'okt' => '10', 'nov' => '11', 'des' => '12'
        ];

        // Get current date for limiting data display
        $currentYear = date('Y');
        $currentMonth = date('n'); // 1-12 without leading zeros

        $selectFields = [
            'nomor_anggota',
            'nama_lengkap as nama'
        ];

        // Generate monthly columns dynamically - only up to current month
        foreach ($months as $monthName => $monthNum) {
            $monthNumInt = intval($monthNum);
            
            // Only include months up to current month if it's current year
            if ($tahun == $currentYear && $monthNumInt > $currentMonth) {
                // For future months in current year, show null/empty
                $selectFields[] = DB::raw("NULL as {$monthName}");
            } else {
                $selectFields[] = DB::raw("(CASE 
                    WHEN MONTH(tanggal_bergabung) = {$monthNum} AND YEAR(tanggal_bergabung) = {$tahun} 
                    THEN simpanan_pokok ELSE 0 END) + 
                    (CASE WHEN tanggal_bergabung < '{$tahun}-{$monthNum}-01' 
                    THEN simpanan_wajib_bulanan ELSE 0 END) as {$monthName}");
            }
        }

        // Add total saldo calculation - adjusted for current period
        if ($tahun == $currentYear) {
            $selectFields[] = DB::raw("(CASE 
                WHEN YEAR(tanggal_bergabung) = {$tahun} AND MONTH(tanggal_bergabung) <= {$currentMonth}
                THEN simpanan_pokok + (simpanan_wajib_bulanan * ({$currentMonth} - MONTH(tanggal_bergabung)))
                WHEN tanggal_bergabung < '{$tahun}-01-01' 
                THEN simpanan_wajib_bulanan * {$currentMonth}
                ELSE 0 END) as total_saldo");
        } else {
            $selectFields[] = DB::raw("(CASE 
                WHEN YEAR(tanggal_bergabung) = {$tahun} 
                THEN simpanan_pokok + (simpanan_wajib_bulanan * (12 - MONTH(tanggal_bergabung)))
                WHEN tanggal_bergabung < '{$tahun}-01-01' 
                THEN simpanan_wajib_bulanan * 12
                ELSE 0 END) as total_saldo");
        }

        return Anggotum::select($selectFields)
            ->where('isactive', '1')
            ->where('tanggal_bergabung', '<=', "{$tahun}-12-31")
            ->orderBy('nomor_anggota')
            ->get();
    }

    /**
     * Get Simpanan Pokok data per month - limited to current period
     */
    private function getSimpananPokokDataLimited($tahun)
    {
        $currentYear = date('Y');
        $currentMonth = date('n');
        
        $spData = Anggotum::getSimpananPokokByMonth($tahun);
        
        // If current year, limit to current month only
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
     * Get Simpanan Wajib data per month - limited to current period
     */
    private function getSimpananWajibDataLimited($tahun)
    {
        $currentYear = date('Y');
        $currentMonth = date('n');
        
        $swData = Anggotum::getSimpananWajibByMonth($tahun);
        
        // If current year, limit to current month only
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
     * Export data to Excel or PDF - MSJ Framework pattern
     */
    private function exportData($dmenu, $exportType, $request = null)
    {
        // Get tahun from request
        $tahun = $request ? $request->input('tahun', date('Y')) : date('Y');

        // For now, return info message
        return redirect()->back()->with('info', 'Fitur export sedang dalam pengembangan');
    }
}