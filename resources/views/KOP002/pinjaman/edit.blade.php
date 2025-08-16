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
                    <h6 class="mb-0 text-dark">Pembayaran Cicilan Pinjaman</h6>
                    <p class="text-sm mb-0 text-secondary">Kelola pembayaran cicilan anggota</p>
                </div>
            </div>
        </div>
    </div>

    <div class="container-fluid py-4">
        <div class="row">
            {{-- Info Pinjaman --}}
            <div class="col-lg-4 col-md-6">
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
                        {{-- Data Pinjaman --}}
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
                                    <h4 class="font-weight-bolder text-info">{{ $summary['total_cicilan'] }}</h4>
                                    <span class="text-sm text-secondary">Total Cicilan</span>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="text-center">
                                    <h4 class="font-weight-bolder text-success">{{ $summary['cicilan_lunas'] }}</h4>
                                    <span class="text-sm text-secondary">Sudah Lunas</span>
                                </div>
                            </div>
                        </div>

                        <div class="row mt-3">
                            <div class="col-6">
                                <div class="text-center">
                                    <h4 class="font-weight-bolder text-warning">{{ $summary['cicilan_pending'] }}</h4>
                                    <span class="text-sm text-secondary">Belum Bayar</span>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="text-center">
                                    <h5 class="font-weight-bolder text-danger">{{ $format->CurrencyFormat($summary['sisa_pembayaran']) }}</h5>
                                    <span class="text-sm text-secondary">Sisa Bayar</span>
                                </div>
                            </div>
                        </div>

                        {{-- Progress Bar --}}
                        @php
                            $progress = $summary['total_cicilan'] > 0 ? ($summary['cicilan_lunas'] / $summary['total_cicilan']) * 100 : 0;
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
                    </div>
                </div>
            </div>

            {{-- Form Pembayaran Cicilan --}}
            <div class="col-lg-8 col-md-6">
                <div class="card h-100">
                    <div class="card-header pb-0 bg-gradient-info">
                        <div class="d-flex align-items-center">
                            <div class="icon icon-lg icon-shape bg-white shadow text-center border-radius-xl">
                                <i class="fas fa-money-bill-wave text-info text-lg opacity-10"></i>
                            </div>
                            <div class="ms-3">
                                <h6 class="mb-0 text-white">Pembayaran Cicilan</h6>
                                <p class="text-sm mb-0 text-white opacity-8">Proses pembayaran cicilan pinjaman</p>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        {{-- Alert Messages --}}
                        @include('components.alert')

                        @if($cicilan_pending->count() > 0)
                            <form role="form" method="POST" action="{{ url($url_menu . '/' . $idencrypt) }}" id="pembayaran-form">
                                @csrf
                                @method('PUT')

                                {{-- Pilih Cicilan --}}
                                <div class="row">
                                    <div class="col-12">
                                        <div class="form-group">
                                            <label class="form-control-label text-dark font-weight-bold">
                                                <i class="fas fa-list-ol me-1"></i>Pilih Cicilan
                                                <span class="text-danger">*</span>
                                            </label>
                                            <select class="form-control border px-3" name="cicilan_id" id="cicilan_id" required>
                                                <option value="">-- Pilih Cicilan yang akan dibayar --</option>
                                                @foreach($cicilan_pending as $cicilan)
                                                    <option value="{{ $cicilan->id }}"
                                                            data-nominal="{{ $cicilan->total_bayar }}"
                                                            data-jatuh-tempo="{{ date('d/m/Y', strtotime($cicilan->tanggal_jatuh_tempo)) }}">
                                                        Cicilan ke-{{ $cicilan->angsuran_ke }} - {{ $format->CurrencyFormat($cicilan->total_bayar) }}
                                                        (Jatuh Tempo: {{ date('d/m/Y', strtotime($cicilan->tanggal_jatuh_tempo)) }})
                                                    </option>
                                                @endforeach
                                            </select>
                                            <small class="text-muted">
                                                <i class="fas fa-info-circle me-1"></i>Pilih cicilan yang akan dibayar
                                            </small>
                                        </div>
                                    </div>
                                </div>

                                {{-- Nominal dan Tanggal --}}
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label class="form-control-label text-dark font-weight-bold">
                                                <i class="fas fa-money-bill me-1"></i>Nominal Dibayar
                                                <span class="text-danger">*</span>
                                            </label>
                                            <div class="input-group">
                                                <span class="input-group-text">Rp</span>
                                                <input class="form-control border" type="number" name="nominal_dibayar" id="nominal_dibayar"
                                                       step="0.01" min="1" placeholder="0" required>
                                            </div>
                                            <small class="text-muted">
                                                <i class="fas fa-calculator me-1"></i>Nominal akan terisi otomatis saat pilih cicilan
                                            </small>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label class="form-control-label text-dark font-weight-bold">
                                                <i class="fas fa-calendar me-1"></i>Tanggal Bayar
                                                <span class="text-danger">*</span>
                                            </label>
                                            <input class="form-control border" type="date" name="tanggal_bayar"
                                                   value="{{ date('Y-m-d') }}" required>
                                            <small class="text-muted">
                                                <i class="fas fa-clock me-1"></i>Tanggal pembayaran cicilan
                                            </small>
                                        </div>
                                    </div>
                                </div>

                                {{-- Metode Pembayaran --}}
                                <div class="row">
                                    <div class="col-12">
                                        <div class="form-group">
                                            <label class="form-control-label text-dark font-weight-bold">
                                                <i class="fas fa-credit-card me-1"></i>Metode Pembayaran
                                                <span class="text-danger">*</span>
                                            </label>
                                            <select class="form-control border px-3" name="metode_pembayaran" required>
                                                <option value="">-- Pilih Metode Pembayaran --</option>
                                                <option value="transfer">
                                                    <i class="fas fa-university"></i> Transfer Bank
                                                </option>
                                                <option value="potong_gaji">
                                                    <i class="fas fa-cut"></i> Potong Gaji
                                                </option>
                                            </select>
                                            <small class="text-muted">
                                                <i class="fas fa-info-circle me-1"></i>Pilih cara pembayaran cicilan
                                            </small>
                                        </div>
                                    </div>
                                </div>

                                {{-- Keterangan --}}
                                <div class="row">
                                    <div class="col-12">
                                        <div class="form-group">
                                            <label class="form-control-label text-dark font-weight-bold">
                                                <i class="fas fa-sticky-note me-1"></i>Keterangan
                                                <span class="text-muted">(Opsional)</span>
                                            </label>
                                            <textarea class="form-control border" name="keterangan" rows="3"
                                                      placeholder="Masukkan keterangan tambahan jika diperlukan..."></textarea>
                                            <small class="text-muted">
                                                <i class="fas fa-edit me-1"></i>Catatan tambahan untuk pembayaran ini
                                            </small>
                                        </div>
                                    </div>
                                </div>

                                {{-- Action Buttons --}}
                                <div class="row">
                                    <div class="col-12">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <small class="text-muted">
                                                    <i class="fas fa-shield-alt me-1"></i>
                                                    Pastikan data sudah benar sebelum memproses
                                                </small>
                                            </div>
                                            <div>
                                                <button type="button" class="btn btn-outline-secondary me-2" onclick="resetForm()">
                                                    <i class="fas fa-undo me-1"></i>Reset
                                                </button>
                                                <button type="submit" class="btn btn-success btn-lg">
                                                    <i class="fas fa-money-bill-wave me-2"></i>
                                                    Proses Pembayaran
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        @else
                            {{-- No Pending Cicilan Alert --}}
                            <div class="alert alert-success border-0 text-white" style="background: linear-gradient(135deg, #28a745 0%, #20c997 100%);">
                                <div class="d-flex align-items-center">
                                    <div class="icon icon-shape bg-white shadow text-center border-radius-xl me-3">
                                        <i class="fas fa-check-circle text-success text-lg opacity-10"></i>
                                    </div>
                                    <div>
                                        <h6 class="text-white mb-0">Semua Cicilan Sudah Lunas!</h6>
                                        <p class="text-white opacity-8 mb-0">
                                            Tidak ada cicilan yang perlu dibayar. Pinjaman ini telah diselesaikan.
                                        </p>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>

                {{-- History Pembayaran --}}
                @if($cicilan_lunas->count() > 0)
                    <div class="card mt-4">
                        <div class="card-header pb-0 bg-gradient-success">
                            <div class="d-flex align-items-center">
                                <div class="icon icon-lg icon-shape bg-white shadow text-center border-radius-xl">
                                    <i class="fas fa-history text-success text-lg opacity-10"></i>
                                </div>
                                <div class="ms-3">
                                    <h6 class="mb-0 text-white">History Pembayaran</h6>
                                    <p class="text-sm mb-0 text-white opacity-8">5 pembayaran cicilan terakhir</p>
                                </div>
                            </div>
                        </div>
                        <div class="card-body px-0 pt-0 pb-2">
                            <div class="table-responsive p-0">
                                <table class="table align-items-center mb-0">
                                    <thead>
                                        <tr>
                                            <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Cicilan</th>
                                            <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Nominal</th>
                                            <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Tanggal</th>
                                            <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Metode</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($cicilan_lunas as $cicilan)
                                            <tr>
                                                <td>
                                                    <div class="d-flex px-2 py-1">
                                                        <div class="d-flex flex-column justify-content-center">
                                                            <h6 class="mb-0 text-sm">Cicilan ke-{{ $cicilan->angsuran_ke }}</h6>
                                                            <p class="text-xs text-secondary mb-0">
                                                                {{ date('d M Y', strtotime($cicilan->tanggal_jatuh_tempo)) }}
                                                            </p>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td>
                                                    <p class="text-xs font-weight-bold mb-0">{{ $format->CurrencyFormat($cicilan->nominal_dibayar) }}</p>
                                                    <p class="text-xs text-secondary mb-0">Dibayar</p>
                                                </td>
                                                <td class="align-middle text-center text-sm">
                                                    <span class="text-secondary text-xs font-weight-bold">
                                                        {{ date('d/m/Y', strtotime($cicilan->tanggal_bayar)) }}
                                                    </span>
                                                </td>
                                                <td class="align-middle text-center">
                                                    @php
                                                        $metodeClass = match($cicilan->metode_pembayaran) {
                                                            'transfer' => 'bg-gradient-info',
                                                            'potong_gaji' => 'bg-gradient-warning',
                                                            default => 'bg-gradient-secondary'
                                                        };
                                                        $metodeIcon = match($cicilan->metode_pembayaran) {
                                                            'transfer' => 'fas fa-university',
                                                            'potong_gaji' => 'fas fa-cut',
                                                            default => 'fas fa-credit-card'
                                                        };
                                                    @endphp
                                                    <span class="badge {{ $metodeClass }} px-2 py-1">
                                                        <i class="{{ $metodeIcon }} me-1"></i>
                                                        {{ ucfirst(str_replace('_', ' ', $cicilan->metode_pembayaran)) }}
                                                    </span>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>

@endsection

@push('addjs')
<script>
$(document).ready(function() {
    // Auto fill nominal when cicilan selected
    $('#cicilan_id').on('change', function() {
        var selectedOption = $(this).find('option:selected');
        var nominal = selectedOption.data('nominal');

        if (nominal) {
            $('#nominal_dibayar').val(nominal);
        } else {
            $('#nominal_dibayar').val('');
        }
    });

    // Reset form function
    window.resetForm = function() {
        $('#pembayaran-form')[0].reset();
        $('#nominal_dibayar').val('');
    };

    // Form validation
    $('#pembayaran-form').on('submit', function(e) {
        var cicilan_id = $('#cicilan_id').val();
        var nominal = $('#nominal_dibayar').val();

        if (!cicilan_id) {
            e.preventDefault();
            alert('Pilih cicilan yang akan dibayar!');
            return false;
        }

        if (!nominal || nominal <= 0) {
            e.preventDefault();
            alert('Masukkan nominal pembayaran yang valid!');
            return false;
        }

        return confirm('Apakah Anda yakin ingin memproses pembayaran cicilan ini?');
    });
});
</script>
@endpush
