<?php

namespace App\Http\Controllers;

use App\Helpers\Format_Helper;
use App\Helpers\Function_Helper;
use App\Helpers\Koperasi\Approval\ApprovalWorkflowHelper;
use App\Helpers\Koperasi\Pengajuan\PengajuanPinjamanWorkflowHelper;
use App\Helpers\Koperasi\Pengajuan\PengajuanPinjamanCalculationHelper;
use App\Helpers\Koperasi\Pengajuan\PengajuanPinjamanValidationHelper;
use App\Helpers\Koperasi\Pengajuan\PengajuanPinjamanAuthHelper;
use App\Models\PengajuanPinjaman;
use App\Models\Anggotum;
use App\Models\MasterPaketPinjaman;
use App\Models\PeriodePencairan;
use App\Models\Pinjaman;
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

        // Get pengajuan pinjaman data using model methods (MSJ Framework standard)
        $query = PengajuanPinjaman::with(['anggota', 'paketPinjaman', 'periodePencairan'])
            ->where('isactive', '1');

        // Apply search filter using helper method
        $search = request('search');
        $query = PengajuanPinjamanAuthHelper::applySearchFilter($query, $search);

        // Apply authorization rules using helper method
        $query = PengajuanPinjamanAuthHelper::applyAuthorizationRules($query, $data['authorize'], $data['users_rules']);

        $collectionData = $query->orderBy('created_at', 'desc')->get();

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

        // Get summary statistics using helper method
        $data['stats'] = PengajuanPinjamanCalculationHelper::getStatistics($collectionData);

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

        // Check if user is anggota (regular member) using helper method
        $userRole = PengajuanPinjamanAuthHelper::getUserRole($data);
        $data['is_anggota_biasa'] = Anggotum::isRegularMember($userRole);
        $data['hide_stock_info'] = $data['is_anggota_biasa']; // Hide stock information for regular members

        $data['current_anggota'] = null;
        if ($data['is_anggota_biasa']) {
            $data['current_anggota'] = Anggotum::findByUserCredentials(
                $data['user_login']->email,
                $data['user_login']->username
            );
        }

        // Get form data using model methods
        $data['anggota_list'] = Anggotum::getActiveList();
        $data['paket_list'] = MasterPaketPinjaman::getActiveList();

        // Static tenor options using model constant
        $data['tenor_list'] = collect([
            (object) ['id' => '6 bulan', 'nama_tenor' => '6 bulan', 'tenor_bulan' => 6],
            (object) ['id' => '10 bulan', 'nama_tenor' => '10 bulan', 'tenor_bulan' => 10],
            (object) ['id' => '12 bulan', 'nama_tenor' => '12 bulan', 'tenor_bulan' => 12],
        ]);

        $data['periode_list'] = PeriodePencairan::where('isactive', '1')
            ->select('id', 'tahun', 'bulan')
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

        // Determine if user is anggota biasa (regular member)
        $isAnggotaBiasa = false;
        if (isset($data['user_login']->idroles) && strpos($data['user_login']->idroles, 'anggot') !== false) {
            $isAnggotaBiasa = true;
            // Find anggota by email or username matching
            $anggota = Anggotum::where('email', $data['user_login']->email)
                              ->orWhere('user_create', $data['user_login']->username)
                              ->where('isactive', '1')
                              ->first();

            if ($anggota) {
                $anggotaId = $anggota->id;
                // Override request with the found anggota_id
                request()->merge(['anggota_id' => $anggotaId]);
            } else {
                // Use MSJ framework Session flash message with badge info component styling
                Session::flash('message', 'Data anggota Anda tidak ditemukan. Silakan hubungi admin untuk verifikasi data keanggotaan.');
                Session::flash('class', 'danger');
                return redirect()->back();
            }
        }

        if (!$anggotaId) {
            // Use MSJ framework Session flash message with badge info component styling
            Session::flash('message', 'Anggota harus dipilih untuk melanjutkan pengajuan pinjaman.');
            Session::flash('class', 'warning');
            return redirect()->back()->withInput();
        }

        // Validate anggota status - check if member is active
        $anggota = Anggotum::find($anggotaId);
        if (!$anggota) {
            Session::flash('message', 'Data anggota tidak ditemukan. Silakan hubungi admin untuk verifikasi data keanggotaan.');
            Session::flash('class', 'danger');
            return redirect()->back()->withInput();
        }

        if ($anggota->isactive == '0') {
            Session::flash('message', 'Status keanggotaan Anda tidak aktif. Anda tidak dapat membuat pengajuan pinjaman. Silakan hubungi admin untuk mengaktifkan kembali keanggotaan Anda.');
            Session::flash('class', 'danger');
            return redirect()->back()->withInput();
        }

        // Check for existing pending application using helper method
        // Cek semua status yang masih dalam proses (diajukan, review_admin, review_panitia, review_ketua)
        if (PengajuanPinjamanValidationHelper::hasExistingPengajuan($anggotaId)) {
            Session::flash('message', 'Anggota masih memiliki pengajuan pinjaman yang sedang dalam proses persetujuan. Tidak dapat mengajukan pinjaman baru selama masih ada pengajuan yang pending.');
            Session::flash('class', 'danger');
            return redirect()->back()->withInput();
        }

        // Check top-up eligibility and validate loan type
        if ($anggotaId) {
            $jenis_pengajuan = PengajuanPinjaman::determineLoanType($anggotaId);

            // If system determines it's a top-up, validate eligibility
            if ($jenis_pengajuan === 'top_up') {
                $eligibility = PengajuanPinjaman::checkTopUpEligibility($anggotaId);

                if (!$eligibility) {
                    // This shouldn't happen if determineLoanType works correctly, but add safety check
                    Session::flash('message', 'Anggota tidak memenuhi syarat untuk pinjaman top-up. Sistem akan memproses sebagai pinjaman baru.');
                    Session::flash('class', 'warning');
                    $jenis_pengajuan = 'baru';
                } else {
                    // Validate that member has only 2 or fewer remaining installments
                    if ($eligibility->sisa_cicilan > 2) {
                        Session::flash('message', 'Pinjaman top-up hanya dapat diajukan jika sisa cicilan 2 bulan atau kurang. Sisa cicilan Anda: ' . $eligibility->sisa_cicilan . ' bulan. Sistem akan memproses sebagai pinjaman baru.');
                        Session::flash('class', 'warning');
                        $jenis_pengajuan = 'baru';
                    }
                }
            }

            request()->merge(['jenis_pengajuan' => $jenis_pengajuan]);
        }

        // Validation using helper method
        $validator = Validator::make(request()->all(), PengajuanPinjamanValidationHelper::getValidationRules());

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
            // Get basic data (anggota already retrieved and validated above)
            $paket = MasterPaketPinjaman::find(request('paket_pinjaman_id'));
            $tenor_pinjaman = request('tenor_pinjaman');
            $tenor_bulan = PengajuanPinjamanCalculationHelper::getTenorBulan($tenor_pinjaman);
            $jumlah_paket = request('jumlah_paket_dipilih');

            // Calculate loan amounts using helper method
            $calculations = PengajuanPinjamanCalculationHelper::calculateLoanAmounts($jumlah_paket, $tenor_bulan);

            // Stock validation removed as per koperasi system preferences
            // Stock information is for display only, no blocking validation
            // This follows the preference: "auto-approve loan applications without stock validation"

            // Get user role and username for workflow processing using helper methods
            $userRole = PengajuanPinjamanAuthHelper::getUserRole($data);
            $currentUsername = PengajuanPinjamanAuthHelper::getCurrentUsername($data);

            // Determine initial status based on user role and approval workflow
            // Enum values: 'draft', 'diajukan', 'review_admin', 'review_panitia', 'review_ketua', 'disetujui', 'ditolak', 'dibatalkan'
            switch ($userRole) {
                case 'ketuum': // Ketua Umum - can auto-approve (final level)
                    $initialStatus = 'disetujui';
                    $approvalDate = now();
                    break;

                case 'akredt': // Admin Kredit - starts at review_panitia level
                    $initialStatus = 'review_panitia';
                    $approvalDate = null;
                    break;

                case 'kadmin': // Ketua Admin - starts at review_admin level
                    $initialStatus = 'review_admin';
                    $approvalDate = null;
                    break;

                default: // Anggota and other users - normal workflow
                    $initialStatus = 'diajukan';
                    $approvalDate = null;
                    break;
            }

            // Create pengajuan using calculated values
            $pengajuan = PengajuanPinjaman::create([
                'anggota_id' => request('anggota_id'),
                'paket_pinjaman_id' => request('paket_pinjaman_id'),
                'jumlah_paket_dipilih' => $jumlah_paket,
                'tenor_pinjaman' => $tenor_pinjaman,
                'jumlah_pinjaman' => $calculations['jumlah_pinjaman'],
                'bunga_per_bulan' => $calculations['bunga_per_bulan'],
                'cicilan_per_bulan' => $calculations['cicilan_per_bulan'],
                'total_pembayaran' => $calculations['total_pembayaran'],
                'tujuan_pinjaman' => request('tujuan_pinjaman'),
                'jenis_pengajuan' => request('jenis_pengajuan'),
                'periode_pencairan_id' => request('periode_pencairan_id'),
                'status_pengajuan' => $initialStatus,
                'status_pencairan' => 'belum_cair',
                'tanggal_pengajuan' => now(),
                'tanggal_approval' => $approvalDate,
                'approved_by' => ($userRole === 'kadmin' || $userRole === 'ketuum') ? $currentUsername : null,
                'isactive' => '1',
                'user_create' => $currentUsername,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Update stock terpakai using model method (only for non-regular members)
            $isAnggotaBiasa = Anggotum::isRegularMember($userRole);
            if (!$isAnggotaBiasa) {
                $paket->updateStockUsage($jumlah_paket, 'increment');
            }

            // Process approval workflow using model method
            $isBypass = $pengajuan->processAfterCreation($userRole, $currentUsername, $jumlah_paket, $tenor_pinjaman, $data['format']);

            DB::commit();

            // Log success
            $syslog->log_insert('C', $data['dmenu'], 'Pengajuan Pinjaman Created: ID ' . $pengajuan->id, '1');

            // Create appropriate success message based on user role and approval status
            $jenis_text = $jenis_pengajuan === 'top_up' ? 'Top Up (otomatis terdeteksi)' : 'Pinjaman Baru';

            if ($userRole === 'ketuum') {
                Session::flash('message', 'Pengajuan pinjaman berhasil dibuat dan otomatis disetujui sebagai: ' . $jenis_text . '. Pinjaman telah aktif dan siap dicairkan.');
                Session::flash('class', 'success');
            } else {
                Session::flash('message', 'Pengajuan pinjaman berhasil dibuat sebagai: ' . $jenis_text . '. Status: Diajukan, menunggu proses approval.');
                Session::flash('class', 'success');
            }

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

        // Recalculate using model method for display
        $tenor_bulan = PengajuanPinjaman::getTenorBulan($pengajuan->tenor_pinjaman);
        $jumlah_paket = $pengajuan->jumlah_pinjaman / PengajuanPinjamanCalculationHelper::NILAI_PER_PAKET;
        $calculations = PengajuanPinjaman::calculateLoanAmounts($jumlah_paket, $tenor_bulan);

        // Add calculations to data
        $data['cicilan_per_bulan_correct'] = $calculations['cicilan_per_bulan'];
        $data['total_pembayaran_correct'] = $calculations['total_pembayaran'];
        $data['cicilan_pokok'] = $calculations['jumlah_pinjaman'] / $tenor_bulan;
        $data['bunga_flat'] = $calculations['jumlah_pinjaman'] * (PengajuanPinjamanCalculationHelper::BUNGA_PER_BULAN / 100);

        // Get additional data for view using Eloquent
        $data['periode_list'] = PeriodePencairan::where('isactive', '1')
            ->select('id', 'tahun', 'bulan')
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

        // Check if editable using model method
        if (!$pengajuan->isEditable()) {
            $data['url_menu'] = 'error';
            $data['title_group'] = 'Error';
            $data['title_menu'] = 'Error';
            $data['errorpages'] = 'Pengajuan tidak dapat diedit pada status ini!';
            return view("pages.errorpages", $data);
        }

        $data['pengajuan'] = $pengajuan;

        // Check if user is anggota (anggota_koperasi)
        $userRole = PengajuanPinjaman::getUserRole($data);
        $data['is_anggota_biasa'] = Anggotum::isRegularMember($userRole);
        $data['hide_stock_info'] = $data['is_anggota_biasa'];

        $data['current_anggota'] = null;
        if ($data['is_anggota_biasa']) {
            $data['current_anggota'] = Anggotum::findByUserCredentials(
                $data['user_login']->email,
                $data['user_login']->username
            );
        }

        // Get form data using model methods
        $data['anggota_list'] = Anggotum::getActiveList();
        $data['paket_list'] = MasterPaketPinjaman::getActiveList();

        $data['tenor_list'] = collect([
            (object) ['id' => '6 bulan', 'nama_tenor' => '6 bulan', 'tenor_bulan' => 6],
            (object) ['id' => '10 bulan', 'nama_tenor' => '10 bulan', 'tenor_bulan' => 10],
            (object) ['id' => '12 bulan', 'nama_tenor' => '12 bulan', 'tenor_bulan' => 12],
        ]);

        // Get periode pencairan list
        $data['periode_list'] = PeriodePencairan::where('isactive', '1')
            ->select('id', 'tahun', 'bulan')
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

        // Handle role-based anggota_id assignment using model methods
        $anggotaId = request('anggota_id');
        $userRole = PengajuanPinjaman::getUserRole($data);

        // Check if user is anggota and auto-assign anggota_id
        if (Anggotum::isRegularMember($userRole)) {
            $anggota = Anggotum::findByUserCredentials(
                $data['user_login']->email,
                $data['user_login']->username
            );

            if ($anggota) {
                $anggotaId = $anggota->id;
                request()->merge(['anggota_id' => $anggotaId]);
            } else {
                Session::flash('message', 'Data anggota Anda tidak ditemukan. Silakan hubungi admin untuk verifikasi data keanggotaan.');
                Session::flash('class', 'danger');
                return redirect()->back();
            }
        }

        if (!$anggotaId) {
            Session::flash('message', 'Anggota harus dipilih untuk melanjutkan pengajuan pinjaman.');
            Session::flash('class', 'warning');
            return redirect()->back()->withInput();
        }

        // Validate anggota status - check if member is active
        $anggota = Anggotum::find($anggotaId);
        if (!$anggota) {
            Session::flash('message', 'Data anggota tidak ditemukan. Silakan hubungi admin untuk verifikasi data keanggotaan.');
            Session::flash('class', 'danger');
            return redirect()->back()->withInput();
        }

        if ($anggota->isactive == '0') {
            Session::flash('message', 'Status keanggotaan Anda tidak aktif. Anda tidak dapat mengedit pengajuan pinjaman. Silakan hubungi admin untuk mengaktifkan kembali keanggotaan Anda.');
            Session::flash('class', 'danger');
            return redirect()->back()->withInput();
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

        // Check for existing pending application using model method (excluding current one)
        // Cek semua status yang masih dalam proses (diajukan, review_admin, review_panitia, review_ketua)
        if (PengajuanPinjaman::hasExistingPengajuan($anggotaId, $id)) {
            Session::flash('message', 'Anggota masih memiliki pengajuan pinjaman yang sedang dalam proses persetujuan. Tidak dapat mengedit pengajuan ini selama masih ada pengajuan lain yang pending.');
            Session::flash('class', 'danger');
            return redirect()->back()->withInput();
        }

        // Check top-up eligibility and validate loan type
        if ($anggotaId) {
            $jenis_pengajuan = PengajuanPinjaman::determineLoanType($anggotaId);

            // If system determines it's a top-up, validate eligibility
            if ($jenis_pengajuan === 'top_up') {
                $eligibility = PengajuanPinjaman::checkTopUpEligibility($anggotaId);

                if (!$eligibility) {
                    // This shouldn't happen if determineLoanType works correctly, but add safety check
                    Session::flash('message', 'Anggota tidak memenuhi syarat untuk pinjaman top-up. Sistem akan memproses sebagai pinjaman baru.');
                    Session::flash('class', 'warning');
                    $jenis_pengajuan = 'baru';
                } else {
                    // Validate that member has only 2 or fewer remaining installments
                    if ($eligibility->sisa_cicilan > 2) {
                        Session::flash('message', 'Pinjaman top-up hanya dapat diajukan jika sisa cicilan 2 bulan atau kurang. Sisa cicilan Anda: ' . $eligibility->sisa_cicilan . ' bulan. Sistem akan memproses sebagai pinjaman baru.');
                        Session::flash('class', 'warning');
                        $jenis_pengajuan = 'baru';
                    }
                }
            }

            request()->merge(['jenis_pengajuan' => $jenis_pengajuan]);
        }

        // Validation using model method
        $validator = Validator::make(request()->all(), PengajuanPinjaman::getValidationRules());

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

            // Check if editable using model method
            if (!$pengajuan->isEditable()) {
                $data['url_menu'] = 'error';
                $data['title_group'] = 'Error';
                $data['title_menu'] = 'Error';
                $data['errorpages'] = 'Pengajuan tidak dapat diedit pada status ini!';
                return view("pages.errorpages", $data);
            }

            // Get basic data and calculate using model methods
            $paket = MasterPaketPinjaman::find(request('paket_pinjaman_id'));
            $tenor_pinjaman = request('tenor_pinjaman');
            $tenor_bulan = PengajuanPinjaman::getTenorBulan($tenor_pinjaman);
            $jumlah_paket = request('jumlah_paket_dipilih');

            // Calculate amounts using model method
            $calculations = PengajuanPinjaman::calculateLoanAmounts($jumlah_paket, $tenor_bulan);

            // Update stock tracking using model methods (for information only, no validation)
            if ($pengajuan->paket_pinjaman_id != request('paket_pinjaman_id') || $pengajuan->jumlah_paket_dipilih != $jumlah_paket) {
                // Restore old stock count
                $old_paket = MasterPaketPinjaman::find($pengajuan->paket_pinjaman_id);
                $old_paket->updateStockUsage($pengajuan->jumlah_paket_dipilih, 'decrement');

                // Update new stock count (no validation, just tracking)
                $paket->updateStockUsage($jumlah_paket, 'increment');
            }

            // Update pengajuan using calculated values
            $pengajuan->update([
                'anggota_id' => request('anggota_id'),
                'paket_pinjaman_id' => request('paket_pinjaman_id'),
                'jumlah_paket_dipilih' => $jumlah_paket,
                'tenor_pinjaman' => $tenor_pinjaman,
                'jumlah_pinjaman' => $calculations['jumlah_pinjaman'],
                'bunga_per_bulan' => $calculations['bunga_per_bulan'],
                'cicilan_per_bulan' => $calculations['cicilan_per_bulan'],
                'total_pembayaran' => $calculations['total_pembayaran'],
                'tujuan_pinjaman' => request('tujuan_pinjaman'),
                'jenis_pengajuan' => request('jenis_pengajuan'),
                'periode_pencairan_id' => request('periode_pencairan_id'),
                'user_update' => PengajuanPinjaman::getCurrentUsername($data),
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
                    ->select('id', 'tahun', 'bulan')
                    ->get()
                    ->map(function($periode) {
                        return (object) [
                            'id' => $periode->id,
                            'nama_periode' => $periode->nama_periode
                        ];
                    });

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
        // Function helper
        $syslog = new Function_Helper;

        try {
            // Get data for export using same logic as index
            $query = PengajuanPinjaman::with(['anggota', 'paketPinjaman', 'periodePencairan'])
                ->where('isactive', '1');

            // Apply search filter if exists
            $search = $request->input('search');
            $query = PengajuanPinjaman::applySearchFilter($query, $search);

            $exportData = $query->orderBy('created_at', 'desc')->get();

            // Prepare export data following MSJ Framework pattern
            $data = [];
            $data[] = ['No', 'No. Pengajuan', 'Anggota', 'Paket', 'Jumlah Pinjaman', 'Tenor', 'Status', 'Tanggal'];

            foreach ($exportData as $index => $pengajuan) {
                $data[] = [
                    $index + 1,
                    $pengajuan->nomor_pengajuan ?? '-',
                    $pengajuan->anggota->nama_lengkap ?? '-',
                    $pengajuan->paketPinjaman->periode ?? '-',
                    'Rp ' . number_format($pengajuan->jumlah_pinjaman, 0, ',', '.'),
                    $pengajuan->tenor_pinjaman,
                    ucfirst(str_replace('_', ' ', $pengajuan->status_pengajuan)),
                    $pengajuan->tanggal_pengajuan ? date('d/m/Y', strtotime($pengajuan->tanggal_pengajuan)) : '-'
                ];
            }

            // Generate filename
            $fileName = 'pengajuan_pinjaman_' . date('Y-m-d_H-i-s');

            // Log export activity
            $syslog->log_insert('E', $dmenu, ucfirst($exportType) . ' Export: ' . $fileName, '1');

            // Use PhpSpreadsheet for export (following MSJ Framework pattern from MasterController)
            $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            $sheet->setTitle('Pengajuan Pinjaman');

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
            // Log error
            $syslog->log_insert('E', $dmenu, 'Export Error: ' . $e->getMessage(), '0');

            return redirect()->back()->with('error', 'Export failed: ' . $e->getMessage());
        }
    }
}
