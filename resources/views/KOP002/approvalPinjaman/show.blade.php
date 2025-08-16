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
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="container-fluid py-4">
        <div class="row">
            {{-- Detail Pengajuan --}}
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header pb-0">
                        <h6>Detail Pengajuan Pinjaman</h6>
                        <p class="text-sm mb-0">ID: {{ $pengajuan->id }}</p>
                    </div>
                    <div class="card-body">
                        {{-- Alert Messages --}}
                        @include('components.alert')

                        <div class="row">
                            {{-- Data Anggota --}}
                            <div class="col-md-6">
                                <h6 class="text-sm font-weight-bold mb-3">Data Anggota</h6>
                                <div class="form-group">
                                    <label class="form-control-label">Nomor Anggota</label>
                                    <input class="form-control" type="text" value="{{ $pengajuan->anggotum->nomor_anggota ?? 'N/A' }}" readonly>
                                </div>
                                <div class="form-group">
                                    <label class="form-control-label">Nama Lengkap</label>
                                    <input class="form-control" type="text" value="{{ $pengajuan->anggotum->nama_lengkap ?? 'N/A' }}" readonly>
                                </div>
                                <div class="form-group">
                                    <label class="form-control-label">Email</label>
                                    <input class="form-control" type="text" value="{{ $pengajuan->anggotum->email ?? 'N/A' }}" readonly>
                                </div>
                                <div class="form-group">
                                    <label class="form-control-label">No. HP</label>
                                    <input class="form-control" type="text" value="{{ $pengajuan->anggotum->no_hp ?? 'N/A' }}" readonly>
                                </div>
                            </div>

                            {{-- Data Pinjaman --}}
                            <div class="col-md-6">
                                <h6 class="text-sm font-weight-bold mb-3">Data Pinjaman</h6>
                                <div class="form-group">
                                    <label class="form-control-label">Jenis Pengajuan</label>
                                    <input class="form-control" type="text" value="{{ ucfirst($pengajuan->jenis_pengajuan) }}" readonly>
                                </div>
                                <div class="form-group">
                                    <label class="form-control-label">Paket Pinjaman</label>
                                    <input class="form-control" type="text" value="Paket {{ $pengajuan->master_paket_pinjaman->periode ?? 'N/A' }} (1% per bulan)" readonly>
                                </div>
                                <div class="form-group">
                                    <label class="form-control-label">Tenor Pinjaman</label>
                                    <input class="form-control" type="text" value="{{ $pengajuan->tenor_pinjaman ?? 'N/A' }}" readonly>
                                </div>
                                <div class="form-group">
                                    <label class="form-control-label">Jumlah Paket</label>
                                    <input class="form-control" type="text" value="{{ $pengajuan->jumlah_paket_dipilih ?? 1 }} paket" readonly>
                                </div>
                                <div class="form-group">
                                    <label class="form-control-label">Periode Pencairan</label>
                                    <input class="form-control" type="text" value="{{ $pengajuan->periode_pencairan->nama_periode ?? 'Belum ditentukan' }}" readonly>
                                </div>
                                <div class="form-group">
                                    <label class="form-control-label">Ketersediaan Paket</label>
                                    <input class="form-control" type="text" value="{{ ($pengajuan->master_paket_pinjaman->stock_limit ?? 0) - ($pengajuan->master_paket_pinjaman->stock_terpakai ?? 0) }} dari {{ $pengajuan->master_paket_pinjaman->stock_limit ?? 0 }} tersisa" readonly>
                                </div>
                            </div>

                            {{-- Perhitungan --}}
                            <div class="col-md-12">
                                <h6 class="text-sm font-weight-bold mb-3 mt-3">Perhitungan Pinjaman</h6>
                                <div class="row">
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label class="form-control-label">Jumlah Pinjaman</label>
                                            <input class="form-control text-primary font-weight-bold" type="text"
                                                   value="Rp {{ number_format($pengajuan->jumlah_pinjaman, 0, ',', '.') }}" readonly>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label class="form-control-label">Bunga per Bulan</label>
                                            <input class="form-control" type="text" value="1%" readonly>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label class="form-control-label">Cicilan per Bulan</label>
                                            <input class="form-control text-warning font-weight-bold" type="text"
                                                   value="Rp {{ number_format($pengajuan->cicilan_per_bulan, 0, ',', '.') }}" readonly>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label class="form-control-label">Total Pembayaran</label>
                                            <input class="form-control text-danger font-weight-bold" type="text"
                                                   value="Rp {{ number_format($pengajuan->total_pembayaran, 0, ',', '.') }}" readonly>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- Tujuan Pinjaman --}}
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label class="form-control-label">Tujuan Pinjaman</label>
                                    <textarea class="form-control" rows="3" readonly>{{ $pengajuan->tujuan_pinjaman }}</textarea>
                                </div>
                            </div>

                            {{-- Status Current --}}
                            <div class="col-md-12">
                                <div class="alert alert-info">
                                    <strong>Status Saat Ini:</strong>
                                    <span class="badge bg-primary ms-2">{{ ucfirst(str_replace('_', ' ', $pengajuan->status_pengajuan)) }}</span>
                                    <br>
                                    <small>Tanggal Pengajuan: {{ date('d/m/Y H:i', strtotime($pengajuan->created_at)) }}</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Approval Panel --}}
            <div class="col-md-4">
                {{-- Form Approval --}}
                <div class="card">
                    <div class="card-header pb-0">
                        <h6>Form Approval</h6>
                        <p class="text-sm mb-0">Proses persetujuan pengajuan</p>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="{{ url($url_menu) }}" id="approval-form">
                            @csrf
                            <input type="hidden" name="pengajuan_id" value="{{ $pengajuan->id }}">

                            <div class="form-group">
                                <label class="form-control-label">Keputusan <span class="text-danger">*</span></label>
                                <div class="d-flex gap-2">
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="action" value="approve" id="approve" required>
                                        <label class="form-check-label text-success" for="approve">
                                            <i class="fas fa-check-circle me-1"></i> Setujui
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="action" value="reject" id="reject" required>
                                        <label class="form-check-label text-danger" for="reject">
                                            <i class="fas fa-times-circle me-1"></i> Tolak
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="form-control-label">Catatan</label>
                                <textarea class="form-control" name="catatan" rows="4"
                                          placeholder="Berikan catatan untuk keputusan Anda..."></textarea>
                            </div>

                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-paper-plane me-1"></i> Proses Approval
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                {{-- Approval History --}}
                <div class="card mt-3">
                    <div class="card-header pb-0">
                        <h6>Riwayat Approval</h6>
                    </div>
                    <div class="card-body">
                        @if($approval_history->count() > 0)
                            <div class="timeline timeline-one-side">
                                @foreach($approval_history as $history)
                                    <div class="timeline-block mb-3">
                                        <span class="timeline-step">
                                            <i class="fas fa-user-check text-success text-gradient"></i>
                                        </span>
                                        <div class="timeline-content">
                                            <h6 class="text-dark text-sm font-weight-bold mb-0">
                                                {{ $history->approver_name }} ({{ $history->approver_jabatan }})
                                            </h6>
                                            <p class="text-secondary font-weight-bold text-xs mt-1 mb-0">
                                                {{ ucfirst(str_replace('_', ' ', $history->status_approval)) }}
                                            </p>
                                            <p class="text-sm mt-3 mb-2">
                                                {{ $history->catatan ?: 'Tidak ada catatan' }}
                                            </p>
                                            <small class="text-secondary">{{ date('d/m/Y H:i', strtotime($history->created_at)) }}</small>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <p class="text-sm text-secondary">Belum ada riwayat approval</p>
                        @endif
                    </div>
                </div>

                {{-- Loan History --}}
                @if($loan_history->count() > 0)
                    <div class="card mt-3">
                        <div class="card-header pb-0">
                            <h6>Riwayat Pinjaman Anggota</h6>
                        </div>
                        <div class="card-body">
                            @foreach($loan_history as $loan)
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <div>
                                        <h6 class="text-sm mb-0">{{ $loan->nomor_pinjaman }}</h6>
                                        <p class="text-xs text-secondary mb-0">{{ $loan->periode }}</p>
                                    </div>
                                    <div class="text-end">
                                        @php
                                            $loan_status_config = match($loan->status) {
                                                'lunas' => ['class' => 'badge badge-sm bg-success text-white', 'icon' => 'fas fa-check-circle'],
                                                'aktif' => ['class' => 'badge badge-sm bg-info text-white', 'icon' => 'fas fa-play-circle'],
                                                default => ['class' => 'badge badge-sm bg-warning text-dark', 'icon' => 'fas fa-exclamation-triangle']
                                            };
                                        @endphp
                                        <span class="{{ $loan_status_config['class'] }}">
                                            <i class="{{ $loan_status_config['icon'] }} me-1"></i>
                                            {{ ucfirst($loan->status) }}
                                        </span>
                                        <p class="text-xs text-secondary mb-0">Rp {{ number_format($loan->nominal_pinjaman, 0, ',', '.') }}</p>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection

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
