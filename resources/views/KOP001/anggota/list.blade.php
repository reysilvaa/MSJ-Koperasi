@extends('layouts.app', ['class' => 'g-sidenav-show bg-gray-100'])

@section('content')
    @include('layouts.navbars.auth.topnav', ['title' => $title_menu])
    
    <div class="container-fluid py-4">
        {{-- Alert Messages --}}
        @if(session('message'))
            <div class="alert alert-{{ session('class', 'info') }} alert-dismissible fade show" role="alert">
                <i class="fas fa-{{ session('class') == 'success' ? 'check-circle' : 'exclamation-circle' }} me-2"></i>
                {{ session('message') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        {{-- Header Card --}}
        <div class="row">
            <div class="col-12">
                <div class="card mb-4">
                    <div class="card-header pb-0 d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="mb-1">{{ $title_menu }}</h6>
                            <p class="text-sm mb-0">Kelola data anggota koperasi</p>
                        </div>
                        <div class="d-flex gap-2">
                            <a href="{{ $url_menu }}/add" class="btn btn-primary btn-sm">
                                <i class="fas fa-plus me-1"></i> Tambah Anggota
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Filter Card --}}
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h6 class="mb-0">Filter Data</h6>
                    </div>
                    <div class="card-body">
                        <form method="GET" action="{{ $url_menu }}">
                            <div class="row">
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label class="form-label">Pencarian</label>
                                        <input type="text" class="form-control" name="search" 
                                               value="{{ $search }}" placeholder="NIK, Nama, No Telp, Departemen">
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label class="form-label">Status</label>
                                        <select class="form-select" name="status">
                                            <option value="">Semua Status</option>
                                            @if(isset($statusOptions))
                                                @foreach($statusOptions as $option)
                                                    <option value="{{ $option->value }}" 
                                                            {{ $status == $option->value ? 'selected' : '' }}>
                                                        {{ $option->name }}
                                                    </option>
                                                @endforeach
                                            @endif
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label class="form-label">Jenis Kelamin</label>
                                        <select class="form-select" name="jenis_kelamin">
                                            <option value="">Semua</option>
                                            @if(isset($jenisKelaminOptions))
                                                @foreach($jenisKelaminOptions as $option)
                                                    <option value="{{ $option->value }}" 
                                                            {{ $jenis_kelamin == $option->value ? 'selected' : '' }}>
                                                        {{ $option->name }}
                                                    </option>
                                                @endforeach
                                            @endif
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label class="form-label">Departemen</label>
                                        <input type="text" class="form-control" name="departemen" 
                                               value="{{ $departemen }}" placeholder="Nama departemen">
                                    </div>
                                </div>
                                <div class="col-md-2 d-flex align-items-end">
                                    <div class="form-group w-100">
                                        <button type="submit" class="btn btn-info w-100">
                                            <i class="fas fa-search me-1"></i> Filter
                                        </button>
                                    </div>
                                </div>
                            </div>
                            @if($search || $status !== '' || $jenis_kelamin || $departemen)
                                <div class="row mt-2">
                                    <div class="col-12">
                                        <a href="{{ $url_menu }}" class="btn btn-secondary btn-sm">
                                            <i class="fas fa-times me-1"></i> Reset Filter
                                        </a>
                                    </div>
                                </div>
                            @endif
                        </form>
                    </div>
                </div>
            </div>
        </div>

        {{-- Data Table --}}
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h6 class="mb-0">Daftar Anggota ({{ $table_detail->count() }} data)</h6>
                    </div>
                    <div class="card-body px-0 pt-0 pb-2">
                        @if($table_detail->count() > 0)
                            <div class="table-responsive p-0">
                                <table class="table align-items-center mb-0">
                                    <thead>
                                        <tr>
                                            <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                                Anggota
                                            </th>
                                            <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                                Kontak
                                            </th>
                                            <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                                Pekerjaan
                                            </th>
                                            <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                                Bank
                                            </th>
                                            <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 text-center">
                                                Status & Akses
                                            </th>
                                            <th class="text-secondary opacity-7">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($table_detail as $item)
                                            <tr>
                                                <td>
                                                    <div class="d-flex px-2 py-1">
                                                        <div>
                                                            @if($item->foto_ktp && $item->foto_ktp !== 'noimage.png')
                                                                <img src="{{ asset('storage/ktp/' . $item->foto_ktp) }}" 
                                                                     class="avatar avatar-sm me-3" alt="KTP">
                                                            @else
                                                                <div class="avatar avatar-sm me-3 bg-gradient-secondary">
                                                                    <i class="fas fa-user text-white"></i>
                                                                </div>
                                                            @endif
                                                        </div>
                                                        <div class="d-flex flex-column justify-content-center">
                                                            <h6 class="mb-0 text-sm">{{ $item->nama_lengkap }}</h6>
                                                            <p class="text-xs text-secondary mb-0">
                                                                NIK: {{ $item->nik }}
                                                            </p>
                                                            <p class="text-xs text-secondary mb-0">
                                                                {{ $item->jenis_kelamin === 'L' ? 'Laki-laki' : 'Perempuan' }}
                                                                @if($item->tanggal_bergabung)
                                                                    • Bergabung: {{ date('d/m/Y', strtotime($item->tanggal_bergabung)) }}
                                                                @endif
                                                            </p>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td>
                                                    <div class="d-flex flex-column">
                                                        @if($item->no_telp)
                                                            <span class="text-xs">
                                                                <i class="fas fa-phone me-1"></i>{{ $item->no_telp }}
                                                            </span>
                                                        @endif
                                                        @if($item->user_email)
                                                            <span class="text-xs text-secondary">
                                                                <i class="fas fa-envelope me-1"></i>{{ $item->user_email }}
                                                            </span>
                                                        @endif
                                                        @if($item->alamat)
                                                            <span class="text-xs text-secondary">
                                                                <i class="fas fa-map-marker-alt me-1"></i>{{ Str::limit($item->alamat, 30) }}
                                                            </span>
                                                        @endif
                                                    </div>
                                                </td>
                                                <td>
                                                    <div class="d-flex flex-column">
                                                        @if($item->departemen)
                                                            <span class="text-xs font-weight-bold">{{ $item->departemen }}</span>
                                                        @endif
                                                        @if($item->jabatan)
                                                            <span class="text-xs text-secondary">{{ $item->jabatan }}</span>
                                                        @endif
                                                    </div>
                                                </td>
                                                <td>
                                                    <div class="d-flex flex-column">
                                                        @if($item->nama_bank)
                                                            <span class="text-xs font-weight-bold">{{ $item->nama_bank }}</span>
                                                        @endif
                                                        @if($item->no_rekening)
                                                            <span class="text-xs text-secondary">{{ $item->no_rekening }}</span>
                                                        @endif
                                                        @if($item->nama_pemilik_rekening)
                                                            <span class="text-xs text-secondary">{{ $item->nama_pemilik_rekening }}</span>
                                                        @endif
                                                    </div>
                                                </td>
                                                <td class="align-middle text-center text-sm">
                                                    <div class="d-flex flex-column align-items-center">
                                                        {{-- Status Anggota --}}
                                                        @php
                                                            $statusLabel = isset($statusOptions) ? $statusOptions->where('value', $item->isactive)->first() : null;
                                                        @endphp
                                                        <span class="badge badge-sm bg-gradient-{{ $item->isactive == '1' ? 'success' : 'danger' }} mb-1">
                                                            {{ $statusLabel ? $statusLabel->name : ($item->isactive == '1' ? 'Active' : 'Not Active') }}
                                                        </span>
                                                        
                                                        {{-- Status Login --}}
                                                        @if($item->user_id)
                                                            <span class="badge badge-sm bg-gradient-info">
                                                                <i class="fas fa-user-check me-1"></i>Login: Aktif
                                                            </span>
                                                            <small class="text-xs text-secondary mt-1">{{ $item->user_username }}</small>
                                                        @else
                                                            <span class="badge badge-sm bg-gradient-secondary">
                                                                <i class="fas fa-user-times me-1"></i>Login: Tidak
                                                            </span>
                                                        @endif
                                                    </div>
                                                </td>
                                                <td class="align-middle">
                                                    <div class="dropdown">
                                                        <button class="btn btn-link text-secondary mb-0" 
                                                                data-bs-toggle="dropdown">
                                                            <i class="fa fa-ellipsis-v text-xs"></i>
                                                        </button>
                                                        <div class="dropdown-menu">
                                                            <a class="dropdown-item" href="{{ $url_menu }}/show/{{ encrypt($item->nik) }}">
                                                                <i class="fas fa-eye me-2"></i>Detail
                                                            </a>
                                                            <a class="dropdown-item" href="{{ $url_menu }}/edit/{{ encrypt($item->nik) }}">
                                                                <i class="fas fa-edit me-2"></i>Edit
                                                            </a>
                                                            <div class="dropdown-divider"></div>
                                                            
                                                            {{-- Toggle Login Access --}}
                                                            @if($item->user_id)
                                                                <a class="dropdown-item text-warning" 
                                                                   href="#" onclick="confirmToggleLogin('{{ encrypt($item->nik) }}', '{{ $item->nama_lengkap }}', true, '{{ $item->user_email }}')">
                                                                    <i class="fas fa-user-times me-2"></i>Hapus Akses Login
                                                                </a>
                                                            @else
                                                                <a class="dropdown-item text-info" 
                                                                   href="#" onclick="confirmToggleLogin('{{ encrypt($item->nik) }}', '{{ $item->nama_lengkap }}', false)">
                                                                    <i class="fas fa-user-plus me-2"></i>Buat Akses Login
                                                                </a>
                                                            @endif
                                                            
                                                            <div class="dropdown-divider"></div>
                                                            <a class="dropdown-item text-{{ $item->isactive == '1' ? 'danger' : 'success' }}" 
                                                               href="#" onclick="confirmToggleStatus('{{ encrypt($item->nik) }}', '{{ $item->nama_lengkap }}', '{{ $item->isactive }}')">
                                                                <i class="fas fa-{{ $item->isactive == '1' ? 'times' : 'check' }} me-2"></i>
                                                                {{ $item->isactive == '1' ? 'Nonaktifkan' : 'Aktifkan' }}
                                                            </a>
                                                        </div>
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="text-center py-5">
                                <i class="fas fa-users fa-3x text-secondary mb-3"></i>
                                <h5 class="text-secondary">Tidak ada data anggota</h5>
                                <p class="text-sm text-secondary">
                                    @if($search || $status !== '' || $jenis_kelamin || $departemen)
                                        Tidak ada data yang sesuai dengan filter yang dipilih.
                                        <br><a href="{{ $url_menu }}">Reset filter</a> untuk melihat semua data.
                                    @else
                                        Belum ada data anggota yang terdaftar.
                                        <br><a href="{{ $url_menu }}/add">Tambah anggota pertama</a>
                                    @endif
                                </p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Toggle Status Confirmation Modal --}}
    <div class="modal fade" id="toggleStatusModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Konfirmasi Perubahan Status</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Apakah Anda yakin ingin <span id="actionText"></span> anggota <strong id="memberName"></strong>?</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <form id="toggleStatusForm" method="POST" style="display: inline;">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn" id="confirmButton">Konfirmasi</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    {{-- Toggle Login Access Confirmation Modal --}}
    <div class="modal fade" id="toggleLoginModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Konfirmasi Akses Login</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div id="loginModalContent">
                        <!-- Content will be populated by JavaScript -->
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <form id="toggleLoginForm" method="POST" style="display: inline;">
                        @csrf
                        @method('POST')
                        <button type="submit" class="btn" id="confirmLoginButton">Konfirmasi</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('js')
<script>
    function confirmToggleStatus(encryptedId, nama, currentStatus) {
        const isActive = currentStatus === '1';
        const actionText = isActive ? 'menonaktifkan' : 'mengaktifkan';
        const buttonClass = isActive ? 'btn-danger' : 'btn-success';
        
        document.getElementById('memberName').textContent = nama;
        document.getElementById('actionText').textContent = actionText;
        document.getElementById('toggleStatusForm').action = `{{ $url_menu }}/destroy/${encryptedId}`;
        
        const confirmButton = document.getElementById('confirmButton');
        confirmButton.className = `btn ${buttonClass}`;
        confirmButton.textContent = isActive ? 'Nonaktifkan' : 'Aktifkan';
        
        var toggleModal = new bootstrap.Modal(document.getElementById('toggleStatusModal'));
        toggleModal.show();
    }

    function confirmToggleLogin(encryptedId, nama, hasUser, userEmail = '') {
        const modalContent = document.getElementById('loginModalContent');
        const confirmButton = document.getElementById('confirmLoginButton');
        
        if (hasUser) {
            // Removing user access
            modalContent.innerHTML = `
                <p>Apakah Anda yakin ingin <strong class="text-danger">menghapus akses login</strong> untuk anggota <strong>${nama}</strong>?</p>
                <div class="alert alert-warning">
                    <h6><i class="fas fa-exclamation-triangle me-1"></i>Peringatan:</h6>
                    <ul class="mb-0">
                        <li>Email: <strong>${userEmail}</strong> akan dihapus</li>
                        <li>Anggota tidak akan bisa login lagi</li>
                        <li>Data akun akan hilang permanen</li>
                    </ul>
                </div>
            `;
            confirmButton.className = 'btn btn-danger';
            confirmButton.textContent = 'Hapus Akses Login';
        } else {
            // Creating user access
            const nik = encryptedId; // This should be the actual NIK, but for security we'll show the pattern
            modalContent.innerHTML = `
                <p>Apakah Anda yakin ingin <strong class="text-success">membuat akses login</strong> untuk anggota <strong>${nama}</strong>?</p>
                <div class="alert alert-info">
                    <h6><i class="fas fa-info-circle me-1"></i>Akun yang akan dibuat:</h6>
                    <ul class="mb-0">
                        <li><strong>Email:</strong> [NIK]@koperasi.local</li>
                        <li><strong>Username:</strong> [NIK]</li>
                        <li><strong>Password:</strong> [NIK]</li>
                    </ul>
                    <small class="text-muted">*NIK akan digunakan sebagai email, username, dan password default</small>
                </div>
            `;
            confirmButton.className = 'btn btn-success';
            confirmButton.textContent = 'Buat Akses Login';
        }
        
        document.getElementById('toggleLoginForm').action = `{{ $url_menu }}/toggle-user-access/${encryptedId}`;
        
        var toggleModal = new bootstrap.Modal(document.getElementById('toggleLoginModal'));
        toggleModal.show();
    }

    // Auto hide alerts after 5 seconds
    setTimeout(function() {
        var alerts = document.querySelectorAll('.alert');
        alerts.forEach(function(alert) {
            var bsAlert = new bootstrap.Alert(alert);
            bsAlert.close();
        });
    }, 5000);
</script>
@endpush