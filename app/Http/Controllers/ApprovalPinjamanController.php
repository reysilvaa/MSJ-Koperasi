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

        // Decrypt ID
        try {
            $id = decrypt($data['idencrypt']);
        } catch (\Exception $e) {
            Session::flash('message', 'ID tidak valid!');
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

        // Get member's loan history using Eloquent
        $data['loan_history'] = Pinjaman::with('master_paket_pinjaman')
            ->where('anggota_id', $pengajuan->anggota_id)
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        // Log access
        $syslog->log_insert('R', $data['dmenu'], 'Approval Pinjaman Detail Accessed: ID ' . $pengajuan->id, '1');

        return view('KOP002.approvalPinjaman.show', $data);
    }

    /**
     * Process approval - KOP202/update
     */
    public function update($data)
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

        // Decrypt ID
        try {
            $id = decrypt($data['idencrypt']);
        } catch (\Exception $e) {
            Session::flash('message', 'ID tidak valid!');
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

            // Create approval history
            ApprovalHistory::create([
                'pengajuan_pinjaman_id' => $id,
                'status_sebelum' => $pengajuan->getOriginal('status_pengajuan'),
                'status_sesudah' => $new_status,
                'catatan' => $catatan,
                'approved_by' => $approved_by,
                'created_at' => now(),
            ]);

            // If final approval, create pinjaman record
            if ($new_status === 'disetujui') {
                Pinjaman::create([
                    'anggota_id' => $pengajuan->anggota_id,
                    'pengajuan_pinjaman_id' => $id,
                    'paket_pinjaman_id' => $pengajuan->paket_pinjaman_id,
                    'nomor_pinjaman' => $data['format']->IDFormat('KOP301'),
                    'jumlah_pinjaman' => $pengajuan->jumlah_pinjaman,
                    'tenor_pinjaman' => $pengajuan->tenor_pinjaman,
                    'bunga_per_bulan' => $pengajuan->bunga_per_bulan,
                    'cicilan_per_bulan' => $pengajuan->cicilan_per_bulan,
                    'total_pembayaran' => $pengajuan->total_pembayaran,
                    'tanggal_pinjaman' => now(),
                    'status_pinjaman' => 'aktif',
                    'isactive' => '1',
                    'created_at' => now(),
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
        return Pinjaman::create([
            'nomor_pinjaman' => $format->IDFormat('KOP301'),
            'pengajuan_pinjaman_id' => $pengajuan->id,
            'anggota_id' => $pengajuan->anggota_id,
            'paket_pinjaman_id' => $pengajuan->paket_pinjaman_id,
            'jumlah_pinjaman' => $pengajuan->jumlah_pinjaman,
            'bunga_per_bulan' => $pengajuan->bunga_per_bulan,
            'cicilan_per_bulan' => $pengajuan->cicilan_per_bulan,
            'total_pembayaran' => $pengajuan->total_pembayaran,
            'tenor_bulan' => (int) filter_var($pengajuan->tenor_pinjaman, FILTER_SANITIZE_NUMBER_INT),
            'tanggal_pencairan' => now(),
            'status_pinjaman' => 'aktif',
            'isactive' => '1',
            'user_create' => $pengajuan->user_create ?? 'system',
            'created_at' => now(),
            'updated_at' => now(),
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
