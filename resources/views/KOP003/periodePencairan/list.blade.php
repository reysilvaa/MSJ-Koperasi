@extends('layouts.app', ['class' => 'g-sidenav-show bg-gray-100'])
{{-- section content --}}
@section('content')
    @include('layouts.navbars.auth.topnav', ['title' => ''])

    <div class="container-fluid py-4">
        {{-- Alert Section --}}
        <div class="row mb-4">
            <div class="col-md-12">
                {{-- alert --}}
                @include('components.alert')
            </div>
        </div>

     {{-- Main Table Card --}}
        <div class="container-fluid">
               {{-- Generate Form Card --}}
        <div class="row mb-4">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h6 class="mb-0">Generate Periode Pencairan Custom</h6>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="{{ url($url_menu) }}">
                            @csrf
                            <div class="mb-3">
                                <label for="tahun" class="form-label">Tahun</label>
                                <input type="number"
                                       class="form-control @error('tahun') is-invalid @enderror"
                                       id="tahun"
                                       name="tahun"
                                       value="{{ old('tahun', date('Y')) }}"
                                       min="2020"
                                       max="2030"
                                       required>
                                @error('tahun')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="form-text text-muted">
                                    Akan membuat 12 periode (Januari - Desember) untuk tahun yang dipilih
                                </small>
                            </div>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-plus me-2"></i>Generate Periode
                            </button>
                        </form>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                {{-- Additional content can be added here if needed --}}
            </div>
        </div>
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



                            {{-- Export Buttons --}}
                            <div class="row px-4 py-2">
                                <div class="col-lg-12 d-flex justify-content-start align-items-center">
                                    {{-- Export Buttons --}}
                                    <div class="dt-buttons">
                                        <a href="{{ url($url_menu) }}?export=excel" class="btn btn-secondary" tabindex="0">
                                            <span><i class="fas fa-file-excel me-1 text-lg text-success" aria-hidden="true"></i><span class="font-weight-bold"> Excel</span></span>
                                        </a>
                                        <a href="{{ url($url_menu) }}?export=pdf" class="btn btn-secondary" tabindex="0">
                                            <span><i class="fas fa-file-pdf me-1 text-lg text-danger" aria-hidden="true"></i><span class="font-weight-bold"> PDF</span></span>
                                        </a>
                                    </div>
                                </div>
                            </div>

                            {{-- Table Section --}}
                            <div class="row px-4 py-2">
                                <div class="table-responsive">
                                    <table class="table display" id="list_{{ $dmenu }}">
                                        <thead class="thead-light" style="background-color: #00b7bd4f;">
                                            <tr>
                                                <th width="110">Action</th>
                                                <th>No</th>
                                                <th>Tahun</th>
                                                <th>Bulan</th>
                                                <th>Nama Periode</th>
                                                <th>Status</th>
                                                <th>Dibuat</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($periods as $index => $period)
                                                <tr {{ $period->isactive == '0' ? 'class=not style=background-color:#ffe9ed;' : '' }}>
                                                    <td class="text-sm font-weight-normal">
                                                        <div class="btn-group">
                                                            {{-- Main View Button --}}
                                                            <button class="btn btn-primary btn-sm mb-0 px-3" type="button"
                                                                title="View Data"
                                                                onclick="window.location='{{ url($url_menu . '/show' . '/' . encrypt($period->id)) }}'">
                                                                <i class="fas fa-eye" aria-hidden="true"></i><span class="font-weight-bold"> View</span>
                                                            </button>

                                                            {{-- Dropdown Toggle --}}
                                                            <button type="button" class="btn btn-sm btn-primary mb-0 px-3 dropdown-toggle dropdown-toggle-split"
                                                                data-bs-toggle="dropdown" aria-expanded="false">
                                                                <span class="visually-hidden">Toggle Dropdown</span>
                                                            </button>

                                                            {{-- Dropdown Menu --}}
                                                            <ul class="dropdown-menu">
                                                                {{-- Edit Option --}}
                                                                @if ($authorize->edit == '1' && $period->isactive == 1)
                                                                    <li>
                                                                        <button type="button" class="btn btn-sm btn-warning mx-2 mb-0 w-90"
                                                                            title="Edit Data"
                                                                            onclick="window.location='{{ url($url_menu . '/edit' . '/' . encrypt($period->id)) }}'">
                                                                            <i class="fas fa-edit" aria-hidden="true"></i><span class="font-weight-bold"> Edit</span>
                                                                        </button>
                                                                    </li>
                                                                    <li><hr class="dropdown-divider"></li>
                                                                @endif

                                                                {{-- Activate/Deactivate Option --}}
                                                                @if ($authorize->delete == '1')
                                                                    <form action="{{ url($url_menu . '/' . encrypt($period->id)) }}" method="POST" style="display: inline">
                                                                        @csrf
                                                                        @method('DELETE')
                                                                        <li>
                                                                            <button type="submit"
                                                                                class="btn btn-sm btn-{{ $period->isactive == '0' ? 'success' : 'danger' }} mx-2 mb-0 w-90"
                                                                                title="{{ $period->isactive == '0' ? 'Aktifkan' : 'Non Aktifkan' }} Data"
                                                                                onclick="return deleteData(event, '{{ $period->id }}','{{ $period->isactive == '0' ? 'Aktifkan' : 'Non Aktifkan' }}')">
                                                                                <i class="fas fa-random" aria-hidden="true"></i><span class="font-weight-bold">
                                                                                    {{ $period->isactive == '0' ? 'Aktifkan' : 'Non Aktifkan' }}</span>
                                                                            </button>
                                                                        </li>
                                                                    </form>
                                                                @endif
                                                            </ul>
                                                        </div>
                                                    </td>
                                                    <td class="text-sm font-weight-normal">{{ $index + 1 }}</td>
                                                    <td class="text-sm font-weight-bold text-dark">{{ $period->tahun }}</td>
                                                    <td class="text-sm font-weight-normal">{{ $period->bulan }}</td>
                                                    <td class="text-sm font-weight-bold text-dark">{{ $period->nama_periode }}</td>
                                                    <td class="text-sm font-weight-normal">
                                                        @if($period->isactive == '1')
                                                            <span class="badge badge-sm bg-gradient-success">Aktif</span>
                                                        @else
                                                            <span class="badge badge-sm bg-gradient-secondary">Tidak Aktif</span>
                                                        @endif
                                                    </td>
                                                    <td class="text-sm font-weight-normal">{{ $period->created_at->format('d/m/Y H:i') }}</td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="7" class="text-center py-4">
                                                        <div class="d-flex flex-column align-items-center">
                                                            <i class="fas fa-calendar-times fa-3x text-secondary mb-3"></i>
                                                            <h6 class="text-secondary">Belum ada periode pencairan</h6>
                                                            <p class="text-xs text-secondary mb-0">Gunakan form di atas untuk generate periode</p>
                                                        </div>
                                                    </td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            {{-- Note Section --}}
                            <div class="row px-4 py-2">
                                <div class="col-lg">
                                    <div class="nav-wrapper" id="noted">
                                        <code>Note : <i aria-hidden="true" style="color: #ffc2cd;" class="fas fa-circle"></i> Data not active</code>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    {{-- check flag js on dmenu --}}
    @if ($jsmenu == '1')
        @if (view()->exists("js.{$dmenu}"))
            @push('addjs')
                {{-- file js in folder (resources/views/js) --}}
                @include('js.' . $dmenu);
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
