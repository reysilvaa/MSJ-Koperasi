<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Helpers\Function_Helper;
use App\Helpers\Koperasi\Iuran\IuranHelper;
use App\Models\TblIuran;
use App\Models\User;

class IuranAnggotaController extends Controller
{
    /**
     * Display form untuk laporan iuran tahunan (KOP401)
     */
    public function index($data)
    {
        // Export handling
        if (request()->has('export') || request()->has('pdf')) {
            $exportType = request()->input('export');
            return $this->exportData($data['dmenu'], $exportType, request());
        }

        // Set default data menggunakan helper
        $periodInfo = IuranHelper::getCurrentPeriodInfo();
        $data['tahun_default'] = $periodInfo['tahun_default'];

        return view($data['url'], $data);
    }

    /**
     * Process laporan iuran tahunan dan tampilkan hasil (KOP401)
     */
    public function store($data)
    {
        try {
            $request = request();
            $tahun = $request->input('tahun', date('Y'));

            // Validasi tahun menggunakan helper
            $validation = IuranHelper::validateTahun($tahun);
            if (!$validation['valid']) {
                return redirect()->back()->withErrors(['tahun' => $validation['message']]);
            }

            $tahun = $validation['tahun'];

            // Check for export requests
            if ($request->has('export')) {
                $exportResult = IuranHelper::prepareExportData($tahun, 0, '', $request->get('export'));
                if ($exportResult['redirect_back']) {
                    return redirect()->back()->with('info', $exportResult['message']);
                }
            }

            // Proses laporan menggunakan helper (hanya tahun)
            $result = IuranHelper::processIuranReport($tahun);

            if (!$result['success']) {
                return redirect()->back()->withErrors(['error' => $result['message']]);
            }

            // Merge data hasil dengan data yang sudah ada
            $mergedData = array_merge($data, $result['data']);

            // Pastikan data yang diperlukan ada
            $mergedData['url_menu'] = $data['url_menu'] ?? route('manual.index', ['gmenu' => 'KOP004', 'dmenu' => 'KOP401']);
            $mergedData['authorize'] = $data['authorize'] ?? (object)['excel' => '1', 'pdf' => '1', 'print' => '1'];

            // Return result view
            return view('KOP004.iuranAnggota.result', $mergedData);

        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['error' => 'Terjadi kesalahan sistem: ' . $e->getMessage()]);
        }
    }

    /**
     * Export data to Excel or PDF - menggunakan helper
     */
    private function exportData($dmenu, $exportType, $request = null)
    {
        $tahun = $request ? $request->input('tahun', date('Y')) : date('Y');

        $exportResult = IuranHelper::prepareExportData($tahun, 0, '', $exportType);

        if ($exportResult['redirect_back']) {
            return redirect()->back()->with('info', $exportResult['message']);
        }

        // Implementasi export akan ditambahkan di masa depan
        return redirect()->back()->with('info', $exportResult['message']);
    }
}
