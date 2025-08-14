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
                                        {{-- check authorize add --}}
                                        @if ($authorize->add == '1')
                                            {{-- button add --}}
                                            <button class="btn btn-primary mb-0"
                                                onclick="window.location='{{ URL::to($url_menu . '/add') }}'"><i
                                                    class="fas fa-plus me-1"> </i><span class="font-weight-bold">Tambah</span></button>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            <div class="row px-4 py-2">
                                <div class="table-responsive">
                                    {{-- Alert Messages --}}
                                    @include('components.alert')

                                    <table class="table display" id="list_{{ $dmenu }}">
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
                                                            {{-- View button always visible --}}
                                                            <button class="btn btn-primary btn-sm mb-0 px-3" type="button"
                                                                title="View Data"
                                                                onclick="window.location='{{ url($url_menu . '/show/' . encrypt($pengajuan->id)) }}'">
                                                                <i class="fas fa-eye"> </i><span class="font-weight-bold"> View</span>
                                                            </button>

                                                            {{-- Dropdown only if edit or delete permissions available --}}
                                                            @if($authorize->edit == '1' || ($authorize->delete == '1' && $pengajuan->status_pengajuan == 'draft'))
                                                                <button type="button"
                                                                    class="btn btn-sm btn-primary mb-0 px-3 dropdown-toggle dropdown-toggle-split"
                                                                    data-bs-toggle="dropdown" aria-expanded="false">
                                                                    <span class="visually-hidden">Toggle Dropdown</span>
                                                                </button>
                                                                <ul class="dropdown-menu">
                                                                    {{-- check authorize edit --}}
                                                                    @if($authorize->edit == '1')
                                                                        <li>
                                                                            <hr class="dropdown-divider">
                                                                        </li>
                                                                        <button type="button"
                                                                            class="btn btn-sm btn-warning mx-2 mb-0 w-90"
                                                                            title="Edit Data"
                                                                            onclick="window.location='{{ url($url_menu . '/edit/' . encrypt($pengajuan->id)) }}'">
                                                                            <i class="fas fa-edit"></i><span class="font-weight-bold"> Edit</span>
                                                                        </button>
                                                                    @endif

                                                                    {{-- check authorize delete - only for draft status --}}
                                                                    @if($authorize->delete == '1' && $pengajuan->status_pengajuan == 'draft')
                                                                        <form action="{{ url($url_menu . '/' . encrypt($pengajuan->id)) }}" method="POST" style="display: inline">
                                                                            @csrf
                                                                            @method('DELETE')
                                                                            <li>
                                                                                <hr class="dropdown-divider">
                                                                            </li>
                                                                            <button type="submit"
                                                                                class="btn btn-sm btn-danger mx-2 mb-0 w-90"
                                                                                title="Hapus Data"
                                                                                onclick="return deleteData(event, '{{ $pengajuan->id }}','Hapus')">
                                                                                <i class="fas fa-trash"></i><span class="font-weight-bold"> Hapus</span>
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
                                    <div class="nav-wrapper" id="noted"><code>Note : <i aria-hidden="true"
                                                style="color: #ffc2cd;" class="fas fa-circle"></i> Data not active</code>
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
            // dom: 'Bfrtip',
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

        //function delete
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
