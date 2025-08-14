<?php

namespace App\Http\Controllers;

use App\Helpers\Format_Helper;
use App\Helpers\Function_Helper;
use App\Models\PengajuanPinjaman;
use App\Models\Anggotum;
use App\Models\MasterPaketPinjaman;
use App\Models\PeriodePencairan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;

class PengajuanPinjamanController extends Controller
{
    /**
     * Display a listing of the resource - KOP201
     */
    public function index($data)
    {
        // function helper
        $syslog = new Function_Helper;
        $data['format'] = new Format_Helper;

        // Get pengajuan pinjaman data with relationships
        $data['pengajuan_list'] = DB::table('pengajuan_pinjaman as pp')
            ->leftJoin('anggota as a', 'pp.anggota_id', '=', 'a.id')
            ->leftJoin('master_paket_pinjaman as mpp', 'pp.paket_pinjaman_id', '=', 'mpp.id')
            ->leftJoin('periode_pencairan as pc', 'pp.periode_pencairan_id', '=', 'pc.id')
            ->select(
                'pp.*',
                'a.nomor_anggota',
                'a.nama_lengkap',
                'mpp.periode as nama_paket',
                'pc.nama_periode'
            )
            ->where('pp.isactive', '1')
            ->orderBy('pp.created_at', 'desc')
            ->get();

        // Get summary statistics
        $data['stats'] = [
            'total_pengajuan' => $data['pengajuan_list']->count(),
            'pending_approval' => $data['pengajuan_list']->where('status_pengajuan', 'diajukan')->count(),
            'approved' => $data['pengajuan_list']->where('status_pengajuan', 'disetujui')->count(),
            'rejected' => $data['pengajuan_list']->where('status_pengajuan', 'ditolak')->count(),
        ];

        return view('KOP002.pengajuanPinjaman.list', $data);
    }

    /**
     * Show the form for creating a new resource - KOP201/add
     */
    public function add($data)
    {
        // function helper
        $syslog = new Function_Helper;
        $data['format'] = new Format_Helper;

        // Get form data
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
        // function helper
        $syslog = new Function_Helper;
        $data['format'] = new Format_Helper;

        // Validation rules sesuai business logic
        $validator = Validator::make(request()->all(), [
            'anggota_id' => 'required|exists:anggota,id',
            'paket_pinjaman_id' => 'required|exists:master_paket_pinjaman,id',
            'jumlah_paket_dipilih' => 'required|integer|min:1|max:40',
            'tenor_pinjaman' => 'required|string|in:6 bulan,12 bulan,18 bulan,24 bulan',
            'tujuan_pinjaman' => 'required|string|max:500',
            'jenis_pengajuan' => 'required|in:baru,top_up',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        // Get paket and tenor data for calculation
        $paket = MasterPaketPinjaman::find(request('paket_pinjaman_id'));
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

        // Check eligibility for top-up (sesuai activity diagram)
        $is_eligible_topup = false;
        if (request('jenis_pengajuan') === 'top_up') {
            $active_loan = DB::table('pinjaman')
                ->where('anggota_id', request('anggota_id'))
                ->where('status_pinjaman', 'aktif')
                ->first();

            if ($active_loan) {
                $remaining_payments = DB::table('cicilan_pinjaman')
                    ->where('pinjaman_id', $active_loan->id)
                    ->where('status_pembayaran', 'belum_bayar')
                    ->count();

                $is_eligible_topup = $remaining_payments <= 2;
            }
        }

        // Prepare data for insert
        $insert_data = [
            'nomor_pengajuan' => $data['format']->IDFormat('KOP201'),
            'anggota_id' => request('anggota_id'),
            'paket_pinjaman_id' => request('paket_pinjaman_id'),
            'jumlah_paket_dipilih' => $jumlah_paket,
            'tenor_pinjaman' => request('tenor_pinjaman'),
            'jumlah_pinjaman' => $jumlah_pinjaman,
            'bunga_per_bulan' => $bunga_per_bulan,
            'cicilan_per_bulan' => $cicilan_per_bulan,
            'total_pembayaran' => $total_pembayaran,
            'tujuan_pinjaman' => request('tujuan_pinjaman'),
            'jenis_pengajuan' => request('jenis_pengajuan'),
            'status_pengajuan' => $is_eligible_topup ? 'disetujui' : 'diajukan',
            'tanggal_pengajuan' => now(),
            'user_create' => session('username'),
            'created_at' => now(),
            'updated_at' => now(),
        ];

        // Insert pengajuan
        $result = DB::table('pengajuan_pinjaman')->insert($insert_data);

        if ($result) {
            // Reserve stock temporarily
            DB::table('master_paket_pinjaman')
                ->where('id', request('paket_pinjaman_id'))
                ->increment('stock_terpakai', $jumlah_paket);

            // Log success
            $syslog->log_insert('C', $data['dmenu'], 'Pengajuan Pinjaman Created: ' . $insert_data['nomor_pengajuan'], '1');

            Session::flash('message', 'Pengajuan pinjaman berhasil dibuat!');
            Session::flash('class', 'success');
        } else {
            // Log error
            $syslog->log_insert('E', $data['dmenu'], 'Pengajuan Pinjaman Create Error', '0');

            Session::flash('message', 'Gagal membuat pengajuan pinjaman!');
            Session::flash('class', 'danger');
        }

        return redirect($data['url_menu']);
    }

    /**
     * Display the specified resource - KOP201/show
     */
    public function show($data)
    {
        // function helper
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

        // Get pengajuan detail with relationships
        $pengajuan = DB::table('pengajuan_pinjaman as pp')
            ->leftJoin('anggota as a', 'pp.anggota_id', '=', 'a.id')
            ->leftJoin('master_paket_pinjaman as mpp', 'pp.paket_pinjaman_id', '=', 'mpp.id')
            ->leftJoin('periode_pencairan as pc', 'pp.periode_pencairan_id', '=', 'pc.id')
            ->select(
                'pp.*',
                'a.nomor_anggota',
                'a.nama_lengkap',
                'a.email',
                'a.no_hp',
                'mpp.periode as nama_paket',
                'pc.nama_periode'
            )
            ->where('pp.id', $id)
            ->first();

        if (!$pengajuan) {
            Session::flash('message', 'Data pengajuan tidak ditemukan!');
            Session::flash('class', 'danger');
            return redirect($data['url_menu']);
        }

        // Set data for view compatibility
        $data['pengajuan'] = $pengajuan;
        $data['list'] = $pengajuan; // For view compatibility with $list variable

        // Get additional data for view (in case needed by the view)
        $data['periode_list'] = DB::table('periode_pencairan')
            ->where('isactive', '1')
            ->select('id', 'nama_periode', 'tanggal_mulai', 'tanggal_selesai')
            ->get();

        // Static tenor options
        $data['tenor_list'] = collect([
            (object) ['id' => '6 bulan', 'nama_tenor' => '6 bulan', 'tenor_bulan' => 6],
            (object) ['id' => '10 bulan', 'nama_tenor' => '10 bulan', 'tenor_bulan' => 10],
            (object) ['id' => '12 bulan', 'nama_tenor' => '12 bulan', 'tenor_bulan' => 12],
        ]);

        // Get approval history
        $data['approval_history'] = DB::table('approval_history')
            ->where('pengajuan_pinjaman_id', $id)
            ->orderBy('created_at', 'desc')
            ->get();

        return view('KOP002.pengajuanPinjaman.show', $data);
    }

    /**
     * Show the form for editing the specified resource - KOP201/edit
     */
    public function edit($data)
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

        // Get pengajuan data
        $data['pengajuan'] = PengajuanPinjaman::find($id);

        if (!$data['pengajuan']) {
            Session::flash('message', 'Data pengajuan tidak ditemukan!');
            Session::flash('class', 'danger');
            return redirect($data['url_menu']);
        }

        // Check if editable (only draft and diajukan status)
        if (!in_array($data['pengajuan']->status_pengajuan, ['draft', 'diajukan'])) {
            Session::flash('message', 'Pengajuan tidak dapat diedit pada status ini!');
            Session::flash('class', 'warning');
            return redirect($data['url_menu']);
        }

        // Get form data
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
        $data['periode_list'] = DB::table('periode_pencairan')
            ->where('isactive', '1')
            ->select('id', 'nama_periode', 'tanggal_mulai', 'tanggal_selesai')
            ->get();

        return view('KOP002.pengajuanPinjaman.edit', $data);
    }

    /**
     * Update the specified resource in storage - KOP201/update
     */
    public function update($data)
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

        // Get current pengajuan
        $pengajuan = PengajuanPinjaman::find($id);

        if (!$pengajuan) {
            Session::flash('message', 'Data pengajuan tidak ditemukan!');
            Session::flash('class', 'danger');
            return redirect($data['url_menu']);
        }

        // Validation
        $validator = Validator::make(request()->all(), [
            'anggota_id' => 'required|exists:anggota,id',
            'paket_pinjaman_id' => 'required|exists:master_paket_pinjaman,id',
            'jumlah_paket_dipilih' => 'required|integer|min:1|max:40',
            'tenor_pinjaman' => 'required|string|in:6 bulan,12 bulan,18 bulan,24 bulan',
            'tujuan_pinjaman' => 'required|string|max:500',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        // Recalculate if paket or tenor changed
        $paket = MasterPaketPinjaman::find(request('paket_pinjaman_id'));
        $tenor_pinjaman = request('tenor_pinjaman'); // e.g., "6 bulan", "12 bulan"
        $tenor_bulan = (int) filter_var($tenor_pinjaman, FILTER_SANITIZE_NUMBER_INT);
        $jumlah_paket = request('jumlah_paket_dipilih');

        $nilai_per_paket = 500000;
        $jumlah_pinjaman = $jumlah_paket * $nilai_per_paket;
        $bunga_per_bulan = $paket->bunga_per_bulan;
        $cicilan_per_bulan = ($jumlah_pinjaman * (1 + ($bunga_per_bulan/100))) / $tenor_bulan;
        $total_pembayaran = $cicilan_per_bulan * $tenor_bulan;

        // Update data
        $update_data = [
            'anggota_id' => request('anggota_id'),
            'paket_pinjaman_id' => request('paket_pinjaman_id'),
            'jumlah_paket_dipilih' => $jumlah_paket,
            'tenor_pinjaman' => request('tenor_pinjaman'),
            'jumlah_pinjaman' => $jumlah_pinjaman,
            'bunga_per_bulan' => $bunga_per_bulan,
            'cicilan_per_bulan' => $cicilan_per_bulan,
            'total_pembayaran' => $total_pembayaran,
            'tujuan_pinjaman' => request('tujuan_pinjaman'),
            'user_update' => session('username'),
            'updated_at' => now(),
        ];

        $result = DB::table('pengajuan_pinjaman')
            ->where('id', $id)
            ->update($update_data);

        if ($result) {
            $syslog->log_insert('U', $data['dmenu'], 'Pengajuan Pinjaman Updated: ' . $pengajuan->nomor_pengajuan, '1');
            Session::flash('message', 'Pengajuan pinjaman berhasil diupdate!');
            Session::flash('class', 'success');
        } else {
            $syslog->log_insert('E', $data['dmenu'], 'Pengajuan Pinjaman Update Error', '0');
            Session::flash('message', 'Gagal mengupdate pengajuan pinjaman!');
            Session::flash('class', 'danger');
        }

        return redirect($data['url_menu']);
    }

    /**
     * Remove the specified resource from storage - KOP201/destroy
     */
    public function destroy($data)
    {
        // function helper
        $syslog = new Function_Helper;

        // Decrypt ID
        try {
            $id = decrypt($data['idencrypt']);
        } catch (\Exception $e) {
            Session::flash('message', 'ID tidak valid!');
            Session::flash('class', 'danger');
            return redirect($data['url_menu']);
        }

        // Get pengajuan
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

        // Release reserved stock
        if ($pengajuan->paket_pinjaman_id && $pengajuan->jumlah_paket_dipilih) {
            DB::table('master_paket_pinjaman')
                ->where('id', $pengajuan->paket_pinjaman_id)
                ->decrement('stock_terpakai', $pengajuan->jumlah_paket_dipilih);
        }

        // Soft delete
        $result = DB::table('pengajuan_pinjaman')
            ->where('id', $id)
            ->update([
                'isactive' => '0',
                'user_update' => session('username'),
                'updated_at' => now()
            ]);

        if ($result) {
            $syslog->log_insert('D', $data['dmenu'], 'Pengajuan Pinjaman Deleted: ' . $pengajuan->nomor_pengajuan, '1');
            Session::flash('message', 'Pengajuan pinjaman berhasil dihapus!');
            Session::flash('class', 'success');
        } else {
            $syslog->log_insert('E', $data['dmenu'], 'Pengajuan Pinjaman Delete Error', '0');
            Session::flash('message', 'Gagal menghapus pengajuan pinjaman!');
            Session::flash('class', 'danger');
        }

        return redirect($data['url_menu']);
    }

    /**
     * AJAX endpoint for real-time calculation
     */
    public function ajax($data)
    {
        if (request()->has('calculate')) {
            $paket_id = request('paket_pinjaman_id');
            $tenor_pinjaman = request('tenor_pinjaman');
            $jumlah_paket = request('jumlah_paket_dipilih', 1);

            if ($paket_id && $tenor_pinjaman) {
                $paket = MasterPaketPinjaman::find($paket_id);
                $tenor_bulan = (int) filter_var($tenor_pinjaman, FILTER_SANITIZE_NUMBER_INT);

                if ($paket && $tenor_bulan > 0) {
                    $nilai_per_paket = 500000;
                    $jumlah_pinjaman = $jumlah_paket * $nilai_per_paket;
                    $bunga_per_bulan = $paket->bunga_per_bulan;
                    $cicilan_per_bulan = ($jumlah_pinjaman * (1 + ($bunga_per_bulan/100))) / $tenor_bulan;
                    $total_pembayaran = $cicilan_per_bulan * $tenor_bulan;

                    return response()->json([
                        'success' => true,
                        'jumlah_pinjaman' => number_format($jumlah_pinjaman, 0, ',', '.'),
                        'cicilan_per_bulan' => number_format($cicilan_per_bulan, 0, ',', '.'),
                        'total_pembayaran' => number_format($total_pembayaran, 0, ',', '.'),
                        'bunga_per_bulan' => $bunga_per_bulan,
                        'stock_available' => $paket->stock_limit - $paket->stock_terpakai
                    ]);
                }
            }
        }

        return response()->json(['success' => false]);
    }
}
