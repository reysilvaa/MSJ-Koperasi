<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Helpers\Function_Helper;
use App\Helpers\Koperasi\Iuran\IuranHelper;

class IuranAnggotaController extends Controller
{
    /**
     * Display the filter form for iuran anggota report
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
     * Process the report and show results using Helper
     */
    public function store($data)
    {
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
            $exportResult = IuranHelper::prepareExportData($tahun, $request->get('export'));
            if ($exportResult['redirect_back']) {
                return redirect()->back()->with('info', $exportResult['message']);
            }
        }

        // Proses laporan menggunakan helper
        $result = IuranHelper::processIuranReport($tahun);

        if (!$result['success']) {
            return redirect()->back()->withErrors(['error' => $result['message']]);
        }

        // Merge data hasil dengan data yang sudah ada
        $data = array_merge($data, $result['data']);

        // Return result view
        return view('KOP004.iuranAnggota.result', $data);
    }

    /**
     * Export data to Excel or PDF - menggunakan helper
     */
    private function exportData($dmenu, $exportType, $request = null)
    {
        $tahun = $request ? $request->input('tahun', date('Y')) : date('Y');

        $exportResult = IuranHelper::prepareExportData($tahun, $exportType);

        if ($exportResult['redirect_back']) {
            return redirect()->back()->with('info', $exportResult['message']);
        }

        // Implementasi export akan ditambahkan di masa depan
        return redirect()->back()->with('info', $exportResult['message']);
    }
}
