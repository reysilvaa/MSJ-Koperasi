<?php

namespace App\Http\Controllers;

use App\Helpers\Format_Helper;
use App\Helpers\Function_Helper;
use App\Models\PengajuanPinjaman;
use App\Models\ApprovalHistory;
use App\Models\Pinjaman;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;

class ApprovalPinjamanController extends Controller
{
    /**
     * Display a listing of the resource - KOP202
     */
    public function index($data)
    {
        // function helper
        $syslog = new Function_Helper;
        $data['format'] = new Format_Helper;

        // Get user role for filtering
        $user_role = $data['user_login']->idroles;

        // Filter pengajuan based on role and status
        $query = DB::table('pengajuan_pinjaman as pp')
            ->leftJoin('anggota as a', 'pp.anggota_id', '=', 'a.id')
            ->leftJoin('master_paket_pinjaman as mpp', 'pp.paket_pinjaman_id', '=', 'mpp.id')
            ->select(
                'pp.*',
                'a.nomor_anggota',
                'a.nama_lengkap'
            )
            ->where('pp.isactive', '1');

        // Role-based filtering sesuai activity diagram
        switch ($user_role) {
            case 'akredt': // Admin Kredit
                $query->whereIn('pp.status_pengajuan', ['diajukan', 'review_admin']);
                break;
            case 'kadmin': // Ketua Admin - review panitia
                $query->whereIn('pp.status_pengajuan', ['review_admin', 'review_panitia']);
                break;
            case 'ketuum': // Ketua Umum - final approval
                $query->whereIn('pp.status_pengajuan', ['review_panitia', 'review_ketua']);
                break;
            default:
                $query->whereIn('pp.status_pengajuan', ['diajukan', 'review_admin', 'review_panitia', 'review_ketua']);
        }

        $data['pengajuan_list'] = $query->orderBy('pp.created_at', 'desc')->get();

        // Get statistics
        $data['stats'] = [
            'pending_review' => $data['pengajuan_list']->whereIn('status_pengajuan', ['diajukan', 'review_admin', 'review_panitia'])->count(),
            'need_final_approval' => $data['pengajuan_list']->where('status_pengajuan', 'review_ketua')->count(),
            'total_amount' => $data['pengajuan_list']->sum('jumlah_pinjaman'),
        ];

        return view('KOP002.approvalPinjaman.list', $data);
    }

    /**
     * Show approval form - KOP202/show
     */
    public function show($data)
    {
        // function helper
        $syslog = new Function_Helper;
        $data['format'] = new Format_Helper;

        // Decrypt ID
        try {
            $id = decrypt($data['idencrypt']);
        } catch (\Exception $e) {
            Session::flash('message', 'ID tidak valid!');
            Session::flash('class', 'danger');
            return redirect($data['url_menu']);
        }

        // Get pengajuan detail
        $data['pengajuan'] = DB::table('pengajuan_pinjaman as pp')
            ->leftJoin('anggota as a', 'pp.anggota_id', '=', 'a.id')
            ->leftJoin('master_paket_pinjaman as mpp', 'pp.paket_pinjaman_id', '=', 'mpp.id')
            ->leftJoin('periode_pencairan as pc', 'pp.periode_pencairan_id', '=', 'pc.id')
            ->select(
                'pp.*',
                'a.nomor_anggota',
                'a.nama_lengkap',
                'a.email',
                'a.no_telepon',
                'a.alamat',
                'pc.nama_periode'
            )
            ->where('pp.id', $id)
            ->first();

        if (!$data['pengajuan']) {
            Session::flash('message', 'Data pengajuan tidak ditemukan!');
            Session::flash('class', 'danger');
            return redirect($data['url_menu']);
        }

        // Get approval history
        $data['approval_history'] = DB::table('approval_history as ah')
            ->leftJoin('users as u', 'ah.approved_by', '=', 'u.username')
            ->select('ah.*', 'u.firstname', 'u.lastname')
            ->where('ah.pengajuan_pinjaman_id', $id)
            ->orderBy('ah.created_at', 'desc')
            ->get();

        // Get member's loan history
        $data['loan_history'] = DB::table('pinjaman as p')
            ->leftJoin('master_paket_pinjaman as mpp', 'p.paket_pinjaman_id', '=', 'mpp.id')
            ->select('p.*', 'mpp.nama_paket')
            ->where('p.anggota_id', $data['pengajuan']->anggota_id)
            ->orderBy('p.created_at', 'desc')
            ->limit(5)
            ->get();

        return view('KOP002.approvalPinjaman.show', $data);
    }

    /**
     * Process approval - KOP202/store
     */
    public function store($data)
    {
        // function helper
        $syslog = new Function_Helper;

        // Validation
        $validator = Validator::make(request()->all(), [
            'pengajuan_id' => 'required|exists:pengajuan_pinjaman,id',
            'action' => 'required|in:approve,reject',
            'catatan' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $pengajuan_id = request('pengajuan_id');
        $action = request('action');
        $catatan = request('catatan');
        $user_role = $data['user_login']->idroles;

        // Get pengajuan
        $pengajuan = PengajuanPinjaman::find($pengajuan_id);

        if (!$pengajuan) {
            Session::flash('message', 'Data pengajuan tidak ditemukan!');
            Session::flash('class', 'danger');
            return redirect($data['url_menu']);
        }

        // Determine next status based on current status and user role
        $next_status = $this->getNextStatus($pengajuan->status_pengajuan, $user_role, $action);

        if (!$next_status) {
            Session::flash('message', 'Anda tidak memiliki wewenang untuk approval ini!');
            Session::flash('class', 'warning');
            return redirect($data['url_menu']);
        }

        // Update pengajuan status
        $update_result = DB::table('pengajuan_pinjaman')
            ->where('id', $pengajuan_id)
            ->update([
                'status_pengajuan' => $next_status,
                'catatan_approval' => $catatan,
                'approved_by' => session('username'),
                'tanggal_approval' => now(),
                'user_update' => session('username'),
                'updated_at' => now(),
            ]);

        // Insert approval history
        DB::table('approval_history')->insert([
            'pengajuan_pinjaman_id' => $pengajuan_id,
            'status_sebelum' => $pengajuan->status_pengajuan,
            'status_sesudah' => $next_status,
            'catatan' => $catatan,
            'approved_by' => session('username'),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // If final approval, create pinjaman record
        if ($next_status === 'disetujui') {
            $this->createPinjamanRecord($pengajuan);
        }

        // If rejected, release stock
        if ($action === 'reject') {
            DB::table('master_paket_pinjaman')
                ->where('id', $pengajuan->paket_pinjaman_id)
                ->decrement('stock_terpakai', $pengajuan->jumlah_paket_dipilih);
        }

        if ($update_result) {
            $syslog->log_insert('U', $data['dmenu'], 'Approval: ' . $pengajuan->nomor_pengajuan . ' - ' . $next_status, '1');
            Session::flash('message', 'Approval berhasil diproses!');
            Session::flash('class', 'success');
        } else {
            $syslog->log_insert('E', $data['dmenu'], 'Approval Error', '0');
            Session::flash('message', 'Gagal memproses approval!');
            Session::flash('class', 'danger');
        }

        return redirect($data['url_menu']);
    }

    /**
     * Get next status based on current status and user role
     */
    private function getNextStatus($current_status, $user_role, $action)
    {
        if ($action === 'reject') {
            return 'ditolak';
        }

        $workflow = [
            'diajukan' => [
                'akredt' => 'review_admin', // Admin Kredit
            ],
            'review_admin' => [
                'kadmin' => 'review_panitia', // Ketua Admin
            ],
            'review_panitia' => [
                'ketuum' => 'disetujui', // Ketua Umum
            ],
        ];

        return $workflow[$current_status][$user_role] ?? null;
    }

    /**
     * Create pinjaman record when approved
     */
    private function createPinjamanRecord($pengajuan)
    {
        $format = new Format_Helper;

        DB::table('pinjaman')->insert([
            'nomor_pinjaman' => $format->IDFormat('KOP203'),
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
            'user_create' => session('username'),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
