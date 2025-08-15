<?php

namespace App\Http\Controllers;

use App\Helpers\Format_Helper;
use App\Helpers\Function_Helper;
use App\Models\PengajuanPinjaman;
use App\Models\ApprovalHistory;
use App\Models\Pinjaman;
use App\Models\Anggotum;
use App\Models\MasterPaketPinjaman;
use App\Models\PeriodePencairan;
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

        // Get user role for filtering
        $user_role = $data['user_login']->idroles;

        // Filter pengajuan based on role and status using Eloquent
        $query = PengajuanPinjaman::with(['anggotum', 'master_paket_pinjaman', 'periode_pencairan'])
            ->where('isactive', '1');

        // Role-based filtering sesuai activity diagram (CORRECTED FLOW)
        switch ($user_role) {
            case 'kadmin': // Ketua Admin - review pertama
                $query->whereIn('status_pengajuan', ['diajukan', 'review_admin']);
                break;
            case 'akredt': // Admin Kredit - review kedua
                $query->whereIn('status_pengajuan', ['review_admin', 'review_panitia']);
                break;
            case 'ketuum': // Ketua Umum - final approval
                $query->whereIn('status_pengajuan', ['review_panitia', 'review_ketua']);
                break;
            default: // Default: show all pending approvals
                $query->whereIn('status_pengajuan', ['diajukan', 'review_admin', 'review_panitia', 'review_ketua']);
        }

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

        return view('KOP002.approvalPinjaman.list', $data);
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
            Session::flash('message', 'Parameter ID tidak ditemukan!');
            Session::flash('class', 'danger');
            return redirect($data['url_menu']);
        }

        // Decrypt ID
        try {
            $id = decrypt($data['idencrypt']);
        } catch (\Exception $e) {
            // Log the actual error for debugging
            $syslog->log_insert('E', $data['dmenu'], 'ID Decryption Error: ' . $e->getMessage() . ' - ID: ' . $data['idencrypt'], '0');

            Session::flash('message', 'ID tidak valid! Silakan coba lagi atau hubungi administrator.');
            Session::flash('class', 'danger');
            return redirect($data['url_menu']);
        }

        // Get pengajuan detail using Eloquent
        $pengajuan = PengajuanPinjaman::with(['anggotum', 'master_paket_pinjaman', 'periode_pencairan'])
            ->find($id);

        if (!$pengajuan) {
            Session::flash('message', 'Data pengajuan tidak ditemukan!');
            Session::flash('class', 'danger');
            return redirect($data['url_menu']);
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

        return view('KOP002.approvalPinjaman.show', $data);
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
            'catatan' => 'required|string|max:500',
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

                Session::flash('message', 'ID tidak valid! Silakan coba lagi atau hubungi administrator.');
                Session::flash('class', 'danger');
                return redirect($data['url_menu']);
            }
        }

        if (!$id) {
            Session::flash('message', 'Parameter ID tidak ditemukan!');
            Session::flash('class', 'danger');
            return redirect($data['url_menu']);
        }

        // Get pengajuan using Eloquent
        $pengajuan = PengajuanPinjaman::find($id);

        if (!$pengajuan) {
            Session::flash('message', 'Data pengajuan tidak ditemukan!');
            Session::flash('class', 'danger');
            return redirect($data['url_menu']);
        }

        // Check authorization
        if ($data['authorize']->edit == '0') {
            Session::flash('message', 'Anda tidak memiliki akses untuk melakukan approval!');
            Session::flash('class', 'danger');
            return redirect($data['url_menu']);
        }

        DB::beginTransaction();

        try {
            $action = request('action');
            $catatan = request('catatan');
            $user_role = $data['user_login']->idroles;
            $approved_by = $data['user_login']->username;

            // Determine next status based on role and action (CORRECTED FLOW)
            $status_map = [
                'kadmin' => ['approve' => 'review_admin', 'reject' => 'ditolak'],     // Ketua Admin -> review_admin
                'akredt' => ['approve' => 'review_panitia', 'reject' => 'ditolak'],   // Admin Kredit -> review_panitia
                'ketuum' => ['approve' => 'disetujui', 'reject' => 'ditolak'],        // Ketua Umum -> disetujui (final)
            ];

            $new_status = $status_map[$user_role][$action] ?? 'ditolak';

            // Update pengajuan status
            $pengajuan->update([
                'status_pengajuan' => $new_status,
                'updated_at' => now(),
            ]);

            // Create approval history with correct column names
            $level_map = [
                'kadmin' => 'Ketua Admin',
                'akredt' => 'Admin Kredit',
                'ketuum' => 'Ketua Umum'
            ];

            $status_approval = $action === 'approve' ? 'approved' : 'rejected';
            $level_approval = $level_map[$user_role] ?? $user_role;

            ApprovalHistory::create([
                'pengajuan_pinjaman_id' => $id,
                'level_approval' => $level_approval,
                'approver_name' => $approved_by,
                'approver_jabatan' => $level_approval,
                'status_approval' => $status_approval,
                'catatan' => $catatan,
                'tanggal_approval' => now(),
                'urutan' => $this->getApprovalOrder($user_role),
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
     * Helper method to get approval order based on user role
     */
    private function getApprovalOrder($user_role)
    {
        $order_map = [
            'kadmin' => 1, // Ketua Admin - first approval
            'akredt' => 2, // Admin Kredit - second approval
            'ketuum' => 3, // Ketua Umum - final approval
        ];

        return $order_map[$user_role] ?? 0;
    }

    /**
     * Helper method to get next status based on current status and user role (CORRECTED FLOW)
     */
    private function getNextStatus($current_status, $user_role, $action)
    {
        if ($action === 'reject') {
            return 'ditolak';
        }

        $workflow = [
            'diajukan' => [
                'kadmin' => 'review_admin', // Ketua Admin (level tertinggi)
            ],
            'review_admin' => [
                'akredt' => 'review_panitia', // Admin Kredit (level menengah)
            ],
            'review_panitia' => [
                'ketuum' => 'disetujui', // Ketua Umum (final approval)
            ],
        ];

        return $workflow[$current_status][$user_role] ?? null;
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
     * Export data functionality following MSJ Framework standard
     */
    private function exportData($dmenu, $exportType, $request)
    {
        // Implementation for export functionality
        // This would follow MSJ Framework export standards
        return response()->json(['message' => 'Export functionality not implemented yet']);
    }
}
