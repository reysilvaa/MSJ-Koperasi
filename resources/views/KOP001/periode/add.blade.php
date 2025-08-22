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
                                <h6 class="mb-0">{{ $title_menu }}</h6>
                                <p class="text-sm mb-0">Generate periode pencairan untuk tahun tertentu</p>
                            </div>
                            <div class="btn-group" role="group">
                                <button type="button" class="btn btn-outline-secondary btn-sm" 
                                    onclick="window.location='{{ url($url_menu) }}'">
                                    <i class="fas fa-arrow-left me-1"></i>Kembali
                                </button>
                                @if($authorize->add == '1')
                                    <button type="submit" form="form-add" class="btn btn-primary btn-sm">
                                        <i class="fas fa-save me-1"></i>Generate
                                    </button>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="card-body">
                        {{-- Alert Messages --}}
                        @include('components.alert')

                        @if($authorize->add == '1')
                            <form id="form-add" method="POST" action="{{ url($url_menu) }}">
                                @csrf
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="card">
                                            <div class="card-header">
                                                <h6 class="mb-0">Informasi Periode</h6>
                                            </div>
                                            <div class="card-body">
                                                <div class="mb-3">
                                                    <label for="tahun" class="form-label">
                                                        Tahun <span class="text-danger">*</span>
                                                    </label>
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
                                                        Masukkan tahun untuk generate periode (2020-2030)
                                                    </small>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="card">
                                            <div class="card-header">
                                                <h6 class="mb-0">Informasi Generate</h6>
                                            </div>
                                            <div class="card-body">
                                                <div class="alert alert-info">
                                                    <h6 class="alert-heading">
                                                        <i class="fas fa-info-circle me-2"></i>Informasi
                                                    </h6>
                                                    <p class="mb-2">Sistem akan membuat 12 periode pencairan:</p>
                                                    <ul class="mb-0">
                                                        <li>Januari - Desember untuk tahun yang dipilih</li>
                                                        <li>Periode yang sudah ada akan dilewati</li>
                                                        <li>Semua periode akan dibuat dengan status aktif</li>
                                                    </ul>
                                                </div>

                                                <div class="mt-3">
                                                    <h6>Preview Periode:</h6>
                                                    <div class="row" id="preview-periode">
                                                        <div class="col-12">
                                                            <small class="text-muted">Pilih tahun untuk melihat preview</small>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        @else
                            <div class="alert alert-warning">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                Anda tidak memiliki akses untuk menambah data periode pencairan.
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
    // Update preview when year changes
    $('#tahun').on('input change', function() {
        updatePreview();
    });

    // Initial preview
    updatePreview();

    function updatePreview() {
        const tahun = $('#tahun').val();
        if (tahun && tahun >= 2020 && tahun <= 2030) {
            const months = [
                'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
                'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
            ];
            
            let html = '';
            months.forEach((month, index) => {
                const periodeId = tahun + String(index + 1).padStart(2, '0');
                html += `
                    <div class="col-md-4 mb-2">
                        <div class="badge bg-light text-dark border">
                            <i class="fas fa-calendar me-1"></i>
                            ${month} ${tahun}
                            <small class="text-muted">(${periodeId})</small>
                        </div>
                    </div>
                `;
            });
            
            $('#preview-periode').html(html);
        } else {
            $('#preview-periode').html('<div class="col-12"><small class="text-muted">Masukkan tahun yang valid (2020-2030)</small></div>');
        }
    }

    // Form validation
    $('#form-add').on('submit', function(e) {
        const tahun = $('#tahun').val();
        
        if (!tahun || tahun < 2020 || tahun > 2030) {
            e.preventDefault();
            Swal.fire({
                title: 'Validasi Error',
                text: 'Tahun harus antara 2020-2030',
                icon: 'error',
                confirmButtonColor: '#028284'
            });
            return false;
        }

        // Confirmation dialog
        e.preventDefault();
        Swal.fire({
            title: 'Konfirmasi Generate',
            text: `Apakah Anda yakin ingin generate periode untuk tahun ${tahun}?`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Ya, Generate',
            cancelButtonText: 'Batal',
            confirmButtonColor: '#028284'
        }).then((result) => {
            if (result.isConfirmed) {
                // Show loading
                Swal.fire({
                    title: 'Memproses...',
                    text: 'Sedang generate periode pencairan',
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