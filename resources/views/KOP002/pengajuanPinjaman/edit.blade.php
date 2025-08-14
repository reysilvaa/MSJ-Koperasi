@extends('layouts.app', ['class' => 'g-sidenav-show bg-gray-100'])

@section('content')
    @include('layouts.navbars.auth.topnav', ['title' => $title_menu])

    <div class="card shadow-lg mx-4">
        <div class="card-body p-3">
            <div class="row gx-4">
                <div class="col-lg">
                    <div class="nav-wrapper">
                        <button class="btn btn-secondary mb-0" onclick="history.back()">
                            <i class="fas fa-circle-left me-1"></i>
                            <span class="font-weight-bold">Kembali</span>
                        </button>
                        @if($authorize->edit == '1')
                            <button class="btn btn-primary mb-0" type="submit" form="pengajuan-form">
                                <i class="fas fa-floppy-disk me-1"></i>
                                <span class="font-weight-bold">Update Pengajuan</span>
                            </button>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="container-fluid py-4">
        <div class="row">
            {{-- Form Pengajuan --}}
            <div class="col-md-8">
                <div class="card">
                    <form role="form" method="POST" action="{{ url($url_menu . '/' . $idencrypt) }}" id="pengajuan-form">
                        @csrf
                        @method('PUT')
                        <div class="card-header pb-0">
                            <h6>Edit Pengajuan Pinjaman</h6>
                            <p class="text-sm mb-0">Edit data pengajuan pinjaman</p>
                        </div>
                        <div class="card-body">
                            {{-- Alert Messages --}}
                            @include('components.alert')

                            <div class="row">
                                {{-- Nomor Pengajuan (Read Only) --}}
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="form-control-label">ID Pengajuan</label>
                                        <input type="text" class="form-control" value="{{ $pengajuan->id }}" readonly>
                                    </div>
                                </div>

                                {{-- Status Pengajuan (Read Only) --}}
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="form-control-label">Status Pengajuan</label>
                                        <input type="text" class="form-control" value="{{ ucfirst(str_replace('_', ' ', $pengajuan->status_pengajuan)) }}" readonly>
                                    </div>
                                </div>

                                {{-- Anggota --}}
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="form-control-label">Anggota <span class="text-danger">*</span></label>
                                        <select class="form-control" name="anggota_id" id="anggota_id" required>
                                            <option value="">Pilih Anggota</option>
                                            @foreach($anggota_list as $anggota)
                                                <option value="{{ $anggota->id }}" {{ $pengajuan->anggota_id == $anggota->id ? 'selected' : '' }}>
                                                    {{ $anggota->nomor_anggota }} - {{ $anggota->nama_lengkap }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                {{-- Paket Pinjaman --}}
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="form-control-label">Paket Pinjaman <span class="text-danger">*</span></label>
                                        <select class="form-control" name="paket_pinjaman_id" id="paket_pinjaman_id" required>
                                            <option value="">Pilih Paket Pinjaman</option>
                                            @foreach($paket_list as $paket)
                                                <option value="{{ $paket->id }}"
                                                        data-bunga="{{ $paket->bunga_per_bulan }}"
                                                        data-stock="{{ $paket->stock_limit - $paket->stock_terpakai }}"
                                                        data-stock-limit="{{ $paket->stock_limit }}"
                                                        data-stock-terpakai="{{ $paket->stock_terpakai }}"
                                                        {{ $pengajuan->paket_pinjaman_id == $paket->id ? 'selected' : '' }}>
                                                    {{ $paket->periode }} (Bunga: {{ $paket->bunga_per_bulan }}% per bulan)
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                {{-- Jumlah Paket --}}
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="form-control-label">Jumlah Paket <span class="text-danger">*</span></label>
                                        <input type="number" class="form-control" name="jumlah_paket_dipilih" id="jumlah_paket_dipilih"
                                               value="{{ $pengajuan->jumlah_paket_dipilih }}" min="1" max="40" required>
                                        <p class="text-secondary text-xs pt-1 px-1">*) Maksimal 40 paket (1 paket = Rp 500.000)</p>
                                    </div>
                                </div>

                                {{-- Tenor Pinjaman --}}
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="form-control-label">Tenor Pinjaman <span class="text-danger">*</span></label>
                                        <select class="form-control" name="tenor_pinjaman" id="tenor_pinjaman" required>
                                            <option value="">Pilih Tenor</option>
                                            @foreach($tenor_list as $tenor)
                                                <option value="{{ $tenor->id }}"
                                                        data-bulan="{{ $tenor->tenor_bulan }}"
                                                        {{ $pengajuan->tenor_pinjaman == $tenor->id ? 'selected' : '' }}>
                                                    {{ $tenor->nama_tenor }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                {{-- Periode Pencairan --}}
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="form-control-label">Periode Pencairan <span class="text-danger">*</span></label>
                                        <select class="form-control" name="periode_pencairan_id" id="periode_pencairan_id" required>
                                            <option value="">Pilih Periode Pencairan</option>
                                            @foreach($periode_list as $periode)
                                                <option value="{{ $periode->id }}"
                                                        {{ $pengajuan->periode_pencairan_id == $periode->id ? 'selected' : '' }}>
                                                    {{ $periode->nama_periode }}
                                                    ({{ date('d/m/Y', strtotime($periode->tanggal_mulai)) }} - {{ date('d/m/Y', strtotime($periode->tanggal_selesai)) }})
                                                </option>
                                            @endforeach
                                        </select>
                                        <p class="text-secondary text-xs pt-1 px-1">*) Pilih periode kapan pinjaman akan dicairkan</p>
                                    </div>
                                </div>

                                {{-- Jenis Pengajuan --}}
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="form-control-label">Jenis Pengajuan <span class="text-danger">*</span></label>
                                        <select class="form-control" name="jenis_pengajuan" id="jenis_pengajuan" required>
                                            <option value="baru" {{ $pengajuan->jenis_pengajuan == 'baru' ? 'selected' : '' }}>Pinjaman Baru</option>
                                            <option value="top_up" {{ $pengajuan->jenis_pengajuan == 'top_up' ? 'selected' : '' }}>Top Up</option>
                                        </select>
                                    </div>
                                </div>

                                {{-- Tujuan Pinjaman --}}
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label class="form-control-label">Tujuan Pinjaman <span class="text-danger">*</span></label>
                                        <textarea class="form-control" name="tujuan_pinjaman" rows="3" maxlength="500" required>{{ $pengajuan->tujuan_pinjaman }}</textarea>
                                        <p class="text-secondary text-xs pt-1 px-1">*) Jelaskan tujuan penggunaan pinjaman (maksimal 500 karakter)</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            {{-- Informasi Perhitungan --}}
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header pb-0">
                        <h6>Informasi Perhitungan</h6>
                    </div>
                    <div class="card-body">
                        <div class="info-item">
                            <small class="text-muted">Jumlah Pinjaman:</small>
                            <p class="mb-2" id="display-jumlah-pinjaman">{{ $format->CurrencyFormat($pengajuan->jumlah_pinjaman) }}</p>
                        </div>
                        <div class="info-item">
                            <small class="text-muted">Bunga per Bulan:</small>
                            <p class="mb-2" id="display-bunga">{{ $pengajuan->bunga_per_bulan }}%</p>
                        </div>
                        <div class="info-item">
                            <small class="text-muted">Cicilan per Bulan:</small>
                            <p class="mb-2" id="display-cicilan">{{ $format->CurrencyFormat($pengajuan->cicilan_per_bulan) }}</p>
                        </div>
                        <div class="info-item">
                            <small class="text-muted">Total Pembayaran:</small>
                            <p class="mb-2" id="display-total">{{ $format->CurrencyFormat($pengajuan->total_pembayaran) }}</p>
                        </div>
                        <hr>
                        <div class="info-item">
                            <small class="text-muted">Stock Tersedia:</small>
                            <p class="mb-0" id="display-stock">-</p>
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
            // Real-time calculation when form values change
            function updateCalculation() {
                const paketSelect = $('#paket_pinjaman_id');
                const jumlahPaket = parseInt($('#jumlah_paket_dipilih').val()) || 1;
                const tenorSelect = $('#tenor_pinjaman');

                if (paketSelect.val() && tenorSelect.val()) {
                    const selectedPaket = paketSelect.find(':selected');
                    const selectedTenor = tenorSelect.find(':selected');
                    const bunga = parseFloat(selectedPaket.data('bunga')) || 0;
                    const stock = parseInt(selectedPaket.data('stock')) || 0;
                    const tenor = parseInt(selectedTenor.data('bulan')) || 1;

                    const nilaiPerPaket = 500000;
                    const jumlahPinjaman = jumlahPaket * nilaiPerPaket;
                    const cicilanPerBulan = (jumlahPinjaman * (1 + (bunga/100))) / tenor;
                    const totalPembayaran = cicilanPerBulan * tenor;

                    // Update display
                    $('#display-jumlah-pinjaman').text(formatCurrency(jumlahPinjaman));
                    $('#display-bunga').text(bunga + '%');
                    $('#display-cicilan').text(formatCurrency(Math.round(cicilanPerBulan)));
                    $('#display-total').text(formatCurrency(Math.round(totalPembayaran)));

                    // Validate stock
                    if (jumlahPaket > stock) {
                        $('#display-stock').html('<span class="text-danger">' + stock + ' paket (Tidak mencukupi!)</span>');
                        $('button[type="submit"]').prop('disabled', true);
                    } else {
                        $('#display-stock').text(stock + ' paket');
                        $('button[type="submit"]').prop('disabled', false);
                    }
                } else {
                    // Reset display
                    $('#display-jumlah-pinjaman').text('{{ $format->CurrencyFormat($pengajuan->jumlah_pinjaman) }}');
                    $('#display-bunga').text('{{ $pengajuan->bunga_per_bulan }}%');
                    $('#display-cicilan').text('{{ $format->CurrencyFormat($pengajuan->cicilan_per_bulan) }}');
                    $('#display-total').text('{{ $format->CurrencyFormat($pengajuan->total_pembayaran) }}');
                    $('#display-stock').text('-');
                }
            }

            // Function to format currency
            function formatCurrency(amount) {
                return new Intl.NumberFormat('id-ID', {
                    style: 'currency',
                    currency: 'IDR',
                    minimumFractionDigits: 0
                }).format(amount);
            }

            // Character counter for tujuan pinjaman
            function updateCharacterCounter() {
                const textarea = $('textarea[name="tujuan_pinjaman"]');
                const maxLength = 500;
                const currentLength = textarea.val().length;
                const remaining = maxLength - currentLength;

                let counterHtml = `<small class="text-muted">${currentLength}/${maxLength} karakter`;
                if (remaining < 50) {
                    counterHtml = `<small class="text-warning">${currentLength}/${maxLength} karakter (sisa: ${remaining})`;
                }
                if (remaining <= 0) {
                    counterHtml = `<small class="text-danger">${currentLength}/${maxLength} karakter (melebihi batas!)`;
                }
                counterHtml += '</small>';

                // Remove existing counter and add new one
                textarea.next('small').remove();
                textarea.after(counterHtml);
            }

            // Event listeners
            $('#paket_pinjaman_id, #jumlah_paket_dipilih, #tenor_pinjaman').on('change input', updateCalculation);
            $('textarea[name="tujuan_pinjaman"]').on('input', updateCharacterCounter);

            // Initial calculation and character counter
            updateCalculation();
            updateCharacterCounter();

            // Form validation
            $('#pengajuan-form').on('submit', function(e) {
                const stockAvailable = parseInt($('#paket_pinjaman_id').find(':selected').data('stock')) || 0;
                const jumlahPaket = parseInt($('#jumlah_paket_dipilih').val()) || 1;

                if (jumlahPaket > stockAvailable) {
                    e.preventDefault();
                    Swal.fire({
                        title: 'Stock Tidak Mencukupi!',
                        text: `Stock tersedia: ${stockAvailable} paket, Anda meminta: ${jumlahPaket} paket`,
                        icon: 'error',
                        confirmButtonColor: '#d33'
                    });
                    return false;
                }

                // Additional validation
                if (jumlahPaket < 1 || jumlahPaket > 40) {
                    e.preventDefault();
                    Swal.fire({
                        title: 'Validasi Error',
                        text: 'Jumlah paket harus antara 1-40 paket!',
                        icon: 'error',
                        confirmButtonColor: '#d33'
                    });
                    return false;
                }
            });

            console.log('PengajuanPinjaman Edit Form JavaScript initialized successfully');
        });
    </script>
@endpush


