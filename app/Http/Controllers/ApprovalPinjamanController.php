<?php

namespace App\Http\Controllers;

use App\Helpers\Format_Helper;
use App\Helpers\Function_Helper;
use App\Models\PengajuanPinjaman;
use App\Models\ApprovalHistory;
use App\Models\Pinjaman;
use App\Models\Anggotum;
use App\Models\MasterPaketPinjaman;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;

class ApprovalPinjamanController extends Controller
{

    /**
     * Display a listing of the resource - KOP202/list
     */
    public function index($data)
    {
        // Export handling - EXACT MSJ Framework pattern
        if (request()->has('export') || request()->has('pdf')) {
            $exportType = request()->input('export');
            return $this->exportData($data['dmenu'], $exportType);
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

        // Get user role and username using model helper methods
        $user_role = PengajuanPinjaman::getUserRole($data);
        $current_username = PengajuanPinjaman::getCurrentUsername($data);

        // Check basic approval permission
        if ($data['authorize']->approval != '1') {
            Session::flash('message', 'Anda tidak memiliki akses approval untuk modul ini, Hubungi Admin.');
            Session::flash('class', 'warning');
            return redirect()->back();
        }

        // Apply role-based filtering using model method
        $query = PengajuanPinjaman::filterByRole($user_role, $current_username);

        // Search functionality
        $search = request('search');
        if (!empty($search)) {
            $query->where(function($q) use ($search) {
                $q->where('id', 'like', "%$search%")
                  ->orWhereHas('anggotum', function($qa) use ($search) {
                      $qa->where('nama_lengkap', 'like', "%$search%")
                        ->orWhere('nomor_anggota', 'like', "%$search%");
                  });
            });
        }

        // Check authorization rules - Skip for pengajuan_pinjaman as it doesn't have rules column
        // The pengajuan_pinjaman table doesn't use role-based record filtering
        // Authorization is handled at the controller/menu level instead

        $collectionData = $query->orderBy('created_at', 'desc')->get();

        // Pagination
        $currentPage = \Illuminate\Pagination\LengthAwarePaginator::resolveCurrentPage();
        $perPage = 10;
        $currentItems = $collectionData->slice(($currentPage - 1) * $perPage, $perPage)->all();
        $data['list'] = new \Illuminate\Pagination\LengthAwarePaginator(
            $currentItems,
            $collectionData->count(),
            $perPage,
            $currentPage,
            ['path' => request()->url(), 'query' => request()->query()]
        );

        // Get statistics
        $data['stats'] = [
            'pending_review' => $collectionData->whereIn('status_pengajuan', ['diajukan', 'review_admin', 'review_panitia'])->count(),
            'need_final_approval' => $collectionData->where('status_pengajuan', 'review_ketua')->count(),
            'total_amount' => $collectionData->sum('jumlah_pinjaman'),
        ];

        // Log access
        $syslog->log_insert('R', $data['dmenu'], 'Approval Pinjaman List Accessed', '1');

        return view($data['url'], $data);
    }

    /**
     * Show approval form - KOP202/show
     */
    public function show($data)
    {
        // Function helper
        $syslog = new Function_Helper;
        $data['format'] = new Format_Helper;

        // Get table structure data
        $data['table_header'] = DB::table('sys_table')
            ->where(['gmenu' => $data['gmenuid'], 'dmenu' => $data['dmenu']])
            ->orderBy('urut')
            ->get();

        $data['table_primary'] = DB::table('sys_table')
            ->where(['gmenu' => $data['gmenuid'], 'dmenu' => $data['dmenu'], 'primary' => '1'])
            ->orderBy('urut')
            ->get();

        // Check if idencrypt parameter exists
        if (!isset($data['idencrypt']) || empty($data['idencrypt'])) {
            $data['url_menu'] = 'error';
            $data['title_group'] = 'Error';
            $data['title_menu'] = 'Error';
            $data['errorpages'] = 'Parameter ID tidak ditemukan!';
            return view("pages.errorpages", $data);
        }

        // Decrypt ID
        try {
            $id = decrypt($data['idencrypt']);
        } catch (\Exception $e) {
            // Log the actual error for debugging
            $syslog->log_insert('E', $data['dmenu'], 'ID Decryption Error: ' . $e->getMessage() . ' - ID: ' . $data['idencrypt'], '0');

            $data['url_menu'] = 'error';
            $data['title_group'] = 'Error';
            $data['title_menu'] = 'Error';
            $data['errorpages'] = 'ID tidak valid! Silakan coba lagi atau hubungi administrator.';
            return view("pages.errorpages", $data);
        }

        // Get pengajuan detail using Eloquent
        $pengajuan = PengajuanPinjaman::with(['anggotum', 'master_paket_pinjaman', 'periode_pencairan'])
            ->find($id);

        if (!$pengajuan) {
            $data['url_menu'] = 'error';
            $data['title_group'] = 'Error';
            $data['title_menu'] = 'Error';
            $data['errorpages'] = 'Data pengajuan tidak ditemukan!';
            return view("pages.errorpages", $data);
        }

        // Check authorization - Skip rules check for pengajuan_pinjaman
        // Authorization is handled at controller/menu level, not record level

        $data['pengajuan'] = $pengajuan;
        $data['list'] = $pengajuan; // For view compatibility

        // Get approval history using Eloquent
        $data['approval_history'] = ApprovalHistory::with('user')
            ->where('pengajuan_pinjaman_id', $id)
            ->orderBy('created_at', 'desc')
            ->get();

        // Get member's loan history using query bertingkat
        $data['loan_history'] = DB::table('pinjaman as p')
            ->leftJoin('pengajuan_pinjaman as pp', 'p.pengajuan_pinjaman_id', '=', 'pp.id')
            ->leftJoin('master_paket_pinjaman as mpp', 'pp.paket_pinjaman_id', '=', 'mpp.id')
            ->select(
                'p.*',
                'mpp.periode'
            )
            ->where('p.anggota_id', $pengajuan->anggota_id)
            ->orderBy('p.created_at', 'desc')
            ->limit(5)
            ->get();

        // Log access
        $syslog->log_insert('R', $data['dmenu'], 'Approval Pinjaman Detail Accessed: ID ' . $pengajuan->id, '1');

        return view($data['url'], $data);
    }


    /**
     * Process approval - KOP202/update
     */
    public function store($data)
    {
        // Function helper
        $syslog = new Function_Helper;
        $data['format'] = new Format_Helper;

        // Validate input
        $validator = Validator::make(request()->all(), [
            'action' => 'required|in:approve,reject',
            'catatan' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        // Get ID from form data (pengajuan_id) or from URL parameter (idencrypt)
        $id = null;

        if (request()->has('pengajuan_id') && !empty(request('pengajuan_id'))) {
            // ID from form submission
            $id = request('pengajuan_id');
        } elseif (isset($data['idencrypt']) && !empty($data['idencrypt'])) {
            // ID from URL parameter (encrypted)
            try {
                $id = decrypt($data['idencrypt']);
            } catch (\Exception $e) {
                // Log the actual error for debugging
                $syslog->log_insert('E', $data['dmenu'], 'ID Decryption Error: ' . $e->getMessage() . ' - ID: ' . $data['idencrypt'], '0');

                $data['url_menu'] = 'error';
                $data['title_group'] = 'Error';
                $data['title_menu'] = 'Error';
                $data['errorpages'] = 'ID tidak valid! Silakan coba lagi atau hubungi administrator.';
                return view("pages.errorpages", $data);
            }
        }

        if (!$id) {
            $data['url_menu'] = 'error';
            $data['title_group'] = 'Error';
            $data['title_menu'] = 'Error';
            $data['errorpages'] = 'Parameter ID tidak ditemukan!';
            return view("pages.errorpages", $data);
        }

        // Get pengajuan using Eloquent
        $pengajuan = PengajuanPinjaman::find($id);

        if (!$pengajuan) {
            $data['url_menu'] = 'error';
            $data['title_group'] = 'Error';
            $data['title_menu'] = 'Error';
            $data['errorpages'] = 'Data pengajuan tidak ditemukan!';
            return view("pages.errorpages", $data);
        }

        // Check authorization
        if ($data['authorize']->edit == '0') {
            $data['url_menu'] = $data['url_menu'];
            $data['title_group'] = 'Error';
            $data['title_menu'] = 'Error';
            $data['errorpages'] = 'Not Authorized!';
            //insert log
            $syslog->log_insert('E', $data['url_menu'], 'Not Authorized!' . ' - Edit -' . $data['url_menu'], '0');
            //return error page
            return view("pages.errorpages", $data);
        }

        // Get user role and username using model helper methods
        $user_role = PengajuanPinjaman::getUserRole($data);
        $current_username = PengajuanPinjaman::getCurrentUsername($data);

        // Check basic approval permission
        if ($data['authorize']->approval != '1') {
            Session::flash('message', 'Anda tidak memiliki akses approval untuk modul ini, Hubungi Admin.');
            Session::flash('class', 'warning');
            return redirect()->back();
        }

        // Check if this user has already approved this application at their role level
        if (ApprovalHistory::hasExistingApproval($id, $current_username, $user_role)) {
            $current_level = ApprovalHistory::getApprovalLevel($user_role);
            Session::flash('message', 'Anda sudah melakukan approval untuk pengajuan pinjaman ini sebelumnya pada level ' . $current_level . '. Setiap admin hanya dapat melakukan approval sekali untuk setiap pengajuan.');
            Session::flash('class', 'warning');
            return redirect()->back();
        }

        // Validate workflow permissions using model method
        $currentStatus = $pengajuan->status_pengajuan;

        // Debug logging
        $syslog->log_insert('I', $data['dmenu'], 'Approval attempt: User Role=' . $user_role . ', Current Status=' . $currentStatus . ', Pengajuan ID=' . $id, '1');

        if (!ApprovalHistory::validateWorkflowPermissions($currentStatus, $user_role)) {
            $syslog->log_insert('W', $data['dmenu'], 'Approval denied: User Role=' . $user_role . ' cannot approve status=' . $currentStatus, '0');
            Session::flash('message', 'Anda tidak memiliki wewenang untuk melakukan approval pada status pengajuan ini. Status saat ini: ' . $currentStatus . '. Role Anda: ' . $user_role);
            Session::flash('class', 'warning');
            return redirect()->back();
        }

        DB::beginTransaction();

        try {
            $action = request('action');
            $catatan = request('catatan') ?? '';
            $approved_by = PengajuanPinjaman::getCurrentUsername($data);

            // Determine next status based on role and action using model method
            $new_status = ApprovalHistory::getNextStatus($user_role, $action);

            // Update pengajuan status
            $pengajuan->update([
                'status_pengajuan' => $new_status,
                'updated_at' => now(),
            ]);

            // Create approval history using model methods
            $status_approval = $action === 'approve' ? 'approved' : 'rejected';
            $level_approval = ApprovalHistory::getApprovalLevel($user_role);

            ApprovalHistory::create([
                'pengajuan_pinjaman_id' => $id,
                'level_approval' => $level_approval,
                'status_approval' => $status_approval,
                'catatan' => $catatan,
                'tanggal_approval' => now(),
                'urutan' => ApprovalHistory::getApprovalOrder($user_role),
                'isactive' => '1',
                'user_create' => $approved_by,
            ]);

            // If final approval, create pinjaman record
            if ($new_status === 'disetujui') {
                // Calculate tenor in months from string like "6 bulan"
                $tenor_bulan = (int) filter_var($pengajuan->tenor_pinjaman, FILTER_SANITIZE_NUMBER_INT);

                // Calculate angsuran details
                $nominal_pinjaman = $pengajuan->jumlah_pinjaman;
                $bunga_per_bulan = $pengajuan->bunga_per_bulan;
                $angsuran_pokok = $nominal_pinjaman / $tenor_bulan;
                $angsuran_bunga = $nominal_pinjaman * ($bunga_per_bulan / 100);
                $total_angsuran = $angsuran_pokok + $angsuran_bunga;

                // Calculate dates
                $tanggal_pencairan = now();
                $tanggal_jatuh_tempo = now()->addMonths($tenor_bulan);
                $tanggal_angsuran_pertama = now()->addMonth();

                Pinjaman::create([
                    'nomor_pinjaman' => $data['format']->IDFormat('KOP301'),
                    'pengajuan_pinjaman_id' => $id,
                    'anggota_id' => $pengajuan->anggota_id,
                    'nominal_pinjaman' => $nominal_pinjaman,
                    'bunga_per_bulan' => $bunga_per_bulan,
                    'tenor_bulan' => $tenor_bulan,
                    'angsuran_pokok' => $angsuran_pokok,
                    'angsuran_bunga' => $angsuran_bunga,
                    'total_angsuran' => $total_angsuran,
                    'tanggal_pencairan' => $tanggal_pencairan,
                    'tanggal_jatuh_tempo' => $tanggal_jatuh_tempo,
                    'tanggal_angsuran_pertama' => $tanggal_angsuran_pertama,
                    'status' => 'aktif',
                    'sisa_pokok' => $nominal_pinjaman,
                    'total_dibayar' => 0,
                    'angsuran_ke' => 0,
                    'isactive' => '1',
                    'user_create' => $approved_by,
                ]);
            }

            DB::commit();

            // Log success
            $syslog->log_insert('U', $data['dmenu'], 'Approval Processed: ID ' . $pengajuan->id . ' - ' . $new_status, '1');

            Session::flash('message', 'Approval berhasil diproses!');
            Session::flash('class', 'success');

        } catch (\Exception $e) {
            DB::rollback();

            // Log error
            $syslog->log_insert('E', $data['dmenu'], 'Approval Process Error: ' . $e->getMessage(), '0');

            Session::flash('message', 'Gagal memproses approval: ' . $e->getMessage());
            Session::flash('class', 'danger');
        }

        return redirect($data['url_menu']);
    }





    /**
     * Helper method to create pinjaman record when approved
     */
    private function createPinjamanRecord($pengajuan, $format)
    {
        // Calculate tenor in months from string like "6 bulan"
        $tenor_bulan = (int) filter_var($pengajuan->tenor_pinjaman, FILTER_SANITIZE_NUMBER_INT);

        // Calculate angsuran details
        $nominal_pinjaman = $pengajuan->jumlah_pinjaman;
        $bunga_per_bulan = $pengajuan->bunga_per_bulan;
        $angsuran_pokok = $nominal_pinjaman / $tenor_bulan;
        $angsuran_bunga = $nominal_pinjaman * ($bunga_per_bulan / 100);
        $total_angsuran = $angsuran_pokok + $angsuran_bunga;

        // Calculate dates
        $tanggal_pencairan = now();
        $tanggal_jatuh_tempo = now()->addMonths($tenor_bulan);
        $tanggal_angsuran_pertama = now()->addMonth();

        return Pinjaman::create([
            'nomor_pinjaman' => $format->IDFormat('KOP301'),
            'pengajuan_pinjaman_id' => $pengajuan->id,
            'anggota_id' => $pengajuan->anggota_id,
            'nominal_pinjaman' => $nominal_pinjaman,
            'bunga_per_bulan' => $bunga_per_bulan,
            'tenor_bulan' => $tenor_bulan,
            'angsuran_pokok' => $angsuran_pokok,
            'angsuran_bunga' => $angsuran_bunga,
            'total_angsuran' => $total_angsuran,
            'tanggal_pencairan' => $tanggal_pencairan,
            'tanggal_jatuh_tempo' => $tanggal_jatuh_tempo,
            'tanggal_angsuran_pertama' => $tanggal_angsuran_pertama,
            'status' => 'aktif',
            'sisa_pokok' => $nominal_pinjaman,
            'total_dibayar' => 0,
            'angsuran_ke' => 0,
            'isactive' => '1',
            'user_create' => $pengajuan->user_create ?? 'system',
        ]);
    }

    /**
     * Export data functionality - Custom implementation for ApprovalPinjaman module
     */
    private function exportData($dmenu, $exportType)
    {
        try {
            // Get user role and username using model helper methods
            $user_role = PengajuanPinjaman::getUserRole(['user_login' => session('user_login')]);
            $current_username = PengajuanPinjaman::getCurrentUsername(['user_login' => session('user_login')]);

            // Get data using same logic as index method
            $query = PengajuanPinjaman::filterByRole($user_role, $current_username);

            // Apply search filter if exists
            $search = request('search');
            if (!empty($search)) {
                $query->where(function($q) use ($search) {
                    $q->where('id', 'like', "%$search%")
                      ->orWhereHas('anggotum', function($qa) use ($search) {
                          $qa->where('nama_lengkap', 'like', "%$search%")
                            ->orWhere('nomor_anggota', 'like', "%$search%");
                      });
                });
            }

            $exportData = $query->orderBy('created_at', 'desc')->get();

            // Prepare export data following MSJ Framework pattern
            $data = [];
            $headers = ['No', 'No. Pengajuan', 'Anggota', 'Paket', 'Jumlah Pinjaman', 'Status', 'Tanggal'];
            $data[] = $headers;

            foreach ($exportData as $index => $pengajuan) {
                $data[] = [
                    $index + 1,
                    $pengajuan->id ?? '-',
                    $pengajuan->anggotum->nama_lengkap ?? '-',
                    $pengajuan->master_paket_pinjaman->periode ?? '-',
                    'Rp ' . number_format($pengajuan->jumlah_pinjaman, 0, ',', '.'),
                    ucfirst(str_replace('_', ' ', $pengajuan->status_pengajuan)),
                    $pengajuan->tanggal_pengajuan ? date('d/m/Y', strtotime($pengajuan->tanggal_pengajuan)) : '-'
                ];
            }

            // Generate filename
            $fileName = 'approval_pinjaman_' . date('Y-m-d_H-i-s');

            // Use PhpSpreadsheet for export (following MSJ Framework pattern)
            $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            $sheet->setTitle('Approval Pinjaman');

            // Set data
            $sheet->fromArray($data, null, 'A1');

            // Auto-size columns
            foreach (range('A', $sheet->getHighestColumn()) as $col) {
                $sheet->getColumnDimension($col)->setAutoSize(true);
            }

            // Apply header styling
            $headerRange = 'A1:' . $sheet->getHighestColumn() . '1';
            $sheet->getStyle($headerRange)->getFont()->setBold(true);
            $sheet->getStyle($headerRange)->getFill()
                ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                ->getStartColor()->setARGB('FFE0E0E0');

            if ($exportType === 'excel') {
                $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
                $filePath = public_path("{$fileName}.xlsx");
                $writer->save($filePath);
                return response()->download($filePath)->deleteFileAfterSend(true);

            } elseif ($exportType === 'pdf') {
                // Configure for PDF
                $sheet->getPageSetup()
                    ->setOrientation(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::ORIENTATION_LANDSCAPE)
                    ->setPaperSize(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::PAPERSIZE_A4);
                $sheet->getPageMargins()->setTop(0.5)->setRight(0.5)->setLeft(0.5)->setBottom(0.5);

                $writer = new \PhpOffice\PhpSpreadsheet\Writer\Pdf\Mpdf($spreadsheet);
                $filePath = public_path("{$fileName}.pdf");
                $writer->save($filePath);
                return response()->download($filePath)->deleteFileAfterSend(true);
            }

            return response()->json(['error' => 'Invalid export type'], 400);

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Export failed: ' . $e->getMessage());
        }
    }


}
