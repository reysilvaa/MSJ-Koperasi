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
                                    <p class="text-sm mb-0 text-capitalize font-weight-bold">Total Periode</p>
                                    <h5 class="font-weight-bolder mb-0">{{ $stats['total_periode'] }}</h5>
                                </div>
                            </div>
                            <div class="col-4 text-end">
                                <div class="icon icon-shape bg-gradient-primary shadow text-center border-radius-md">
                                    <i class="fas fa-calendar-alt text-lg opacity-10"></i>
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
                                    <p class="text-sm mb-0 text-capitalize font-weight-bold">Periode Aktif</p>
                                    <h5 class="font-weight-bolder mb-0">{{ $stats['periode_aktif'] }}</h5>
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
            <div class="col-xl-3 col-sm-6 mb-xl-0 mb-4">
                <div class="card">
                    <div class="card-body p-3">
                        <div class="row">
                            <div class="col-8">
                                <div class="numbers">
                                    <p class="text-sm mb-0 text-capitalize font-weight-bold">Periode Non-Aktif</p>
                                    <h5 class="font-weight-bolder mb-0">{{ $stats['periode_nonaktif'] }}</h5>
                                </div>
                            </div>
                            <div class="col-4 text-end">
                                <div class="icon icon-shape bg-gradient-secondary shadow text-center border-radius-md">
                                    <i class="fas fa-times-circle text-lg opacity-10"></i>
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
                                    <p class="text-sm mb-0 text-capitalize font-weight-bold">Tahun Terbaru</p>
                                    <h5 class="font-weight-bolder mb-0">{{ $stats['tahun_terbaru'] ?? '-' }}</h5>
                                </div>
                            </div>
                            <div class="col-4 text-end">
                                <div class="icon icon-shape bg-gradient-info shadow text-center border-radius-md">
                                    <i class="fas fa-calendar-check text-lg opacity-10"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Main Content --}}
        <div class="container-fluid">
            {{-- Generate Form Card --}}
            <div class="row mb-4">
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h6 class="mb-0">Generate Periode Pencairan</h6>
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

                            {{-- Table Section --}}
                            <div class="row px-4 py-2">
                                <div class="table-responsive">
                                    {{-- Alert Messages --}}
                                    @include('components.alert')

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
                                            @forelse($list as $index => $item)
                                                <tr class="{{ $item->isactive == '0' ? 'not even' : '' }}" style="{{ $item->isactive == '0' ? 'background-color:#ffe9ed;' : '' }}">
                                                    <td class="text-sm font-weight-normal sorting_1">
                                                        <div class="btn-group">
                                                            {{-- View button always visible --}}
                                                            <button class="btn btn-primary btn-sm mb-0 px-3" type="button"
                                                                title="View Data"
                                                                onclick="window.location='{{ url($url_menu . '/show/' . encrypt($item->id)) }}'">
                                                                <i class="fas fa-eye" aria-hidden="true"> </i><span class="font-weight-bold"> View</span>
                                                            </button>

                                                            {{-- Dropdown only if edit or delete permissions available --}}
                                                            @if($authorize->edit == '1' || $authorize->delete == '1')
                                                                <button type="button"
                                                                    class="btn btn-sm btn-primary mb-0 px-3 dropdown-toggle dropdown-toggle-split"
                                                                    data-bs-toggle="dropdown" aria-expanded="false">
                                                                    <span class="visually-hidden">Toggle Dropdown</span>
                                                                </button>
                                                                <ul class="dropdown-menu">
                                                                    {{-- check authorize edit - only for active records --}}
                                                                    @if($authorize->edit == '1' && $item->isactive == 1)
                                                                        <li>
                                                                            <hr class="dropdown-divider">
                                                                        </li>
                                                                        <button type="button"
                                                                            class="btn btn-sm btn-warning mx-2 mb-0 w-90"
                                                                            title="Edit Data"
                                                                            onclick="window.location='{{ url($url_menu . '/edit/' . encrypt($item->id)) }}'">
                                                                            <i class="fas fa-edit"></i><span class="font-weight-bold"> Edit</span>
                                                                        </button>
                                                                    @endif

                                                                    {{-- check authorize delete - for activate/deactivate --}}
                                                                    @if($authorize->delete == '1')
                                                                        <form action="{{ url($url_menu . '/' . encrypt($item->id)) }}" method="POST" style="display: inline">
                                                                            @csrf
                                                                            @method('DELETE')
                                                                            <li>
                                                                                <hr class="dropdown-divider">
                                                                            </li>
                                                                            <button type="submit"
                                                                                class="btn btn-sm btn-{{ $item->isactive == '0' ? 'success' : 'danger' }} mx-2 mb-0 w-90"
                                                                                title="{{ $item->isactive == '0' ? 'Buka' : 'Tutup' }} Data"
                                                                                onclick="return deleteData(event, '{{ $item->id }}','{{ $item->isactive == '0' ? 'Aktifkan' : 'Non Aktifkan' }}')">
                                                                                <i class="fas fa-random" aria-hidden="true"></i><span class="font-weight-bold">
                                                                                    {{ $item->isactive == '0' ? 'Buka' : 'Tutup' }}</span>
                                                                            </button>
                                                                        </form>
                                                                    @endif
                                                                    <li>
                                                                        <hr class="dropdown-divider">
                                                                    </li>
                                                                </ul>
                                                            @endif
                                                        </div>
                                                    </td>
                                                    <td>{{ $list->firstItem() + $index }}</td>
                                                    <td class="text-sm font-weight-normal">{{ $item->tahun }}</td>
                                                    <td class="text-sm font-weight-normal">{{ $item->bulan }}</td>
                                                    <td class="text-sm font-weight-normal">{{ $item->nama_periode }}</td>
                                                    <td class="text-sm font-weight-normal">
                                                        @if($item->isactive == '1')
                                                            <span class="badge bg-success text-white">
                                                                <i class="fas fa-check-circle me-1"></i>
                                                                Buka
                                                            </span>
                                                        @else
                                                            <span class="badge bg-secondary text-white">
                                                                <i class="fas fa-times-circle me-1"></i>
                                                                Tutup
                                                            </span>
                                                        @endif
                                                    </td>
                                                    <td class="text-sm font-weight-normal">{{ $item->created_at->format('d/m/Y H:i') }}</td>
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
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            Data {{ $list->firstItem() }} - {{ $list->lastItem() }}
                                            dari {{ $list->total() }} data.
                                        </div>
                                        <div>
                                            {{ $list->appends(request()->query())->links('pagination::bootstrap-4') }}
                                        </div>
                                    </div>
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

@push('js')
    <script>
        let columnAbjad = '';
        $(document).ready(function() {
            // Column detection for status styling (MSJ Framework pattern)
            let numColumns = $('#list_{{ $dmenu }} thead th').length;
            let columnNames = '';
            $('#list_{{ $dmenu }} thead th').each(function(index) {
                columnNames = $(this).text();
                if (columnNames == 'Status' || columnNames == 'status') {
                    columnAbjad = String.fromCharCode(65 + index);
                }
            });
        });

        // Initialize DataTable following MSJ Framework standards
        $('#list_{{ $dmenu }}').DataTable({
            paging: false, // MSJ Framework standard for custom modules
            info: false,
            searching: true,
            responsive: true,
            language: {
                search: "Cari :",
                lengthMenu: "Tampilkan _MENU_ baris",
                zeroRecords: "Maaf - Data tidak ada",
                info: "Data _START_ - _END_ dari _TOTAL_",
                infoEmpty: "Tidak ada data",
                infoFiltered: "(pencarian dari _MAX_ data)"
            },
            responsive: true,
            dom: '<"row d-flex justify-content-between align-items-center"<"col-lg-12 d-flex justify-content-between align-items-center"Bf>>rtip',
            buttons: [
                @if($authorize->excel == '1')
                {
                    text: '<i class="fas fa-file-excel me-1 text-lg text-success"></i><span class="font-weight-bold"> Excel</span>',
                    action: function() {
                        // Server-side export following MSJ Framework pattern
                        window.location.href = "{{ url($url_menu) }}?export=excel";
                    }
                },
                @endif
                @if($authorize->pdf == '1')
                {
                    extend: 'pdfHtml5',
                    text: '<i class="fas fa-file-pdf me-1 text-lg text-danger"></i><span class="font-weight-bold"> PDF</span>',
                    action: function() {
                        // Server-side export following MSJ Framework pattern
                        window.location.href = "{{ url($url_menu) }}?export=pdf";
                    }
                },
                @endif
            ],
            initComplete: function() {
                // Custom search bar following MSJ Framework pattern
                var searchBarHtml = `
                    <div class="dataTables_filter d-flex justify-content-end">
                        <form method="GET" action="{{ url($url_menu) }}" class="d-flex">
                            <input type="text" name="search" class="form-control me-2" placeholder="Search..." value="{{ request('search') }}">
                        </form>
                    </div>
                `;
                $('.dataTables_filter').remove(); // Remove default DataTables search
                $(searchBarHtml).insertAfter('.dt-buttons'); // Add search bar after buttons
            }
        });

        // MSJ Framework standard button styling
        $('.dt-button').addClass('btn btn-secondary');
        $('.dt-button').removeClass('dt-button');

        // MSJ Framework standard authorization cleanup
        <?= $authorize->add == '0' ? 'btnadd.remove();' : '' ?>
        <?= $authorize->excel == '0' ? "$('.buttons-excel').remove();" : '' ?>
        <?= $authorize->pdf == '0' ? "$('.buttons-pdf').remove();" : '' ?>
        <?= $authorize->print == '0' ? "$('.buttons-print').remove();" : '' ?>

        // Delete confirmation function
        function deleteData(event, name, msg) {
            event.preventDefault(); // Prevent default form submission
            Swal.fire({
                title: 'Konfirmasi',
                text: `Apakah Anda Yakin ${msg} Data ${name} ini?`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: `Ya, ${msg}`,
                cancelButtonText: 'Batal',
                confirmButtonColor: '#028284'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Find the closest form element and submit it manually
                    event.target.closest('form').submit();
                }
            });
        }
    </script>
@endpush
