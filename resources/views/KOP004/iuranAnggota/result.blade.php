@extends('layouts.app', ['class' => 'g-sidenav-show bg-gray-100'])
{{-- section content --}}
@section('content')
    @include('layouts.navbars.auth.topnav', ['title' => ''])
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div class="row mx-1">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">Result {{ $title_menu }}</h5>
                        </div>
                        <hr class="horizontal dark mt-0">
                        <div class="row px-4 py-2">
                            <div class="col-lg">
                                <div class="nav-wrapper row">
                                    <div class="col">
                                        {{-- button back --}}
                                        <button class="btn btn-secondary mb-0" onclick="history.back()"><i
                                                class="fas fa-circle-left me-1"> </i><span
                                                class="font-weight-bold">Kembali</span></button>
                                    </div>
                                    <div class="col-md-3 md-auto justify-content-end row">
                                        <div class="col">
                                            {{-- set value filter --}}
                                            <input type="hidden" name="ftahun" value="{{ $filter['tahun'] ?? date('Y') }}" />
                                        </div>
                                        <div class="col">
                                            {{-- display label alias on class filter --}}
                                            Filter Tahun : {{ $filter['tahun'] ?? date('Y') }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row px-4 py-2">
                            <div class="table-responsive">
                                <table class="table display" id="list_{{ $dmenu }}">
                                    {{-- check result data --}}
                                    @if ($table_result)
                                        <thead class="thead-light" style="background-color: #00b7bd4f;">
                                            <tr>
                                                <th>No</th>
                                                {{-- set table header --}}
                                                @foreach ($table_result as $result)
                                                    @php
                                                        $sAsArray = array_keys((array) $result);
                                                        $i = 0;
                                                    @endphp
                                                @endforeach
                                                @foreach ($sAsArray as $header)
                                                    @if (substr($header, 0, 3) == 'IMG')
                                                        <th>{{ substr($header, 4) }}</th>
                                                    @elseif (substr($header, 0, 3) == 'CDT')
                                                        <th>{{ substr($header, 6) . '-' . substr($header, 3, 2) }}</th>
                                                    @elseif (substr($header, 0, 3) == 'CST')
                                                        <th>{{ substr($header, 4) }}</th>
                                                    @else
                                                        <th>{{ $header }}</th>
                                                    @endif
                                                    @php
                                                        $i++;
                                                    @endphp
                                                @endforeach
                                            </tr>
                                        </thead>
                                        <tbody>
                                            {{-- retrieve table result --}}
                                            @foreach ($table_result as $detail)
                                                <tr
                                                    {{ @$detail->isactive == '0' || @$detail->ISACTIVE == '0' ? 'class=not style=background-color:#ffe9ed;' : '' }}>
                                                    <td>{{ $loop->iteration }}</td>
                                                    @foreach ($table_result as $result)
                                                        @php
                                                            $field = array_keys((array) $result);
                                                            $i = 0;
                                                        @endphp
                                                    @endforeach
                                                    @foreach ($field as $header)
                                                        @php
                                                            $string = $header;
                                                        @endphp
                                                        <td class="text-sm font-weight-normal">
                                                            {{ $detail->$string }}
                                                        </td>
                                                        @php
                                                            $i++;
                                                        @endphp
                                                    @endforeach
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    @endif
                                </table>
                            </div>
                        </div>
                        <div class="row px-4 py-2">
                            <div class="col-lg">
                                @if ($table_result)
                                    <div class="nav-wrapper" id="noted"><code>Note : <i aria-hidden="true"
                                                style="color: #ffc2cd;" class="fas fa-circle"></i> Data not active</code>
                                    </div>
                                @else
                                    <div class="nav-wrapper"><code> Data not found!</code></div>
                                @endif
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
            let table = $('#list_{{ $dmenu }}').DataTable();
            let indexStatus = 0;
            let numColumns = $('#list_{{ $dmenu }}').DataTable().columns().count();
            let columnNames = '';
            for (let index = 0; index < numColumns; index++) {
                columnNames = $('#list_{{ $dmenu }}').DataTable().columns(index).header()[0].textContent;
                if (columnNames == 'Status' || columnNames == 'status' || columnNames == 'STATUS') {
                    columnAbjad = String.fromCharCode(65 + index);
                }
            }
            //check note
            if ($('*').hasClass('exp')) {
                $('#noted').html(`<code>Note :( <i aria-hidden="true" style="color: #ffc2cd;"
                class="fas fa-circle"></i> Data not active ), ( <i aria-hidden="true"
                style="color: #ffe768;" class="fas fa-circle"></i> Data Expired )</code>`)
            }
            if ($('*').hasClass('stock')) {
                $('#noted').html(`<code>Note :( <i aria-hidden="true" style="color: #ffc2cd;"
                class="fas fa-circle"></i> Data not active ), ( <i aria-hidden="true"
                style="color: #f93c3c;" class="fas fa-circle"></i> Stock < Min Stock )</code>`)
            }
            if ($('*').hasClass('exp') && $('*').hasClass('stock')) {
                $('#noted').html(`<code>Note :( <i aria-hidden="true" style="color: #ffc2cd;"
                class="fas fa-circle"></i> Data not active ), ( <i aria-hidden="true"
                style="color: #ffe768;" class="fas fa-circle"></i> Data Expired ), ( <i aria-hidden="true"
                style="color: #f93c3c;" class="fas fa-circle"></i> Stock < Min Stock )</code>`)
            }
        });
        //set table into datatables
        $('#list_{{ $dmenu }}').DataTable({
            "language": {
                "search": "Cari :",
                "lengthMenu": "Tampilkan _MENU_ baris",
                "zeroRecords": "Maaf - Data tidak ada",
                "info": "Data _START_ - _END_ dari _TOTAL_",
                "infoEmpty": "Tidak ada data",
                "infoFiltered": "(pencarian dari _MAX_ data)"
            },
            responsive: true,
            dom: 'Bfrtip',
            buttons: [{
                    extend: 'excelHtml5',
                    text: '<i class="fas fa-file-excel me-1 text-lg text-success"> </i><span class="font-weight-bold"> Excel',
                    autoFilter: true,
                    sheetName: 'Exported data',
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
                    orientation: 'landscape',
                    pageSize: 'A4',
                    exportOptions: {
                        columns: ':visible:not(:last-child)'
                    },
                },
                {
                    extend: 'print',
                    text: '<i class="fas fa-print me-1 text-lg text-info"> </i><span class="font-weight-bold"> Print',
                    orientation: 'landscape',
                    pageSize: 'A4',
                    exportOptions: {
                        columns: ':visible:not(:last-child)'
                    },
                },
            ]
        });
        //set color button datatables
        $('.dt-button').addClass('btn btn-secondary');
        $('.dt-button').removeClass('dt-button');
        //check authorize button datatables
        <?= $authorize->excel == '0' ? "$('.buttons-excel').remove();" : '' ?>
        <?= $authorize->pdf == '0' ? "$('.buttons-pdf').remove();" : '' ?>
        <?= $authorize->print == '0' ? "$('.buttons-print').remove();" : '' ?>
        //function delete
        function deleteData(name, msg) {
            pesan = confirm('Apakah Anda Yakin ' + msg + ' Data ' + name + ' ini ?');
            if (pesan) return true
            else return false
        }
    </script>
@endpush
