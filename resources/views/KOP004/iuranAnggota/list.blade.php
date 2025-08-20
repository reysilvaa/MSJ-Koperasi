@extends('layouts.app', ['class' => 'g-sidenav-show bg-gray-100'])

{{-- section content --}}
@section('content')
    @include('layouts.navbars.auth.topnav', ['title' => ''])
    <div class="card shadow-lg mx-4">
        <div class="card-body p-3">
            <div class="row gx-4">
                <div class="col-lg">
                    <div class="nav-wrapper">
                        {{-- check authorize add (execute report) --}}
                        @if ($authorize->add == '1')
                            {{-- button execute --}}
                            <button type="submit" form="{{ $dmenu }}-form" class="btn btn-primary mb-0">
                                <i class="fas fa-bolt me-1"></i><span class="font-weight-bold">Execute</span>
                            </button>
                        @endif

                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="container-fluid py-4">
        <div class="row">
            <div class="col-lg-5">
                <div class="card">
                    <form role="form" method="POST" action="{{ URL::to($url_menu) }}" id="{{ $dmenu }}-form">
                        @csrf
                        <div class="card-body">
                            <p class="text-uppercase text-sm">Filter {{ $title_menu }}</p>
                            <hr class="horizontal dark mt-0">
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label for="tahun" class="form-control-label">Tahun Laporan <span class="text-danger">*</span></label>
                                        <input type="number"
                                               class="form-control"
                                               id="tahun"
                                               name="tahun"
                                               value="{{ old('tahun', $tahun_default ?? date('Y')) }}"
                                               min="2000"
                                               max="{{ date('Y') }}"
                                               required>
                                        <p class='text-secondary text-xs pt-1 px-1'>
                                            *) Masukkan tahun laporan (2000 - {{ date('Y') }})
                                        </p>
                                        <p class='text-info text-xs pt-1 px-1'>
                                            <i class="fas fa-info-circle"></i> Laporan akan menampilkan data iuran per bulan dalam satu tahun
                                        </p>
                                        @error('tahun')
                                            <p class='text-danger text-xs pt-1'> {{ $message }} </p>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                            <hr class="horizontal dark">
                        </div>
                        <div class="card-footer align-items-center pt-0 pb-2">
                            {{-- Footer kosong sesuai pattern MSJ Framework --}}
                        </div>
                    </form>
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
        $(document).ready(function() {
            // Form validation for filter form
            $('#{{ $dmenu }}-form').on('submit', function(e) {
                const tahun = $('#tahun').val();

                if (!tahun || !$.isNumeric(tahun)) {
                    e.preventDefault();
                    alert('Tahun harus berupa angka');
                    $('#tahun').focus();
                    return false;
                }
            });

            // Focus on tahun input when page loads
            $('#tahun').focus();
        });
    </script>
@endpush
