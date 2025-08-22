<?php

namespace App\Http\Controllers;

use App\Helpers\Function_Helper;
use App\Helpers\Format_Helper;
use App\Models\PeriodePencairan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use Illuminate\Pagination\LengthAwarePaginator;

class PeriodePencairanController extends Controller
{
    /**
     * Display a listing of the resource - KOP301/list
     */
    public function index($data)
    {
        // Export handling
        if (request()->has('export') || request()->has('pdf')) {
            $exportType = request()->input('export');
            return $this->exportData($data['dmenu'], $exportType, request());
        }

        // Function helper
        $syslog = new Function_Helper;
        $data['format'] = new Format_Helper;
        

        // Get table structure data
        $data['table_header'] = DB::table('sys_table')
            ->where(['gmenu' => $data['gmenuid'], 'dmenu' => $data['dmenu'], 'list' => '1'])
            ->orderBy('urut')
            ->get();

        $data['table_primary'] = DB::table('sys_table')
            ->where(['gmenu' => $data['gmenuid'], 'dmenu' => $data['dmenu'], 'primary' => '1'])
            ->orderBy('urut')
            ->get();

        // Get periode pencairan data - show both active and inactive records
        $query = PeriodePencairan::query();

        // Apply search filter
        $search = request('search');
        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('tahun', 'like', "%{$search}%")
                  ->orWhere('bulan', 'like', "%{$search}%")
                  ->orWhere('nama_periode', 'like', "%{$search}%");
            });
        }

        // Apply authorization rules using model method if exists
        if (method_exists(PeriodePencairan::class, 'applyAuthorizationRules')) {
            $query = PeriodePencairan::applyAuthorizationRules($query, $data['authorize'], $data['users_rules']);
        }

        $collectionData = $query->orderBy('isactive', 'desc') // Show active first
                               ->orderBy('tahun', 'desc')
                               ->orderBy('bulan', 'asc')
                               ->get();

        // MSJ Framework standard: Use Laravel Pagination for view compatibility
        $currentPage = LengthAwarePaginator::resolveCurrentPage();
        $perPage = 10;
        $currentItems = $collectionData->slice(($currentPage - 1) * $perPage, $perPage)->all();
        $data['list'] = new LengthAwarePaginator(
            $currentItems,
            $collectionData->count(),
            $perPage,
            $currentPage,
            ['path' => request()->url(), 'query' => request()->query()]
        );

        // Get summary statistics
        if (method_exists(PeriodePencairan::class, 'getStatistics')) {
            $data['stats'] = PeriodePencairan::getStatistics($collectionData);
        } else {
            // Manual statistics calculation
            $data['stats'] = [
                'total_periode' => $collectionData->count(),
                'periode_aktif' => $collectionData->where('isactive', '1')->count(),
                'periode_nonaktif' => $collectionData->where('isactive', '0')->count(),
                'tahun_terbaru' => $collectionData->max('tahun')
            ];
        }

        // Log access
        $syslog->log_insert('R', $data['dmenu'], 'Periode Pencairan List Accessed', '1');

        return view($data['url'], $data);
    }

    /**
     * Show the form for creating a new resource - KOP301/add
     */
    public function add($data)
    {
        // Function helper
        $syslog = new Function_Helper;
        $data['format'] = new Format_Helper;

        // Check authorization
        if ($data['authorize']->add == '0') {
            $data['errorpages'] = 'Not Authorized!';
            $syslog->log_insert('E', $data['url_menu'], 'Not Authorized! - Add Periode', '0');
            return view("pages.errorpages", $data);
        }

        // Get table structure data
        $data['table_header'] = DB::table('sys_table')
            ->where(['gmenu' => $data['gmenuid'], 'dmenu' => $data['dmenu']])
            ->orderBy('urut')
            ->get();

        $data['table_primary'] = DB::table('sys_table')
            ->where(['gmenu' => $data['gmenuid'], 'dmenu' => $data['dmenu'], 'primary' => '1'])
            ->orderBy('urut')
            ->get();

        // Log access
        $syslog->log_insert('R', $data['dmenu'], 'Periode Pencairan Add Form Accessed', '1');

        return view($data['url'], $data);
    }

    /**
     * Store a newly created resource in storage - KOP301/store
     */
    public function store($data)
    {
        $syslog = new Function_Helper;

        // Check authorization
        if ($data['authorize']->add == '0') {
            $data['errorpages'] = 'Not Authorized!';
            $syslog->log_insert('E', $data['url_menu'], 'Not Authorized! - Store Periode', '0');
            return view("pages.errorpages", $data);
        }

        // Validate input
        $validator = Validator::make(request()->all(), [
            'tahun' => 'required|numeric|min:2020|max:2030'
        ]);

        if ($validator->fails()) {
            Session::flash('message', 'Tahun harus berupa angka antara 2020-2030');
            Session::flash('class', 'danger');
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $tahun = request('tahun');
        $username = $data['user_login']->username ?? 'system';

        try {
            DB::beginTransaction();

            // Generate periode
            if (method_exists(PeriodePencairan::class, 'generateYearlyPeriods')) {
                $created_periods = PeriodePencairan::generateYearlyPeriods($tahun, $username);
            } else {
                // Manual generation if method doesn't exist
                $created_periods = [];
                $months = [
                    1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
                    5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
                    9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
                ];

                foreach ($months as $bulan => $nama_bulan) {
                    $existing = PeriodePencairan::where('tahun', $tahun)
                                              ->where('bulan', $bulan)
                                              ->first();
                    
                    if (!$existing) {
                        $periode = PeriodePencairan::create([
                            'tahun' => $tahun,
                            'bulan' => $bulan,
                            'nama_periode' => $nama_bulan . ' ' . $tahun,
                            'isactive' => '1',
                            'user_create' => $username,
                            'user_update' => $username
                        ]);
                        $created_periods[] = $periode;
                    }
                }
            }

            if (count($created_periods) > 0) {
                // Log success
                $syslog->log_insert('C', $data['dmenu'],
                    "Generate periode tahun {$tahun} - " . count($created_periods) . " periode dibuat", '1');

                DB::commit();

                Session::flash('message', "Berhasil membuat " . count($created_periods) . " periode untuk tahun {$tahun}");
                Session::flash('class', 'success');
                return redirect($data['url_menu']);
            } else {
                DB::rollback();

                Session::flash('message', "Semua periode untuk tahun {$tahun} sudah ada");
                Session::flash('class', 'warning');
                return redirect($data['url_menu']);
            }

        } catch (\Exception $e) {
            DB::rollback();

            // Log error
            $syslog->log_insert('E', $data['dmenu'],
                "Error generate periode tahun {$tahun}: " . $e->getMessage(), '0');

            Session::flash('message', 'Terjadi kesalahan: ' . $e->getMessage());
            Session::flash('class', 'danger');
            return redirect($data['url_menu']);
        }
    }

    /**
     * Display the specified resource - KOP301/show
     */
    public function show($data)
    {
        $syslog = new Function_Helper;
        $data['format'] = new Format_Helper;
        $id = decrypt($data['idencrypt']);

        // Check authorization
        if ($data['authorize']->view == '0') {
            $data['errorpages'] = 'Not Authorized!';
            $syslog->log_insert('E', $data['url_menu'], 'Not Authorized! - Show Periode', '0');
            return view("pages.errorpages", $data);
        }

        // Get table structure data
        $data['table_header'] = DB::table('sys_table')
            ->where(['gmenu' => $data['gmenuid'], 'dmenu' => $data['dmenu']])
            ->orderBy('urut')
            ->get();

        $data['table_primary'] = DB::table('sys_table')
            ->where(['gmenu' => $data['gmenuid'], 'dmenu' => $data['dmenu'], 'primary' => '1'])
            ->orderBy('urut')
            ->get();

        // Get periode data
        $data['list'] = PeriodePencairan::find($id);

        if (!$data['list']) {
            Session::flash('message', 'Data periode tidak ditemukan');
            Session::flash('class', 'danger');
            return redirect($data['url_menu']);
        }

        // Log access
        $syslog->log_insert('R', $data['dmenu'], 'Periode Pencairan Show: ' . $id, '1');

        return view($data['url'], $data);
    }

    /**
     * Show the form for editing the specified resource - KOP301/edit
     */
    public function edit($data)
    {
        $syslog = new Function_Helper;
        $data['format'] = new Format_Helper;
        $id = decrypt($data['idencrypt']);

        // Check authorization
        if ($data['authorize']->edit == '0') {
            $data['errorpages'] = 'Not Authorized!';
            $syslog->log_insert('E', $data['url_menu'], 'Not Authorized! - Edit Periode', '0');
            return view("pages.errorpages", $data);
        }

        // Get table structure data
        $data['table_header'] = DB::table('sys_table')
            ->where(['gmenu' => $data['gmenuid'], 'dmenu' => $data['dmenu']])
            ->orderBy('urut')
            ->get();

        $data['table_primary'] = DB::table('sys_table')
            ->where(['gmenu' => $data['gmenuid'], 'dmenu' => $data['dmenu'], 'primary' => '1'])
            ->orderBy('urut')
            ->get();

        // Get periode data
        $data['list'] = PeriodePencairan::find($id);

        if (!$data['list']) {
            Session::flash('message', 'Data periode tidak ditemukan');
            Session::flash('class', 'danger');
            return redirect($data['url_menu']);
        }

        // Log access
        $syslog->log_insert('R', $data['dmenu'], 'Periode Pencairan Edit Form: ' . $id, '1');

        return view($data['url'], $data);
    }

    /**
     * Update the specified resource in storage - KOP301/update
     */
    public function update($data)
    {
        $syslog = new Function_Helper;
        $id = decrypt($data['idencrypt']);

        // Check authorization
        if ($data['authorize']->edit == '0') {
            $data['errorpages'] = 'Not Authorized!';
            $syslog->log_insert('E', $data['url_menu'], 'Not Authorized! - Update Periode', '0');
            return view("pages.errorpages", $data);
        }

        // Validate input
        $validator = Validator::make(request()->all(), [
            'tahun' => 'required|numeric|min:2020|max:2030',
            'bulan' => 'required|numeric|min:1|max:12'
        ]);

        if ($validator->fails()) {
            Session::flash('message', 'Data tidak valid');
            Session::flash('class', 'danger');
            return redirect()->back()->withErrors($validator)->withInput();
        }

        try {
            DB::beginTransaction();

            $periode = PeriodePencairan::find($id);
            if (!$periode) {
                Session::flash('message', 'Data periode tidak ditemukan');
                Session::flash('class', 'danger');
                return redirect($data['url_menu']);
            }

            // Update data
            $periode->update([
                'tahun' => request('tahun'),
                'bulan' => request('bulan'),
                'user_update' => $data['user_login']->username ?? 'system'
            ]);

            // Log success
            $syslog->log_insert('U', $data['dmenu'], 'Updated Periode: ' . $id, '1');

            DB::commit();

            Session::flash('message', 'Edit Data Berhasil!');
            Session::flash('class', 'success');
            return redirect($data['url_menu']);

        } catch (\Exception $e) {
            DB::rollback();

            // Log error
            $syslog->log_insert('E', $data['dmenu'], 'Update Error: ' . $e->getMessage(), '0');

            Session::flash('message', 'Edit Data Gagal!');
            Session::flash('class', 'danger');
            return redirect($data['url_menu']);
        }
    }

    /**
     * Remove the specified resource from storage - KOP301/destroy
     * This performs soft delete by toggling isactive status
     */
    public function destroy($data)
    {
        $syslog = new Function_Helper;
        $id = decrypt($data['idencrypt']);

        // Check authorization
        if ($data['authorize']->delete == '0') {
            $data['errorpages'] = 'Not Authorized!';
            $syslog->log_insert('E', $data['url_menu'], 'Not Authorized! - Delete Periode', '0');
            return view("pages.errorpages", $data);
        }

        try {
            DB::beginTransaction();

            $periode = PeriodePencairan::find($id);
            if (!$periode) {
                Session::flash('message', 'Data periode tidak ditemukan');
                Session::flash('class', 'danger');
                return redirect($data['url_menu']);
            }

            // Toggle active status (soft delete/activate)
            $newStatus = $periode->isactive == '1' ? '0' : '1';
            $periode->update([
                'isactive' => $newStatus,
                'user_update' => $data['user_login']->username ?? 'system'
            ]);

            // Log success
            $action = $newStatus == '1' ? 'Activated' : 'Deactivated';
            $syslog->log_insert('D', $data['dmenu'], $action . ' Periode: ' . $id, '1');

            DB::commit();

            $message = $newStatus == '1' ? 'Aktifkan Data Berhasil!' : 'Non-Aktifkan Data Berhasil!';
            Session::flash('message', $message);
            Session::flash('class', 'success');
            return redirect($data['url_menu']);

        } catch (\Exception $e) {
            DB::rollback();

            // Log error
            $syslog->log_insert('E', $data['dmenu'], 'Toggle Status Error: ' . $e->getMessage(), '0');

            Session::flash('message', 'Operasi Gagal!');
            Session::flash('class', 'danger');
            return redirect($data['url_menu']);
        }
    }

    /**
     * Export data functionality
     */
    private function exportData($dmenu, $exportType, $request)
    {
        // Implementation for export functionality
        // This would typically handle Excel/PDF export
        // For now, return a simple response
        return response()->json(['message' => 'Export functionality not implemented yet']);
    }
}