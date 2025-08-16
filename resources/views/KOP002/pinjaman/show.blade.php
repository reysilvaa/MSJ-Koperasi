@extends('layouts.app', ['class' => 'g-sidenav-show bg-gray-100'])

@section('content')
    @include('layouts.navbars.auth.topnav', ['title' => $title_menu])

    {{-- Header Navigation --}}
    <div class="card shadow-lg mx-4">
        <div class="card-body p-3">
            <div class="row gx-4">
                <div class="col-lg-6">
                    <div class="nav-wrapper">
                        <button class="btn btn-secondary mb-0" onclick="history.back()">
                            <i class="fas fa-arrow-left me-1"></i>
                            <span class="font-weight-bold">Kembali</span>
                        </button>
                    </div>
                </div>
                <div class="col-lg-6 text-end">
                    <h6 class="mb-0 text-dark">Detail Jadwal Cicilan</h6>
                    <p class="text-sm mb-0 text-secondary">Lihat detail pembayaran cicilan pinjaman</p>
                </div>
            </div>
        </div>
    </div>

    <div class="container-fluid py-4">
        <div class="row">
            {{-- Info Pinjaman --}}
            <div class="col-lg-4">
                <div class="card h-100">
                    <div class="card-header pb-0 bg-gradient-primary">
                        <div class="d-flex align-items-center">
                            <div class="icon icon-lg icon-shape bg-white shadow text-center border-radius-xl">
                                <i class="fas fa-file-invoice-dollar text-primary text-lg opacity-10"></i>
                            </div>
                            <div class="ms-3">
                                <h6 class="mb-0 text-white">Informasi Pinjaman</h6>
                                <p class="text-sm mb-0 text-white opacity-8">Detail data pinjaman</p>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-12">
                                <div class="info-item mb-3">
                                    <label class="text-xs text-uppercase text-secondary font-weight-bolder opacity-7">Nomor Pinjaman</label>
                                    <h6 class="mb-0 font-weight-bold text-dark">{{ $pinjaman->nomor_pinjaman }}</h6>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-12">
                                <div class="info-item mb-3">
                                    <label class="text-xs text-uppercase text-secondary font-weight-bolder opacity-7">Anggota</label>
                                    <h6 class="mb-0 text-dark">{{ $pinjaman->nama_lengkap }}</h6>
                                    <span class="text-xs text-secondary">{{ $pinjaman->nomor_anggota }}</span>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-12">
                                <div class="info-item mb-3">
                                    <label class="text-xs text-uppercase text-secondary font-weight-bolder opacity-7">Nominal Pinjaman</label>
                                    <h5 class="mb-0 font-weight-bold text-success">{{ $format->CurrencyFormat($pinjaman->nominal_pinjaman) }}</h5>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-12">
                                <div class="info-item mb-3">
                                    <label class="text-xs text-uppercase text-secondary font-weight-bolder opacity-7">Status</label>
                                    <br>
                                    @php
                                        $status_config = match($pinjaman->status) {
                                            'aktif' => ['class' => 'badge bg-success text-white px-3 py-2', 'icon' => 'fas fa-play-circle'],
                                            'lunas' => ['class' => 'badge bg-info text-white px-3 py-2', 'icon' => 'fas fa-check-circle'],
                                            'bermasalah' => ['class' => 'badge bg-warning text-dark px-3 py-2', 'icon' => 'fas fa-exclamation-triangle'],
                                            default => ['class' => 'badge bg-secondary text-white px-3 py-2', 'icon' => 'fas fa-question']
                                        };
                                    @endphp
                                    <span class="{{ $status_config['class'] }}">
                                        <i class="{{ $status_config['icon'] }} me-1"></i>{{ ucfirst($pinjaman->status) }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        <hr class="horizontal dark">

                        {{-- Summary Cicilan --}}
                        <div class="row">
                            <div class="col-6">
                                <div class="text-center">
                                    <h4 class="font-weight-bolder text-info">{{ $summary['total_cicilan'] ?? 0 }}</h4>
                                    <span class="text-sm text-secondary">Total Cicilan</span>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="text-center">
                                    <h4 class="font-weight-bolder text-success">{{ $summary['cicilan_lunas'] ?? 0 }}</h4>
                                    <span class="text-sm text-secondary">Sudah Lunas</span>
                                </div>
                            </div>
                        </div>

                        <div class="row mt-3">
                            <div class="col-6">
                                <div class="text-center">
                                    <h4 class="font-weight-bolder text-warning">{{ $summary['cicilan_pending'] ?? 0 }}</h4>
                                    <span class="text-sm text-secondary">Belum Bayar</span>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="text-center">
                                    <h5 class="font-weight-bolder text-danger">{{ $format->CurrencyFormat($summary['sisa_pembayaran'] ?? 0) }}</h5>
                                    <span class="text-sm text-secondary">Sisa Bayar</span>
                                </div>
                            </div>
                        </div>

                        {{-- Progress Bar --}}
                        @php
                            $progress = ($summary['total_cicilan'] ?? 0) > 0 ? (($summary['cicilan_lunas'] ?? 0) / ($summary['total_cicilan'] ?? 1)) * 100 : 0;
                        @endphp
                        <div class="mt-4">
                            <div class="d-flex justify-content-between">
                                <span class="text-xs font-weight-bold">Progress Pembayaran</span>
                                <span class="text-xs font-weight-bold">{{ number_format($progress, 1) }}%</span>
                            </div>
                            <div class="progress mt-2">
                                <div class="progress-bar bg-gradient-success" role="progressbar"
                                     style="width: {{ $progress }}%" aria-valuenow="{{ $progress }}"
                                     aria-valuemin="0" aria-valuemax="100"></div>
                            </div>
                        </div>

                        {{-- Action Button --}}
                        @if(($summary['cicilan_pending'] ?? 0) > 0)
                            <div class="mt-4">
                                <a href="{{ url($url_menu . '/' . $idencrypt . '/edit') }}" class="btn btn-success w-100">
                                    <i class="fas fa-money-bill-wave me-2"></i>
                                    Bayar Cicilan
                                </a>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Jadwal Cicilan --}}
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header pb-0 bg-gradient-info">
                        <div class="d-flex align-items-center">
                            <div class="icon icon-lg icon-shape bg-white shadow text-center border-radius-xl">
                                <i class="fas fa-calendar-alt text-info text-lg opacity-10"></i>
                            </div>
                            <div class="ms-3">
                                <h6 class="mb-0 text-white">Jadwal Cicilan</h6>
                                <p class="text-sm mb-0 text-white opacity-8">Daftar cicilan dan status pembayaran</p>
                            </div>
                        </div>
                    </div>
                    <div class="card-body px-0 pt-0 pb-2">
                        @if(isset($cicilan_list) && $cicilan_list->count() > 0)
                            <div class="table-responsive p-0">
                                <table class="table align-items-center mb-0">
                                    <thead>
                                        <tr>
                                            <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Cicilan</th>
                                            <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Nominal</th>
                                            <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Jatuh Tempo</th>
                                            <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Status</th>
                                            <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Tgl Bayar</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($cicilan_list as $cicilan)
                                            <tr>
                                                <td>
                                                    <div class="d-flex px-2 py-1">
                                                        <div class="d-flex flex-column justify-content-center">
                                                            <h6 class="mb-0 text-sm">Cicilan ke-{{ $cicilan->angsuran_ke }}</h6>
                                                            <p class="text-xs text-secondary mb-0">
                                                                {{ $cicilan->tenor_bulan }} bulan
                                                            </p>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td>
                                                    <p class="text-xs font-weight-bold mb-0">{{ $format->CurrencyFormat($cicilan->nominal_angsuran) }}</p>
                                                    @if($cicilan->status == 'lunas' && $cicilan->nominal_dibayar)
                                                        <p class="text-xs text-success mb-0">Dibayar: {{ $format->CurrencyFormat($cicilan->nominal_dibayar) }}</p>
                                                    @endif
                                                </td>
                                                <td class="align-middle text-center text-sm">
                                                    <span class="text-secondary text-xs font-weight-bold">
                                                        {{ date('d/m/Y', strtotime($cicilan->tanggal_jatuh_tempo)) }}
                                                    </span>
                                                </td>
                                                <td class="align-middle text-center">
                                                    @php
                                                        $statusClass = match($cicilan->status) {
                                                            'lunas' => 'bg-gradient-success',
                                                            'belum_bayar' => 'bg-gradient-warning',
                                                            'terlambat' => 'bg-gradient-danger',
                                                            default => 'bg-gradient-secondary'
                                                        };
                                                        $statusIcon = match($cicilan->status) {
                                                            'lunas' => 'fas fa-check-circle',
                                                            'belum_bayar' => 'fas fa-clock',
                                                            'terlambat' => 'fas fa-exclamation-triangle',
                                                            default => 'fas fa-question-circle'
                                                        };
                                                    @endphp
                                                    <span class="badge {{ $statusClass }} px-2 py-1">
                                                        <i class="{{ $statusIcon }} me-1"></i>
                                                        {{ ucfirst(str_replace('_', ' ', $cicilan->status)) }}
                                                    </span>
                                                </td>
                                                <td class="align-middle text-center text-sm">
                                                    @if($cicilan->status == 'lunas' && $cicilan->tanggal_dibayar)
                                                        <span class="text-success text-xs font-weight-bold">
                                                            {{ date('d/m/Y', strtotime($cicilan->tanggal_dibayar)) }}
                                                        </span>
                                                    @else
                                                        <span class="text-secondary text-xs">-</span>
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="alert alert-warning mx-4">
                                <div class="d-flex align-items-center">
                                    <div class="icon icon-shape bg-white shadow text-center border-radius-xl me-3">
                                        <i class="fas fa-exclamation-triangle text-warning text-lg opacity-10"></i>
                                    </div>
                                    <div>
                                        <h6 class="text-dark mb-0">Belum Ada Jadwal Cicilan</h6>
                                        <p class="text-secondary mb-0">
                                            Jadwal cicilan belum dibuat untuk pinjaman ini.
                                            <a href="{{ url($url_menu . '/add') }}" class="text-primary">Generate jadwal cicilan</a>
                                        </p>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection
