@extends('layouts.app', ['class' => 'g-sidenav-show bg-gray-100'])

@section('content')
    @include('layouts.navbars.auth.topnav', ['title' => $title_menu])
    
    <div class="container-fluid py-4">
        {{-- Header --}}
        <div class="row">
            <div class="col-12">
                <div class="card mb-4">
                    <div class="card-header pb-0 d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="mb-1">{{ $title_menu }}</h6>
                            <p class="text-sm mb-0">Informasi lengkap anggota koperasi</p>
                        </div>
                        <div class="d-flex gap-2">
                            <a href="{{ route('anggota.edit', $anggota->nik) }}" class="btn btn-warning btn-sm">
                                <i class="fas fa-edit me-1"></i> Edit
                            </a>
                            <a href="{{ route('anggota.index') }}" class="btn btn-secondary btn-sm">
                                <i class="fas fa-arrow-left me-1"></i> Kembali
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            {{-- Profile Card --}}
            <div class="col-md-4">
                <div class="card">
                    <div class="card-body text-center">
                        {{-- Photo --}}
                        <div class="mb-3">
                            @if($anggota->foto_ktp && $anggota->foto_ktp !== 'noimage.png')
                                <img src="{{ asset('storage/ktp/' . $anggota->foto_ktp) }}" 
                                     alt="Foto KTP" class="img-fluid rounded" style="max-width: 250px;">
                            @else
                                <div class="avatar avatar-xl bg-gradient-secondary mx-auto">
                                    <i class="fas fa-user fa-2x text-white"></i>
                                </div>
                            @endif
                        </div>

                        {{-- Basic Info --}}
                        <h5 class="mb-1">{{ $anggota->nama_lengkap }}</h5>
                        <p class="text-sm text-secondary mb-2">NIK: {{ $anggota->nik }}</p>
                        
                        {{-- Status Badge --}}
                        <span class="badge badge-lg bg-gradient-{{ $anggota->isactive == '1' ? 'success' : 'danger' }} mb-3">
                            {{ getEnumLabel('isactive', $anggota->isactive) }}
                        </span>

                        {{-- Quick Info --}}
                        <div class="text-start">
                            <div class="row mb-2">
                                <div class="col-4 text-secondary">
                                    <i class="fas fa-venus-mars me-1"></i>Gender:
                                </div>
                                <div class="col-8">
                                    {{ $anggota->jenis_kelamin === 'L' ? 'Laki-laki' : 'Perempuan' }}
                                </div>
                            </div>
                            
                            @if($anggota->tanggal_bergabung)
                                <div class="row mb-2">
                                    <div class="col-4 text-secondary">
                                        <i class="fas fa-calendar me-1"></i>Bergabung:
                                    </div>
                                    <div class="col-8">
                                        {{ date('d/m/Y', strtotime($anggota->tanggal_bergabung)) }}
                                    </div>
                                </div>
                            @endif

                            @if($anggota->user_email)
                                <div class="row mb-2">
                                    <div class="col-4 text-secondary">
                                        <i class="fas fa-user-circle me-1"></i>User:
                                    </div>
                                    <div class="col-8">
                                        <span class="badge bg-info">{{ $anggota->user_username }}</span>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- Quick Stats --}}
                <div class="card mt-4">
                    <div class="card-header">
                        <h6 class="mb-0">Ringkasan Transaksi</h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-6 text-center">
                                <div class="border-end">
                                    <h4 class="text-primary mb-0">{{ $pinjaman->count() }}</h4>
                                    <p class="text-sm mb-0">Pinjaman</p>
                                </div>
                            </div>
                            <div class="col-6 text-center">
                                <h4 class="text-success mb-0">{{ $potongan->count() }}</h4>
                                <p class="text-sm mb-0">Potongan</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Detail Information --}}
            <div class="col-md-8">
                {{-- Personal Information --}}
                <div class="card mb-4">
                    <div class="card-header">
                        <h6 class="mb-0">Informasi Pribadi</h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="info-item mb-3">
                                    <label class="text-sm text-secondary">NIK</label>
                                    <p class="mb-0">{{ $anggota->nik }}</p>
                                </div>
                                <div class="info-item mb-3">
                                    <label class="text-sm text-secondary">Nama Lengkap</label>
                                    <p class="mb-0">{{ $anggota->nama_lengkap }}</p>
                                </div>
                                <div class="info-item mb-3">
                                    <label class="text-sm text-secondary">Jenis Kelamin</label>
                                    <p class="mb-0">{{ $anggota->jenis_kelamin === 'L' ? 'Laki-laki' : 'Perempuan' }}</p>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="info-item mb-3">
                                    <label class="text-sm text-secondary">No Telepon</label>
                                    <p class="mb-0">
                                        @if($anggota->no_telp)
                                            <a href="tel:{{ $anggota->no_telp }}" class="text-decoration-none">
                                                <i class="fas fa-phone me-1"></i>{{ $anggota->no_telp }}
                                            </a>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </p>
                                </div>
                                <div class="info-item mb-3">
                                    <label class="text-sm text-secondary">Email User</label>
                                    <p class="mb-0">
                                        @if($anggota->user_email)
                                            <a href="mailto:{{ $anggota->user_email }}" class="text-decoration-none">
                                                <i class="fas fa-envelope me-1"></i>{{ $anggota->user_email }}
                                            </a>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </p>
                                </div>
                                <div class="info-item mb-3">
                                    <label class="text-sm text-secondary">Tanggal Bergabung</label>
                                    <p class="mb-0">
                                        {{ $anggota->tanggal_bergabung ? date('d F Y', strtotime($anggota->tanggal_bergabung)) : '-' }}
                                    </p>
                                </div>
                            </div>
                        </div>
                        
                        @if($anggota->alamat)
                            <div class="info-item">
                                <label class="text-sm text-secondary">Alamat</label>
                                <p class="mb-0">{{ $anggota->alamat }}</p>
                            </div>
                        @endif
                    </div>
                </div>

                {{-- Work Information --}}
                @if($anggota->departemen || $anggota->jabatan)
                    <div class="card mb-4">
                        <div class="card-header">
                            <h6 class="mb-0">Informasi Pekerjaan</h6>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="info-item">
                                        <label class="text-sm text-secondary">Departemen</label>
                                        <p class="mb-0">{{ $anggota->departemen ?: '-' }}</p>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="info-item">
                                        <label class="text-sm text-secondary">Jabatan</label>
                                        <p class="mb-0">{{ $anggota->jabatan ?: '-' }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif

                {{-- Bank Information --}}
                @if($anggota->nama_bank || $anggota->no_rekening || $anggota->nama_pemilik_rekening)
                    <div class="card mb-4">
                        <div class="card-header">
                            <h6 class="mb-0">Informasi Bank</h6>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="info-item">
                                        <label class="text-sm text-secondary">Nama Bank</label>
                                        <p class="mb-0">{{ $anggota->nama_bank ?: '-' }}</p>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="info-item">
                                        <label class="text-sm text-secondary">No Rekening</label>
                                        <p class="mb-0">{{ $anggota->no_rekening ?: '-' }}</p>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="info-item">
                                        <label class="text-sm text-secondary">Nama Pemilik</label>
                                        <p class="mb-0">{{ $anggota->nama_pemilik_rekening ?: '-' }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif

                {{-- Audit Information --}}
                <div class="card">
                    <div class="card-header">
                        <h6 class="mb-0">Informasi Sistem</h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="info-item mb-2">
                                    <label class="text-sm text-secondary">Dibuat</label>
                                    <p class="mb-0">
                                        {{ date('d F Y, H:i', strtotime($anggota->created_at)) }}
                                        @if($anggota->user_create)
                                            <br><small class="text-muted">oleh: {{ $anggota->user_create }}</small>
                                        @endif
                                    </p>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="info-item mb-2">
                                    <label class="text-sm text-secondary">Terakhir Diubah</label>
                                    <p class="mb-0">
                                        {{ date('d F Y, H:i', strtotime($anggota->updated_at)) }}
                                        @if($anggota->user_update)
                                            <br><small class="text-muted">oleh: {{ $anggota->user_update }}</small>
                                        @endif
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Transaction History --}}
        <div class="row mt-4">
            {{-- Pinjaman History --}}
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h6 class="mb-0">Riwayat Pinjaman</h6>
                        <span class="badge bg-primary">{{ $pinjaman->count() }} data</span>
                    </div>
                    <div class="card-body">
                        @if($pinjaman->count() > 0)
                            <div class="table-responsive">
                                <table class="table table-sm">
                                    <thead>
                                        <tr>
                                            <th class="text-xs">No Pinjaman</th>
                                            <th class="text-xs">Nominal</th>
                                            <th class="text-xs">Status</th>
                                            <th class="text-xs">Tanggal</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($pinjaman->take(5) as $item)
                                            <tr>
                                                <td class="text-xs">{{ $item->nomor_pinjaman }}</td>
                                                <td class="text-xs">Rp {{ number_format($item->nominal_pinjaman, 0, ',', '.') }}</td>
                                                <td class="text-xs">
                                                    <span class="badge badge-sm bg-{{ $item->status_approval === 'approve' ? 'success' : ($item->status_approval === 'pending' ? 'warning' : 'danger') }}">
                                                        {{ ucfirst($item->status_approval) }}
                                                    </span>
                                                </td>
                                                <td class="text-xs">{{ date('d/m/Y', strtotime($item->tanggal_pengajuan)) }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            @if($pinjaman->count() > 5)
                                <div class="text-center mt-2">
                                    <small class="text-muted">Dan {{ $pinjaman->count() - 5 }} data lainnya...</small>
                                </div>
                            @endif
                        @else
                            <div class="text-center py-3">
                                <i class="fas fa-money-bill-wave fa-2x text-secondary mb-2"></i>
                                <p class="text-sm text-secondary mb-0">Belum ada riwayat pinjaman</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Potongan History --}}
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h6 class="mb-0">Riwayat Potongan</h6>
                        <span class="badge bg-success">{{ $potongan->count() }} data</span>
                    </div>
                    <div class="card-body">
                        @if($potongan->count() > 0)
                            <div class="table-responsive">
                                <table class="table table-sm">
                                    <thead>
                                        <tr>
                                            <th class="text-xs">Periode</th>
                                            <th class="text-xs">Simpanan</th>
                                            <th class="text-xs">Cicilan</th>
                                            <th class="text-xs">Total</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($potongan as $item)
                                            <tr>
                                                <td class="text-xs">{{ $item->periode }}</td>
                                                <td class="text-xs">Rp {{ number_format($item->simpanan, 0, ',', '.') }}</td>
                                                <td class="text-xs">Rp {{ number_format($item->cicilan_pinjaman, 0, ',', '.') }}</td>
                                                <td class="text-xs font-weight-bold">Rp {{ number_format($item->total_potongan, 0, ',', '.') }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="text-center py-3">
                                <i class="fas fa-cut fa-2x text-secondary mb-2"></i>
                                <p class="text-sm text-secondary mb-0">Belum ada riwayat potongan</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('css')
<style>
    .info-item label {
        font-weight: 600;
        margin-bottom: 2px;
        display: block;
    }
    
    .avatar-xl {
        width: 120px;
        height: 120px;
        display: flex;
        align-items: center;
        justify-content: center;
    }
</style>
@endpush