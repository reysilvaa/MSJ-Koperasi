@extends('layouts.app', ['class' => 'g-sidenav-show bg-gray-100'])

@section('content')
    @include('layouts.navbars.auth.topnav', ['title' => $title_menu])

    <div class="container-fluid py-4">
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    {{-- Top Navigation Card --}}
                    <div class="card-header pb-0">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <h6 class="mb-0">{{ $title_menu }} - Edit</h6>
                                <p class="text-sm mb-0">Edit periode pencairan {{ $list->id }}</p>
                            </div>
                            <div class="btn-group" role="group">
                                <button type="button" class="btn btn-outline-secondary btn-sm" 
                                    onclick="window.location='{{ url($url_menu) }}'">
                                    <i class="fas fa-arrow-left me-1"></i>Kembali
                                </button>
                                @if($authorize->edit == '1')
                                    <button type="submit" form="form-edit" class="btn btn-primary btn-sm">
                                        <i class="fas fa-save me-1"></i>Simpan
                                    </button>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="card-body">
                        {{-- Alert Messages --}}
                        @include('components.alert')

                        @if($authorize->edit == '1')
                            <form id="form-edit" method="POST" action="{{ url($url_menu . '/' . encrypt($list->id)) }}">
                                @csrf
                                @method('PUT')
                                
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="card">
                                            <div class="card-header">
                                                <h6 class="mb-0">Informasi Periode</h6>
                                            </div>
                                            <div class="card-body">
                                                <div class="mb-3">
                                                    <label for="id" class="form-label">ID Periode</label>
                                                    <input type="text" class="form-control" id="id" value="{{ $list->id }}" readonly>
                                                    <small class="form-text text-muted">ID periode tidak dapat diubah</small>
                                                </div>

                                                <div class="mb-3">
                                                    <label for="tahun" class="form-label">
                                                        Tahun <span class="text-danger">*</span>
                                                    </label>
                                                    <input type="number"
                                                        class="form-control @error('tahun') is-invalid @enderror"
                                                        id="tahun"
                                                        name="tahun"
                                                        value="{{ old('tahun', $list->tahun) }}"
                                                        min="2020"
                                                        max="2030"
                                                        required>
                                                    @error('tahun')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                    <small class="form-text text-muted">
                                                        Tahun periode (2020-2030)
                                                    </small>
                                                </div>

                                                <div class="mb-3">
                                                    <label for="bulan" class="form-label">
                                                        Bulan <span class="text-danger">*</span>
                                                    </label>
                                                    <select class="form-control @error('bulan') is-invalid @enderror" 
                                                        id="bulan" name="bulan" required>
                                                        <option value="">Pilih Bulan</option>
                                                        @php
                                                            $months = [
                                                                1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
                                                                5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
                                                                9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
                                                            ];
                                                        @endphp
                                                        @foreach($months as $num => $name)
                                                            <option value="{{ $num }}" 
                                                                {{ old('bulan', $list->bulan) == $num ? 'selected' : '' }}>
                                                                {{ $name }} ({{ $num }})
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                    @error('bulan')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                    <small class="form-text text-muted">
                                                        Pilih bulan untuk periode
                                                    </small>
                                                </div>

                                                <div class="mb-3">
                                                    <label class="form-label">Preview Periode</label>
                                                    <div class="form-control-plaintext">
                                                        <span id="preview-periode" class="badge bg-info text-white">
                                                            {{ $months[$list->bulan] ?? $list->bulan }} {{ $list->tahun }}
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="card">
                                            <div class="card-header">
                                                <h6 class="mb-0">Informasi Status</h6>
                                            </div>
                                            <div class="card-body">
                                                <div class="mb-3">
                                                    <label class="form-label">Status Saat Ini</label>
                                                    <div class="form-control-plaintext">
                                                        @if($list->isactive == '1')
                                                            <span class="badge bg-success">
                                                                <i class="fas fa-check-circle me-1"></i>Aktif
                                                            </span>
                                                        @else
                                                            <span class="badge bg-secondary">
                                                                <i class="fas fa-times-circle me-1"></i>Non-Aktif
                                                            </span>
                                                        @endif
                                                    </div>
                                                </div>

                                                <div class="mb-3">
                                                    <label class="form-label">Dibuat</label>
                                                    <div class="form-control-plaintext">
                                                        {{ $list->created_at->format('d/m/Y H:i:s') }}
                                                    </div>
                                                </div>

                                                <div class="mb-3">
                                                    <label class="form-label">Terakhir Diupdate</label>
                                                    <div class="form-control-plaintext">
                                                        {{ $list->updated_at->format('d/m/Y H:i:s') }}
                                                    </div>
                                                </div>

                                                <div class="mb-3">
                                                    <label class="form-label">User Create</label>
                                                    <div class="form-control-plaintext">
                                                        {{ $list->user_create ?? '-' }}
                                                    </div>
                                                </div>

                                                <div class="mb-3">
                                                    <label class="form-label">User Update</label>
                                                    <div class="form-control-plaintext">
                                                        {{ $list->user_update ?? '-' }}
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        {{-- Warning Card --}}
                                        @php
                                            $piutangCount = \App\Models\TrsPiutang::where('mst_periode_id', $list->id)->count();
                                        @endphp
                                        
                                        @if($piutangCount > 0)
                                            <div class="card mt-3">
                                                <div class="card-header bg-warning">
                                                    <h6 class="mb-0 text-white">
                                                        <i class="fas fa-exclamation-triangle me-2"></i>Peringatan
                                                    </h6>
                                                </div>
                                                <div class="card-body">
                                                    <p class="mb-2">
                                                        Periode ini memiliki <strong>{{ $piutangCount }} data piutang</strong> terkait.
                                                    </p>
                                                    <p class="mb-0 text-sm">
                                                        Perubahan pada periode ini dapat mempengaruhi data piutang yang sudah ada.
                                                        Pastikan perubahan yang dilakukan sudah benar.
                                                    </p>
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </form>
                        @else
                            <div class="alert alert-warning">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                Anda tidak memiliki akses untuk mengedit data periode pencairan.
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('js')
<script>
$(document).ready(function() {
    const months = {
        1: 'Januari', 2: 'Februari', 3: 'Maret', 4: 'April',
        5: 'Mei', 6: 'Juni', 7: 'Juli', 8: 'Agustus',
        9: 'September', 10: 'Oktober', 11: 'November', 12: 'Desember'
    };

    // Update preview when inputs change
    $('#tahun, #bulan').on('input change', function() {
        updatePreview();
    });

    function updatePreview() {
        const tahun = $('#tahun').val();
        const bulan = $('#bulan').val();
        
        if (tahun && bulan) {
            const monthName = months[parseInt(bulan)] || bulan;
            $('#preview-periode').text(`${monthName} ${tahun}`);
        } else {
            $('#preview-periode').text('Pilih tahun dan bulan');
        }
    }

    // Form validation and submission
    $('#form-edit').on('submit', function(e) {
        e.preventDefault();
        
        const tahun = $('#tahun').val();
        const bulan = $('#bulan').val();
        
        // Validation
        if (!tahun || tahun < 2020 || tahun > 2030) {
            Swal.fire({
                title: 'Validasi Error',
                text: 'Tahun harus antara 2020-2030',
                icon: 'error',
                confirmButtonColor: '#028284'
            });
            return false;
        }
        
        if (!bulan || bulan < 1 || bulan > 12) {
            Swal.fire({
                title: 'Validasi Error',
                text: 'Pilih bulan yang valid',
                icon: 'error',
                confirmButtonColor: '#028284'
            });
            return false;
        }

        // Confirmation dialog
        const monthName = months[parseInt(bulan)];
        Swal.fire({
            title: 'Konfirmasi Perubahan',
            text: `Apakah Anda yakin ingin mengubah periode menjadi ${monthName} ${tahun}?`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Ya, Simpan',
            cancelButtonText: 'Batal',
            confirmButtonColor: '#028284'
        }).then((result) => {
            if (result.isConfirmed) {
                // Show loading
                Swal.fire({
                    title: 'Menyimpan...',
                    text: 'Sedang menyimpan perubahan',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });
                
                // Submit form
                this.submit();
            }
        });
    });
});
</script>
@endpush