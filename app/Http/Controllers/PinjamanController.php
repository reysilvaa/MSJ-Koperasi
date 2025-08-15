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
                'mpp.periode',
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

        // Get cicilan yang siap untuk dipotong gaji (jatuh tempo bulan ini atau sebelumnya)
        $bulan_ini = date('Y-m-01'); // Awal bulan ini
        $akhir_bulan_ini = date('Y-m-t'); // Akhir bulan ini

        $data['cicilan_potong_gaji'] = DB::table('cicilan_pinjaman as cp')
            ->leftJoin('pinjaman as p', 'cp.pinjaman_id', '=', 'p.id')
            ->leftJoin('anggota as a', 'p.anggota_id', '=', 'a.id')
            ->select(
                'cp.*',
                'p.nomor_pinjaman',
                'a.nama_lengkap',
                'a.nomor_anggota',
                DB::raw('DATE_FORMAT(cp.tanggal_jatuh_tempo, "%Y-%m") as bulan_tempo'),
                DB::raw('DATE_FORMAT(cp.tanggal_jatuh_tempo, "%M %Y") as nama_bulan')
            )
            ->where('cp.status', 'belum_bayar')
            ->where('cp.tanggal_jatuh_tempo', '<=', $akhir_bulan_ini)
            ->where('p.status', 'aktif')
            ->where('p.isactive', '1')
            ->orderBy('cp.tanggal_jatuh_tempo')
            ->orderBy('a.nama_lengkap')
            ->get();

        // Group cicilan by month untuk tampilan
        $data['cicilan_by_month'] = $data['cicilan_potong_gaji']->groupBy('bulan_tempo');

        // Summary untuk potong gaji
        $data['summary_potong_gaji'] = [
            'total_cicilan' => $data['cicilan_potong_gaji']->count(),
            'total_nominal' => $data['cicilan_potong_gaji']->sum('nominal_angsuran'),
            'jumlah_anggota' => $data['cicilan_potong_gaji']->unique('nama_lengkap')->count(),
        ];

        // Get summary statistics
        $data['stats'] = [
            'total_pinjaman_aktif' => $data['pinjaman_list']->where('status', 'aktif')->count(),
            'total_cicilan_pending' => $data['cicilan_potong_gaji']->count(),
            'total_cicilan_lunas' => $data['pinjaman_list']->sum('cicilan_lunas'),
            'total_terbayar' => $data['pinjaman_list']->sum('total_terbayar'),
        ];

        return view($data['url'], $data);
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
            $data['url_menu'] = 'error';
            $data['title_group'] = 'Error';
            $data['title_menu'] = 'Error';
            $data['errorpages'] = 'ID tidak valid!';
            return view("pages.errorpages", $data);
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
                'a.no_hp',
                'a.alamat',
                'mpp.periode',
                'pp.id as pengajuan_id',
                'pp.tujuan_pinjaman'
            )
            ->where('p.id', $id)
            ->first();

        if (!$data['pinjaman']) {
            $data['url_menu'] = 'error';
            $data['title_group'] = 'Error';
            $data['title_menu'] = 'Error';
            $data['errorpages'] = 'Data pinjaman tidak ditemukan!';
            return view("pages.errorpages", $data);
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

        return view($data['url'], $data);
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
            ->leftJoin('master_paket_pinjaman as mpp', 'p.id_paket_pinjaman', '=', 'mpp.id')
            ->select('p.*', 'a.nama_lengkap', 'a.nomor_anggota', 'mpp.periode')
            ->where('p.status', 'aktif')
            ->where('p.isactive', '1')
            ->whereNotExists(function($query) {
                $query->select(DB::raw(1))
                      ->from('cicilan_pinjaman')
                      ->whereRaw('cicilan_pinjaman.pinjaman_id = p.id');
            })
            ->get();

        return view($data['url'], $data);
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
            Session::flash('message', 'Data tidak valid: ' . $validator->errors()->first());
            Session::flash('class', 'danger');
            return redirect()->back()->withInput();
        }

        $pinjaman_id = request('pinjaman_id');

        // Get pinjaman data with relationships
        $pinjaman = DB::table('pinjaman as p')
            ->leftJoin('anggota as a', 'p.anggota_id', '=', 'a.id')
            ->select('p.*', 'a.nama_lengkap', 'a.nomor_anggota')
            ->where('p.id', $pinjaman_id)
            ->first();

        if (!$pinjaman) {
            $data['url_menu'] = 'error';
            $data['title_group'] = 'Error';
            $data['title_menu'] = 'Error';
            $data['errorpages'] = 'Data pinjaman tidak ditemukan!';
            return view("pages.errorpages", $data);
        }

        // Check if pinjaman is active
        if ($pinjaman->status != 'aktif') {
            $data['url_menu'] = 'error';
            $data['title_group'] = 'Error';
            $data['title_menu'] = 'Error';
            $data['errorpages'] = 'Hanya pinjaman dengan status aktif yang dapat dibuatkan jadwal cicilan!';
            return view("pages.errorpages", $data);
        }

        // Check if cicilan already exists
        $existing_cicilan = DB::table('cicilan_pinjaman')->where('pinjaman_id', $pinjaman_id)->count();
        if ($existing_cicilan > 0) {
            $data['url_menu'] = 'error';
            $data['title_group'] = 'Error';
            $data['title_menu'] = 'Error';
            $data['errorpages'] = 'Jadwal cicilan sudah ada untuk pinjaman ini!';
            return view("pages.errorpages", $data);
        }

        // Generate cicilan schedule
        $tenor_bulan = $pinjaman->tenor_bulan;
        $tanggal_mulai = $pinjaman->tanggal_pencairan;

        // Use existing calculated amounts from pinjaman table
        $angsuran_pokok = $pinjaman->angsuran_pokok;
        $angsuran_bunga = $pinjaman->angsuran_bunga;
        $total_angsuran = $pinjaman->total_angsuran;

        try {
            DB::beginTransaction();

            for ($i = 1; $i <= $tenor_bulan; $i++) {
                // Untuk potong gaji, set tanggal jatuh tempo di akhir bulan
                // Memberikan fleksibilitas untuk pembayaran kapan saja dalam bulan tersebut
                $tanggal_jatuh_tempo = date('Y-m-t', strtotime($tanggal_mulai . " +{$i} month")); // Akhir bulan

                DB::table('cicilan_pinjaman')->insert([
                    'pinjaman_id' => $pinjaman_id,
                    'angsuran_ke' => $i,
                    'tanggal_jatuh_tempo' => $tanggal_jatuh_tempo,
                    'nominal_pokok' => round($angsuran_pokok, 2),
                    'nominal_bunga' => round($angsuran_bunga, 2),
                    'nominal_denda' => 0,
                    'total_bayar' => round($total_angsuran, 2),
                    'nominal_dibayar' => 0,
                    'sisa_bayar' => round($total_angsuran, 2),
                    'status' => 'belum_bayar',
                    'hari_terlambat' => 0,
                    'metode_pembayaran' => null, // Akan diisi saat pembayaran
                    'keterangan' => 'Cicilan bulan ' . date('M Y', strtotime($tanggal_jatuh_tempo)),
                    'user_create' => $data['user_login']->username,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            DB::commit();

            $syslog->log_insert('C', $data['dmenu'], 'Cicilan Schedule Generated: ' . $pinjaman->nomor_pinjaman . ' (' . $tenor_bulan . ' cicilan)', '1');

            Session::flash('message', 'Jadwal cicilan berhasil dibuat untuk ' . $tenor_bulan . ' bulan!');
            Session::flash('class', 'success');

        } catch (\Exception $e) {
            DB::rollback();

            $syslog->log_insert('E', $data['dmenu'], 'Error Generate Cicilan: ' . $e->getMessage(), '0');

            Session::flash('message', 'Gagal membuat jadwal cicilan: ' . $e->getMessage());
            Session::flash('class', 'danger');
        }

        return redirect($data['url_menu']);
    }

    /**
     * Edit pinjaman untuk pembayaran cicilan - KOP203/edit
     */
    public function edit($data)
    {
        // function helper
        $data['format'] = new Format_Helper;

        // Decrypt ID
        try {
            $id = decrypt($data['idencrypt']);
        } catch (\Exception) {
            $data['url_menu'] = 'error';
            $data['title_group'] = 'Error';
            $data['title_menu'] = 'Error';
            $data['errorpages'] = 'ID tidak valid!';
            return view("pages.errorpages", $data);
        }

        // Get pinjaman data with cicilan yang belum dibayar
        $data['pinjaman'] = DB::table('pinjaman as p')
            ->leftJoin('anggota as a', 'p.anggota_id', '=', 'a.id')
            ->leftJoin('pengajuan_pinjaman as pp', 'p.pengajuan_pinjaman_id', '=', 'pp.id')
            ->leftJoin('master_paket_pinjaman as mpp', 'pp.paket_pinjaman_id', '=', 'mpp.id')
            ->select(
                'p.*',
                'a.nomor_anggota',
                'a.nama_lengkap',
                'a.email',
                'a.no_hp',
                'mpp.periode',
                'pp.id as pengajuan_id'
            )
            ->where('p.id', $id)
            ->first();

        if (!$data['pinjaman']) {
            $data['url_menu'] = 'error';
            $data['title_group'] = 'Error';
            $data['title_menu'] = 'Error';
            $data['errorpages'] = 'Data pinjaman tidak ditemukan!';
            return view("pages.errorpages", $data);
        }

        // Get cicilan yang belum dibayar
        $data['cicilan_pending'] = DB::table('cicilan_pinjaman')
            ->where('pinjaman_id', $id)
            ->where('status', 'belum_bayar')
            ->orderBy('angsuran_ke')
            ->get();

        // Get cicilan yang sudah dibayar (untuk history)
        $data['cicilan_lunas'] = DB::table('cicilan_pinjaman')
            ->where('pinjaman_id', $id)
            ->where('status', 'lunas')
            ->orderBy('angsuran_ke', 'desc')
            ->limit(5)
            ->get();

        // Calculate summary
        $data['summary'] = [
            'total_cicilan' => DB::table('cicilan_pinjaman')->where('pinjaman_id', $id)->count(),
            'cicilan_lunas' => DB::table('cicilan_pinjaman')->where('pinjaman_id', $id)->where('status', 'lunas')->count(),
            'cicilan_pending' => DB::table('cicilan_pinjaman')->where('pinjaman_id', $id)->where('status', 'belum_bayar')->count(),
            'total_terbayar' => DB::table('cicilan_pinjaman')->where('pinjaman_id', $id)->where('status', 'lunas')->sum('nominal_dibayar'),
            'sisa_pembayaran' => $data['pinjaman']->nominal_pinjaman - DB::table('cicilan_pinjaman')->where('pinjaman_id', $id)->where('status', 'lunas')->sum('nominal_dibayar'),
        ];

        return view($data['url'], $data);
    }



    /**
     * Update - Proses pembayaran cicilan - KOP203/update
     */
    public function update($data)
    {
        // function helper
        $syslog = new Function_Helper;

        // Validate input
        $validator = Validator::make(request()->all(), [
            'cicilan_id' => 'required|exists:cicilan_pinjaman,id',
            'nominal_dibayar' => 'required|numeric|min:1',
            'tanggal_bayar' => 'required|date',
            'metode_pembayaran' => 'required|in:tunai,transfer,potong_gaji',
            'keterangan' => 'nullable|string|max:255'
        ]);

        if ($validator->fails()) {
            Session::flash('message', 'Data tidak valid: ' . $validator->errors()->first());
            Session::flash('class', 'danger');
            return redirect()->back()->withInput();
        }

        $cicilan_id = request('cicilan_id');
        $nominal_dibayar = request('nominal_dibayar');
        $tanggal_bayar = request('tanggal_bayar');
        $metode_pembayaran = request('metode_pembayaran');
        $keterangan = request('keterangan');

        // Get cicilan data
        $cicilan = DB::table('cicilan_pinjaman')->where('id', $cicilan_id)->first();

        if (!$cicilan || $cicilan->status == 'lunas') {
            $data['url_menu'] = 'error';
            $data['title_group'] = 'Error';
            $data['title_menu'] = 'Error';
            $data['errorpages'] = 'Cicilan tidak ditemukan atau sudah lunas!';
            return view("pages.errorpages", $data);
        }

        // Update cicilan status
        $updated = DB::table('cicilan_pinjaman')
            ->where('id', $cicilan_id)
            ->update([
                'status' => 'lunas',
                'nominal_dibayar' => $nominal_dibayar,
                'tanggal_bayar' => $tanggal_bayar,
                'metode_pembayaran' => $metode_pembayaran,
                'keterangan' => $keterangan,
                'sisa_bayar' => 0,
                'updated_at' => now(),
                'user_update' => $data['user_login']->username
            ]);

        if ($updated) {
            // Check if all cicilan sudah lunas
            $remaining_cicilan = DB::table('cicilan_pinjaman')
                ->where('pinjaman_id', $cicilan->pinjaman_id)
                ->where('status', 'belum_bayar')
                ->count();

            // If all cicilan lunas, update pinjaman status
            if ($remaining_cicilan == 0) {
                DB::table('pinjaman')
                    ->where('id', $cicilan->pinjaman_id)
                    ->update([
                        'status' => 'lunas',
                        'updated_at' => now(),
                        'user_update' => $data['user_login']->username
                    ]);
            }

            //insert log
            $syslog->log_insert('U', $data['dmenu'], 'Pembayaran Cicilan ID: ' . $cicilan_id . ' Nominal: ' . number_format($nominal_dibayar), '1');

            Session::flash('message', 'Pembayaran cicilan berhasil diproses!');
            Session::flash('class', 'success');
        } else {
            Session::flash('message', 'Gagal memproses pembayaran cicilan!');
            Session::flash('class', 'danger');
        }

        return redirect($data['url_menu']);
    }

    /**
     * Potong gaji bulanan - KOP203/potong_gaji
     */
    public function potong_gaji($data)
    {
        // function helper
        $syslog = new Function_Helper;

        // Validasi input
        $validator = Validator::make(request()->all(), [
            'bulan_potong' => 'required|date_format:Y-m',
            'cicilan_ids' => 'required|array|min:1',
            'cicilan_ids.*' => 'exists:cicilan_pinjaman,id'
        ]);

        if ($validator->fails()) {
            Session::flash('message', 'Data tidak valid: ' . $validator->errors()->first());
            Session::flash('class', 'danger');
            return redirect()->back();
        }

        $bulan_potong = request('bulan_potong');
        $cicilan_ids = request('cicilan_ids');
        $tanggal_potong = date('Y-m-t', strtotime($bulan_potong . '-01')); // Akhir bulan

        $berhasil = 0;
        $gagal = 0;

        DB::beginTransaction();
        try {
            foreach ($cicilan_ids as $cicilan_id) {
                $cicilan = DB::table('cicilan_pinjaman')->where('id', $cicilan_id)->first();

                if ($cicilan && $cicilan->status == 'belum_bayar') {
                    // Update cicilan menjadi lunas dengan potong gaji
                    DB::table('cicilan_pinjaman')
                        ->where('id', $cicilan_id)
                        ->update([
                            'status' => 'lunas',
                            'nominal_dibayar' => $cicilan->nominal_angsuran,
                            'tanggal_dibayar' => $tanggal_potong,
                            'metode_pembayaran' => 'potong_gaji',
                            'keterangan' => 'Potong gaji bulan ' . date('F Y', strtotime($bulan_potong . '-01')),
                            'updated_at' => now(),
                            'user_update' => $data['user_login']->username
                        ]);

                    // Check if all cicilan sudah lunas untuk update status pinjaman
                    $remaining_cicilan = DB::table('cicilan_pinjaman')
                        ->where('pinjaman_id', $cicilan->pinjaman_id)
                        ->where('status', 'belum_bayar')
                        ->count();

                    if ($remaining_cicilan == 0) {
                        DB::table('pinjaman')
                            ->where('id', $cicilan->pinjaman_id)
                            ->update([
                                'status' => 'lunas',
                                'updated_at' => now(),
                                'user_update' => $data['user_login']->username
                            ]);
                    }

                    $berhasil++;
                } else {
                    $gagal++;
                }
            }

            DB::commit();

            // Log activity
            $syslog->log_insert('U', $data['dmenu'],
                'Potong Gaji Bulanan: ' . $berhasil . ' cicilan berhasil, ' . $gagal . ' gagal', '1');

            Session::flash('message',
                "Potong gaji berhasil diproses! {$berhasil} cicilan berhasil" .
                ($gagal > 0 ? ", {$gagal} cicilan gagal" : ""));
            Session::flash('class', 'success');

        } catch (\Exception $e) {
            DB::rollback();
            Session::flash('message', 'Gagal memproses potong gaji: ' . $e->getMessage());
            Session::flash('class', 'danger');
        }

        return redirect($data['url_menu']);
    }
}