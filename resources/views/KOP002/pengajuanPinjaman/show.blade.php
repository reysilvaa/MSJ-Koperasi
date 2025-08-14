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
                        @if($authorize->edit == '1' && in_array($pengajuan->status_pengajuan, ['draft', 'diajukan']))
                            <button class="btn btn-warning mb-0" onclick="window.location='{{ url($url_menu . '/edit/' . $idencrypt) }}'">
                                <i class="fas fa-edit me-1"></i>
                                <span class="font-weight-bold">Edit</span>
                            </button>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="container-fluid py-4">
        <div class="row">
            {{-- Main Information --}}
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header pb-0">
                        <h6>Detail Pengajuan Pinjaman</h6>
                        <p class="text-sm mb-0">Informasi lengkap pengajuan pinjaman</p>
                    </div>
                    <div class="card-body">
                        {{-- Alert Messages --}}
                        @include('components.alert')

                        <div class="row">
                            {{-- ID Pengajuan --}}
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-control-label">ID Pengajuan</label>
                                    <input type="text" class="form-control" value="{{ $pengajuan->id }}" readonly>
                                </div>
                            </div>

                            {{-- Status Pengajuan --}}
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-control-label">Status Pengajuan</label>
                                    <div class="mt-2">
                                        @php
                                            $status_class = match($pengajuan->status_pengajuan) {
                                                'draft' => 'badge-secondary',
                                                'diajukan' => 'badge-info',
                                                'review_admin' => 'badge-warning',
                                                'review_panitia' => 'badge-warning',
                                                'review_ketua' => 'badge-primary',
                                                'disetujui' => 'badge-success',
                                                'ditolak' => 'badge-danger',
                                                default => 'badge-secondary'
                                            };
                                        @endphp
                                        <span class="badge {{ $status_class }} badge-lg">{{ ucfirst(str_replace('_', ' ', $pengajuan->status_pengajuan)) }}</span>
                                    </div>
                                </div>
                            </div>

                            {{-- Anggota --}}
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-control-label">Anggota</label>
                                    <input type="text" class="form-control"
                                           value="{{ $pengajuan->anggota->nama_lengkap ?? 'N/A' }} ({{ $pengajuan->anggota->nomor_anggota ?? 'N/A' }})" readonly>
                                </div>
                            </div>

                            {{-- Jenis Pengajuan --}}
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-control-label">Jenis Pengajuan</label>
                                    <input type="text" class="form-control" value="{{ ucfirst($pengajuan->jenis_pengajuan) }}" readonly>
                                </div>
                            </div>

                            {{-- Paket Pinjaman --}}
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-control-label">Paket Pinjaman</label>
                                    <input type="text" class="form-control"
                                           value="{{ $pengajuan->paketPinjaman->periode ?? 'N/A' }} ({{ $pengajuan->bunga_per_bulan }}% per bulan)" readonly>
                                </div>
                            </div>

                            {{-- Jumlah Paket --}}
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-control-label">Jumlah Paket</label>
                                    <input type="text" class="form-control" value="{{ $pengajuan->jumlah_paket_dipilih }} paket" readonly>
                                </div>
                            </div>

                            {{-- Tenor Pinjaman --}}
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-control-label">Tenor Pinjaman</label>
                                    <input type="text" class="form-control" value="{{ $pengajuan->tenor_pinjaman }}" readonly>
                                </div>
                            </div>

                            {{-- Periode Pencairan --}}
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-control-label">Periode Pencairan</label>
                                    <input type="text" class="form-control"
                                           value="{{ $pengajuan->periodePencairan->nama_periode ?? 'N/A' }}" readonly>
                                </div>
                            </div>

                            {{-- Tujuan Pinjaman --}}
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label class="form-control-label">Tujuan Pinjaman</label>
                                    <textarea class="form-control" rows="3" readonly>{{ $pengajuan->tujuan_pinjaman }}</textarea>
                                </div>
                            </div>

                            {{-- Tanggal Pengajuan --}}
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-control-label">Tanggal Pengajuan</label>
                                    <input type="text" class="form-control"
                                           value="{{ $pengajuan->tanggal_pengajuan ? date('d/m/Y H:i', strtotime($pengajuan->tanggal_pengajuan)) : 'N/A' }}" readonly>
                                </div>
                            </div>

                            {{-- Status Pencairan --}}
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-control-label">Status Pencairan</label>
                                    <input type="text" class="form-control" value="{{ ucfirst(str_replace('_', ' ', $pengajuan->status_pencairan)) }}" readonly>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Calculation Panel --}}
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header pb-0">
                        <h6>Perhitungan Pinjaman</h6>
                        <p class="text-sm mb-0">Detail perhitungan finansial</p>
                    </div>
                    <div class="card-body">
                        <div class="calculation-panel">
                            <div class="row">
                                <div class="col-12">
                                    <div class="info-item mb-3">
                                        <label class="text-sm font-weight-bold">Jumlah Pinjaman:</label>
                                        <h5 class="text-primary mb-0">{{ $format->CurrencyFormat($pengajuan->jumlah_pinjaman) }}</h5>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <div class="info-item mb-3">
                                        <label class="text-sm font-weight-bold">Bunga per Bulan:</label>
                                        <h6 class="text-info mb-0">{{ $pengajuan->bunga_per_bulan }}%</h6>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <div class="info-item mb-3">
                                        <label class="text-sm font-weight-bold">Cicilan per Bulan:</label>
                                        <h5 class="text-warning mb-0">{{ $format->CurrencyFormat($pengajuan->cicilan_per_bulan) }}</h5>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <div class="info-item mb-3">
                                        <label class="text-sm font-weight-bold">Total Pembayaran:</label>
                                        <h5 class="text-danger mb-0">{{ $format->CurrencyFormat($pengajuan->total_pembayaran) }}</h5>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Approval History --}}
                @if(isset($approval_history) && $approval_history->count() > 0)
                <div class="card mt-3">
                    <div class="card-header pb-0">
                        <h6>Riwayat Persetujuan</h6>
                    </div>
                    <div class="card-body">
                        @foreach($approval_history as $history)
                            <div class="timeline-item mb-3">
                                <div class="d-flex">
                                    <div class="flex-shrink-0">
                                        <div class="bg-primary rounded-circle p-2">
                                            <i class="fas fa-user text-white text-xs"></i>
                                        </div>
                                    </div>
                                    <div class="flex-grow-1 ms-3">
                                        <h6 class="mb-1">{{ $history->action }}</h6>
                                        <p class="text-sm text-muted mb-1">{{ $history->keterangan }}</p>
                                        <small class="text-xs text-secondary">{{ date('d/m/Y H:i', strtotime($history->created_at)) }}</small>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
                @endif
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
