@extends('layouts.app', ['class' => 'g-sidenav-show bg-gray-100'])

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
        <div class="row">
            <div class="col-12">
                <div class="card mb-4">
                    <div class="card-header pb-0">
                        <div class="d-flex justify-content-between align-items-center">
                            <h6>{{ $title_menu }}</h6>
                            <div class="d-flex">
                                <span class="badge bg-info me-2">Role: {{ ucfirst($user_login->idroles) }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="card-body px-0 pt-0 pb-2">
                        {{-- Alert Messages --}}
                        @include('components.alert')

                        <div class="table-responsive p-0">
                            <table class="table align-items-center mb-0" id="approval-table">
                                <thead>
                                    <tr>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">No. Pengajuan</th>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Anggota</th>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Paket</th>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Jumlah Pinjaman</th>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Status</th>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Tanggal</th>
                                        <th class="text-secondary opacity-7">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($pengajuan_list as $pengajuan)
                                        <tr>
                                            <td>
                                                <div class="d-flex px-2 py-1">
                                                    <div class="d-flex flex-column justify-content-center">
                                                        <h6 class="mb-0 text-sm">{{ $pengajuan->nomor_pengajuan }}</h6>
                                                        <p class="text-xs text-secondary mb-0">{{ $pengajuan->jenis_pengajuan }}</p>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="d-flex px-2 py-1">
                                                    <div class="d-flex flex-column justify-content-center">
                                                        <h6 class="mb-0 text-sm">{{ $pengajuan->nama_lengkap }}</h6>
                                                        <p class="text-xs text-secondary mb-0">{{ $pengajuan->nomor_anggota }}</p>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <p class="text-xs font-weight-bold mb-0">{{ $pengajuan->nama_paket }}</p>
                                                <p class="text-xs text-secondary mb-0">{{ $pengajuan->jumlah_paket_dipilih ?? 1 }} paket</p>
                                            </td>
                                            <td>
                                                <p class="text-xs font-weight-bold mb-0">Rp {{ number_format($pengajuan->jumlah_pinjaman, 0, ',', '.') }}</p>
                                                <p class="text-xs text-secondary mb-0">{{ $pengajuan->tenor_bulan }} bulan</p>
                                            </td>
                                            <td class="align-middle text-center text-sm">
                                                @php
                                                    $status_class = match($pengajuan->status_pengajuan) {
                                                        'diajukan' => 'bg-gradient-info',
                                                        'review_admin' => 'bg-gradient-warning',
                                                        'review_panitia' => 'bg-gradient-warning',
                                                        'review_ketua' => 'bg-gradient-primary',
                                                        'disetujui' => 'bg-gradient-success',
                                                        'ditolak' => 'bg-gradient-danger',
                                                        default => 'bg-gradient-secondary'
                                                    };
                                                @endphp
                                                <span class="badge badge-sm {{ $status_class }}">{{ ucfirst(str_replace('_', ' ', $pengajuan->status_pengajuan)) }}</span>
                                            </td>
                                            <td class="align-middle">
                                                <span class="text-secondary text-xs font-weight-bold">{{ date('d/m/Y', strtotime($pengajuan->created_at)) }}</span>
                                            </td>
                                            <td class="align-middle">
                                                <a href="{{ url($url_menu . '/show/' . encrypt($pengajuan->id)) }}"
                                                   class="btn btn-primary btn-sm mb-0" title="Review & Approval">
                                                    <i class="fas fa-check-circle me-1"></i> Review
                                                </a>
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
            $('#approval-table').DataTable({
                "language": {
                    "search": "Cari :",
                    "lengthMenu": "Tampilkan _MENU_ baris",
                    "zeroRecords": "Tidak ada pengajuan untuk direview",
                    "info": "Menampilkan _START_ sampai _END_ dari _TOTAL_ pengajuan",
                    "infoEmpty": "Tidak ada data",
                    "infoFiltered": "(difilter dari _MAX_ total pengajuan)"
                },
                "pageLength": 10,
                "responsive": true,
                "order": [[5, "desc"]], // Sort by tanggal
                "columnDefs": [
                    { "orderable": false, "targets": 6 } // Disable sorting on Action column
                ]
            });
        });
    </script>
@endpush
            <div class="col-md-{{ $colomh > 1 ? '8' : '9' }}">
                <div class="row mx-1">
                    <div class="card" style="min-height: 650px;">
                        <div class="row">
                            <div class="card-header col-md-auto">
                                <h5 class="mb-0" id="label_detail">List Detail <?php Session::has('message'); ?></h5>
                            </div>
                            <div class="col">
                                {{-- alert --}}
                                @include('components.alert')
                            </div>
                        </div>
                        <hr class="horizontal dark mt-0">
                        <div class="row px-4 py-2">
                            <div class="table-responsive">
                                <table class="table display" id="list_detail">
                                    <thead class="thead-light" style="background-color: #00b7bd4f;">
                                        <tr>
                                            <th>Action</th>
                                            {{-- retrieve table header --}}
                                            @foreach ($table_header_d as $header_d)
                                                <th>{{ $header_d->alias }}</th>
                                            @endforeach
                                        </tr>
                                    </thead>
                                    <tbody>
                                        {{-- retrieve table detail --}}
                                        @foreach ($table_detail_d as $detail)
                                            @php
                                                $primary = '';
                                                foreach ($table_primary_h as $h) {
                                                    $primary == ''
                                                        ? ($primary = $detail->{$h->field})
                                                        : ($primary = $primary . ':' . $detail->{$h->field});
                                                }
                                                foreach ($table_primary_d as $p) {
                                                    $primary == ''
                                                        ? ($primary = $detail->{$p->field})
                                                        : ($primary = $primary . ':' . $detail->{$p->field});
                                                }
                                            @endphp
                                            <tr {{ $detail->isactive == '0' ? 'style=background-color:#ffe9ed;' : '' }}>
                                                <td class="text-sm font-weight-normal">
                                                    <button type="submit" class="btn btn-primary mb-0 py-1 px-2"
                                                        title="View Data"
                                                        onclick="window.location='{{ url($url_menu . '/show' . '/' . encrypt($primary)) }}'">
                                                        <i class="fas fa-eye"></i>
                                                    </button>
                                                    {{-- check authorize edit --}}
                                                    @if ($authorize->edit == '1')
                                                        {{-- button edit --}}
                                                        <button type="button" class="btn btn-warning mb-0 py-1 px-2"
                                                            title="Edit Data"
                                                            onclick="window.location='{{ url($url_menu . '/edit' . '/' . encrypt($primary)) }}'">
                                                            <i class="fas fa-edit"></i>
                                                        </button>
                                                    @endif
                                                    {{-- check authorize delete --}}
                                                    @if ($authorize->delete == '1')
                                                        <form
                                                            onsubmit="return deleteData(event,'{{ $primary }}','Hapus')"
                                                            action="{{ url($url_menu . '/' . encrypt($primary)) }}"
                                                            method="POST" style="display: inline">
                                                            @csrf
                                                            @method('DELETE')
                                                            {{-- button delete --}}
                                                            <button type="submit" class="btn btn-danger mb-0 py-1 px-2"
                                                                title="Hapus Data">
                                                                <i class="fas fa-trash"></i>
                                                            </button>
                                                        </form>
                                                    @endif
                                                </td>
                                                {{-- retrieve table detail --}}
                                                @foreach ($table_header_d as $field)
                                                    @php
                                                        $string = $field->field;
                                                    @endphp
                                                    {{-- field type enum --}}
                                                    @if ($field->type == 'enum')
                                                        <td
                                                            class="text-sm font-weight-{{ $field->primary == '1' ? 'bold text-dark' : 'normal' }}">
                                                            @if ($field->query != '')
                                                                @php
                                                                    $data_query = DB::select($field->query);
                                                                @endphp
                                                                @if (Str::contains($field->class, 'select-multiple'))
                                                                    <?php
                                                                    $multiple = array_map('trim', explode(',', $detail->$string));
                                                                    $text = '';
                                                                    ?>
                                                                    @foreach ($data_query as $q)
                                                                        @php
                                                                            $sAsArray = array_values((array) $q);
                                                                            if (in_array($sAsArray[0], $multiple)) {
                                                                                if ($text == '') {
                                                                                    $text = $sAsArray[1];
                                                                                } else {
                                                                                    $text = $text . ',' . $sAsArray[1];
                                                                                }
                                                                            }
                                                                        @endphp
                                                                    @endforeach
                                                                    {{ $text }}
                                                                @else
                                                                    @foreach ($data_query as $q)
                                                                        <?php $sAsArray = array_values((array) $q); ?>
                                                                        {{ $detail->$string == $sAsArray[0] ? $sAsArray[1] : '' }}
                                                                    @endforeach
                                                                @endif
                                                            @endif
                                                        </td>
                                                        {{-- field type file --}}
                                                    @elseif ($field->type == 'file')
                                                        <td
                                                            class="text-sm font-weight-{{ $field->primary == '1' ? 'bold text-dark' : 'normal' }}">
                                                            @if ($detail->$string)
                                                                <a target="_blank"
                                                                    class="btn btn-sm btn-outline-success mb-0 py-1 px-2"
                                                                    href="{{ asset('/storage' . '/' . $detail->$string) }}">
                                                                    <i aria-hidden="true" class="fas fa-file-lines text-lg">
                                                                    </i>
                                                                    {{ $field->alias }}</a>
                                                            @endif
                                                        </td>
                                                        {{-- field type image --}}
                                                    @elseif($field->type == 'image')
                                                        <td
                                                            class="text-sm font-weight-{{ $field->primary == '1' ? 'bold text-dark' : 'normal' }}">
                                                            <span class="my-2 text-xs">
                                                                <img src="{{ file_exists(public_path('storage/' . $detail->$string . 'tumb.png')) ? asset('/storage' . '/' . $detail->$string . 'tumb.png') : asset('storage/' . $detail->$string) }}"
                                                                    alt="image" style="height: 35px;"
                                                                    data-bs-toggle="modal"
                                                                    data-bs-target="#imageModal{{ $field->field }}">
                                                            </span>
                                                            <span
                                                                style="display: none;">{{ asset('/storage' . '/' . $detail->$string) }}
                                                            </span>
                                                            <!-- Modal -->
                                                            <div class="modal fade" id="imageModal{{ $field->field }}"
                                                                tabindex="-1" role="dialog"
                                                                aria-labelledby="imageModalLabel" aria-hidden="true">
                                                                <div class="modal-dialog modal-dialog-centered"
                                                                    role="document">
                                                                    <div class="modal-content">
                                                                        <div class="modal-header">
                                                                            <h5 class="modal-title" id="imageModalLabel">
                                                                                Preview Image
                                                                            </h5>
                                                                            <button type="button" class="btn-close"
                                                                                data-bs-dismiss="modal" aria-label="Close">
                                                                                <span aria-hidden="true">&times;</span>
                                                                            </button>
                                                                        </div>
                                                                        <div class="modal-body">
                                                                            <img src="{{ asset('/storage' . '/' . $detail->$string) }}"
                                                                                id="preview" alt="image"
                                                                                class="w-100 border-radius-lg shadow-sm">
                                                                        </div>
                                                                        <div class="modal-footer">
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </td>
                                                        {{-- field type join --}}
                                                    @elseif($field->type == 'join')
                                                        <td
                                                            class="text-sm font-weight-{{ $field->primary == '1' ? 'bold text-dark' : 'normal' }}">
                                                            @if ($field->query != '')
                                                                @php
                                                                    $query =
                                                                        $field->query . "'" . $detail->$string . "'";
                                                                    $data_query = DB::select($query);
                                                                @endphp
                                                                @foreach ($data_query as $q)
                                                                    <?php $sAsArray = array_values((array) $q); ?>
                                                                    @if ($field->default != 'image')
                                                                        {{ $sAsArray[0] != '' ? $sAsArray[0] : '' }}
                                                                    @else
                                                                        <span class="my-2 text-xs">
                                                                            <img src="{{ asset('/storage' . '/' . $sAsArray[0]) }}"
                                                                                alt="image" style="height: 40px;"
                                                                                data-bs-toggle="modal"
                                                                                data-bs-target="#imageModalJoin{{ $primary }}">
                                                                        </span>
                                                                        <span
                                                                            style="display: none;">{{ asset('/storage' . '/' . $sAsArray[0]) }}
                                                                        </span>
                                                                        <!-- Modal -->
                                                                        <div class="modal fade"
                                                                            id="imageModalJoin{{ $primary }}"
                                                                            tabindex="-1" role="dialog"
                                                                            aria-labelledby="imageModalJoinLabel"
                                                                            aria-hidden="true">
                                                                            <div class="modal-dialog modal-dialog-centered"
                                                                                role="document">
                                                                                <div class="modal-content">
                                                                                    <div class="modal-header">
                                                                                        <h5 class="modal-title"
                                                                                            id="imageModalJoinLabel">
                                                                                            Preview Image
                                                                                        </h5>
                                                                                        <button type="button"
                                                                                            class="btn-close"
                                                                                            data-bs-dismiss="modal"
                                                                                            aria-label="Close">
                                                                                            <span
                                                                                                aria-hidden="true">&times;</span>
                                                                                        </button>
                                                                                    </div>
                                                                                    <div class="modal-body">
                                                                                        <img src="{{ asset('/storage' . '/' . $sAsArray[0]) }}"
                                                                                            id="preview" alt="image"
                                                                                            class="w-100 border-radius-lg shadow-sm">
                                                                                    </div>
                                                                                    <div class="modal-footer">
                                                                                    </div>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                    @endif
                                                                @endforeach
                                                            @endif
                                                        </td>
                                                        {{-- field type currency --}}
                                                    @elseif ($field->type == 'currency')
                                                        <td
                                                            class="text-sm font-weight-{{ $field->primary == '1' ? 'bold text-dark' : 'normal' }}">
                                                            {{ $format->CurrencyFormat($detail->$string, $field->decimals, $field->sub) }}
                                                        </td>
                                                    @else
                                                        <td
                                                            class="text-sm font-weight-{{ $field->primary == '1' ? 'bold text-dark' : 'normal' }}">
                                                            {{ $detail->$string }}</td>
                                                    @endif
                                                @endforeach
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
        //set table header into datatables
        $('#list_header').DataTable({
            "language": {
                "search": "Cari :",
                "lengthMenu": "Tampilkan _MENU_ baris",
                "zeroRecords": "Maaf - Data tidak ada",
                "info": "Data _START_ - _END_ dari _TOTAL_",
                "infoEmpty": "Tidak ada data",
                "infoFiltered": "(pencarian dari _MAX_ data)"
            },
            "pageLength": 15,
            responsive: true,
            dom: 'frtip'
        });
        //set table detail into datatables
        $('#list_detail').DataTable({
            "language": {
                "search": "Cari :",
                "lengthMenu": "Tampilkan _MENU_ baris",
                "zeroRecords": "Maaf - Data tidak ada",
                "info": "Data _START_ - _END_ dari _TOTAL_",
                "infoEmpty": "Tidak ada data",
                "infoFiltered": "(pencarian dari _MAX_ data)"
            },
            "pageLength": 15,
            responsive: true,
            dom: 'Bfrtip',
            buttons: [{
                    text: '<i class="fas fa-plus me-1 text-lg btn-add"> </i><span class="font-weight-bold"> Tambah'
                },
                {
                    extend: 'excelHtml5',
                    text: '<i class="fas fa-file-excel me-1 text-lg text-success"> </i><span class="font-weight-bold"> Excel',
                    autoFilter: true,
                    sheetName: 'Exported data',
                    // title: 'Nama File Excel',
                    customize: function(xlsx) {
                        var sheet = xlsx.xl.worksheets['sheet1.xml'];
                        // Loop over the cells in column
                        sheet.querySelectorAll('row c[r^="' + columnAbjad + '"]').forEach((row) => {
                            // Get the value
                            let cell = row.querySelector('is t');
                            if (cell && cell.textContent === 'Not Active') {
                                row.setAttribute('s', '10'); //red background
                            }
                        });
                    },
                    exportOptions: {
                        columns: ':visible:not(:last-child)'
                    },
                },
                {
                    extend: 'pdfHtml5',
                    text: '<i class="fas fa-file-pdf me-1 text-lg text-danger"> </i><span class="font-weight-bold"> PDF',
                    exportOptions: {
                        columns: ':visible:not(:last-child)'
                    },
                },
                {
                    extend: 'print',
                    text: '<i class="fas fa-print me-1 text-lg text-info"> </i><span class="font-weight-bold"> Print',
                    exportOptions: {
                        columns: ':visible:not(:last-child)'
                    },
                },
            ]
        });
        //set color button datatables
        $('.dt-button').addClass('btn btn-secondary');
        $('.dt-button').removeClass('dt-button');
        //setting button add
        var id = "{{ Session::has('idtrans') ? encrypt(Session::get('idtrans')) : '' }}";
        var btnadd = $('.btn-add').parents('.btn-secondary');
        btnadd.removeClass('btn-secondary');
        btnadd.addClass('btn btn-primary');
        btnadd.attr('onclick', "window.location='{{ URL::to($url_menu . '/add/') }}" + "/" + id +
            "'");
        (id != '') ? $('#label_detail').text("List Detail -> {{ Session::get('idtrans') }}"): '';
        //check authorize button datatables
        <?= $authorize->add == '0' ? 'btnadd.remove();' : '' ?>
        <?= $authorize->excel == '0' ? "$('.buttons-excel').remove();" : '' ?>
        <?= $authorize->pdf == '0' ? "$('.buttons-pdf').remove();" : '' ?>
        <?= $authorize->print == '0' ? "$('.buttons-print').remove();" : '' ?>
        // function detail ajax
        function detail(id, gmenu, dmenu) {
            $.ajax({
                url: "{{ url($url_menu) . '/ajax' }}",
                type: "GET",
                dataType: "JSON",
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                data: {
                    id: id,
                    gmenu: gmenu,
                    dmenu: dmenu
                },
                success: function(data) {
                    // set title detail
                    $('#label_detail').text('List Detail -> ' + data['ajaxid']);
                    // reset datatables
                    $('#list_detail').DataTable().destroy();
                    $('#list_detail tbody').remove();
                    // retrieve data into tabel
                    $('#list_detail').append('<tbody></tbody>');
                    var i = 1;
                    var result = eval(data['table_detail_d_ajax']);
                    //looping data detail
                    for (var index in result) {
                        var result1 = eval(data['table_primary_d_ajax']);
                        var result2 = eval(data['table_header_d_ajax']);
                        var primary = '';
                        for (var index1 in result1) {
                            // initialized primary key
                            primary == '' ? (primary = result[index][result1[index1].field]) : (primary =
                                primary + ':' + result[index][result1[index1].field]);
                        }
                        // set variable columns
                        var vtd = `
                                    <td class="text-sm font-weight-normal">
                                        <button type="submit" class="btn btn-primary mb-0 py-1 px-2"
                                            title="View Data"
                                            onclick="window.location='{{ url($url_menu . '/show/') }}` + '/' + data[
                                'encrypt_primary'][
                                index
                            ] + `'">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        {{-- check authorize edit --}}
                                        @if ($authorize->edit == '1')
                                            {{-- button edit --}}
                                            <button type="button" class="btn btn-warning mb-0 py-1 px-2"
                                                title="Edit Data"
                                                onclick="window.location='{{ url($url_menu . '/edit/') }}` + '/' +
                            data[
                                'encrypt_primary'][
                                index
                            ] + `'">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                        @endif
                                        {{-- check authorize delete --}}
                                        @if ($authorize->delete == '1')
                                            <form onsubmit="return deleteData(event,'` + primary + `','Hapus')"
                                                action="{{ url($url_menu . '/') }}` + '/' + data['encrypt_primary'][
                                index
                            ] + `"
                                                method="POST" style="display: inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-danger mb-0 py-1 px-2"
                                                    title="Hapus Data">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        @endif
                                    </td>
                                    `;
                        for (var index2 in result2) {
                            var primary_color = result2[index2].primary == '1' ? 'bold text-dark' : 'normal';
                            var vtex = result[index][result2[index2].field];
                            if (result2[index2].type == 'enum') {
                                if (result2[index2].query != '') {
                                    var query = result2[index2].query;
                                    var v_enum = eval(data[result2[index2].field]);
                                    $.each(v_enum, function(index, q) {
                                        var sAsArray = $.map(q, function(value,
                                            key) {
                                            return value;
                                        });
                                        if (vtex == sAsArray[0]) {
                                            vtd +=
                                                `<td class="text-sm font-weight-` +
                                                primary_color + `">` + sAsArray[1] +
                                                `</td>`;
                                        }
                                        return vtd;
                                    });
                                } else {
                                    vtd +=
                                        `<td class="text-sm font-weight-` + primary_color + `">` +
                                        result[index][result2[index2].field] + `</td>`;
                                }
                            } else if (result2[index2].type == 'join') {
                                if (result2[index2].query != '') {
                                    var query = result2[index2].query + "'" + result2[index2].field + "'";
                                    var v_enum = eval(data['data_join'][index]);
                                    $.each(v_enum, function(index, q) {
                                        var sAsArray = $.map(q, function(value,
                                            key) {
                                            return value;
                                        });
                                        vtd +=
                                            `<td class="text-sm font-weight-` +
                                            primary_color + `">` + sAsArray[0] +
                                            `</td>`;
                                        return vtd;
                                    });
                                } else {
                                    vtd +=
                                        `<td class="text-sm font-weight-` + primary_color + `">` +
                                        result[index][result2[index2].field] + `</td>`;
                                }
                            } else if (result2[index2].type == 'currency') {
                                vtd +=
                                    `<td class="text-sm font-weight-` + primary_color +
                                    `">` + currencyFormat(result[index][result2[index2].field], result2[index2]
                                        .decimals, result2[index2].sub) + `</td>`;


                            } else {
                                vtd +=
                                    `<td class="text-sm font-weight-` + primary_color + `">` +
                                    result[index][result2[index2].field] + `</td>`;
                            }
                        }
                        i++;
                        // add rows into table
                        $('#list_detail tbody').append(`<tr>` + vtd + `</tr>`);
                    }
                    // initialized datatbles
                    let columnAbjad = '';
                    $(document).ready(function() {
                        let numColumns = $('#list_{{ $dmenu }}').DataTable().columns()
                            .count();
                        let columnNames = '';
                        // set color datatables
                        for (let index = 0; index < numColumns; index++) {
                            columnNames = $('#list_{{ $dmenu }}').DataTable().columns(index)
                                .header()[0].textContent;
                            if (columnNames == 'Status' || columnNames == 'status') {
                                columnAbjad = String.fromCharCode(65 + index);
                            }
                        }
                        // function set color row DataTable
                        $('.odd').click(function() {
                            $('.odd').css('background-color', '');
                            $('.even').css('background-color', '');
                            $('.not').css('background-color', '#ffe9ed');
                            $(this).css('background-color', '#f9f4ea');
                        });
                        $('.even').click(function() {
                            $('.odd').css('background-color', '');
                            $('.even').css('background-color', '');
                            $('.not').css('background-color', '#ffe9ed');
                            $(this).css('background-color', '#f9f4ea');
                        });
                        var table = $('#list_{{ @$dmenu }}').DataTable();
                        // Add the click event to all rows on all pages
                        table.on('draw', function() {
                            $('.odd').click(function() {
                                $('.odd').css('background-color', '');
                                $('.even').css('background-color', '');
                                $('.not').css('background-color', '#ffe9ed');
                                $(this).css('background-color', '#f9f4ea');
                            });
                            $('.even').click(function() {
                                $('.odd').css('background-color', '');
                                $('.even').css('background-color', '');
                                $('.not').css('background-color', '#ffe9ed');
                                $(this).css('background-color', '#f9f4ea');
                            });
                        });
                        // end function set color row DataTable
                    });
                    //redraw table into datatables
                    $('#list_detail').DataTable({
                        "language": {
                            "search": "Cari :",
                            "lengthMenu": "Tampilkan _MENU_ baris",
                            "zeroRecords": "Maaf - Data tidak ada",
                            "info": "Data _START_ - _END_ dari _TOTAL_",
                            "infoEmpty": "Tidak ada data",
                            "infoFiltered": "(pencarian dari _MAX_ data)"
                        },
                        "pageLength": 15,
                        responsive: true,
                        dom: 'Bfrtip',
                        buttons: [{
                                text: '<i class="fas fa-plus me-1 text-lg btn-add"> </i><span class="font-weight-bold"> Tambah'
                            },
                            {
                                extend: 'excelHtml5',
                                text: '<i class="fas fa-file-excel me-1 text-lg text-success"> </i><span class="font-weight-bold"> Excel',
                                autoFilter: true,
                                sheetName: 'Exported data',
                                // title: 'Nama File Excel',
                                customize: function(xlsx) {
                                    var sheet = xlsx.xl.worksheets['sheet1.xml'];
                                    // Loop over the cells in column
                                    sheet.querySelectorAll('row c[r^="' + columnAbjad +
                                            '"]')
                                        .forEach((row) => {
                                            // Get the value
                                            let cell = row.querySelector('is t');
                                            if (cell && cell.textContent ===
                                                'Not Active') {
                                                row.setAttribute('s',
                                                    '10'); //red background
                                            }
                                        });
                                },
                                exportOptions: {
                                    columns: ':visible:not(:last-child)'
                                },
                            },
                            {
                                extend: 'pdfHtml5',
                                text: '<i class="fas fa-file-pdf me-1 text-lg text-danger"> </i><span class="font-weight-bold"> PDF',
                                exportOptions: {
                                    columns: ':visible:not(:last-child)'
                                },
                            },
                            {
                                extend: 'print',
                                text: '<i class="fas fa-print me-1 text-lg text-info"> </i><span class="font-weight-bold"> Print',
                                exportOptions: {
                                    columns: ':visible:not(:last-child)'
                                },
                            },
                        ]
                    }).draw();
                    //set color button datatables
                    $('.dt-button').addClass('btn btn-secondary');
                    $('.dt-button').removeClass('dt-button');
                    //setting button add
                    var btnadd = $('.btn-add').parents('.btn-secondary');
                    btnadd.removeClass('btn-secondary');
                    btnadd.addClass('btn btn-primary');
                    btnadd.attr('onclick', "window.location='{{ URL::to($url_menu . '/add/') }}" + "/" +
                        id +
                        "'");
                    //check authorize button datatables
                    <?= $authorize->add == '0' ? 'btnadd.remove();' : '' ?>
                    <?= $authorize->excel == '0' ? "$('.buttons-excel').remove();" : '' ?>
                    <?= $authorize->pdf == '0' ? "$('.buttons-pdf').remove();" : '' ?>
                    <?= $authorize->print == '0' ? "$('.buttons-print').remove();" : '' ?>
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    Swal.fire({
                        title: 'Sorry!',
                        text: 'Error Get data!',
                        icon: 'error',
                        confirmButtonColor: '#3085d6'
                    });
                }
            });
        }
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
        // function currency
        function currencyFormat(nominal, decimal = 0, prefix = 'Rp.') {
            return prefix + ' ' + parseFloat(nominal).toFixed(decimal).replace(/\d(?=(\d{3})+\.)/g, '$&,');
        }
    </script>
@endpush
