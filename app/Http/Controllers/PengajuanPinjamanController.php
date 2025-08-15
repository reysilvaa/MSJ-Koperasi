<?php

namespace App\Http\Controllers;

use App\Helpers\Format_Helper;
use App\Helpers\Function_Helper;
use App\Models\PengajuanPinjaman;
use App\Models\Anggotum;
use App\Models\MasterPaketPinjaman;
use App\Models\PeriodePencairan;
use App\Models\ApprovalHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use Illuminate\Pagination\LengthAwarePaginator;

class PengajuanPinjamanController extends Controller
{

    /**
     * Display a listing of the resource - KOP201/list
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

        // Get pengajuan pinjaman data using Eloquent with relationships
        $query = PengajuanPinjaman::with(['anggota', 'paketPinjaman', 'periodePencairan'])
            ->where('isactive', '1');

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

        // Check authorization rules
        if ($data['authorize']->rules == '1') {
            $roles = $data['users_rules'];
            $query->where(function ($q) use ($roles) {
                foreach ($roles as $role) {
                    $q->orWhereRaw("FIND_IN_SET(?, REPLACE(rules, ' ', ''))", [$role]);
                }
            });
        }

        $collectionData = $query->orderBy('created_at', 'desc')->get();

        // Pagination
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
        $data['stats'] = [
            'total_pengajuan' => $collectionData->count(),
            'pending_approval' => $collectionData->where('status_pengajuan', 'diajukan')->count(),
            'approved' => $collectionData->where('status_pengajuan', 'disetujui')->count(),
            'rejected' => $collectionData->where('status_pengajuan', 'ditolak')->count(),
        ];

        // Log access
        $syslog->log_insert('R', $data['dmenu'], 'Pengajuan Pinjaman List Accessed', '1');

        return view($data['url'], $data);
    }

    /**
     * Show the form for creating a new resource - KOP201/add
     */
    public function add($data)
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

        // Check if user is anggota (regular member) and get their anggota data
        $data['current_anggota'] = null;
        $data['is_anggota_biasa'] = false;
        $data['hide_stock_info'] = false;

        if (isset($data['user_login']->idroles) && strpos($data['user_login']->idroles, 'anggot') !== false) {
            $data['is_anggota_biasa'] = true;
            $data['hide_stock_info'] = true; // Hide stock information for regular members
            $data['current_anggota'] = Anggotum::where('email', $data['user_login']->email)
                                              ->orWhere('user_create', $data['user_login']->username)
                                              ->where('status_keanggotaan', 'aktif')
                                              ->where('isactive', '1')
                                              ->select('id', 'nomor_anggota', 'nama_lengkap')
                                              ->first();
        }

        // Get form data using Eloquent
        $data['anggota_list'] = Anggotum::where('status_keanggotaan', 'aktif')
            ->where('isactive', '1')
            ->select('id', 'nomor_anggota', 'nama_lengkap')
            ->get();

        $data['paket_list'] = MasterPaketPinjaman::where('isactive', '1')
            ->select('id', 'periode', 'stock_limit', 'stock_terpakai')
            ->get();

        // Static tenor options since we removed master_tenor table
        $data['tenor_list'] = collect([
            (object) ['id' => '6 bulan', 'nama_tenor' => '6 bulan', 'tenor_bulan' => 6],
            (object) ['id' => '10 bulan', 'nama_tenor' => '10 bulan', 'tenor_bulan' => 10],
            (object) ['id' => '12 bulan', 'nama_tenor' => '12 bulan', 'tenor_bulan' => 12],
        ]);

        $data['periode_list'] = PeriodePencairan::where('isactive', '1')
            ->select('id', 'nama_periode')
            ->get();

        return view($data['url'], $data);
    }

    /**
     * Store a newly created resource in storage - KOP201/store
     */
    public function store($data)
    {
        // Function helper
        $syslog = new Function_Helper;
        $data['format'] = new Format_Helper;

        // Handle role-based anggota_id assignment
        $anggotaId = request('anggota_id');

        // Check if user is anggota (regular member) and auto-assign anggota_id
        if (isset($data['user_login']->idroles) && strpos($data['user_login']->idroles, 'anggot') !== false) {
            // Find anggota by email or username matching
            $anggota = Anggotum::where('email', $data['user_login']->email)
                              ->orWhere('user_create', $data['user_login']->username)
                              ->where('status_keanggotaan', 'aktif')
                              ->where('isactive', '1')
                              ->first();

            if ($anggota) {
                $anggotaId = $anggota->id;
                // Override request with the found anggota_id
                request()->merge(['anggota_id' => $anggotaId]);
            } else {
                $data['url_menu'] = 'error';
                $data['title_group'] = 'Error';
                $data['title_menu'] = 'Error';
                $data['errorpages'] = 'Data anggota Anda tidak ditemukan. Silakan hubungi admin.';
                return view("pages.errorpages", $data);
            }
        }

        if (!$anggotaId) {
            $data['url_menu'] = 'error';
            $data['title_group'] = 'Error';
            $data['title_menu'] = 'Error';
            $data['errorpages'] = 'Anggota harus dipilih.';
            return view("pages.errorpages", $data);
        }

        // Check if anggota already has pending application (any status that's still in process)
        $pendingStatuses = ['diajukan', 'review_admin', 'review_panitia', 'review_ketua'];
        $existingPending = PengajuanPinjaman::where('anggota_id', $anggotaId)
            ->whereIn('status_pengajuan', $pendingStatuses)
            ->where('isactive', '1')
            ->first();

        if ($existingPending) {
            $statusText = [
                'diajukan' => 'Diajukan',
                'review_admin' => 'Review Admin',
                'review_panitia' => 'Review Panitia',
                'review_ketua' => 'Review Ketua'
            ];
            $currentStatus = $statusText[$existingPending->status_pengajuan] ?? $existingPending->status_pengajuan;
            $data['url_menu'] = 'error';
            $data['title_group'] = 'Error';
            $data['title_menu'] = 'Error';
            $data['errorpages'] = 'Anggota masih memiliki pengajuan pinjaman dengan status "' . $currentStatus . '". Tidak dapat mengajukan pinjaman baru selama masih dalam proses persetujuan.';
            return view("pages.errorpages", $data);
        }

        // Automatic eligibility check and set jenis_pengajuan
        if ($anggotaId) {
            // Check if anggota has active loan and remaining payments ≤ 2
            $eligibility = DB::select("
                SELECT
                    pp.id as pengajuan_id,
                    p.id as pinjaman_id,
                    p.tenor_bulan,
                    COUNT(cp.id) as total_cicilan_lunas,
                    (p.tenor_bulan - COUNT(cp.id)) as sisa_cicilan
                FROM pengajuan_pinjaman pp
                INNER JOIN pinjaman p ON pp.id = p.pengajuan_pinjaman_id
                LEFT JOIN cicilan_pinjaman cp ON p.id = cp.pinjaman_id AND cp.status = 'lunas'
                WHERE pp.anggota_id = ?
                AND pp.status_pengajuan = 'disetujui'
                AND p.status = 'aktif'
                AND pp.isactive = '1'
                AND p.isactive = '1'
                GROUP BY pp.id, p.id, p.tenor_bulan
                HAVING sisa_cicilan <= 2
                LIMIT 1
            ", [$anggotaId]);

            // Automatically set jenis_pengajuan based on eligibility
            $jenis_pengajuan = !empty($eligibility) ? 'top_up' : 'baru';

            // Override the request with the determined jenis_pengajuan
            request()->merge(['jenis_pengajuan' => $jenis_pengajuan]);
        }

        // Validation rules - Range validation removed as per koperasi system preferences
        $validator = Validator::make(request()->all(), [
            'anggota_id' => 'required|exists:anggota,id',
            'paket_pinjaman_id' => 'required|exists:master_paket_pinjaman,id',
            'jumlah_paket_dipilih' => 'required|integer|min:1', // max removed for flexibility
            'tenor_pinjaman' => 'required|string|in:6 bulan,10 bulan,12 bulan',
            'tujuan_pinjaman' => 'required|string|max:500',
            'jenis_pengajuan' => 'required|in:baru,top_up',
            'periode_pencairan_id' => 'required|exists:periode_pencairan,id',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        // Check authorization
        if ($data['authorize']->add == '0') {
            $data['url_menu'] = $data['url_menu'];
            $data['title_group'] = 'Error';
            $data['title_menu'] = 'Error';
            $data['errorpages'] = 'Not Authorized!';
            //insert log
            $syslog->log_insert('E', $data['url_menu'], 'Not Authorized!' . ' - Add -' . $data['url_menu'], '0');
            //return error page
            return view("pages.errorpages", $data);
        }

        DB::beginTransaction();

        try {
            // Get paket and tenor data for calculation using Eloquent
            $paket = MasterPaketPinjaman::find(request('paket_pinjaman_id'));
            $anggota = Anggotum::find(request('anggota_id'));
            $tenor_pinjaman = request('tenor_pinjaman'); // e.g., "6 bulan", "12 bulan"
            $tenor_bulan = (int) filter_var($tenor_pinjaman, FILTER_SANITIZE_NUMBER_INT);
            $jumlah_paket = request('jumlah_paket_dipilih');

            // Business logic calculation sesuai docs/PENGAJUAN_PINJAMAN_FIX.md
            $nilai_per_paket = 500000; // Rp 500.000 per paket
            $jumlah_pinjaman = $jumlah_paket * $nilai_per_paket;
            $bunga_per_bulan = 1.0; // Fixed 1% per bulan

            // Perhitungan Bunga Flat (CORRECTED)
            $cicilan_pokok = $jumlah_pinjaman / $tenor_bulan;
            $bunga_flat = $jumlah_pinjaman * ($bunga_per_bulan / 100);
            $cicilan_per_bulan = $cicilan_pokok + $bunga_flat;
            $total_pembayaran = $cicilan_per_bulan * $tenor_bulan;

            // Stock validation removed as per koperasi system preferences
            // Stock information is for display only, no blocking validation
            // This follows the preference: "auto-approve loan applications without stock validation"

            // Create pengajuan using Eloquent
            $pengajuan = PengajuanPinjaman::create([
                'anggota_id' => request('anggota_id'),
                'paket_pinjaman_id' => request('paket_pinjaman_id'),
                'jumlah_paket_dipilih' => $jumlah_paket,
                'tenor_pinjaman' => $tenor_pinjaman, // Store as string like "6 bulan"
                'jumlah_pinjaman' => $jumlah_pinjaman,
                'bunga_per_bulan' => $bunga_per_bulan,
                'cicilan_per_bulan' => $cicilan_per_bulan,
                'total_pembayaran' => $total_pembayaran,
                'tujuan_pinjaman' => request('tujuan_pinjaman'),
                'jenis_pengajuan' => request('jenis_pengajuan'),
                'periode_pencairan_id' => request('periode_pencairan_id'),
                'status_pengajuan' => 'diajukan', // Auto submit sesuai requirement
                'status_pencairan' => 'belum_cair', // Fixed enum value
                'tanggal_pengajuan' => now(),
                'isactive' => '1',
                'user_create' => $data['user_login']->username ?? 'system',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Update stock terpakai using Eloquent (only for non-regular members)
            if (!$isAnggotaBiasa) {
                $paket->increment('stock_terpakai', $jumlah_paket);
            }

            DB::commit();

            // Log success
            $syslog->log_insert('C', $data['dmenu'], 'Pengajuan Pinjaman Created: ID ' . $pengajuan->id, '1');

            $jenis_text = $jenis_pengajuan === 'top_up' ? 'Top Up (otomatis terdeteksi)' : 'Pinjaman Baru';
            Session::flash('message', 'Pengajuan pinjaman berhasil dibuat sebagai: ' . $jenis_text);
            Session::flash('class', 'success');

        } catch (\Exception $e) {
            DB::rollback();

            // Log error
            $syslog->log_insert('E', $data['dmenu'], 'Pengajuan Pinjaman Create Error: ' . $e->getMessage(), '0');

            Session::flash('message', 'Gagal membuat pengajuan pinjaman: ' . $e->getMessage());
            Session::flash('class', 'danger');
        }

        return redirect($data['url_menu']);
    }

    /**
     * Display the specified resource - KOP201/show
     */
    public function show($data)
    {
        // Function helper
        $syslog = new Function_Helper;
        $data['format'] = new Format_Helper;

        // Get table structure data for view
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
            $data['url_menu'] = 'error';
            $data['title_group'] = 'Error';
            $data['title_menu'] = 'Error';
            $data['errorpages'] = 'ID tidak valid!';
            return view("pages.errorpages", $data);
        }

        // Get pengajuan detail using Eloquent with relationships
        $pengajuan = PengajuanPinjaman::with(['anggota', 'paketPinjaman', 'periodePencairan'])
            ->find($id);

        if (!$pengajuan) {
            $data['url_menu'] = 'error';
            $data['title_group'] = 'Error';
            $data['title_menu'] = 'Error';
            $data['errorpages'] = 'Data pengajuan tidak ditemukan!';
            return view("pages.errorpages", $data);
        }

        // Check authorization
        if ($data['authorize']->rules == '1') {
            $roles = $data['users_rules'];
            $hasAccess = false;
            foreach ($roles as $role) {
                if (in_array($role, explode(',', str_replace(' ', '', $pengajuan->rules ?? '')))) {
                    $hasAccess = true;
                    break;
                }
            }
            if (!$hasAccess) {
                $data['url_menu'] = $data['url_menu'];
                $data['title_group'] = 'Error';
                $data['title_menu'] = 'Error';
                $data['errorpages'] = 'Not Authorized!';
                //insert log
                $syslog->log_insert('E', $data['url_menu'], 'Not Authorized!' . ' - Show -' . $data['url_menu'], '0');
                //return error page
                return view("pages.errorpages", $data);
            }
        }

        // Set data for view compatibility
        $data['pengajuan'] = $pengajuan;
        $data['list'] = $pengajuan; // For view compatibility with $list variable

        // Recalculate with correct bunga flat formula for display
        $tenor_bulan = (int) filter_var($pengajuan->tenor_pinjaman, FILTER_SANITIZE_NUMBER_INT);
        $jumlah_pinjaman = $pengajuan->jumlah_pinjaman;
        $bunga_per_bulan = 1.0; // Fixed 1% per bulan

        // Perhitungan Bunga Flat yang Benar
        $cicilan_pokok = $jumlah_pinjaman / $tenor_bulan;
        $bunga_flat = $jumlah_pinjaman * ($bunga_per_bulan / 100);
        $cicilan_per_bulan_correct = $cicilan_pokok + $bunga_flat;
        $total_pembayaran_correct = $cicilan_per_bulan_correct * $tenor_bulan;

        // Add corrected calculations to data
        $data['cicilan_per_bulan_correct'] = $cicilan_per_bulan_correct;
        $data['total_pembayaran_correct'] = $total_pembayaran_correct;
        $data['cicilan_pokok'] = $cicilan_pokok;
        $data['bunga_flat'] = $bunga_flat;

        // Get additional data for view using Eloquent
        $data['periode_list'] = PeriodePencairan::where('isactive', '1')
            ->select('id', 'nama_periode')
            ->get();

        // Static tenor options
        $data['tenor_list'] = collect([
            (object) ['id' => '6 bulan', 'nama_tenor' => '6 bulan', 'tenor_bulan' => 6],
            (object) ['id' => '10 bulan', 'nama_tenor' => '10 bulan', 'tenor_bulan' => 10],
            (object) ['id' => '12 bulan', 'nama_tenor' => '12 bulan', 'tenor_bulan' => 12],
        ]);

        // Get approval history using Eloquent
        $data['approval_history'] = ApprovalHistory::where('pengajuan_pinjaman_id', $id)
            ->orderBy('created_at', 'desc')
            ->get();

        // Log access
        $syslog->log_insert('R', $data['dmenu'], 'Pengajuan Pinjaman Detail Accessed: ID ' . $pengajuan->id, '1');

        return view($data['url'], $data);
    }

    /**
     * Show the form for editing the specified resource - KOP201/edit
     */
    public function edit($data)
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
            $data['url_menu'] = 'error';
            $data['title_group'] = 'Error';
            $data['title_menu'] = 'Error';
            $data['errorpages'] = 'ID tidak valid!';
            return view("pages.errorpages", $data);
        }

        // Get pengajuan data using Eloquent
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

        // Check if editable (only draft and diajukan status)
        if (!in_array($pengajuan->status_pengajuan, ['draft', 'diajukan'])) {
            $data['url_menu'] = 'error';
            $data['title_group'] = 'Error';
            $data['title_menu'] = 'Error';
            $data['errorpages'] = 'Pengajuan tidak dapat diedit pada status ini!';
            return view("pages.errorpages", $data);
        }

        $data['pengajuan'] = $pengajuan;

        // Check if user is anggota (regular member) and get their anggota data
        $data['current_anggota'] = null;
        $data['is_anggota_biasa'] = false;
        $data['hide_stock_info'] = false;

        if (isset($data['user_login']->idroles) && strpos($data['user_login']->idroles, 'anggot') !== false) {
            $data['is_anggota_biasa'] = true;
            $data['hide_stock_info'] = true; // Hide stock information for regular members
            $data['current_anggota'] = Anggotum::where('email', $data['user_login']->email)
                                              ->orWhere('user_create', $data['user_login']->username)
                                              ->where('status_keanggotaan', 'aktif')
                                              ->where('isactive', '1')
                                              ->select('id', 'nomor_anggota', 'nama_lengkap')
                                              ->first();
        }

        // Get form data using Eloquent
        $data['anggota_list'] = Anggotum::where('status_keanggotaan', 'aktif')
            ->where('isactive', '1')
            ->select('id', 'nomor_anggota', 'nama_lengkap')
            ->get();

        $data['paket_list'] = MasterPaketPinjaman::where('isactive', '1')
            ->select('id', 'periode', 'stock_limit', 'stock_terpakai')
            ->get();

        $data['tenor_list'] = collect([
            (object) ['id' => '6 bulan', 'nama_tenor' => '6 bulan', 'tenor_bulan' => 6],
            (object) ['id' => '10 bulan', 'nama_tenor' => '10 bulan', 'tenor_bulan' => 10],
            (object) ['id' => '12 bulan', 'nama_tenor' => '12 bulan', 'tenor_bulan' => 12],
        ]);

        // Get periode pencairan list
        $data['periode_list'] = PeriodePencairan::where('isactive', '1')
            ->select('id', 'nama_periode')
            ->get();

        return view($data['url'], $data);
    }

    /**
     * Update the specified resource in storage - KOP201/update
     */
    public function update($data)
    {
        // Function helper
        $syslog = new Function_Helper;
        $data['format'] = new Format_Helper;

        // Handle role-based anggota_id assignment
        $anggotaId = request('anggota_id');

        // Check if user is anggota (regular member) and auto-assign anggota_id
        if (isset($data['user_login']->idroles) && strpos($data['user_login']->idroles, 'anggot') !== false) {
            // Find anggota by email or username matching
            $anggota = Anggotum::where('email', $data['user_login']->email)
                              ->orWhere('user_create', $data['user_login']->username)
                              ->where('status_keanggotaan', 'aktif')
                              ->where('isactive', '1')
                              ->first();

            if ($anggota) {
                $anggotaId = $anggota->id;
                // Override request with the found anggota_id
                request()->merge(['anggota_id' => $anggotaId]);
            } else {
                $data['url_menu'] = 'error';
                $data['title_group'] = 'Error';
                $data['title_menu'] = 'Error';
                $data['errorpages'] = 'Data anggota Anda tidak ditemukan. Silakan hubungi admin.';
                return view("pages.errorpages", $data);
            }
        }

        if (!$anggotaId) {
            $data['url_menu'] = 'error';
            $data['title_group'] = 'Error';
            $data['title_menu'] = 'Error';
            $data['errorpages'] = 'Anggota harus dipilih.';
            return view("pages.errorpages", $data);
        }

        // Decrypt ID first to get current pengajuan
        try {
            $id = decrypt($data['idencrypt']);
        } catch (\Exception $e) {
            $data['url_menu'] = 'error';
            $data['title_group'] = 'Error';
            $data['title_menu'] = 'Error';
            $data['errorpages'] = 'ID tidak valid!';
            return view("pages.errorpages", $data);
        }

        // Check if anggota has other pending applications (excluding current one)
        $pendingStatuses = ['diajukan', 'review_admin', 'review_panitia', 'review_ketua'];
        $existingPending = PengajuanPinjaman::where('anggota_id', $anggotaId)
            ->whereIn('status_pengajuan', $pendingStatuses)
            ->where('id', '!=', $id)
            ->where('isactive', '1')
            ->first();

        if ($existingPending) {
            $statusText = [
                'diajukan' => 'Diajukan',
                'review_admin' => 'Review Admin',
                'review_panitia' => 'Review Panitia',
                'review_ketua' => 'Review Ketua'
            ];
            $currentStatus = $statusText[$existingPending->status_pengajuan] ?? $existingPending->status_pengajuan;
            $data['url_menu'] = 'error';
            $data['title_group'] = 'Error';
            $data['title_menu'] = 'Error';
            $data['errorpages'] = 'Anggota masih memiliki pengajuan pinjaman lain dengan status "' . $currentStatus . '". Tidak dapat mengupdate pengajuan ini selama masih ada pengajuan dalam proses persetujuan.';
            return view("pages.errorpages", $data);
        }

        // Automatic eligibility check and set jenis_pengajuan
        if ($anggotaId) {
            // Check if anggota has active loan and remaining payments ≤ 2
            $eligibility = DB::select("
                SELECT
                    pp.id as pengajuan_id,
                    p.id as pinjaman_id,
                    p.tenor_bulan,
                    COUNT(cp.id) as total_cicilan_lunas,
                    (p.tenor_bulan - COUNT(cp.id)) as sisa_cicilan
                FROM pengajuan_pinjaman pp
                INNER JOIN pinjaman p ON pp.id = p.pengajuan_pinjaman_id
                LEFT JOIN cicilan_pinjaman cp ON p.id = cp.pinjaman_id AND cp.status = 'lunas'
                WHERE pp.anggota_id = ?
                AND pp.status_pengajuan = 'disetujui'
                AND p.status = 'aktif'
                AND pp.isactive = '1'
                AND p.isactive = '1'
                GROUP BY pp.id, p.id, p.tenor_bulan
                HAVING sisa_cicilan <= 2
                LIMIT 1
            ", [$anggotaId]);

            // Automatically set jenis_pengajuan based on eligibility
            $jenis_pengajuan = !empty($eligibility) ? 'top_up' : 'baru';

            // Override the request with the determined jenis_pengajuan
            request()->merge(['jenis_pengajuan' => $jenis_pengajuan]);
        }

        // Validation rules - Range validation removed as per koperasi system preferences
        $validator = Validator::make(request()->all(), [
            'anggota_id' => 'required|exists:anggota,id',
            'paket_pinjaman_id' => 'required|exists:master_paket_pinjaman,id',
            'jumlah_paket_dipilih' => 'required|integer|min:1', // max removed for flexibility
            'tenor_pinjaman' => 'required|string|in:6 bulan,10 bulan,12 bulan',
            'tujuan_pinjaman' => 'required|string|max:500',
            'jenis_pengajuan' => 'required|in:baru,top_up',
            'periode_pencairan_id' => 'required|exists:periode_pencairan,id',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
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

        DB::beginTransaction();

        try {
            // Get pengajuan using Eloquent (UPDATE method)
            $pengajuan = PengajuanPinjaman::find($id);

            if (!$pengajuan) {
                $data['url_menu'] = 'error';
                $data['title_group'] = 'Error';
                $data['title_menu'] = 'Error';
                $data['errorpages'] = 'Data pengajuan tidak ditemukan!';
                return view("pages.errorpages", $data);
            }

            // Check if editable
            if (!in_array($pengajuan->status_pengajuan, ['draft', 'diajukan'])) {
                $data['url_menu'] = 'error';
                $data['title_group'] = 'Error';
                $data['title_menu'] = 'Error';
                $data['errorpages'] = 'Pengajuan tidak dapat diedit pada status ini!';
                return view("pages.errorpages", $data);
            }

            // Get paket data for calculation
            $paket = MasterPaketPinjaman::find(request('paket_pinjaman_id'));
            $tenor_pinjaman = request('tenor_pinjaman');
            $tenor_bulan = (int) filter_var($tenor_pinjaman, FILTER_SANITIZE_NUMBER_INT);
            $jumlah_paket = request('jumlah_paket_dipilih');

            // Calculate new amounts
            $nilai_per_paket = 500000;
            $jumlah_pinjaman = $jumlah_paket * $nilai_per_paket;
            $bunga_per_bulan = 1.0; // Fixed 1% per bulan

            $cicilan_pokok = $jumlah_pinjaman / $tenor_bulan;
            $bunga_flat = $jumlah_pinjaman * ($bunga_per_bulan / 100);
            $cicilan_per_bulan = $cicilan_pokok + $bunga_flat;
            $total_pembayaran = $cicilan_per_bulan * $tenor_bulan;

            // Update stock tracking (for information only, no validation)
            if ($pengajuan->paket_pinjaman_id != request('paket_pinjaman_id') || $pengajuan->jumlah_paket_dipilih != $jumlah_paket) {
                // Restore old stock count
                $old_paket = MasterPaketPinjaman::find($pengajuan->paket_pinjaman_id);
                $old_paket->decrement('stock_terpakai', $pengajuan->jumlah_paket_dipilih);

                // Update new stock count (no validation, just tracking)
                $paket->increment('stock_terpakai', $jumlah_paket);
            }

            // Update pengajuan using Eloquent
            $pengajuan->update([
                'anggota_id' => request('anggota_id'),
                'paket_pinjaman_id' => request('paket_pinjaman_id'),
                'jumlah_paket_dipilih' => $jumlah_paket,
                'tenor_pinjaman' => $tenor_pinjaman,
                'jumlah_pinjaman' => $jumlah_pinjaman,
                'bunga_per_bulan' => $bunga_per_bulan,
                'cicilan_per_bulan' => $cicilan_per_bulan,
                'total_pembayaran' => $total_pembayaran,
                'tujuan_pinjaman' => request('tujuan_pinjaman'),
                'jenis_pengajuan' => request('jenis_pengajuan'),
                'periode_pencairan_id' => request('periode_pencairan_id'),
                'user_update' => $data['user_login']->username ?? 'system',
                'updated_at' => now(),
            ]);

            DB::commit();

            // Log success
            $syslog->log_insert('U', $data['dmenu'], 'Pengajuan Pinjaman Updated: ID ' . $pengajuan->id, '1');

            $jenis_text = request('jenis_pengajuan') === 'top_up' ? 'Top Up (otomatis terdeteksi)' : 'Pinjaman Baru';
            Session::flash('message', 'Pengajuan pinjaman berhasil diupdate sebagai: ' . $jenis_text);
            Session::flash('class', 'success');

        } catch (\Exception $e) {
            DB::rollback();

            // Log error
            $syslog->log_insert('E', $data['dmenu'], 'Pengajuan Pinjaman Update Error: ' . $e->getMessage(), '0');

            Session::flash('message', 'Gagal mengupdate pengajuan pinjaman: ' . $e->getMessage());
            Session::flash('class', 'danger');
        }

        return redirect($data['url_menu']);
    }

    /**
     * Remove the specified resource from storage - KOP201/destroy
     */
    public function destroy($data)
    {
        // Function helper
        $syslog = new Function_Helper;

        // Decrypt ID
        try {
            $id = decrypt($data['idencrypt']);
        } catch (\Exception $e) {
            $data['url_menu'] = 'error';
            $data['title_group'] = 'Error';
            $data['title_menu'] = 'Error';
            $data['errorpages'] = 'ID tidak valid!';
            return view("pages.errorpages", $data);
        }

        // Check authorization
        if ($data['authorize']->delete == '0') {
            $data['url_menu'] = $data['url_menu'];
            $data['title_group'] = 'Error';
            $data['title_menu'] = 'Error';
            $data['errorpages'] = 'Not Authorized!';
            //insert log
            $syslog->log_insert('E', $data['url_menu'], 'Not Authorized!' . ' - Delete -' . $data['url_menu'], '0');
            //return error page
            return view("pages.errorpages", $data);
        }

        DB::beginTransaction();

        try {
            // Get pengajuan using Eloquent (DESTROY method)
            $pengajuan = PengajuanPinjaman::find($id);

            if (!$pengajuan) {
                $data['url_menu'] = 'error';
                $data['title_group'] = 'Error';
                $data['title_menu'] = 'Error';
                $data['errorpages'] = 'Data pengajuan tidak ditemukan!';
                return view("pages.errorpages", $data);
            }

            // Check if deletable (only draft status)
            if ($pengajuan->status_pengajuan !== 'draft') {
                $data['url_menu'] = 'error';
                $data['title_group'] = 'Error';
                $data['title_menu'] = 'Error';
                $data['errorpages'] = 'Hanya pengajuan dengan status draft yang dapat dihapus!';
                return view("pages.errorpages", $data);
            }

            // Restore stock
            $paket = MasterPaketPinjaman::find($pengajuan->paket_pinjaman_id);
            $paket->decrement('stock_terpakai', $pengajuan->jumlah_paket_dipilih);

            // Soft delete using Eloquent
            $pengajuan->update([
                'isactive' => '0',
                'user_update' => $data['user_login']->username ?? 'system',
                'updated_at' => now(),
            ]);

            DB::commit();

            // Log success
            $syslog->log_insert('D', $data['dmenu'], 'Pengajuan Pinjaman Deleted: ID ' . $pengajuan->id, '1');

            Session::flash('message', 'Pengajuan pinjaman berhasil dihapus!');
            Session::flash('class', 'success');

        } catch (\Exception $e) {
            DB::rollback();

            // Log error
            $syslog->log_insert('E', $data['dmenu'], 'Pengajuan Pinjaman Delete Error: ' . $e->getMessage(), '0');

            Session::flash('message', 'Gagal menghapus pengajuan pinjaman: ' . $e->getMessage());
            Session::flash('class', 'danger');
        }

        return redirect($data['url_menu']);
    }

    /**
     * AJAX handler for dynamic data - KOP201/ajax
     */
    public function ajax($data)
    {
        $action = request('action');

        // Check if user is regular member (anggota biasa)
        $user = session('user');
        $hideStockInfo = false;
        if ($user && isset($user->idroles) && strpos($user->idroles, 'anggot') !== false) {
            $hideStockInfo = true;
        }

        switch ($action) {
            case 'get_paket_info':
                $paket = MasterPaketPinjaman::find(request('paket_id'));
                if ($paket) {
                    $responseData = [
                        'bunga_per_bulan' => 1.0, // Fixed 1% per bulan
                    ];

                    // Only include stock information for non-regular members
                    if (!$hideStockInfo) {
                        $responseData['stock_available'] = $paket->stock_limit - $paket->stock_terpakai;
                        $responseData['stock_limit'] = $paket->stock_limit;
                        $responseData['stock_terpakai'] = $paket->stock_terpakai;
                    }

                    return response()->json([
                        'success' => true,
                        'data' => $responseData
                    ]);
                }
                break;

            case 'get_anggota':
                $anggotaList = Anggotum::where('status_keanggotaan', 'aktif')
                    ->where('isactive', '1')
                    ->select('id', 'nomor_anggota', 'nama_lengkap')
                    ->get();

                return response()->json([
                    'success' => true,
                    'data' => $anggotaList
                ]);

            case 'get_paket':
                $paketList = MasterPaketPinjaman::where('isactive', '1')
                    ->select('id', 'periode', 'stock_limit', 'stock_terpakai')
                    ->get();

                return response()->json([
                    'success' => true,
                    'data' => $paketList
                ]);

            case 'get_tenor':
                $tenorList = collect([
                    (object) ['id' => '6 bulan', 'nama_tenor' => '6 bulan', 'tenor_bulan' => 6],
                    (object) ['id' => '10 bulan', 'nama_tenor' => '10 bulan', 'tenor_bulan' => 10],
                    (object) ['id' => '12 bulan', 'nama_tenor' => '12 bulan', 'tenor_bulan' => 12],
                ]);

                return response()->json([
                    'success' => true,
                    'data' => $tenorList
                ]);

            case 'get_periode':
                $periodeList = PeriodePencairan::where('isactive', '1')
                    ->select('id', 'nama_periode')
                    ->get();

                return response()->json([
                    'success' => true,
                    'data' => $periodeList
                ]);

            default:
                return response()->json(['success' => false, 'message' => 'Invalid action']);
        }

        return response()->json(['success' => false, 'message' => 'Action not found']);
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
