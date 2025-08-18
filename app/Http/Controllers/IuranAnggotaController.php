<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Helpers\Function_Helper;

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
     * Process the report and show results
     */
    public function store($data)
    {
        $request = request();

        // Get filter parameters
        $tahun = $request->input('tahun', date('Y'));

        // Validate tahun - hanya pastikan input adalah angka
        if (!is_numeric($tahun)) {
            return redirect()->back()->withErrors(['tahun' => 'Tahun harus berupa angka']);
        }

        // Check for export requests
        if ($request->has('export')) {
            return $this->exportData($tahun, $request->get('export'));
        }

        // Build the query for iuran anggota
        $query = "SELECT
            a.nomor_anggota AS nomor_anggota,
            a.nama_lengkap AS nama,

            -- Januari
            (CASE
                WHEN MONTH(a.tanggal_bergabung) = 1 AND YEAR(a.tanggal_bergabung) = ?
                    THEN a.simpanan_pokok
                ELSE 0
            END) +
            (CASE
                WHEN a.tanggal_bergabung <= CONCAT(?, '-01-31')
                    THEN a.simpanan_wajib_bulanan
                ELSE 0
            END) AS jan,

            -- Februari
            (CASE
                WHEN MONTH(a.tanggal_bergabung) = 2 AND YEAR(a.tanggal_bergabung) = ?
                    THEN a.simpanan_pokok
                ELSE 0
            END) +
            (CASE
                WHEN a.tanggal_bergabung <= CONCAT(?, '-02-28')
                    THEN a.simpanan_wajib_bulanan
                ELSE 0
            END) AS feb,

            -- Maret
            (CASE
                WHEN MONTH(a.tanggal_bergabung) = 3 AND YEAR(a.tanggal_bergabung) = ?
                    THEN a.simpanan_pokok
                ELSE 0
            END) +
            (CASE
                WHEN a.tanggal_bergabung <= CONCAT(?, '-03-31')
                    THEN a.simpanan_wajib_bulanan
                ELSE 0
            END) AS mar,

            -- April
            (CASE
                WHEN MONTH(a.tanggal_bergabung) = 4 AND YEAR(a.tanggal_bergabung) = ?
                    THEN a.simpanan_pokok
                ELSE 0
            END) +
            (CASE
                WHEN a.tanggal_bergabung <= CONCAT(?, '-04-30')
                    THEN a.simpanan_wajib_bulanan
                ELSE 0
            END) AS apr,

            -- Mei
            (CASE
                WHEN MONTH(a.tanggal_bergabung) = 5 AND YEAR(a.tanggal_bergabung) = ?
                    THEN a.simpanan_pokok
                ELSE 0
            END) +
            (CASE
                WHEN a.tanggal_bergabung <= CONCAT(?, '-05-31')
                    THEN a.simpanan_wajib_bulanan
                ELSE 0
            END) AS mei,

            -- Juni
            (CASE
                WHEN MONTH(a.tanggal_bergabung) = 6 AND YEAR(a.tanggal_bergabung) = ?
                    THEN a.simpanan_pokok
                ELSE 0
            END) +
            (CASE
                WHEN a.tanggal_bergabung <= CONCAT(?, '-06-30')
                    THEN a.simpanan_wajib_bulanan
                ELSE 0
            END) AS jun,

            -- Juli
            (CASE
                WHEN MONTH(a.tanggal_bergabung) = 7 AND YEAR(a.tanggal_bergabung) = ?
                    THEN a.simpanan_pokok
                ELSE 0
            END) +
            (CASE
                WHEN a.tanggal_bergabung <= CONCAT(?, '-07-31')
                    THEN a.simpanan_wajib_bulanan
                ELSE 0
            END) AS jul,

            -- Agustus
            (CASE
                WHEN MONTH(a.tanggal_bergabung) = 8 AND YEAR(a.tanggal_bergabung) = ?
                    THEN a.simpanan_pokok
                ELSE 0
            END) +
            (CASE
                WHEN a.tanggal_bergabung <= CONCAT(?, '-08-31')
                    THEN a.simpanan_wajib_bulanan
                ELSE 0
            END) AS agu,

            -- September
            (CASE
                WHEN MONTH(a.tanggal_bergabung) = 9 AND YEAR(a.tanggal_bergabung) = ?
                    THEN a.simpanan_pokok
                ELSE 0
            END) +
            (CASE
                WHEN a.tanggal_bergabung <= CONCAT(?, '-09-30')
                    THEN a.simpanan_wajib_bulanan
                ELSE 0
            END) AS sep,

            -- Oktober
            (CASE
                WHEN MONTH(a.tanggal_bergabung) = 10 AND YEAR(a.tanggal_bergabung) = ?
                    THEN a.simpanan_pokok
                ELSE 0
            END) +
            (CASE
                WHEN a.tanggal_bergabung <= CONCAT(?, '-10-31')
                    THEN a.simpanan_wajib_bulanan
                ELSE 0
            END) AS okt,

            -- November
            (CASE
                WHEN MONTH(a.tanggal_bergabung) = 11 AND YEAR(a.tanggal_bergabung) = ?
                    THEN a.simpanan_pokok
                ELSE 0
            END) +
            (CASE
                WHEN a.tanggal_bergabung <= CONCAT(?, '-11-30')
                    THEN a.simpanan_wajib_bulanan
                ELSE 0
            END) AS nov,

            -- Desember
            (CASE
                WHEN MONTH(a.tanggal_bergabung) = 12 AND YEAR(a.tanggal_bergabung) = ?
                    THEN a.simpanan_pokok
                ELSE 0
            END) +
            (CASE
                WHEN a.tanggal_bergabung <= CONCAT(?, '-12-31')
                    THEN a.simpanan_wajib_bulanan
                ELSE 0
            END) AS des,

            -- Total saldo
            (CASE
                WHEN YEAR(a.tanggal_bergabung) = ?
                    THEN a.simpanan_pokok + (a.simpanan_wajib_bulanan * (13 - MONTH(a.tanggal_bergabung)))
                WHEN a.tanggal_bergabung <= CONCAT(?, '-12-31')
                    THEN a.simpanan_wajib_bulanan * 12
                ELSE 0
            END) AS total_saldo

        FROM anggota a
        WHERE a.isactive = '1'
            AND a.tanggal_bergabung <= CONCAT(?, '-12-31')
        ORDER BY a.nomor_anggota";

        // Prepare parameters - we need 27 instances of the same year value
        // Each month needs 2 parameters (1 for SP, 1 for SW) + 2 for total_saldo + 1 for WHERE = 27 total
        $params = array_fill(0, 27, $tahun);
        // dd($params);

        try {
            // Execute query
            $data['table_result'] = DB::select($query, $params);
            $data['filter'] = ['tahun' => $tahun];
            $data['title_menu'] = 'Laporan Iuran Bulanan';

            // Log activity
            $syslog = new Function_Helper;
            $syslog->log_insert('V', 'KOP401', "Generate laporan iuran anggota tahun {$tahun}", '1');

            // Return result view - MSJ Framework pattern for manual layout
            return view('KOP004.iuranAnggota.result', $data);

        } catch (\Exception $e) {
            // Log error
            $syslog = new Function_Helper;
            $syslog->log_insert('E', 'KOP401', "Error generate laporan iuran", '0');

            return redirect()->back()->withErrors(['error' => 'Terjadi kesalahan saat memproses laporan: ' . $e->getMessage()]);
        }
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
