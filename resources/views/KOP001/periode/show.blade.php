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
                                <h6 class="mb-0">{{ $title_menu }} - Detail</h6>
                                <p class="text-sm mb-0">Informasi detail periode pencairan</p>
                            </div>
                            <div class="btn-group" role="group">
                                <button type="button" class="btn btn-outline-secondary btn-sm" 
                                    onclick="window.location='{{ url($url_menu) }}'">
                                    <i class="fas fa-arrow-left me-1"></i>Kembali
                                </button>
                                @if($authorize->edit == '1' && $list->isactive == '1')
                                    <button type="button" class="btn btn-warning btn-sm"
                                        onclick="window.location='{{ url($url_menu . '/edit/' . encrypt($list->id)) }}'">
                                        <i class="fas fa-edit me-1"></i>Edit
                                    </button>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="card-body">
                        {{-- Alert Messages --}}
                        @include('components.alert')

                        <div class="row">
                            <div class="col-md-6">
                                <div class="card">
                                    <div class="card-header">
                                        <h6 class="mb-0">Informasi Periode</h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="row mb-3">
                                            <div class="col-sm-4">
                                                <strong>ID Periode:</strong>
                                            </div>
                                            <div class="col-sm-8">
                                                <span class="badge bg-primary">{{ $list->id }}</span>
                                            </div>
                                        </div>

                                        <div class="row mb-3">
                                            <div class="col-sm-4">
                                                <strong>Tahun:</strong>
                                            </div>
                                            <div class="col-sm-8">
                                                {{ $list->tahun }}
                                            </div>
                                        </div>

                                        <div class="row mb-3">
                                            <div class="col-sm-4">
                                                <strong>Bulan:</strong>
                                            </div>
                                            <div class="col-sm-8">
                                                @php
                                                    $months = [
                                                        1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
                                                        5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
                                                        9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
                                                    ];
                                                @endphp
                                                {{ $months[$list->bulan] ?? $list->bulan }} ({{ $list->bulan }})
                                            </div>
                                        </div>

                                        <div class="row mb-3">
                                            <div class="col-sm-4">
                                                <strong>Periode:</strong>
                                            </div>
                                            <div class="col-sm-8">
                                                <span class="badge bg-info text-white">
                                                    {{ $months[$list->bulan] ?? $list->bulan }} {{ $list->tahun }}
                                                </span>
                                            </div>
                                        </div>

                                        <div class="row mb-3">
                                            <div class="col-sm-4">
                                                <strong>Status:</strong>
                                            </div>
                                            <div class="col-sm-8">
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
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="card">
                                    <div class="card-header">
                                        <h6 class="mb-0">Informasi Sistem</h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="row mb-3">
                                            <div class="col-sm-4">
                                                <strong>Dibuat:</strong>
                                            </div>
                                            <div class="col-sm-8">
                                                {{ $list->created_at->format('d/m/Y H:i:s') }}
                                            </div>
                                        </div>

                                        <div class="row mb-3">
                                            <div class="col-sm-4">
                                                <strong>Diupdate:</strong>
                                            </div>
                                            <div class="col-sm-8">
                                                {{ $list->updated_at->format('d/m/Y H:i:s') }}
                                            </div>
                                        </div>

                                        <div class="row mb-3">
                                            <div class="col-sm-4">
                                                <strong>User Create:</strong>
                                            </div>
                                            <div class="col-sm-8">
                                                {{ $list->user_create ?? '-' }}
                                            </div>
                                        </div>

                                        <div class="row mb-3">
                                            <div class="col-sm-4">
                                                <strong>User Update:</strong>
                                            </div>
                                            <div class="col-sm-8">
                                                {{ $list->user_update ?? '-' }}
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                {{-- Related Data Card --}}
                                <div class="card mt-3">
                                    <div class="card-header">
                                        <h6 class="mb-0">Data Terkait</h6>
                                    </div>
                                    <div class="card-body">
                                        @php
                                            // Get related piutang count
                                            $piutangCount = \App\Models\TrsPiutang::where('mst_periode_id', $list->id)->count();
                                        @endphp
                                        
                                        <div class="row mb-3">
                                            <div class="col-sm-6">
                                                <strong>Piutang Terkait:</strong>
                                            </div>
                                            <div class="col-sm-6">
                                                <span class="badge bg-{{ $piutangCount > 0 ? 'success' : 'secondary' }}">
                                                    {{ $piutangCount }} Data
                                                </span>
                                            </div>
                                        </div>

                                        @if($piutangCount > 0)
                                            <div class="alert alert-warning">
                                                <i class="fas fa-exclamation-triangle me-2"></i>
                                                <small>Periode ini memiliki {{ $piutangCount }} data piutang terkait. 
                                                Hati-hati saat mengubah status periode.</small>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Action Buttons --}}
                        <div class="row mt-4">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-header">
                                        <h6 class="mb-0">Aksi</h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="btn-group" role="group">
                                            @if($authorize->edit == '1' && $list->isactive == '1')
                                                <button type="button" class="btn btn-warning"
                                                    onclick="window.location='{{ url($url_menu . '/edit/' . encrypt($list->id)) }}'">
                                                    <i class="fas fa-edit me-1"></i>Edit Periode
                                                </button>
                                            @endif

                                            @if($authorize->delete == '1')
                                                <form action="{{ url($url_menu . '/' . encrypt($list->id)) }}" method="POST" style="display: inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" 
                                                        class="btn btn-{{ $list->isactive == '0' ? 'success' : 'danger' }}"
                                                        onclick="return confirmToggle(event, '{{ $list->id }}', '{{ $list->isactive == '0' ? 'Aktifkan' : 'Non-Aktifkan' }}')">
                                                        <i class="fas fa-{{ $list->isactive == '0' ? 'check' : 'times' }} me-1"></i>
                                                        {{ $list->isactive == '0' ? 'Aktifkan' : 'Non-Aktifkan' }} Periode
                                                    </button>
                                                </form>
                                            @endif
                                        </div>
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
function confirmToggle(event, id, action) {
    event.preventDefault();
    
    Swal.fire({
        title: 'Konfirmasi',
        text: `Apakah Anda yakin ingin ${action} periode ${id}?`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: `Ya, ${action}`,
        cancelButtonText: 'Batal',
        confirmButtonColor: '#028284'
    }).then((result) => {
        if (result.isConfirmed) {
            event.target.closest('form').submit();
        }
    });
    
    return false;
}
</script>
@endpush