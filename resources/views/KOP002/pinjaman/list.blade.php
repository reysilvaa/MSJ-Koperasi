@extends('layouts.app', ['class' => 'g-sidenav-show bg-gray-100'])

@section('content')
    @include('layouts.navbars.auth.topnav', ['title' => $title_menu])

    <div class="container-fluid py-4">
        {{-- Statistics Cards --}}
        <div class="row mb-4">
            <div class="col-xl-3 col-sm-6 mb-xl-0 mb-4">
                <div class="card">
                    <div class="card-body p-3">
                        <div class="row">
                            <div class="col-8">
                                <div class="numbers">
                                    <p class="text-sm mb-0 text-capitalize font-weight-bold">Pinjaman Aktif</p>
                                    <h5 class="font-weight-bolder mb-0">{{ $stats['total_pinjaman_aktif'] }}</h5>
                                </div>
                            </div>
                            <div class="col-4 text-end">
                                <div class="icon icon-shape bg-gradient-success shadow text-center border-radius-md">
                                    <i class="fas fa-money-check-alt text-lg opacity-10"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-sm-6 mb-xl-0 mb-4">
                <div class="card">
                    <div class="card-body p-3">
                        <div class="row">
                            <div class="col-8">
                                <div class="numbers">
                                    <p class="text-sm mb-0 text-capitalize font-weight-bold">Total Nilai</p>
                                    <h5 class="font-weight-bolder mb-0">Rp {{ number_format($stats['total_nilai_pinjaman'], 0, ',', '.') }}</h5>
                                </div>
                            </div>
                            <div class="col-4 text-end">
                                <div class="icon icon-shape bg-gradient-primary shadow text-center border-radius-md">
                                    <i class="fas fa-coins text-lg opacity-10"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-sm-6 mb-xl-0 mb-4">
                <div class="card">
                    <div class="card-body p-3">
                        <div class="row">
                            <div class="col-8">
                                <div class="numbers">
                                    <p class="text-sm mb-0 text-capitalize font-weight-bold">Total Terbayar</p>
                                    <h5 class="font-weight-bolder mb-0">Rp {{ number_format($stats['total_terbayar'], 0, ',', '.') }}</h5>
                                </div>
                            </div>
                            <div class="col-4 text-end">
                                <div class="icon icon-shape bg-gradient-info shadow text-center border-radius-md">
                                    <i class="fas fa-hand-holding-usd text-lg opacity-10"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-sm-6">
                <div class="card">
                    <div class="card-body p-3">
                        <div class="row">
                            <div class="col-8">
                                <div class="numbers">
                                    <p class="text-sm mb-0 text-capitalize font-weight-bold">Bermasalah</p>
                                    <h5 class="font-weight-bolder mb-0">{{ $stats['pinjaman_bermasalah'] }}</h5>
                                </div>
                            </div>
                            <div class="col-4 text-end">
                                <div class="icon icon-shape bg-gradient-danger shadow text-center border-radius-md">
                                    <i class="fas fa-exclamation-triangle text-lg opacity-10"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Main Content --}}
        <div class="row">
            <div class="col-12">
                <div class="card mb-4">
                    <div class="card-header pb-0">
                        <div class="d-flex justify-content-between align-items-center">
                            <h6>{{ $title_menu }}</h6>
                            @if($authorize->add == '1')
                                <a href="{{ url($url_menu . '/add') }}" class="btn btn-primary btn-sm">
                                    <i class="fas fa-plus me-1"></i> Generate Cicilan
                                </a>
                            @endif
                        </div>
                    </div>
                    <div class="card-body px-0 pt-0 pb-2">
                        {{-- Alert Messages --}}
                        @include('components.alert')

                        <div class="table-responsive p-0">
                            <table class="table align-items-center mb-0" id="pinjaman-table">
                                <thead>
                                    <tr>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">No. Pinjaman</th>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Anggota</th>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Paket</th>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Jumlah Pinjaman</th>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Progress</th>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Status</th>
                                        <th class="text-secondary opacity-7">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($pinjaman_list as $pinjaman)
                                        <tr>
                                            <td>
                                                <div class="d-flex px-2 py-1">
                                                    <div class="d-flex flex-column justify-content-center">
                                                        <h6 class="mb-0 text-sm">{{ $pinjaman->nomor_pinjaman }}</h6>
                                                        <p class="text-xs text-secondary mb-0">ID Pengajuan: {{ $pinjaman->pengajuan_id ?? $pinjaman->pengajuan_pinjaman_id }}</p>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="d-flex px-2 py-1">
                                                    <div class="d-flex flex-column justify-content-center">
                                                        <h6 class="mb-0 text-sm">{{ $pinjaman->nama_lengkap }}</h6>
                                                        <p class="text-xs text-secondary mb-0">{{ $pinjaman->nomor_anggota }}</p>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <p class="text-xs font-weight-bold mb-0">{{ $pinjaman->periode }}</p>
                                                <p class="text-xs text-secondary mb-0">{{ $pinjaman->tenor_bulan }} bulan</p>
                                            </td>
                                            <td>
                                                <p class="text-xs font-weight-bold mb-0">Rp {{ number_format($pinjaman->jumlah_pinjaman, 0, ',', '.') }}</p>
                                                <p class="text-xs text-secondary mb-0">Cicilan: Rp {{ number_format($pinjaman->cicilan_per_bulan, 0, ',', '.') }}</p>
                                            </td>
                                            <td class="align-middle">
                                                <div class="d-flex align-items-center">
                                                    <span class="me-2 text-xs font-weight-bold">{{ $pinjaman->progress_percent }}%</span>
                                                    <div class="progress" style="width: 60px; height: 6px;">
                                                        <div class="progress-bar bg-gradient-{{ $pinjaman->progress_percent >= 80 ? 'success' : ($pinjaman->progress_percent >= 50 ? 'warning' : 'info') }}"
                                                             style="width: {{ $pinjaman->progress_percent }}%"></div>
                                                    </div>
                                                </div>
                                                <p class="text-xs text-secondary mb-0">{{ $pinjaman->cicilan_lunas }}/{{ $pinjaman->total_cicilan }} cicilan</p>
                                            </td>
                                            <td class="align-middle text-center text-sm">
                                                @php
                                                    $status_class = match($pinjaman->status) {
                                                        'aktif' => 'bg-gradient-success',
                                                        'lunas' => 'bg-gradient-info',
                                                        'bermasalah' => 'bg-gradient-danger',
                                                        'hapus_buku' => 'bg-gradient-dark',
                                                        default => 'bg-gradient-secondary'
                                                    };
                                                @endphp
                                                <span class="badge badge-sm {{ $status_class }}">{{ ucfirst(str_replace('_', ' ', $pinjaman->status)) }}</span>
                                            </td>
                                            <td class="align-middle">
                                                <a href="{{ url($url_menu . '/show/' . encrypt($pinjaman->id)) }}"
                                                   class="btn btn-info btn-sm mb-0" title="Lihat Detail">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                @if($authorize->edit == '1')
                                                    <a href="{{ url($url_menu . '/edit/' . encrypt($pinjaman->id)) }}"
                                                       class="btn btn-warning btn-sm mb-0" title="Edit Status">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('js')
    <script>
        $(document).ready(function() {
            // Initialize DataTable
            $('#pinjaman-table').DataTable({
                "language": {
                    "search": "Cari :",
                    "lengthMenu": "Tampilkan _MENU_ baris",
                    "zeroRecords": "Tidak ada data pinjaman",
                    "info": "Menampilkan _START_ sampai _END_ dari _TOTAL_ pinjaman",
                    "infoEmpty": "Tidak ada data",
                    "infoFiltered": "(difilter dari _MAX_ total pinjaman)"
                },
                "pageLength": 10,
                "responsive": true,
                "order": [[0, "desc"]], // Sort by nomor pinjaman
                "columnDefs": [
                    { "orderable": false, "targets": 6 } // Disable sorting on Action column
                ]
            });
        });
    </script>
@endpush
