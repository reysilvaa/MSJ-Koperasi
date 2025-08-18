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
                                                class="font-weight-bold">Kembali</button>
                                    </div>
                                    <div class="col-md-3 md-auto justify-content-end row">
                                        <div class="col">
                                            {{-- set value filter --}}
                                            <input type="hidden" name="ftahun" value="{{ $filter['tahun'] ?? date('Y') }}" />
                                        </div>
                                        <div class="col">
                                            {{-- display label alias on class filter --}}
                                            Filter Tahun :
                                            <select class="form-select" id="filter_tahun" style="width: 150px;">
                                                <option value="{{ $filter['tahun'] ?? date('Y') }}" selected>
                                                    {{ $filter['tahun'] ?? date('Y') }}
                                                    @if ($filter['tahun'] == date('Y'))
                                                        (s/d {{ date('M') }})
                                                    @endif
                                                </option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Table with MSJ Framework Style --}}
                        <div class="row px-4 py-2">
                            <div class="table-responsive">
                                @if ($table_result && count($table_result) > 0)
                                    @php
                                        $monthNames = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agus', 'Sept', 'Oct', 'Nov', 'Des'];
                                        $bulanFields = ['jan', 'feb', 'mar', 'apr', 'mei', 'jun', 'jul', 'agu', 'sep', 'okt', 'nov', 'des'];
                                        $maxMonth = $max_month ?? 12;
                                    @endphp

                                    <table class="table" id="iuran_table_{{ $dmenu ?? 'KOP401' }}">
                                        <thead class="thead-light" style="background-color: #00b7bd4f;">
                                            <tr>
                                                <th rowspan="2" style="width: 40px;">No.</th>
                                                <th rowspan="2" style="width: 120px;">Name</th>
                                                <th colspan="{{ $maxMonth }}">TAHUN {{ $filter['tahun'] ?? date('Y') }}
                                                    @if ($filter['tahun'] == date('Y'))
                                                        (s/d {{ $monthNames[$maxMonth - 1] }})
                                                    @endif
                                                </th>
                                                <th rowspan="2">TOTAL<br>SALDO</th>
                                            </tr>
                                            <tr>
                                                @for ($i = 0; $i < $maxMonth; $i++)
                                                    <th>{{ $monthNames[$i] }}</th>
                                                @endfor
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @php
                                                $totalPerBulan = array_fill(1, $maxMonth, 0);
                                                $grandTotal = 0;
                                            @endphp

                                            @foreach ($table_result as $index => $anggota)
                                                @php
                                                    $isResign = isset($anggota->status_anggota) && $anggota->status_anggota == 'resign';
                                                    $totalSaldo = 0;
                                                @endphp
                                                <tr {{ $isResign ? 'class=not style=background-color:#ffe9ed;' : '' }}>
                                                    <td class="text-sm font-weight-normal">{{ $index + 1 }}</td>
                                                    <td class="text-sm font-weight-normal">{{ $anggota->nama ?? 'N/A' }}</td>

                                                    @for ($fieldIndex = 0; $fieldIndex < $maxMonth; $fieldIndex++)
                                                        @php
                                                            $fieldBulan = $bulanFields[$fieldIndex];
                                                            $bulanNumber = $fieldIndex + 1;
                                                            $nilaiIuran = $anggota->$fieldBulan ?? 0;

                                                            // Jika anggota resign dan bulan > bulan resign, tampilkan dash
                                                            $showDash = $isResign && isset($anggota->bulan_resign) && $bulanNumber > $anggota->bulan_resign;

                                                            if (!$showDash && $nilaiIuran > 0) {
                                                                $totalPerBulan[$bulanNumber] += $nilaiIuran;
                                                                $totalSaldo += $nilaiIuran;
                                                            }
                                                        @endphp

                                                        <td class="text-sm font-weight-normal">
                                                            @if ($showDash)
                                                                -
                                                            @elseif ($nilaiIuran > 0)
                                                                {{ number_format($nilaiIuran, 0, '.', '.') }}
                                                            @elseif (is_null($nilaiIuran))
                                                                -
                                                            @else
                                                                -
                                                            @endif
                                                        </td>
                                                    @endfor

                                                    <td class="text-sm font-weight-normal">{{ number_format($totalSaldo, 0, '.', '.') }}</td>
                                                    @php $grandTotal += $totalSaldo; @endphp
                                                </tr>
                                            @endforeach

                                            {{-- Sum Row --}}
                                            <tr>
                                                <td colspan="2" class="text-sm font-weight-normal"></td>
                                                @for ($bulan = 1; $bulan <= $maxMonth; $bulan++)
                                                    <td class="text-sm font-weight-normal">
                                                        @if ($totalPerBulan[$bulan] > 0)
                                                            {{ number_format($totalPerBulan[$bulan], 0, '.', '.') }}
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                @endfor
                                                <td class="text-sm font-weight-normal">{{ number_format($grandTotal, 0, '.', '.') }}</td>
                                            </tr>

                                            {{-- SP Row (Simpanan Pokok) --}}
                                            <tr>
                                                <td class="text-sm font-weight-normal"></td>
                                                <td class="text-sm font-weight-normal">sp</td>
                                                @php
                                                    $totalSP = 0;
                                                    $spData = $sp_data ?? [];
                                                @endphp
                                                @for ($bulan = 1; $bulan <= $maxMonth; $bulan++)
                                                    @php
                                                        $spValue = $spData[$bulan] ?? 0;
                                                        $totalSP += $spValue;
                                                    @endphp
                                                    <td class="text-sm font-weight-normal">
                                                        @if ($spValue > 0)
                                                            {{ number_format($spValue, 0, '.', '.') }}
                                                        @else
                                                            0
                                                        @endif
                                                    </td>
                                                @endfor
                                                <td class="text-sm font-weight-normal">{{ number_format($totalSP, 0, '.', '.') }}</td>
                                            </tr>

                                            {{-- SW Row (Simpanan Wajib/Sukarela) --}}
                                            <tr>
                                                <td class="text-sm font-weight-normal"></td>
                                                <td class="text-sm font-weight-normal">sw</td>
                                                @php
                                                    $totalSW = 0;
                                                    $swData = $sw_data ?? [];
                                                @endphp
                                                @for ($bulan = 1; $bulan <= $maxMonth; $bulan++)
                                                    @php
                                                        $swValue = $swData[$bulan] ?? 0;
                                                        $totalSW += $swValue;
                                                    @endphp
                                                    <td class="text-sm font-weight-normal">
                                                        @if ($swValue > 0)
                                                            {{ number_format($swValue, 0, '.', '.') }}
                                                        @else
                                                            0
                                                        @endif
                                                    </td>
                                                @endfor
                                                <td class="text-sm font-weight-normal">{{ number_format($totalSW, 0, '.', '.') }}</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                @else
                                    <div class="alert alert-info text-center">
                                        <i class="fas fa-info-circle me-2"></i>
                                        Data iuran anggota untuk tahun {{ $filter['tahun'] ?? date('Y') }} tidak ditemukan.
                                    </div>
                                @endif
                            </div>
                        </div>

                        <div class="row px-4 py-2">
                            <div class="col-lg">
                                @if ($table_result && count($table_result) > 0)
                                    <div class="nav-wrapper" id="noted"><code>Note : <i aria-hidden="true"
                                                style="color: #ffc2cd;" class="fas fa-circle"></i> Data not active</code>
                                    </div>
                                @else
                                    <div class="nav-wrapper">
                                        <code>Data tidak ditemukan untuk tahun {{ $filter['tahun'] ?? date('Y') }}!</code>
                                    </div>
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
