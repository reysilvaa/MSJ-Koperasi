<?php

namespace App\Http\Controllers;

use App\Helpers\Format_Helper;
use App\Helpers\Function_Helper;
use App\Models\Pinjaman;
use App\Models\CicilanPinjaman;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Session;

class PinjamanController extends Controller
{
    /**
     * Display a listing of the resource - KOP203
     */
    public function index($data)
    {
        // function helper
        $syslog = new Function_Helper;
        $data['format'] = new Format_Helper;

        // Get pinjaman aktif data with relationships
        $data['pinjaman_list'] = DB::table('pinjaman as p')
            ->leftJoin('anggota as a', 'p.anggota_id', '=', 'a.id')
            ->leftJoin('pengajuan_pinjaman as pp', 'p.pengajuan_pinjaman_id', '=', 'pp.id')
            ->leftJoin('master_paket_pinjaman as mpp', 'pp.paket_pinjaman_id', '=', 'mpp.id')
            ->select(
                'p.*',
                'a.nomor_anggota',
                'a.nama_lengkap',
                'mpp.nama_paket',
                'pp.id as pengajuan_id',
                DB::raw('(SELECT COUNT(*) FROM cicilan_pinjaman WHERE pinjaman_id = p.id AND status = "lunas") as cicilan_lunas'),
                DB::raw('(SELECT COUNT(*) FROM cicilan_pinjaman WHERE pinjaman_id = p.id) as total_cicilan'),
                DB::raw('(SELECT SUM(nominal_dibayar) FROM cicilan_pinjaman WHERE pinjaman_id = p.id AND status = "lunas") as total_terbayar')
            )
            ->where('p.isactive', '1')
            ->orderBy('p.created_at', 'desc')
            ->get();

        // Calculate remaining payments for each loan
        foreach ($data['pinjaman_list'] as $pinjaman) {
            $pinjaman->sisa_cicilan = $pinjaman->total_cicilan - $pinjaman->cicilan_lunas;
            $pinjaman->sisa_pembayaran = $pinjaman->total_angsuran - ($pinjaman->total_terbayar ?? 0);
            $pinjaman->progress_percent = $pinjaman->total_cicilan > 0 ?
                round(($pinjaman->cicilan_lunas / $pinjaman->total_cicilan) * 100, 2) : 0;
        }

        // Get summary statistics
        $data['stats'] = [
            'total_pinjaman_aktif' => $data['pinjaman_list']->where('status_pinjaman', 'aktif')->count(),
            'total_nilai_pinjaman' => $data['pinjaman_list']->sum('jumlah_pinjaman'),
            'total_terbayar' => $data['pinjaman_list']->sum('total_terbayar'),
            'pinjaman_bermasalah' => $data['pinjaman_list']->where('status_pinjaman', 'bermasalah')->count(),
        ];

        return view('KOP002.pinjaman.list', $data);
    }

    /**
     * Display the specified resource - KOP203/show
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

        // Get pinjaman detail with all relationships
        $data['pinjaman'] = DB::table('pinjaman as p')
            ->leftJoin('anggota as a', 'p.anggota_id', '=', 'a.id')
            ->leftJoin('pengajuan_pinjaman as pp', 'p.pengajuan_pinjaman_id', '=', 'pp.id')
            ->leftJoin('master_paket_pinjaman as mpp', 'pp.paket_pinjaman_id', '=', 'mpp.id')
            ->select(
                'p.*',
                'a.nomor_anggota',
                'a.nama_lengkap',
                'a.email',
                'a.no_telepon',
                'a.alamat',
                'mpp.nama_paket',
                'pp.id as pengajuan_id',
                'pp.tujuan_pinjaman'
            )
            ->where('p.id', $id)
            ->first();

        if (!$data['pinjaman']) {
            Session::flash('message', 'Data pinjaman tidak ditemukan!');
            Session::flash('class', 'danger');
            return redirect($data['url_menu']);
        }

        // Get cicilan schedule
        $data['cicilan_list'] = DB::table('cicilan_pinjaman')
            ->where('pinjaman_id', $id)
            ->orderBy('angsuran_ke')
            ->get();

        // Calculate summary
        $data['summary'] = [
            'total_cicilan' => $data['cicilan_list']->count(),
            'cicilan_lunas' => $data['cicilan_list']->where('status', 'lunas')->count(),
            'cicilan_pending' => $data['cicilan_list']->where('status', 'belum_bayar')->count(),
            'total_terbayar' => $data['cicilan_list']->where('status', 'lunas')->sum('nominal_dibayar'),
            'sisa_pembayaran' => $data['pinjaman']->total_angsuran - $data['cicilan_list']->where('status', 'lunas')->sum('nominal_dibayar'),
        ];

        return view('KOP002.pinjaman.show', $data);
    }

    /**
     * Generate cicilan schedule - KOP203/add
     */
    public function add($data)
    {
        // function helper
        $syslog = new Function_Helper;
        $data['format'] = new Format_Helper;

        // Get pinjaman yang belum ada cicilannya
        $data['pinjaman_list'] = DB::table('pinjaman as p')
            ->leftJoin('anggota as a', 'p.anggota_id', '=', 'a.id')
            ->leftJoin('pengajuan_pinjaman as pp', 'p.pengajuan_pinjaman_id', '=', 'pp.id')
            ->leftJoin('master_paket_pinjaman as mpp', 'pp.paket_pinjaman_id', '=', 'mpp.id')
            ->select(
                'p.*',
                'a.nomor_anggota',
                'a.nama_lengkap',
                'mpp.nama_paket'
            )
            ->where('p.status', 'aktif')
            ->where('p.isactive', '1')
            ->whereNotExists(function($query) {
                $query->select(DB::raw(1))
                      ->from('cicilan_pinjaman')
                      ->whereRaw('cicilan_pinjaman.pinjaman_id = p.id');
            })
            ->get();

        return view('KOP002.pinjaman.add', $data);
    }

    /**
     * Generate cicilan schedule - KOP203/store
     */
    public function store($data)
    {
        // function helper
        $syslog = new Function_Helper;

        // Validation
        $validator = Validator::make(request()->all(), [
            'pinjaman_id' => 'required|exists:pinjaman,id',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $pinjaman_id = request('pinjaman_id');

        // Get pinjaman data
        $pinjaman = Pinjaman::find($pinjaman_id);

        if (!$pinjaman) {
            Session::flash('message', 'Data pinjaman tidak ditemukan!');
            Session::flash('class', 'danger');
            return redirect($data['url_menu']);
        }

        // Check if cicilan already exists
        $existing_cicilan = CicilanPinjaman::where('pinjaman_id', $pinjaman_id)->count();
        if ($existing_cicilan > 0) {
            Session::flash('message', 'Jadwal cicilan sudah ada untuk pinjaman ini!');
            Session::flash('class', 'warning');
            return redirect($data['url_menu']);
        }

        // Generate cicilan schedule
        $tenor_bulan = $pinjaman->tenor_bulan;
        $tanggal_mulai = $pinjaman->tanggal_pencairan;

        // Calculate per cicilan amounts
        $nominal_pokok = $pinjaman->nominal_pinjaman / $tenor_bulan;
        $nominal_bunga = ($pinjaman->nominal_pinjaman * ($pinjaman->bunga_per_bulan / 100));
        $total_bayar = $nominal_pokok + $nominal_bunga;

        for ($i = 1; $i <= $tenor_bulan; $i++) {
            $tanggal_jatuh_tempo = date('Y-m-d', strtotime($tanggal_mulai . " +{$i} month"));

            DB::table('cicilan_pinjaman')->insert([
                'pinjaman_id' => $pinjaman_id,
                'angsuran_ke' => $i,
                'tanggal_jatuh_tempo' => $tanggal_jatuh_tempo,
                'nominal_pokok' => $nominal_pokok,
                'nominal_bunga' => $nominal_bunga,
                'nominal_denda' => 0,
                'total_bayar' => $total_bayar,
                'nominal_dibayar' => 0,
                'sisa_bayar' => $total_bayar,
                'status' => 'belum_bayar',
                'hari_terlambat' => 0,
                'user_create' => session('username'),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $syslog->log_insert('C', $data['dmenu'], 'Cicilan Schedule Generated: ' . $pinjaman->nomor_pinjaman, '1');
        Session::flash('message', 'Jadwal cicilan berhasil dibuat!');
        Session::flash('class', 'success');

        return redirect($data['url_menu']);
    }

    /**
     * Update pinjaman status - KOP203/edit
     */
    public function edit($data)
    {
        // Decrypt ID
        try {
            $id = decrypt($data['idencrypt']);
        } catch (\Exception) {
            Session::flash('message', 'ID tidak valid!');
            Session::flash('class', 'danger');
            return redirect($data['url_menu']);
        }

        // Get pinjaman data
        $data['pinjaman'] = Pinjaman::with(['anggotum', 'masterPaketPinjaman'])->find($id);

        if (!$data['pinjaman']) {
            Session::flash('message', 'Data pinjaman tidak ditemukan!');
            Session::flash('class', 'danger');
            return redirect($data['url_menu']);
        }

        // Status options
        $data['status_options'] = [
            'aktif' => 'Aktif',
            'lunas' => 'Lunas',
            'bermasalah' => 'Bermasalah',
            'ditutup' => 'Ditutup',
        ];

        return view('KOP002.pinjaman.edit', $data);
    }

    /**
     * Update pinjaman status - KOP203/update
     */
    public function update($data)
    {
        // function helper
        $syslog = new Function_Helper;

        // Decrypt ID
        try {
            $id = decrypt($data['idencrypt']);
        } catch (\Exception) {
            Session::flash('message', 'ID tidak valid!');
            Session::flash('class', 'danger');
            return redirect($data['url_menu']);
        }

        // Validation
        $validator = Validator::make(request()->all(), [
            'status' => 'required|in:aktif,lunas,bermasalah,hapus_buku',
            'keterangan' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        // Update pinjaman
        $result = DB::table('pinjaman')
            ->where('id', $id)
            ->update([
                'status' => request('status'),
                'keterangan' => request('keterangan'),
                'user_update' => session('username'),
                'updated_at' => now(),
            ]);

        if ($result) {
            $pinjaman = Pinjaman::find($id);
            $syslog->log_insert('U', $data['dmenu'], 'Pinjaman Status Updated: ' . $pinjaman->nomor_pinjaman, '1');
            Session::flash('message', 'Status pinjaman berhasil diupdate!');
            Session::flash('class', 'success');
        } else {
            $syslog->log_insert('E', $data['dmenu'], 'Pinjaman Update Error', '0');
            Session::flash('message', 'Gagal mengupdate status pinjaman!');
            Session::flash('class', 'danger');
        }

        return redirect($data['url_menu']);
    }
}
