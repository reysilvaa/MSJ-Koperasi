@extends('layouts.app', ['class' => 'g-sidenav-show bg-gray-100'])

@php
    // Set default column configuration for master-detail view
    $colomh = 0; // Single list view, no master-detail needed

    // Define empty collections for master-detail variables to prevent errors
    $table_header_d = collect();
    $table_detail_d = collect();
    $table_primary_h = collect();
    $table_primary_d = collect();
@endphp

@section('content')
    @include('layouts.navbars.auth.topnav', ['title' => $title_menu])

    <div class="container-fluid py-4">
        {{-- Statistics Cards --}}
        <div class="row mb-4">
            <div class="col-xl-4 col-sm-6 mb-xl-0 mb-4">
                <div class="card">
                    <div class="card-body p-3">
                        <div class="row">
                            <div class="col-8">
                                <div class="numbers">
                                    <p class="text-sm mb-0 text-capitalize font-weight-bold">Pending Review</p>
                                    <h5 class="font-weight-bolder mb-0">{{ $stats['pending_review'] }}</h5>
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
            <div class="col-xl-4 col-sm-6 mb-xl-0 mb-4">
                <div class="card">
                    <div class="card-body p-3">
                        <div class="row">
                            <div class="col-8">
                                <div class="numbers">
                                    <p class="text-sm mb-0 text-capitalize font-weight-bold">Final Approval</p>
                                    <h5 class="font-weight-bolder mb-0">{{ $stats['need_final_approval'] }}</h5>
                                </div>
                            </div>
                            <div class="col-4 text-end">
                                <div class="icon icon-shape bg-gradient-info shadow text-center border-radius-md">
                                    <i class="fas fa-user-check text-lg opacity-10"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-4 col-sm-6">
                <div class="card">
                    <div class="card-body p-3">
                        <div class="row">
                            <div class="col-8">
                                <div class="numbers">
                                    <p class="text-sm mb-0 text-capitalize font-weight-bold">Total Nilai</p>
                                    <h5 class="font-weight-bolder mb-0">Rp {{ number_format($stats['total_amount'], 0, ',', '.') }}</h5>
                                </div>
                            </div>
                            <div class="col-4 text-end">
                                <div class="icon icon-shape bg-gradient-success shadow text-center border-radius-md">
                                    <i class="fas fa-money-bill-wave text-lg opacity-10"></i>
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
                                    <div class="d-flex">
                                        <span class="badge bg-info me-2">Role: {{ ucfirst($user_login->idroles) }}</span>
                                    </div>
                                </div>
                                <div class="col">
                                    {{-- alert --}}
                                    @include('components.alert')
                                </div>
                            </div>
                            <hr class="horizontal dark mt-0">
                            <div class="row px-4 py-2">
                                <div class="col-lg">
                                    <div class="nav-wrapper">
                                        {{-- No add button for approval module --}}
                                    </div>
                                </div>
                            </div>
                            <div class="row px-4 py-2">
                                <div class="table-responsive">
                                    <table class="table display" id="list_{{ $dmenu }}">
                                        <thead class="thead-light" style="background-color: #00b7bd4f;">
                                            <tr>
                                                <th width="110">Action</th>
                                                <th>No</th>
                                                <th>No. Pengajuan</th>
                                                <th>Anggota</th>
                                                <th>Paket</th>
                                                <th>Jumlah Pinjaman</th>
                                                <th>Status</th>
                                                <th>Tanggal</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($list as $pengajuan)
                                                <tr>
                                                    <td class="text-sm font-weight-normal">
                                                        <div class="btn-group">
                                                            {{-- View/Review button always visible --}}
                                                            <button class="btn btn-primary btn-sm mb-0 px-3" type="button"
                                                                title="Review & Approval"
                                                                onclick="window.location='{{ url($url_menu . '/show/' . encrypt($pengajuan->id)) }}'">
                                                                <i class="fas fa-check-circle"> </i><span class="font-weight-bold"> Review</span>
                                                            </button>

                                                            {{-- Dropdown for additional options --}}
                                                            <button type="button"
                                                                class="btn btn-sm btn-primary mb-0 px-3 dropdown-toggle dropdown-toggle-split"
                                                                data-bs-toggle="dropdown" aria-expanded="false">
                                                                <span class="visually-hidden">Toggle Dropdown</span>
                                                            </button>
                                                            <ul class="dropdown-menu">
                                                                <li>
                                                                    <button type="button" class="btn btn-sm btn-info mx-2 mb-0 w-90"
                                                                        onclick="window.location='{{ url($url_menu . '/show/' . encrypt($pengajuan->id)) }}'">
                                                                        <i class="fas fa-eye"></i><span class="font-weight-bold"> Lihat Detail</span>
                                                                    </button>
                                                                </li>
                                                                @if($authorize->edit == '1' && in_array($pengajuan->status_pengajuan, ['diajukan', 'review_admin', 'review_panitia', 'review_ketua']))
                                                                    <li><hr class="dropdown-divider"></li>
                                                                    <li>
                                                                        <button type="button" class="btn btn-sm btn-success mx-2 mb-0 w-90"
                                                                            onclick="window.location='{{ url($url_menu . '/show/' . encrypt($pengajuan->id)) }}'">
                                                                            <i class="fas fa-check"></i><span class="font-weight-bold"> Proses Approval</span>
                                                                        </button>
                                                                    </li>
                                                                @endif
                                                                @if($pengajuan->status_pengajuan == 'disetujui')
                                                                    <li><hr class="dropdown-divider"></li>
                                                                    <li>
                                                                        <button type="button" class="btn btn-sm btn-warning mx-2 mb-0 w-90"
                                                                            onclick="window.open('{{ url('KOP002/pinjaman/add/' . encrypt($pengajuan->id)) }}', '_blank')">
                                                                            <i class="fas fa-money-bill"></i><span class="font-weight-bold"> Buat Pinjaman</span>
                                                                        </button>
                                                                    </li>
                                                                @endif
                                                                <li>
                                                                    <hr class="dropdown-divider">
                                                                </li>
                                                            </ul>
                                                        </div>
                                                    </td>
                                                    <td>{{ $list->firstItem() + $loop->index }}</td>
                                                    <td class="text-sm font-weight-normal">ID: {{ $pengajuan->id }}</td>
                                                    <td class="text-sm font-weight-normal">
                                                        {{ $pengajuan->anggotum->nama_lengkap ?? 'N/A' }}
                                                        ({{ $pengajuan->anggotum->nomor_anggota ?? 'N/A' }})
                                                    </td>
                                                    <td class="text-sm font-weight-normal">
                                                        {{ $pengajuan->master_paket_pinjaman->periode ?? 'N/A' }}
                                                        ({{ $pengajuan->jumlah_paket_dipilih ?? 1 }} paket)
                                                    </td>
                                                    <td class="text-sm font-weight-normal">Rp {{ number_format($pengajuan->jumlah_pinjaman, 0, ',', '.') }}</td>
                                                    <td class="text-sm font-weight-normal">
                                                        @php
                                                            $status_class = match($pengajuan->status_pengajuan) {
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
                            <div class="row px-4 py-2">
                                <div class="col-lg">
                                    <div class="nav-wrapper" id="noted"><code>Note: Data pengajuan untuk approval</code>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('js')
    <script>
        let columnAbjad = '';
        $(document).ready(function() {
            let numColumns = $('#list_{{ $dmenu }}').DataTable().columns().count();
            let columnNames = '';
            for (let index = 0; index < numColumns; index++) {
                columnNames = $('#list_{{ $dmenu }}').DataTable().columns(index).header()[0].textContent;
                if (columnNames == 'Status' || columnNames == 'status') {
                    columnAbjad = String.fromCharCode(65 + index);
                }
            }
        });

        //set table into datatables
        $('#list_{{ $dmenu }}').DataTable({
            paging: false,
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
                        // Arahkan ke server untuk ekspor Excel
                        window.location.href = "{{ url($url_menu) }}?export=excel";
                    }
                },
                @endif
                @if($authorize->pdf == '1')
                {
                    extend: 'pdfHtml5',
                    text: '<i class="fas fa-file-pdf me-1 text-lg text-danger"></i><span class="font-weight-bold"> PDF</span>',
                    action: function() {
                        // Arahkan ke server untuk ekspor PDF
                        window.location.href = "{{ url($url_menu) }}?export=pdf";
                    }
                },
                @endif
            ],
            initComplete: function() {
                // Tambahkan custom search bar
                var searchBarHtml = `
                    <div class="dataTables_filter d-flex justify-content-end">
                        <form method="GET" action="{{ url($url_menu) }}" class="d-flex">
                            <input type="text" name="search" class="form-control me-2" placeholder="Search..." value="{{ request('search') }}">
                        </form>
                    </div>
                `;
                $('.dataTables_filter').remove(); // Hapus input pencarian default DataTables
                $(searchBarHtml).insertAfter('.dt-buttons'); // Tambahkan search bar setelah tombol
            }
        });

        //set color button datatables
        $('.dt-button').addClass('btn btn-secondary');
        $('.dt-button').removeClass('dt-button');
    </script>
@endpush
