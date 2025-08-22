@extends('layouts.app', ['class' => 'g-sidenav-show bg-gray-100'])

@section('content')
    @include('layouts.navbars.auth.topnav', ['title' => $title_menu])
    
    <div class="container-fluid py-4">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header pb-0">
                        <div class="d-flex align-items-center">
                            <h6 class="mb-0">Edit Anggota: {{ $anggota->nama_lengkap }}</h6>
                            <div class="ms-auto d-flex gap-2">
                                <a href="{{ route('anggota.show', $anggota->nik) }}" class="btn btn-info btn-sm">
                                    <i class="fas fa-eye me-1"></i> Detail
                                </a>
                                <a href="{{ route('anggota.index') }}" class="btn btn-secondary btn-sm">
                                    <i class="fas fa-arrow-left me-1"></i> Kembali
                                </a>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        @if($errors->any())
                            <div class="alert alert-danger">
                                <ul class="mb-0">
                                    @foreach($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <form method="POST" action="{{ route('anggota.update', $anggota->nik) }}" enctype="multipart/form-data">
                            @csrf
                            @method('PUT')
                            
                            {{-- Data Pribadi --}}
                            <div class="row">
                                <div class="col-12">
                                    <h6 class="text-primary">Data Pribadi</h6>
                                    <hr class="horizontal dark mt-0">
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="nik" class="form-control-label">NIK</label>
                                        <input class="form-control" type="text" name="nik" id="nik" 
                                               value="{{ $anggota->nik }}" readonly>
                                        <small class="form-text text-muted">NIK tidak dapat diubah</small>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    {{-- Toggle Akses Login --}}
                                    <div class="form-group">
                                        <div class="d-flex align-items-center justify-content-between border rounded p-3">
                                            <div>
                                                <label class="form-control-label mb-1">Akses Login</label>
                                                <p class="text-xs text-secondary mb-0">
                                                    @if($anggota->user_id)
                                                        <span class="text-success">
                                                            <i class="fas fa-check-circle me-1"></i>Sudah memiliki akses login
                                                        </span>
                                                        <br><small>Email: {{ $anggota->user_email ?? '-' }}</small>
                                                    @else
                                                        <span class="text-muted">
                                                            <i class="fas fa-times-circle me-1"></i>Belum memiliki akses login
                                                        </span>
                                                    @endif
                                                </p>
                                            </div>
                                            <div class="form-check form-switch">
                                                <input class="form-check-input" type="checkbox" 
                                                       name="create_user_access" id="create_user_access" value="1"
                                                       {{ ($anggota->user_id || old('create_user_access')) ? 'checked' : '' }}>
                                                <label class="form-check-label" for="create_user_access"></label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-8">
                                    <div class="form-group">
                                        <label for="nama_lengkap" class="form-control-label">Nama Lengkap *</label>
                                        <input class="form-control" type="text" name="nama_lengkap" id="nama_lengkap" 
                                               value="{{ old('nama_lengkap', $anggota->nama_lengkap) }}" maxlength="100" required>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="jenis_kelamin" class="form-control-label">Jenis Kelamin *</label>
                                        <select class="form-select" name="jenis_kelamin" id="jenis_kelamin" required>
                                            <option value="">Pilih Jenis Kelamin</option>
                                            @foreach($jenisKelaminOptions as $option)
                                                <option value="{{ $option->value }}" 
                                                        {{ old('jenis_kelamin', $anggota->jenis_kelamin) == $option->value ? 'selected' : '' }}>
                                                    {{ $option->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="no_telp" class="form-control-label">No Telepon</label>
                                        <input class="form-control" type="text" name="no_telp" id="no_telp" 
                                               value="{{ old('no_telp', $anggota->no_telp) }}" maxlength="15">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="tanggal_bergabung" class="form-control-label">Tanggal Bergabung</label>
                                        <input class="form-control" type="date" name="tanggal_bergabung" id="tanggal_bergabung" 
                                               value="{{ old('tanggal_bergabung', $anggota->tanggal_bergabung) }}">
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-12">
                                    <div class="form-group">
                                        <label for="alamat" class="form-control-label">Alamat</label>
                                        <textarea class="form-control" name="alamat" id="alamat" rows="3" 
                                                  maxlength="255">{{ old('alamat', $anggota->alamat) }}</textarea>
                                    </div>
                                </div>
                            </div>

                            {{-- Data Pekerjaan --}}
                            <div class="row mt-4">
                                <div class="col-12">
                                    <h6 class="text-primary">Data Pekerjaan</h6>
                                    <hr class="horizontal dark mt-0">
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="departemen" class="form-control-label">Departemen</label>
                                        <input class="form-control" type="text" name="departemen" id="departemen" 
                                               value="{{ old('departemen', $anggota->departemen) }}" maxlength="50">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="jabatan" class="form-control-label">Jabatan</label>
                                        <input class="form-control" type="text" name="jabatan" id="jabatan" 
                                               value="{{ old('jabatan', $anggota->jabatan) }}" maxlength="50">
                                    </div>
                                </div>
                            </div>

                            {{-- Data Bank --}}
                            <div class="row mt-4">
                                <div class="col-12">
                                    <h6 class="text-primary">Data Bank</h6>
                                    <hr class="horizontal dark mt-0">
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="nama_bank" class="form-control-label">Nama Bank</label>
                                        <input class="form-control" type="text" name="nama_bank" id="nama_bank" 
                                               value="{{ old('nama_bank', $anggota->nama_bank) }}" maxlength="50" 
                                               placeholder="Contoh: BCA, Mandiri, BRI">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="no_rekening" class="form-control-label">No Rekening</label>
                                        <input class="form-control" type="text" name="no_rekening" id="no_rekening" 
                                               value="{{ old('no_rekening', $anggota->no_rekening) }}" maxlength="20">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="nama_pemilik_rekening" class="form-control-label">Nama Pemilik Rekening</label>
                                        <input class="form-control" type="text" name="nama_pemilik_rekening" id="nama_pemilik_rekening" 
                                               value="{{ old('nama_pemilik_rekening', $anggota->nama_pemilik_rekening) }}" maxlength="100">
                                    </div>
                                </div>
                            </div>

                            {{-- Upload & Status --}}
                            <div class="row mt-4">
                                <div class="col-12">
                                    <h6 class="text-primary">Upload & Status</h6>
                                    <hr class="horizontal dark mt-0">
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="foto_ktp" class="form-control-label">Foto KTP</label>
                                        
                                        {{-- Current Image --}}
                                        @if($anggota->foto_ktp && $anggota->foto_ktp !== 'noimage.png')
                                            <div class="mb-2">
                                                <label class="form-label text-sm">Foto Saat Ini:</label>
                                                <div>
                                                    <img src="{{ asset('storage/ktp/' . $anggota->foto_ktp) }}" 
                                                         alt="KTP" class="img-thumbnail" style="max-width: 200px;">
                                                </div>
                                            </div>
                                        @endif
                                        
                                        <input class="form-control" type="file" name="foto_ktp" id="foto_ktp" 
                                               accept="image/jpeg,image/png,image/jpg">
                                        <small class="form-text text-muted">
                                            Format: JPG, JPEG, PNG. Maksimal 2MB. 
                                            @if($anggota->foto_ktp && $anggota->foto_ktp !== 'noimage.png')
                                                Kosongkan jika tidak ingin mengubah foto.
                                            @endif
                                        </small>
                                        
                                        {{-- Preview New Image --}}
                                        <div id="preview-container" class="mt-2" style="display: none;">
                                            <label class="form-label text-sm">Preview Foto Baru:</label>
                                            <div>
                                                <img id="preview-image" src="" alt="Preview" class="img-thumbnail" style="max-width: 200px;">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="isactive" class="form-control-label">Status *</label>
                                        <select class="form-select" name="isactive" id="isactive" required>
                                            @foreach($statusOptions as $option)
                                                <option value="{{ $option->value }}" 
                                                        {{ old('isactive', $anggota->isactive) == $option->value ? 'selected' : '' }}>
                                                    {{ $option->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    
                                    {{-- Info box for login credentials preview --}}
                                    <div id="login-info" class="mt-3 p-3 bg-light rounded" style="display: none;">
                                        <h6 class="text-sm mb-2">
                                            <i class="fas fa-info-circle text-info me-1"></i>
                                            <span id="login-info-title">Informasi Login</span>
                                        </h6>
                                        <div class="text-xs">
                                            <div class="mb-1">
                                                <strong>Email:</strong> <span id="preview-email">-</span>
                                            </div>
                                            <div class="mb-1">
                                                <strong>Username:</strong> <span id="preview-username">-</span>
                                            </div>
                                            <div>
                                                <strong>Password:</strong> <span id="preview-password">-</span>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    {{-- Audit Info --}}
                                    <div class="mt-3">
                                        <small class="text-muted">
                                            <strong>Dibuat:</strong> {{ date('d/m/Y H:i', strtotime($anggota->created_at)) }}
                                            @if($anggota->user_create)
                                                oleh {{ $anggota->user_create }}
                                            @endif
                                            <br>
                                            <strong>Diubah:</strong> {{ date('d/m/Y H:i', strtotime($anggota->updated_at)) }}
                                            @if($anggota->user_update)
                                                oleh {{ $anggota->user_update }}
                                            @endif
                                        </small>
                                    </div>
                                </div>
                            </div>

                            {{-- Submit Buttons --}}
                            <div class="row mt-4">
                                <div class="col-12">
                                    <div class="d-flex justify-content-end gap-2">
                                        <a href="{{ route('anggota.show', $anggota->nik) }}" class="btn btn-info">
                                            <i class="fas fa-eye me-1"></i> Detail
                                        </a>
                                        <a href="{{ route('anggota.index') }}" class="btn btn-secondary">
                                            <i class="fas fa-times me-1"></i> Batal
                                        </a>
                                        <button type="reset" class="btn btn-warning">
                                            <i class="fas fa-undo me-1"></i> Reset
                                        </button>
                                        <button type="submit" class="btn btn-primary">
                                            <i class="fas fa-save me-1"></i> Update
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('js')
<script>
    // Store initial state
    const initialHasUser = {{ $anggota->user_id ? 'true' : 'false' }};
    const currentEmail = '{{ $anggota->user_email ?? '' }}';
    const currentUsername = '{{ $anggota->user_username ?? '' }}';
    const nik = '{{ $anggota->nik }}';

    // Preview image upload
    document.getElementById('foto_ktp').addEventListener('change', function(e) {
        const file = e.target.files[0];
        const previewContainer = document.getElementById('preview-container');
        const previewImage = document.getElementById('preview-image');
        
        if (file) {
            // Check file size (2MB = 2048KB)
            if (file.size > 2048 * 1024) {
                alert('Ukuran file terlalu besar. Maksimal 2MB.');
                e.target.value = '';
                previewContainer.style.display = 'none';
                return;
            }
            
            // Check file type
            const allowedTypes = ['image/jpeg', 'image/png', 'image/jpg'];
            if (!allowedTypes.includes(file.type)) {
                alert('Format file tidak didukung. Gunakan JPG, JPEG, atau PNG.');
                e.target.value = '';
                previewContainer.style.display = 'none';
                return;
            }
            
            // Show preview
            const reader = new FileReader();
            reader.onload = function(e) {
                previewImage.src = e.target.result;
                previewContainer.style.display = 'block';
            };
            reader.readAsDataURL(file);
        } else {
            previewContainer.style.display = 'none';
        }
    });

    // Phone number validation
    document.getElementById('no_telp').addEventListener('input', function() {
        this.value = this.value.replace(/\D/g, ''); // Only numbers
    });

    // Account number validation
    document.getElementById('no_rekening').addEventListener('input', function() {
        this.value = this.value.replace(/\D/g, ''); // Only numbers
    });

    // Toggle user access functionality
    document.getElementById('create_user_access').addEventListener('change', function() {
        updateLoginPreview();
    });

    // Function to update login credentials preview
    function updateLoginPreview() {
        const toggleChecked = document.getElementById('create_user_access').checked;
        const loginInfo = document.getElementById('login-info');
        const loginTitle = document.getElementById('login-info-title');
        
        if (toggleChecked) {
            if (initialHasUser) {
                // Show existing user info
                loginTitle.textContent = 'Login Saat Ini';
                document.getElementById('preview-email').textContent = currentEmail || (nik + '@koperasi.local');
                document.getElementById('preview-username').textContent = currentUsername || nik;
                document.getElementById('preview-password').textContent = 'Tidak berubah';
            } else {
                // Show new user info that will be created
                loginTitle.textContent = 'Login Akan Dibuat';
                const newEmail = nik + '@koperasi.local';
                const newUsername = nik;
                const newPassword = nik;
                
                document.getElementById('preview-email').textContent = newEmail;
                document.getElementById('preview-username').textContent = newUsername;
                document.getElementById('preview-password').textContent = newPassword;
            }
            loginInfo.style.display = 'block';
        } else {
            if (initialHasUser) {
                // Show warning that user will be deleted
                loginTitle.textContent = 'Login Akan Dihapus';
                document.getElementById('preview-email').textContent = currentEmail + ' (akan dihapus)';
                document.getElementById('preview-username').textContent = currentUsername + ' (akan dihapus)';
                document.getElementById('preview-password').textContent = 'Akses login akan dihapus';
                loginInfo.style.display = 'block';
            } else {
                loginInfo.style.display = 'none';
            }
        }
    }

    // Form validation before submit
    document.querySelector('form').addEventListener('submit', function(e) {
        const namaLengkap = document.getElementById('nama_lengkap').value;
        const jenisKelamin = document.getElementById('jenis_kelamin').value;
        const createUserAccess = document.getElementById('create_user_access').checked;
        
        if (!namaLengkap.trim()) {
            e.preventDefault();
            alert('Nama lengkap harus diisi');
            document.getElementById('nama_lengkap').focus();
            return;
        }
        
        if (!jenisKelamin) {
            e.preventDefault();
            alert('Jenis kelamin harus dipilih');
            document.getElementById('jenis_kelamin').focus();
            return;
        }
        
        // Confirmation for user access changes
        if (createUserAccess !== initialHasUser) {
            let confirmMessage;
            
            if (createUserAccess && !initialHasUser) {
                // Creating new user access
                confirmMessage = `Akan dibuat akses login dengan:\n\nEmail: ${nik}@koperasi.local\nUsername: ${nik}\nPassword: ${nik}\n\nLanjutkan?`;
            } else if (!createUserAccess && initialHasUser) {
                // Removing existing user access
                confirmMessage = `Akses login akan dihapus!\n\nEmail: ${currentEmail}\nUsername: ${currentUsername}\n\nAnggota tidak akan bisa login lagi. Lanjutkan?`;
            }
            
            if (confirmMessage && !confirm(confirmMessage)) {
                e.preventDefault();
                return;
            }
        }
    });

    // Initialize preview on page load
    document.addEventListener('DOMContentLoaded', function() {
        updateLoginPreview();
    });
</script>
@endpush