@extends('layouts.app', ['class' => 'g-sidenav-show bg-gray-100'])

@section('content')
    @include('layouts.navbars.auth.topnav', ['title' => $title_menu])

    <div class="card shadow-lg mx-4">
        <div class="card-body p-3">
            <div class="row gx-4">
                <div class="col-lg">
                    <div class="nav-wrapper">
                        <button class="btn btn-secondary mb-0" onclick="history.back()">
                            <i class="fas fa-circle-left me-1"></i>
                            <span class="font-weight-bold">Kembali</span>
                        </button>
                        @if($authorize->add == '1')
                            <button class="btn btn-primary mb-0" type="submit" form="pengajuan-form">
                                <i class="fas fa-floppy-disk me-1"></i>
                                <span class="font-weight-bold">Simpan Pengajuan</span>
                            </button>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="container-fluid py-4">
        <div class="row">
            {{-- Form Pengajuan --}}
            <div class="col-md-8">
                <div class="card">
                    <form role="form" method="POST" action="{{ url($url_menu) }}" id="pengajuan-form">
                        @csrf
                        <div class="card-header pb-0">
                            <h6>Form Pengajuan Pinjaman</h6>
                            <p class="text-sm mb-0">Isi form berikut untuk mengajukan pinjaman</p>
                        </div>
                        <div class="card-body">
                            {{-- Alert Messages --}}
                            @include('components.alert')

                            <div class="row">
                                {{-- Nomor Pengajuan (Auto Generated) --}}
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="form-control-label">Status</label>
                                        <input type="text" class="form-control" value="Pengajuan Baru" readonly>
                                    </div>
                                </div>

                                {{-- Jenis Pengajuan (Auto Detected) --}}
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="form-control-label">Jenis Pengajuan</label>
                                        <input type="text" class="form-control" id="jenis_pengajuan_display" value="Pinjaman Baru" readonly>
                                        <input type="hidden" name="jenis_pengajuan" id="jenis_pengajuan" value="baru">
                                        <p class="text-secondary text-xs pt-1 px-1" id="jenis_pengajuan_info">
                                            *) Sistem akan otomatis mendeteksi jenis pengajuan berdasarkan status anggota
                                        </p>
                                    </div>
                                </div>

                                {{-- Anggota --}}
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="form-control-label">Anggota <span class="text-danger">*</span></label>
                                        @if($is_anggota_biasa && $current_anggota)
                                            {{-- For anggota biasa role, show member name directly --}}
                                            <div class="form-control-static bg-light p-2 border rounded">
                                                <strong>{{ $current_anggota->nomor_anggota }} - {{ $current_anggota->nama_lengkap }}</strong>
                                            </div>
                                            <input type="hidden" name="anggota_id" value="{{ $current_anggota->id }}">
                                            <small class="text-muted">Nama anggota diambil otomatis dari akun login Anda</small>
                                        @else
                                                @if(!empty($is_anggota_role) && $is_anggota_role)
                                                    @if(!empty($anggota_not_found) && $anggota_not_found)
                                                        <div class="alert alert-danger">Data anggota Anda tidak ditemukan. Silakan hubungi admin.</div>
                                                    @else
                                                        <div class="input-group">
                                                            <input type="text" class="form-control" name="anggota_id_display" id="anggota_id_display"
                                                                   value="{{ $anggota_list[0]->nomor_anggota ?? '' }} - {{ $anggota_list[0]->nama_lengkap ?? '' }}" readonly>
                                                            <span class="input-group-text bg-secondary text-light">
                                                                <i class="fas fa-lock"></i>
                                                            </span>
                                                        </div>
                                                        <input type="hidden" name="anggota_id" id="anggota_id" value="{{ $anggota_list[0]->id ?? '' }}">
                                                    @endif
                                                @else
                                                    <div class="input-group">
                                                        <input type="text" class="form-control" name="anggota_id_display" id="anggota_id_display"
                                                               value="{{ old('anggota_id') ? ($anggota_list->where('id', old('anggota_id'))->first()->nomor_anggota ?? '') . ' - ' . ($anggota_list->where('id', old('anggota_id'))->first()->nama_lengkap ?? '') : '' }}"
                                                               placeholder="Pilih Anggota" readonly required>
                                                        <span class="input-group-text bg-primary text-light icon-modal-search"
                                                              data-bs-toggle="modal" data-bs-target="#searchModalAnggota"
                                                              style="cursor: pointer;">
                                                            <i class="fas fa-search"></i>
                                                        </span>
                                                    </div>
                                                    <input type="hidden" name="anggota_id" id="anggota_id" value="{{ old('anggota_id') }}" required>
                                                @endif
                                        @endif
                                    </div>
                                </div>

                                {{-- Paket Pinjaman --}}
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="form-control-label">Paket Pinjaman <span class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <input type="text" class="form-control" name="paket_pinjaman_id_display" id="paket_pinjaman_id_display"
                                                   value="{{ old('paket_pinjaman_id') ? ($paket_list->where('id', old('paket_pinjaman_id'))->first()->periode ?? '') . ' (1% per bulan)' : '' }}"
                                                   placeholder="Pilih Paket Pinjaman" readonly required>
                                            <span class="input-group-text bg-primary text-light icon-modal-search"
                                                  data-bs-toggle="modal" data-bs-target="#searchModalPaket"
                                                  style="cursor: pointer;">
                                                <i class="fas fa-search"></i>
                                            </span>
                                        </div>
                                        <input type="hidden" name="paket_pinjaman_id" id="paket_pinjaman_id" value="{{ old('paket_pinjaman_id') }}" required>
                                        <p class="text-secondary text-xs pt-1 px-1">*) Pilih paket sesuai kebutuhan</p>
                                    </div>
                                </div>

                                {{-- Jumlah Paket --}}
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="form-control-label">Jumlah Paket <span class="text-danger">*</span></label>
                                        <input type="number" class="form-control" name="jumlah_paket_dipilih"
                                               id="jumlah_paket_dipilih" min="1" max="40"
                                               value="{{ old('jumlah_paket_dipilih', 1) }}" required>
                                        <p class="text-secondary text-xs pt-1 px-1">*) 1 paket = Rp 500.000 (min: 1, max: 40 paket)</p>
                                        <small id="stock-info" class="text-info"></small>
                                    </div>
                                </div>

                                {{-- Tenor --}}
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="form-control-label">Tenor Pembayaran <span class="text-danger">*</span></label>
                                        <select class="form-control" name="tenor_pinjaman" id="tenor_pinjaman" required>
                                            <option value="">Pilih Tenor</option>
                                            @foreach($tenor_list as $tenor)
                                                <option value="{{ $tenor->id }}"
                                                        data-bulan="{{ $tenor->tenor_bulan }}"
                                                        {{ old('tenor_pinjaman') == $tenor->id ? 'selected' : '' }}>
                                                    {{ $tenor->nama_tenor }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                {{-- Periode Pencairan --}}
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="form-control-label">Periode Pencairan <span class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <input type="text" class="form-control" name="periode_pencairan_id_display" id="periode_pencairan_id_display"
                                                   value="{{ old('periode_pencairan_id') ? ($periode_list->where('id', old('periode_pencairan_id'))->first()->nama_periode ?? '') . ' (' . (\Carbon\Carbon::parse($periode_list->where('id', old('periode_pencairan_id'))->first()->tanggal_mulai ?? now())->format('d M Y')) . ' - ' . (\Carbon\Carbon::parse($periode_list->where('id', old('periode_pencairan_id'))->first()->tanggal_selesai ?? now())->format('d M Y')) . ')' : '' }}"
                                                   placeholder="Pilih Periode" readonly required>
                                            <span class="input-group-text bg-primary text-light icon-modal-search"
                                                  data-bs-toggle="modal" data-bs-target="#searchModalPeriode"
                                                  style="cursor: pointer;">
                                                <i class="fas fa-search"></i>
                                            </span>
                                        </div>
                                        <input type="hidden" name="periode_pencairan_id" id="periode_pencairan_id" value="{{ old('periode_pencairan_id') }}" required>
                                    </div>
                                </div>

                                {{-- Tujuan Pinjaman --}}
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label class="form-control-label">Tujuan Pinjaman <span class="text-danger">*</span></label>
                                        <textarea class="form-control" name="tujuan_pinjaman" rows="3"
                                                  maxlength="500" required>{{ old('tujuan_pinjaman') }}</textarea>
                                        <p class="text-secondary text-xs pt-1 px-1">*) Jelaskan tujuan penggunaan pinjaman (maksimal 500 karakter)</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            {{-- Calculation Panel --}}
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header pb-0">
                        <h6>Estimasi Perhitungan</h6>
                        <p class="text-sm mb-0">Perhitungan otomatis berdasarkan pilihan Anda</p>
                    </div>
                    <div class="card-body">
                        <div class="calculation-panel">
                            <div class="row">
                                <div class="col-12">
                                    <div class="info-item mb-3">
                                        <label class="text-sm font-weight-bold">Jumlah Pinjaman:</label>
                                        <h5 class="text-primary mb-0" id="display-jumlah-pinjaman">Rp 0</h5>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <div class="info-item mb-3">
                                        <label class="text-sm font-weight-bold">Bunga per Bulan:</label>
                                        <h6 class="text-info mb-0" id="display-bunga">1%</h6>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <div class="info-item mb-3">
                                        <label class="text-sm font-weight-bold">Cicilan per Bulan:</label>
                                        <h5 class="text-warning mb-0" id="display-cicilan">Rp 0</h5>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <div class="info-item mb-3">
                                        <label class="text-sm font-weight-bold">Total Pembayaran:</label>
                                        <h5 class="text-danger mb-0" id="display-total">Rp 0</h5>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Stock Information Panel - Only visible for non-member roles --}}
                @if(!$hide_stock_info)
                <div class="card mt-3">
                    <div class="card-header pb-0">
                        <h6>Informasi Stok Paket</h6>
                        <p class="text-sm mb-0">Informasi ketersediaan stok paket pinjaman</p>
                    </div>
                    <div class="card-body">
                        <div id="stock-information-panel" style="display: none;">
                            <div class="row">

                                <div class="col-6">
                                    <div class="info-item mb-3">
                                        <label class="text-sm font-weight-bold">Stok Tersedia:</label>
                                        <h5 class="text-success mb-0" id="display-stock-available">-</h5>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="info-item mb-3">
                                        <label class="text-sm font-weight-bold">Total Limit:</label>
                                        <h6 class="text-info mb-0" id="display-stock-limit">-</h6>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="info-item mb-3">
                                        <label class="text-sm font-weight-bold">Stok Terpakai:</label>
                                        <h6 class="text-warning mb-0" id="display-stock-used">-</h6>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="info-item mb-3">
                                        <label class="text-sm font-weight-bold">Permintaan Anda:</label>
                                        <h6 class="text-primary mb-0" id="display-requested-amount">-</h6>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-12">
                                    <div class="progress mb-2" style="height: 8px;">
                                        <div class="progress-bar" role="progressbar" id="stock-progress-bar"
                                             style="width: 0%" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">
                                        </div>
                                    </div>
                                    <div class="d-flex align-items-center justify-content-between">
                                        <small class="text-muted" id="stock-progress-text">Pilih paket untuk melihat informasi stok</small>
                                        <small class="text-muted" id="stock-usage-percentage"></small>
                                    </div>
                                </div>
                            </div>

                        </div>
                        <div id="stock-no-selection" class="text-center py-3">
                            <i class="fas fa-box-open text-muted" style="font-size: 2rem;"></i>
                            <p class="text-muted mt-2 mb-0">Pilih paket pinjaman untuk melihat informasi stok</p>
                        </div>
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Custom CSS for Stock Information Panel --}}
    @if(!$hide_stock_info)
    @push('css')
    <style>
        #stock-information-panel .info-item {
            border-left: 3px solid #e9ecef;
            padding-left: 12px;
            transition: border-color 0.3s ease;
        }

        #stock-information-panel .info-item:hover {
            border-left-color: #007bff;
        }

        .stock-status-good { border-left-color: #28a745 !important; }
        .stock-status-danger { border-left-color: #dc3545 !important; }

        #stock-no-selection {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            border-radius: 8px;
        }

        .progress {
            background-color: #e9ecef;
            border-radius: 4px;
        }

        .progress-bar {
            transition: width 0.6s ease;
        }
    </style>
    @endpush
    @endif

    {{-- Check flag js on dmenu --}}
    @if ($jsmenu == '1')
        @if (view()->exists("js.{$dmenu}"))
            @push('addjs')
                {{-- file js in folder (resources/views/js) --}}
                @include('js.' . $dmenu)
            @endpush
        @else
            @push('addjs')
                <script>
                    Swal.fire({
                        title: 'JS Not Found!!',
                        text: 'Please Create File JS',
                        icon: 'error',
                        confirmButtonColor: '#028284'
                    });
                </script>
            @endpush
        @endif
    @endif

    {{-- Search Modals --}}
    @if(!$is_anggota_biasa || (!empty($is_anggota_role) && !$is_anggota_role))
    <!-- Anggota Search Modal -->
    <div class="modal fade" id="searchModalAnggota" tabindex="-1" role="dialog" aria-labelledby="searchModalAnggotaLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="searchModalAnggotaLabel">Pilih Anggota</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body table-responsive">
                    <table class="table display" id="list_anggota_search">
                        <thead class="thead-light" style="background-color: #00b7bd4f;">
                            <tr>
                                <th width="20px">Action</th>
                                <th>Nomor Anggota</th>
                                <th>Nama Lengkap</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($anggota_list as $anggota)
                            <tr>
                                <td>
                                    <button type="button" class="btn btn-sm btn-primary select-anggota"
                                            data-id="{{ $anggota->id }}"
                                            data-display="{{ $anggota->nomor_anggota }} - {{ $anggota->nama_lengkap }}"
                                            data-bs-dismiss="modal">
                                        Pilih
                                    </button>
                                </td>
                                <td>{{ $anggota->nomor_anggota }}</td>
                                <td>{{ $anggota->nama_lengkap }}</td>
                                <td>{{ $anggota->status_anggota }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Paket Pinjaman Search Modal -->
    <div class="modal fade" id="searchModalPaket" tabindex="-1" role="dialog" aria-labelledby="searchModalPaketLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="searchModalPaketLabel">Pilih Paket Pinjaman</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body table-responsive">
                    <table class="table display" id="list_paket_search">
                        <thead class="thead-light" style="background-color: #00b7bd4f;">
                            <tr>
                                <th width="20px">Action</th>
                                <th>Periode</th>
                                <th>Bunga</th>
                                @if(!isset($hide_stock_info) || !$hide_stock_info)
                                <th>Stok Tersedia</th>
                                <th>Limit Stok</th>
                                @endif
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($paket_list as $paket)
                            <tr>
                                <td>
                                    <button type="button" class="btn btn-sm btn-primary select-paket"
                                            data-id="{{ $paket->id }}"
                                            data-display="{{ $paket->periode }} (1% per bulan)"
                                            data-bunga="1.0"
                                            @if(!isset($hide_stock_info) || !$hide_stock_info)
                                            data-stock="{{ $paket->stock_limit - $paket->stock_terpakai }}"
                                            data-stock-limit="{{ $paket->stock_limit }}"
                                            data-stock-terpakai="{{ $paket->stock_terpakai }}"
                                            @endif
                                            data-bs-dismiss="modal">
                                        Pilih
                                    </button>
                                </td>
                                <td>{{ $paket->periode }}</td>
                                <td>1% per bulan</td>
                                @if(!isset($hide_stock_info) || !$hide_stock_info)
                                <td>{{ $paket->stock_limit - $paket->stock_terpakai }}</td>
                                <td>{{ $paket->stock_limit }}</td>
                                @endif
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Periode Pencairan Search Modal -->
    <div class="modal fade" id="searchModalPeriode" tabindex="-1" role="dialog" aria-labelledby="searchModalPeriodeLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="searchModalPeriodeLabel">Pilih Periode Pencairan</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body table-responsive">
                    <table class="table display" id="list_periode_search">
                        <thead class="thead-light" style="background-color: #00b7bd4f;">
                            <tr>
                                <th width="20px">Action</th>
                                <th>Nama Periode</th>
                                <th>Tanggal Mulai</th>
                                <th>Tanggal Selesai</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($periode_list as $periode)
                            <tr>
                                <td>
                                    <button type="button" class="btn btn-sm btn-primary select-periode"
                                            data-id="{{ $periode->id }}"
                                            data-display="{{ $periode->nama_periode }} ({{ \Carbon\Carbon::parse($periode->tanggal_mulai)->format('d M Y') }} - {{ \Carbon\Carbon::parse($periode->tanggal_selesai)->format('d M Y') }})"
                                            data-bs-dismiss="modal">
                                        Pilih
                                    </button>
                                </td>
                                <td>{{ $periode->nama_periode }}</td>
                                <td>{{ \Carbon\Carbon::parse($periode->tanggal_mulai)->format('d M Y') }}</td>
                                <td>{{ \Carbon\Carbon::parse($periode->tanggal_selesai)->format('d M Y') }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection


