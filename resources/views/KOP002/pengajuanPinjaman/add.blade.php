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
                                                        <select class="form-control" name="anggota_id" id="anggota_id" required disabled>
                                                            @foreach($anggota_list as $anggota)
                                                                <option value="{{ $anggota->id }}" selected>
                                                                    {{ $anggota->nomor_anggota }} - {{ $anggota->nama_lengkap }}
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                        <input type="hidden" name="anggota_id" value="{{ $anggota_list[0]->id ?? '' }}">
                                                    @endif
                                                @else
                                                    <select class="form-control" name="anggota_id" id="anggota_id" required>
                                                        <option value="">Pilih Anggota</option>
                                                        @foreach($anggota_list as $anggota)
                                                            <option value="{{ $anggota->id }}" {{ old('anggota_id') == $anggota->id ? 'selected' : '' }}>
                                                                {{ $anggota->nomor_anggota }} - {{ $anggota->nama_lengkap }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                @endif
                                        @endif
                                    </div>
                                </div>

                                {{-- Paket Pinjaman --}}
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="form-control-label">Paket Pinjaman <span class="text-danger">*</span></label>
                                        <select class="form-control" name="paket_pinjaman_id" id="paket_pinjaman_id" required>
                                            <option value="">Pilih Paket Pinjaman</option>
                                            @foreach($paket_list as $paket)
                                                <option value="{{ $paket->id }}"
                                                        data-bunga="1.0"
                                                        @if(!isset($hide_stock_info) || !$hide_stock_info)
                                                        data-stock="{{ $paket->stock_limit - $paket->stock_terpakai }}"
                                                        data-stock-limit="{{ $paket->stock_limit }}"
                                                        data-stock-terpakai="{{ $paket->stock_terpakai }}"
                                                        @endif
                                                        {{ old('paket_pinjaman_id') == $paket->id ? 'selected' : '' }}>
                                                    {{ $paket->periode }} (1% per bulan)
                                                </option>
                                            @endforeach
                                        </select>
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
                                        <select class="form-control" name="periode_pencairan_id" id="periode_pencairan_id" required>
                                            <option value="">Pilih Periode</option>
                                            @foreach($periode_list as $periode)
                                                <option value="{{ $periode->id }}"
                                                        {{ old('periode_pencairan_id') == $periode->id ? 'selected' : '' }}>
                                                    {{ $periode->nama_periode }}
                                                    ({{ \Carbon\Carbon::parse($periode->tanggal_mulai)->format('d M Y') }} - {{ \Carbon\Carbon::parse($periode->tanggal_selesai)->format('d M Y') }})
                                                </option>
                                            @endforeach
                                        </select>
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
                        <h6>Simulasi Perhitungan</h6>
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

                {{-- Stock Information - Completely hidden as per koperasi system preferences --}}
                {{-- Stock validation disabled - no stock information displayed --}}
            </div>
        </div>
    </div>

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
@endsection


