@extends('layouts.app', ['class' => 'g-sidenav-show bg-gray-100'])

{{-- section content --}}
@section('content')
    @include('layouts.navbars.auth.topnav', ['title' => ''])

    {{-- MSJ Framework Standard Container --}}
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div class="row mx-1">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">Result {{ $title_menu ?? 'Laporan Iuran Tahunan' }}</h5>
                        </div>
                        <hr class="horizontal dark mt-0">

                        {{-- MSJ Framework Standard Navigation --}}
                        <div class="row px-4 py-2">
                            <div class="col-lg">
                                <div class="nav-wrapper row">
                                    <div class="col">
                                        {{-- Back Button --}}
                                        <button class="btn btn-secondary mb-0" onclick="window.location='{{ URL::to($url_menu) }}'">
                                            <i class="fas fa-circle-left me-1"></i><span class="font-weight-bold">Kembali</span>
                                        </button>
                                    </div>
                                    <div class="col-md-3 md-auto justify-content-end row">
                                        <div class="col">
                                            {{-- Filter Info --}}
                                            <input type="hidden" name="ftahun" value="{{ $filter['tahun'] ?? date('Y') }}" />
                                        </div>
                                        <div class="col">
                                            {{-- Display Filter --}}
                                            Filter Tahun:
                                            <select class="form-select" id="tahun_filter" style="width: 150px;" disabled>
                                                <option value="{{ $filter['tahun'] ?? date('Y') }}" selected>
                                                    {{ $filter['tahun'] ?? date('Y') }}
                                                </option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- MSJ Framework Standard Table Container --}}
                        <div class="row px-4 py-2">
                            <div class="table-responsive">
                                {{-- Alert Messages --}}
                                @include('components.alert')

                                @if(isset($table_result) && count($table_result) > 0)
                                    {{-- Custom Iuran Table with MSJ Framework styling --}}
                                    <table class="table display" id="list_{{ $dmenu }}">
                                        <thead class="thead-light" style="background-color: #00b7bd4f;">
                                            <tr>
                                                <th rowspan="2" style="vertical-align: middle;">No.</th>
                                                <th rowspan="2" style="vertical-align: middle;">Nama Anggota</th>
                                                <th colspan="12" class="text-center" style="background-color: #d4edda;">
                                                    TAHUN {{ $filter['tahun'] ?? date('Y') }}
                                                </th>
                                                <th rowspan="2" style="vertical-align: middle; background-color: #90EE90;">
                                                    TOTAL<br>SALDO
                                                </th>
                                            </tr>
                                            <tr>
                                                <th style="background-color: #00b7bd4f;">Jan</th>
                                                <th style="background-color: #00b7bd4f;">Feb</th>
                                                <th style="background-color: #00b7bd4f;">Mar</th>
                                                <th style="background-color: #00b7bd4f;">Apr</th>
                                                <th style="background-color: #00b7bd4f;">Mei</th>
                                                <th style="background-color: #00b7bd4f;">Jun</th>
                                                <th style="background-color: #00b7bd4f;">Jul</th>
                                                <th style="background-color: #00b7bd4f;">Agus</th>
                                                <th style="background-color: #00b7bd4f;">Sept</th>
                                                <th style="background-color: #00b7bd4f;">Oct</th>
                                                <th style="background-color: #00b7bd4f;">Nov</th>
                                                <th style="background-color: #00b7bd4f;">Dec</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            {{-- Data Anggota --}}
                                            @foreach($table_result as $member)
                                            <tr>
                                                <td class="text-sm font-weight-normal">{{ $member['no'] }}</td>
                                                <td class="text-sm font-weight-normal text-start">{{ $member['nama_lengkap'] }}</td>
                                                @for($bulan = 1; $bulan <= 12; $bulan++)
                                                    @php
                                                        $nominal = $member['bulan'][$bulan]['total'];
                                                        $hasWajib = $member['bulan'][$bulan]['wajib'] > 0;
                                                        $hasPokok = $member['bulan'][$bulan]['pokok'] > 0;

                                                        // Highlight logic - bisa disesuaikan
                                                        $cellClass = '';
                                                        if ($hasWajib && $hasPokok) {
                                                            $cellClass = 'bg-success text-white'; // Hijau jika ada wajib dan pokok
                                                        } elseif ($hasWajib) {
                                                            $cellClass = 'bg-info text-white'; // Biru jika hanya wajib
                                                        } elseif ($hasPokok) {
                                                            $cellClass = 'bg-warning text-dark'; // Kuning jika hanya pokok
                                                        }
                                                    @endphp
                                                    <td class="text-sm font-weight-normal text-end {{ $cellClass }}">
                                                        @if($nominal > 0)
                                                            {{ number_format($nominal, 0, ',', '.') }}
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                @endfor
                                                <td class="text-sm font-weight-bold text-end" style="background-color: #90EE90;">
                                                    {{ number_format($member['total_saldo'], 0, ',', '.') }}
                                                </td>
                                            </tr>
                                            @endforeach

                                            </tbody>
                                    </table>
                                @else
                                    {{-- MSJ Framework Standard No Data Message --}}
                                    <div class="text-center py-4">
                                        <div class="icon icon-shape icon-lg bg-gradient-secondary shadow mx-auto">
                                            <i class="fas fa-search opacity-10"></i>
                                        </div>
                                        <h6 class="mt-3">Tidak ada data</h6>
                                        <p class="text-sm">Data iuran untuk tahun {{ $filter['tahun'] ?? date('Y') }} tidak ditemukan.</p>
                                    </div>
                                @endif
                            </div>
                        </div>

                        {{-- Summary Table (Terpisah) --}}
                        @if(isset($table_result) && count($table_result) > 0 && isset($summary))
                        <div class="row px-4 py-2">
                            <div class="col-12">
                                <h6 class="mb-3">Ringkasan Iuran Tahun {{ $filter['tahun'] ?? date('Y') }}</h6>
                                <div class="table-responsive">
                                    <table class="table table-bordered" id="summary_table">
                                        <thead class="thead-light" style="background-color: #00b7bd4f;">
                                            <tr>
                                                <th style="vertical-align: middle;">Jenis</th>
                                                <th colspan="12" class="text-center" style="background-color: #d4edda;">
                                                    TAHUN {{ $filter['tahun'] ?? date('Y') }}
                                                </th>
                                                <th style="vertical-align: middle; background-color: #90EE90;">
                                                    TOTAL
                                                </th>
                                            </tr>
                                            <tr>
                                                <th style="background-color: #00b7bd4f;">Keterangan</th>
                                                <th style="background-color: #00b7bd4f;">Jan</th>
                                                <th style="background-color: #00b7bd4f;">Feb</th>
                                                <th style="background-color: #00b7bd4f;">Mar</th>
                                                <th style="background-color: #00b7bd4f;">Apr</th>
                                                <th style="background-color: #00b7bd4f;">Mei</th>
                                                <th style="background-color: #00b7bd4f;">Jun</th>
                                                <th style="background-color: #00b7bd4f;">Jul</th>
                                                <th style="background-color: #00b7bd4f;">Agus</th>
                                                <th style="background-color: #00b7bd4f;">Sept</th>
                                                <th style="background-color: #00b7bd4f;">Oct</th>
                                                <th style="background-color: #00b7bd4f;">Nov</th>
                                                <th style="background-color: #00b7bd4f;">Dec</th>
                                                <th style="background-color: #00b7bd4f;">Total</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            {{-- Total Row --}}
                                            <tr style="background-color: #f8f9fa; font-weight: bold;">
                                                <td class="text-sm font-weight-bold text-start">TOTAL KESELURUHAN</td>
                                                @for($bulan = 1; $bulan <= 12; $bulan++)
                                                    <td class="text-sm font-weight-bold text-end">
                                                        @if($summary['total_per_bulan'][$bulan] > 0)
                                                            {{ number_format($summary['total_per_bulan'][$bulan], 0, ',', '.') }}
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                @endfor
                                                <td class="text-sm font-weight-bold text-end" style="background-color: #90EE90;">
                                                    {{ number_format($summary['grand_total'], 0, ',', '.') }}
                                                </td>
                                            </tr>

                                            {{-- SP (Simpanan Pokok) Row --}}
                                            <tr style="background-color: #fff3cd;">
                                                <td class="text-sm font-weight-bold text-start">SP (Simpanan Pokok)</td>
                                                @for($bulan = 1; $bulan <= 12; $bulan++)
                                                    <td class="text-sm font-weight-normal text-end">
                                                        @if($summary['total_sp'][$bulan] > 0)
                                                            {{ number_format($summary['total_sp'][$bulan], 0, ',', '.') }}
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                @endfor
                                                <td class="text-sm font-weight-bold text-end" style="background-color: #90EE90;">
                                                    {{ number_format($summary['grand_total_sp'] ?? array_sum($summary['total_sp']), 0, ',', '.') }}
                                                </td>
                                            </tr>

                                            {{-- SW (Simpanan Wajib) Row --}}
                                            <tr style="background-color: #d1ecf1;">
                                                <td class="text-sm font-weight-bold text-start">SW (Simpanan Wajib)</td>
                                                @for($bulan = 1; $bulan <= 12; $bulan++)
                                                    <td class="text-sm font-weight-normal text-end">
                                                        @if($summary['total_sw'][$bulan] > 0)
                                                            {{ number_format($summary['total_sw'][$bulan], 0, ',', '.') }}
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                @endfor
                                                <td class="text-sm font-weight-bold text-end" style="background-color: #90EE90;">
                                                    {{ number_format($summary['grand_total_sw'] ?? array_sum($summary['total_sw']), 0, ',', '.') }}
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                        @endif

                        {{-- MSJ Framework Standard Notes --}}
                        <div class="row px-4 py-2">
                            <div class="col-lg">
                                @if(isset($table_result) && count($table_result) > 0)
                                    <div class="nav-wrapper" id="noted">
                                        <div class="mb-2">
                                            <strong>Keterangan Warna:</strong>
                                        </div>
                                        <div class="mb-2">
                                            <span class="badge bg-success text-white me-2">■</span> <strong>Wajib + Pokok</strong> - Anggota membayar iuran wajib dan simpanan pokok
                                        </div>
                                        <div class="mb-2">
                                            <span class="badge bg-info text-white me-2">■</span> <strong>Hanya Wajib</strong> - Anggota hanya membayar iuran wajib
                                        </div>
                                        <div class="mb-2">
                                            <span class="badge bg-warning text-dark me-2">■</span> <strong>Hanya Pokok</strong> - Anggota hanya membayar simpanan pokok
                                        </div>
                                    </div>
                                @else
                                    <div class="nav-wrapper"><code>Data not found!</code></div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Hidden form for export --}}
    <form id="exportForm" method="POST" action="{{ URL::to($url_menu) }}" style="display: none;">
        @csrf
        <input type="hidden" name="tahun" value="{{ $filter['tahun'] ?? date('Y') }}">
        <input type="hidden" name="export" id="exportType">
    </form>

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
    $(document).ready(function() {
        // MSJ Framework Standard DataTable initialization
        @if(isset($table_result) && count($table_result) > 0)
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
            paging: true,
            pageLength: 25,
            dom: 'Bfrtip',
            buttons: [
                @if(isset($authorize) && $authorize->excel == '1')
                {
                    extend: 'excelHtml5',
                    text: '<i class="fas fa-file-excel me-1 text-lg text-success"></i><span class="font-weight-bold"> Excel</span>',
                    autoFilter: true,
                    sheetName: 'Laporan Iuran {{ $filter["tahun"] ?? date("Y") }}',
                    title: 'Laporan Iuran Tahunan {{ $filter["tahun"] ?? date("Y") }}',
                    exportOptions: {
                        columns: ':visible'
                    },
                },
                @endif
                @if(isset($authorize) && $authorize->pdf == '1')
                {
                    extend: 'pdfHtml5',
                    text: '<i class="fas fa-file-pdf me-1 text-lg text-danger"></i><span class="font-weight-bold"> PDF</span>',
                    orientation: 'landscape',
                    pageSize: 'A3',
                    title: 'Laporan Iuran Tahunan {{ $filter["tahun"] ?? date("Y") }}',
                    exportOptions: {
                        columns: ':visible'
                    },
                },
                @endif
                @if(isset($authorize) && $authorize->print == '1')
                {
                    extend: 'print',
                    text: '<i class="fas fa-print me-1 text-lg text-info"></i><span class="font-weight-bold"> Print</span>',
                    orientation: 'landscape',
                    pageSize: 'A3',
                    title: 'Laporan Iuran Tahunan {{ $filter["tahun"] ?? date("Y") }}',
                    exportOptions: {
                        columns: ':visible'
                    },
                },
                @endif
            ]
        });

        // MSJ Framework Standard button styling
        $('.dt-button').addClass('btn btn-secondary');
        $('.dt-button').removeClass('dt-button');

        // MSJ Framework Standard authorization cleanup
        @if(!isset($authorize) || $authorize->excel == '0')
            $('.buttons-excel').remove();
        @endif
        @if(!isset($authorize) || $authorize->pdf == '0')
            $('.buttons-pdf').remove();
        @endif
        @if(!isset($authorize) || $authorize->print == '0')
            $('.buttons-print').remove();
        @endif
        @endif
    });

    // Export function (fallback for server-side export)
    function exportData(type) {
        document.getElementById('exportType').value = type;
        document.getElementById('exportForm').submit();
    }
</script>
@endpush

@push('css')
<style>
    /* Custom styling untuk tabel iuran yang tetap mempertahankan struktur asli */
    #list_{{ $dmenu }} {
        font-size: 11px;
    }

    #list_{{ $dmenu }} th,
    #list_{{ $dmenu }} td {
        padding: 4px 6px;
        vertical-align: middle;
    }

    #list_{{ $dmenu }} .text-start {
        text-align: left !important;
    }

    #list_{{ $dmenu }} .text-end {
        text-align: right !important;
    }

    /* Responsive table untuk mobile */
    @media (max-width: 768px) {
        #list_{{ $dmenu }} {
            font-size: 9px;
        }

        #list_{{ $dmenu }} th,
        #list_{{ $dmenu }} td {
            padding: 2px 4px;
        }
    }
</style>
@endpush
