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
                                    <p class="text-sm mb-0 text-capitalize font-weight-bold">Total Pengajuan</p>
                                    <h5 class="font-weight-bolder mb-0">{{ $stats['total_pengajuan'] }}</h5>
                                </div>
                            </div>
                            <div class="col-4 text-end">
                                <div class="icon icon-shape bg-gradient-primary shadow text-center border-radius-md">
                                    <i class="fas fa-file-invoice-dollar text-lg opacity-10"></i>
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
                                    <p class="text-sm mb-0 text-capitalize font-weight-bold">Pending Approval</p>
                                    <h5 class="font-weight-bolder mb-0">{{ $stats['pending_approval'] }}</h5>
                                </div>
                            </div>
                            <div class="col-4 text-end">
                                <div class="icon icon-shape bg-gradient-warning shadow text-center border-radius-md">
                                    <i class="fas fa-clock text-lg opacity-10"></i>
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
                                    <p class="text-sm mb-0 text-capitalize font-weight-bold">Disetujui</p>
                                    <h5 class="font-weight-bolder mb-0">{{ $stats['approved'] }}</h5>
                                </div>
                            </div>
                            <div class="col-4 text-end">
                                <div class="icon icon-shape bg-gradient-success shadow text-center border-radius-md">
                                    <i class="fas fa-check-circle text-lg opacity-10"></i>
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
                                    <p class="text-sm mb-0 text-capitalize font-weight-bold">Ditolak</p>
                                    <h5 class="font-weight-bolder mb-0">{{ $stats['rejected'] }}</h5>
                                </div>
                            </div>
                            <div class="col-4 text-end">
                                <div class="icon icon-shape bg-gradient-danger shadow text-center border-radius-md">
                                    <i class="fas fa-times-circle text-lg opacity-10"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Main Content --}}
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-12">
                    <div class="row mx-1">
                        <div class="card">
                            <div class="row">
                                <div class="card-header col-md-auto">
                                    <h5 class="mb-0">{{ $title_menu }}</h5>
                                </div>
                                <div class="col">
                                    <div class="px-4 pt-2">
                                    </div>
                                </div>
                            </div>
                            <hr class="horizontal dark mt-0">
                            <div class="row px-4 py-2">
                                <div class="col-lg">
                                    <div class="nav-wrapper">
                                        @if($authorize->add == '1')
                                            <button class="btn btn-primary mb-0" onclick="window.location='{{ url($url_menu . '/add') }}'">
                                                <i class="fas fa-plus me-1"></i><span class="font-weight-bold">Tambah</span>
                                            </button>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            <div class="row px-4 py-2">
                                <div class="table-responsive">
                                    {{-- Alert Messages --}}
                                    @include('components.alert')

                                    <table class="table display dataTable no-footer" id="pengajuan-table">
                                        <thead class="thead-light" style="background-color: #00b7bd4f;">
                                            <tr>
                                                <th width="110">Action</th>
                                                <th>No</th>
                                                <th>No. Pengajuan</th>
                                                <th>Anggota</th>
                                                <th>Paket</th>
                                                <th>Jumlah Pinjaman</th>
                                                <th>Tenor</th>
                                                <th>Status</th>
                                                <th>Tanggal</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($list as $index => $pengajuan)
                                                <tr>
                                                    <td class="text-sm font-weight-normal">
                                                        <div class="btn-group">
                                                            @if(isset($authorize->value) && $authorize->value == '1')
                                                                <button class="btn btn-primary btn-sm mb-0 px-3" type="button" title="View Data"
                                                                        onclick="window.location='{{ url($url_menu . '/show/' . encrypt($pengajuan->id)) }}'">
                                                                    <i class="fas fa-eye"></i><span class="font-weight-bold"> View</span>
                                                                </button>
                                                            @endif
                                                            @if($authorize->edit == '1' || ($authorize->delete == '1' && $pengajuan->status_pengajuan == 'draft'))
                                                                <button type="button" class="btn btn-sm btn-primary mb-0 px-3 dropdown-toggle dropdown-toggle-split"
                                                                        data-bs-toggle="dropdown" aria-expanded="false">
                                                                    <span class="visually-hidden">Toggle Dropdown</span>
                                                                </button>
                                                                <ul class="dropdown-menu">
                                                                    @if($authorize->edit == '1')
                                                                        <li>
                                                                            <a class="dropdown-item" href="{{ url($url_menu . '/edit/' . encrypt($pengajuan->id)) }}">
                                                                                <i class="fas fa-edit"></i> Edit
                                                                            </a>
                                                                        </li>
                                                                    @endif
                                                                    @if($authorize->delete == '1' && $pengajuan->status_pengajuan == 'draft')
                                                                        <li><hr class="dropdown-divider"></li>
                                                                        <li>
                                                                            <button type="button" class="dropdown-item text-danger"
                                                                                    onclick="confirmDelete('{{ encrypt($pengajuan->id) }}')">
                                                                                <i class="fas fa-trash"></i> Hapus
                                                                            </button>
                                                                        </li>
                                                                    @endif
                                                                </ul>
                                                            @endif
                                                        </div>
                                                    </td>
                                                    <td>{{ $list->firstItem() + $index }}</td>
                                                    <td class="text-sm font-weight-normal">ID: {{ $pengajuan->id }}</td>
                                                    <td class="text-sm font-weight-normal">
                                                        {{ $pengajuan->anggota->nama_lengkap ?? 'N/A' }}
                                                        ({{ $pengajuan->anggota->nomor_anggota ?? 'N/A' }})
                                                    </td>
                                                    <td class="text-sm font-weight-normal">
                                                        {{ $pengajuan->paketPinjaman->periode ?? 'N/A' }}
                                                        ({{ $pengajuan->jumlah_paket_dipilih ?? 1 }} paket)
                                                    </td>
                                                    <td class="text-sm font-weight-normal">Rp {{ number_format($pengajuan->jumlah_pinjaman, 0, ',', '.') }}</td>
                                                    <td class="text-sm font-weight-normal">{{ $pengajuan->tenor_pinjaman }}</td>
                                                    <td class="text-sm font-weight-normal">
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
                                                        <span class="badge {{ $status_class }}">{{ ucfirst(str_replace('_', ' ', $pengajuan->status_pengajuan)) }}</span>
                                                    </td>
                                                    <td class="text-sm font-weight-normal">{{ date('d/m/Y', strtotime($pengajuan->created_at)) }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            {{-- Pagination --}}
                            <div class="row px-4 py-2">
                                <div class="col-lg">
                                    {{ $list->links() }}
                                </div>
                            </div>
                            <div class="row px-4 py-2">
                                <div class="col-lg">
                                    <div class="nav-wrapper">
                                        <code>Note: Data pengajuan pinjaman koperasi</code>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
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
