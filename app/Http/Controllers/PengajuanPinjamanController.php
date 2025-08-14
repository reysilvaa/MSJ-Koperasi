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
                $q->where('nomor_pengajuan', 'like', "%$search%")
                  ->orWhereHas('anggota', function($q) use ($search) {
                      $q->where('nama_lengkap', 'like', "%$search%")
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

        return view('KOP002.pengajuanPinjaman.list', $data);
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

        // Get form data using Eloquent
        $data['anggota_list'] = Anggotum::where('status_keanggotaan', 'aktif')
            ->where('isactive', '1')
            ->select('id', 'nomor_anggota', 'nama_lengkap')
            ->get();

        $data['paket_list'] = MasterPaketPinjaman::where('isactive', '1')
            ->select('id', 'periode', 'bunga_per_bulan', 'stock_limit', 'stock_terpakai')
            ->get();

        // Static tenor options since we removed master_tenor table
        $data['tenor_list'] = collect([
            (object) ['id' => '6 bulan', 'nama_tenor' => '6 bulan', 'tenor_bulan' => 6],
            (object) ['id' => '10 bulan', 'nama_tenor' => '10 bulan', 'tenor_bulan' => 10],
            (object) ['id' => '12 bulan', 'nama_tenor' => '12 bulan', 'tenor_bulan' => 12],
        ]);

        $data['periode_list'] = PeriodePencairan::where('isactive', '1')
            ->where('tanggal_selesai', '>=', now())
            ->select('id', 'nama_periode', 'tanggal_mulai', 'tanggal_selesai')
            ->get();

        // Generate nomor pengajuan
        $data['nomor_pengajuan'] = $data['format']->IDFormat('KOP201');

        return view('KOP002.pengajuanPinjaman.add', $data);
    }

    /**
     * Store a newly created resource in storage - KOP201/store
     */
    public function store($data)
    {
        // Function helper
        $syslog = new Function_Helper;
        $data['format'] = new Format_Helper;

        // Validation rules sesuai business logic
        $validator = Validator::make(request()->all(), [
            'anggota_id' => 'required|exists:anggota,id',
            'paket_pinjaman_id' => 'required|exists:master_paket_pinjaman,id',
            'jumlah_paket_dipilih' => 'required|integer|min:1|max:40',
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
            Session::flash('message', 'Anda tidak memiliki akses untuk menambah data!');
            Session::flash('class', 'danger');
            return redirect($data['url_menu']);
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
            $bunga_per_bulan = $paket->bunga_per_bulan; // 1%
            $cicilan_per_bulan = ($jumlah_pinjaman * (1 + ($bunga_per_bulan/100))) / $tenor_bulan;
            $total_pembayaran = $cicilan_per_bulan * $tenor_bulan;

            // Check stock availability
            $stock_available = $paket->stock_limit - $paket->stock_terpakai;
            if ($stock_available < $jumlah_paket) {
                Session::flash('message', 'Stock paket tidak mencukupi! Tersedia: ' . $stock_available . ' paket');
                Session::flash('class', 'danger');
                return redirect()->back()->withInput();
            }

            // Check eligibility for top-up using Eloquent
            if (request('jenis_pengajuan') === 'top_up') {
                $active_loan = PengajuanPinjaman::where('anggota_id', request('anggota_id'))
                    ->where('status_pengajuan', 'disetujui')
                    ->whereHas('pinjamen', function($q) {
                        $q->where('status', 'aktif');
                    })
                    ->first();

                if (!$active_loan) {
                    Session::flash('message', 'Top-up hanya bisa dilakukan jika memiliki pinjaman aktif!');
                    Session::flash('class', 'danger');
                    return redirect()->back()->withInput();
                }
            }

            // Create pengajuan using Eloquent
            $pengajuan = PengajuanPinjaman::create([
                'nomor_pengajuan' => $data['format']->IDFormat('KOP201'),
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
                'status_pengajuan' => 'diajukan', // Auto submit sesuai requirement
                'status_pencairan' => 'belum_dicairkan',
                'tanggal_pengajuan' => now(),
                'isactive' => '1',
                'user_create' => $data['user_login']->username ?? 'system',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Update stock terpakai using Eloquent
            $paket->increment('stock_terpakai', $jumlah_paket);

            DB::commit();

            // Log success
            $syslog->log_insert('C', $data['dmenu'], 'Pengajuan Pinjaman Created: ' . $pengajuan->nomor_pengajuan, '1');

            Session::flash('message', 'Pengajuan pinjaman berhasil dibuat!');
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
            Session::flash('message', 'ID tidak valid!');
            Session::flash('class', 'danger');
            return redirect($data['url_menu']);
        }

        // Get pengajuan detail using Eloquent with relationships
        $pengajuan = PengajuanPinjaman::with(['anggota', 'paketPinjaman', 'periodePencairan'])
            ->find($id);

        if (!$pengajuan) {
            Session::flash('message', 'Data pengajuan tidak ditemukan!');
            Session::flash('class', 'danger');
            return redirect($data['url_menu']);
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
                Session::flash('message', 'Anda tidak memiliki akses untuk data ini!');
                Session::flash('class', 'danger');
                return redirect($data['url_menu']);
            }
        }

        // Set data for view compatibility
        $data['pengajuan'] = $pengajuan;
        $data['list'] = $pengajuan; // For view compatibility with $list variable

        // Get additional data for view using Eloquent
        $data['periode_list'] = PeriodePencairan::where('isactive', '1')
            ->select('id', 'nama_periode', 'tanggal_mulai', 'tanggal_selesai')
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
        $syslog->log_insert('R', $data['dmenu'], 'Pengajuan Pinjaman Detail Accessed: ' . $pengajuan->nomor_pengajuan, '1');

        return view('KOP002.pengajuanPinjaman.show', $data);
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
            Session::flash('message', 'ID tidak valid!');
            Session::flash('class', 'danger');
            return redirect($data['url_menu']);
        }

        // Get pengajuan data using Eloquent
        $pengajuan = PengajuanPinjaman::find($id);

        if (!$pengajuan) {
            Session::flash('message', 'Data pengajuan tidak ditemukan!');
            Session::flash('class', 'danger');
            return redirect($data['url_menu']);
        }

        // Check authorization
        if ($data['authorize']->edit == '0') {
            Session::flash('message', 'Anda tidak memiliki akses untuk edit data!');
            Session::flash('class', 'danger');
            return redirect($data['url_menu']);
        }

        // Check if editable (only draft and diajukan status)
        if (!in_array($pengajuan->status_pengajuan, ['draft', 'diajukan'])) {
            Session::flash('message', 'Pengajuan tidak dapat diedit pada status ini!');
            Session::flash('class', 'warning');
            return redirect($data['url_menu']);
        }

        $data['pengajuan'] = $pengajuan;

        // Get form data using Eloquent
        $data['anggota_list'] = Anggotum::where('status_keanggotaan', 'aktif')
            ->where('isactive', '1')
            ->select('id', 'nomor_anggota', 'nama_lengkap')
            ->get();

        $data['paket_list'] = MasterPaketPinjaman::where('isactive', '1')
            ->select('id', 'periode', 'bunga_per_bulan', 'stock_limit', 'stock_terpakai')
            ->get();

        // Static tenor options since we removed master_tenor table
        $data['tenor_list'] = collect([
            (object) ['id' => '6 bulan', 'nama_tenor' => '6 bulan', 'tenor_bulan' => 6],
            (object) ['id' => '10 bulan', 'nama_tenor' => '10 bulan', 'tenor_bulan' => 10],
            (object) ['id' => '12 bulan', 'nama_tenor' => '12 bulan', 'tenor_bulan' => 12],
        ]);

        // Get periode pencairan list
        $data['periode_list'] = PeriodePencairan::where('isactive', '1')
            ->select('id', 'nama_periode', 'tanggal_mulai', 'tanggal_selesai')
            ->get();

        return view('KOP002.pengajuanPinjaman.edit', $data);
    }

    /**
     * Update the specified resource in storage - KOP201/update
     */
    public function update($data)
    {
        // Function helper
        $syslog = new Function_Helper;
        $data['format'] = new Format_Helper;

        // Validation rules
        $validator = Validator::make(request()->all(), [
            'anggota_id' => 'required|exists:anggota,id',
            'paket_pinjaman_id' => 'required|exists:master_paket_pinjaman,id',
            'jumlah_paket_dipilih' => 'required|integer|min:1|max:40',
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

        // Decrypt ID
        try {
            $id = decrypt($data['idencrypt']);
        } catch (\Exception $e) {
            Session::flash('message', 'ID tidak valid!');
            Session::flash('class', 'danger');
            return redirect($data['url_menu']);
        }

        // Check authorization
        if ($data['authorize']->edit == '0') {
            Session::flash('message', 'Anda tidak memiliki akses untuk edit data!');
            Session::flash('class', 'danger');
            return redirect($data['url_menu']);
        }

        DB::beginTransaction();

        try {
            // Get pengajuan using Eloquent
            $pengajuan = PengajuanPinjaman::find($id);

            if (!$pengajuan) {
                Session::flash('message', 'Data pengajuan tidak ditemukan!');
                Session::flash('class', 'danger');
                return redirect($data['url_menu']);
            }

            // Check if editable
            if (!in_array($pengajuan->status_pengajuan, ['draft', 'diajukan'])) {
                Session::flash('message', 'Pengajuan tidak dapat diedit pada status ini!');
                Session::flash('class', 'warning');
                return redirect($data['url_menu']);
            }

            // Get paket data for calculation
            $paket = MasterPaketPinjaman::find(request('paket_pinjaman_id'));
            $tenor_pinjaman = request('tenor_pinjaman');
            $tenor_bulan = (int) filter_var($tenor_pinjaman, FILTER_SANITIZE_NUMBER_INT);
            $jumlah_paket = request('jumlah_paket_dipilih');

            // Calculate new amounts
            $nilai_per_paket = 500000;
            $jumlah_pinjaman = $jumlah_paket * $nilai_per_paket;
            $bunga_per_bulan = $paket->bunga_per_bulan;
            $cicilan_per_bulan = ($jumlah_pinjaman * (1 + ($bunga_per_bulan/100))) / $tenor_bulan;
            $total_pembayaran = $cicilan_per_bulan * $tenor_bulan;

            // Update stock if paket changed
            if ($pengajuan->paket_pinjaman_id != request('paket_pinjaman_id') || $pengajuan->jumlah_paket_dipilih != $jumlah_paket) {
                // Restore old stock
                $old_paket = MasterPaketPinjaman::find($pengajuan->paket_pinjaman_id);
                $old_paket->decrement('stock_terpakai', $pengajuan->jumlah_paket_dipilih);

                // Check new stock availability
                $stock_available = $paket->stock_limit - $paket->stock_terpakai;
                if ($stock_available < $jumlah_paket) {
                    Session::flash('message', 'Stock paket tidak mencukupi! Tersedia: ' . $stock_available . ' paket');
                    Session::flash('class', 'danger');
                    return redirect()->back()->withInput();
                }

                // Reserve new stock
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
            $syslog->log_insert('U', $data['dmenu'], 'Pengajuan Pinjaman Updated: ' . $pengajuan->nomor_pengajuan, '1');

            Session::flash('message', 'Pengajuan pinjaman berhasil diupdate!');
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
            Session::flash('message', 'ID tidak valid!');
            Session::flash('class', 'danger');
            return redirect($data['url_menu']);
        }

        // Check authorization
        if ($data['authorize']->delete == '0') {
            Session::flash('message', 'Anda tidak memiliki akses untuk hapus data!');
            Session::flash('class', 'danger');
            return redirect($data['url_menu']);
        }

        DB::beginTransaction();

        try {
            // Get pengajuan using Eloquent
            $pengajuan = PengajuanPinjaman::find($id);

            if (!$pengajuan) {
                Session::flash('message', 'Data pengajuan tidak ditemukan!');
                Session::flash('class', 'danger');
                return redirect($data['url_menu']);
            }

            // Check if deletable (only draft status)
            if ($pengajuan->status_pengajuan !== 'draft') {
                Session::flash('message', 'Hanya pengajuan dengan status draft yang dapat dihapus!');
                Session::flash('class', 'warning');
                return redirect($data['url_menu']);
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
            $syslog->log_insert('D', $data['dmenu'], 'Pengajuan Pinjaman Deleted: ' . $pengajuan->nomor_pengajuan, '1');

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

        switch ($action) {
            case 'get_paket_info':
                $paket = MasterPaketPinjaman::find(request('paket_id'));
                if ($paket) {
                    return response()->json([
                        'success' => true,
                        'data' => [
                            'bunga_per_bulan' => $paket->bunga_per_bulan,
                            'stock_available' => $paket->stock_limit - $paket->stock_terpakai,
                            'stock_limit' => $paket->stock_limit,
                            'stock_terpakai' => $paket->stock_terpakai,
                        ]
                    ]);
                }
                break;

            case 'check_anggota_eligibility':
                $anggotaId = request('anggota_id');
                if ($anggotaId) {
                    // Check if anggota has active loan for top-up eligibility
                    $activeLoan = PengajuanPinjaman::where('anggota_id', $anggotaId)
                        ->where('status_pengajuan', 'disetujui')
                        ->whereHas('pinjamen', function($q) {
                            $q->where('status', 'aktif');
                        })
                        ->first();

                    return response()->json([
                        'success' => true,
                        'can_top_up' => (bool)$activeLoan,
                        'message' => $activeLoan ?
                            'Anggota memiliki pinjaman aktif, dapat melakukan Top Up' :
                            'Anggota belum memiliki pinjaman aktif'
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
                    ->select('id', 'periode', 'bunga_per_bulan', 'stock_limit', 'stock_terpakai')
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
                    ->where('tanggal_selesai', '>=', now())
                    ->select('id', 'nama_periode', 'tanggal_mulai', 'tanggal_selesai')
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
