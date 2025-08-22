@extends('layouts.app', ['class' => 'g-sidenav-show bg-gray-100'])
{{-- section content --}}
@section('content')
    @include('layouts.navbars.auth.topnav', ['title' => ''])
    <div class="card shadow-lg mx-4">
        <div class="card-body p-3">
            <div class="row gx-4">
                <div class="col-lg">
                    <div class="nav-wrapper">
                        {{-- button back --}}
                        <button class="btn btn-secondary mb-0" onclick="history.back()"><i class="fas fa-circle-left me-1">
                            </i><span class="font-weight-bold">Kembali</button>
                        {{-- check authorize add --}}
                        @if (isset($authorize) && $authorize->add == '1')
                            {{-- button save --}}
                            <button class="btn btn-primary mb-0"
                                onclick="event.preventDefault(); document.getElementById('anggota-form').submit();"><i
                                    class="fas fa-floppy-disk me-1"> </i><span class="font-weight-bold">Simpan</button>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="container-fluid py-4">
        <div class="row">
            <div class="col-md-12">
                <form role="form" method="POST" action="{{ URL::to($url_menu) }}" id="anggota-form"
                    enctype="multipart/form-data">
                    @csrf
                    <div class="card">
                        <div class="card-body">
                            <p class="text-uppercase text-sm">Insert {{ $title_menu }}</p>
                            <hr class="horizontal dark mt-0">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="row">
                                        {{-- NIK --}}
                                        <div class="col-md-12">
                                            <div class="form-group">
                                                <label for="nik" class="form-control-label">NIK *</label>
                                                <input class="form-control" type="text" name="nik" id="nik" 
                                                       value="{{ old('nik') }}" maxlength="16" required>
                                                <p class='text-secondary text-xs pt-1 px-1'>*) Nomor Induk Kependudukan (16 digit)</p>
                                                @error('nik')
                                                    <p class='text-danger text-xs pt-1'> {{ $message }} </p>
                                                @enderror
                                            </div>
                                        </div>
                                        {{-- Nama Lengkap --}}
                                        <div class="col-md-12">
                                            <div class="form-group">
                                                <label for="nama_lengkap" class="form-control-label">Nama Lengkap *</label>
                                                <input class="form-control" type="text" name="nama_lengkap" id="nama_lengkap" 
                                                       value="{{ old('nama_lengkap') }}" maxlength="100" required>
                                                @error('nama_lengkap')
                                                    <p class='text-danger text-xs pt-1'> {{ $message }} </p>
                                                @enderror
                                            </div>
                                        </div>
                                        {{-- Jenis Kelamin --}}
                                        <div class="col-md-12">
                                            <div class="form-group">
                                                <label for="jenis_kelamin" class="form-control-label">Jenis Kelamin *</label>
                                                <select class="form-select" name="jenis_kelamin" id="jenis_kelamin" required>
                                                    <option value="">Pilih Jenis Kelamin</option>
                                                    @if(isset($jenisKelaminOptions))
                                                        @foreach($jenisKelaminOptions as $option)
                                                            <option value="{{ $option->value }}" 
                                                                    {{ old('jenis_kelamin') == $option->value ? 'selected' : '' }}>
                                                                {{ $option->value }} - {{ $option->name }}
                                                            </option>
                                                        @endforeach
                                                    @endif
                                                </select>
                                                @error('jenis_kelamin')
                                                    <p class='text-danger text-xs pt-1'> {{ $message }} </p>
                                                @enderror
                                            </div>
                                        </div>
                                        {{-- No Telepon --}}
                                        <div class="col-md-12">
                                            <div class="form-group">
                                                <label for="no_telp" class="form-control-label">No Telepon</label>
                                                <input class="form-control" type="text" name="no_telp" id="no_telp" 
                                                       value="{{ old('no_telp') }}" maxlength="15">
                                                @error('no_telp')
                                                    <p class='text-danger text-xs pt-1'> {{ $message }} </p>
                                                @enderror
                                            </div>
                                        </div>
                                        {{-- Alamat --}}
                                        <div class="col-md-12">
                                            <div class="form-group">
                                                <label for="alamat" class="form-control-label">Alamat</label>
                                                <textarea class="form-control" name="alamat" id="alamat" rows="3" 
                                                          maxlength="255">{{ old('alamat') }}</textarea>
                                                @error('alamat')
                                                    <p class='text-danger text-xs pt-1'> {{ $message }} </p>
                                                @enderror
                                            </div>
                                        </div>
                                        {{-- Tanggal Bergabung --}}
                                        <div class="col-md-12">
                                            <div class="form-group">
                                                <label for="tanggal_bergabung" class="form-control-label">Tanggal Bergabung</label>
                                                <input class="form-control" type="date" name="tanggal_bergabung" id="tanggal_bergabung" 
                                                       value="{{ old('tanggal_bergabung', date('Y-m-d')) }}">
                                                @error('tanggal_bergabung')
                                                    <p class='text-danger text-xs pt-1'> {{ $message }} </p>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="row">
                                        {{-- User Account --}}
                                        <div class="col-md-12">
                                            <div class="form-group">
                                                <label for="user_id" class="form-control-label">User Account</label>
                                                <select class="form-select" name="user_id" id="user_id">
                                                    <option value="">Pilih User (Opsional)</option>
                                                    @if(isset($userOptions))
                                                        @foreach($userOptions as $option)
                                                            <option value="{{ $option->value }}" 
                                                                    {{ old('user_id') == $option->value ? 'selected' : '' }}>
                                                                {{ $option->value }} - {{ $option->name }}
                                                            </option>
                                                        @endforeach
                                                    @endif
                                                </select>
                                                <p class='text-secondary text-xs pt-1 px-1'>*) Hubungkan dengan akun user untuk login</p>
                                                @error('user_id')
                                                    <p class='text-danger text-xs pt-1'> {{ $message }} </p>
                                                @enderror
                                            </div>
                                        </div>
                                        {{-- Departemen --}}
                                        <div class="col-md-12">
                                            <div class="form-group">
                                                <label for="departemen" class="form-control-label">Departemen</label>
                                                <input class="form-control" type="text" name="departemen" id="departemen" 
                                                       value="{{ old('departemen') }}" maxlength="50">
                                                @error('departemen')
                                                    <p class='text-danger text-xs pt-1'> {{ $message }} </p>
                                                @enderror
                                            </div>
                                        </div>
                                        {{-- Jabatan --}}
                                        <div class="col-md-12">
                                            <div class="form-group">
                                                <label for="jabatan" class="form-control-label">Jabatan</label>
                                                <input class="form-control" type="text" name="jabatan" id="jabatan" 
                                                       value="{{ old('jabatan') }}" maxlength="50">
                                                @error('jabatan')
                                                    <p class='text-danger text-xs pt-1'> {{ $message }} </p>
                                                @enderror
                                            </div>
                                        </div>
                                        {{-- Nama Bank --}}
                                        <div class="col-md-12">
                                            <div class="form-group">
                                                <label for="nama_bank" class="form-control-label">Nama Bank</label>
                                                <input class="form-control" type="text" name="nama_bank" id="nama_bank" 
                                                       value="{{ old('nama_bank') }}" maxlength="50" 
                                                       placeholder="Contoh: BCA, Mandiri, BRI">
                                                @error('nama_bank')
                                                    <p class='text-danger text-xs pt-1'> {{ $message }} </p>
                                                @enderror
                                            </div>
                                        </div>
                                        {{-- No Rekening --}}
                                        <div class="col-md-12">
                                            <div class="form-group">
                                                <label for="no_rekening" class="form-control-label">No Rekening</label>
                                                <input class="form-control" type="text" name="no_rekening" id="no_rekening" 
                                                       value="{{ old('no_rekening') }}" maxlength="20">
                                                @error('no_rekening')
                                                    <p class='text-danger text-xs pt-1'> {{ $message }} </p>
                                                @enderror
                                            </div>
                                        </div>
                                        {{-- Nama Pemilik Rekening --}}
                                        <div class="col-md-12">
                                            <div class="form-group">
                                                <label for="nama_pemilik_rekening" class="form-control-label">Nama Pemilik Rekening</label>
                                                <input class="form-control" type="text" name="nama_pemilik_rekening" id="nama_pemilik_rekening" 
                                                       value="{{ old('nama_pemilik_rekening') }}" maxlength="100">
                                                @error('nama_pemilik_rekening')
                                                    <p class='text-danger text-xs pt-1'> {{ $message }} </p>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            {{-- Additional Fields Row --}}
                            <div class="row">
                                <div class="col-md-6">
                                    {{-- Foto KTP --}}
                                    <div class="form-group">
                                        <label for="foto_ktp" class="form-control-label">Foto KTP</label>
                                        <div class="col-sm-auto mb-3">
                                            <div class="position-relative">
                                                <div>
                                                    <label for="foto_ktp" style="left: -5px !important;"
                                                        id="foto_ktpedit"
                                                        class="btn btn-xxl btn-icon-only bg-gradient-primary position-absolute bottom-0 mb-n2">
                                                        <i class="fa fa-pen top-0" data-bs-toggle="tooltip"
                                                            data-bs-placement="top" title=""
                                                            aria-hidden="true"
                                                            data-bs-original-title="Edit Image"
                                                            aria-label="Edit Image"></i>
                                                        <span class="sr-only">Edit Image</span>
                                                    </label>
                                                    <span class="h-12 w-12 rounded-full overflow-hidden bg-gray-100">
                                                        <img src="{{ asset('/storage/noimage.png') }}"
                                                            id="foto_ktppreview" alt="image"
                                                            class="w-30 border-radius-lg shadow-sm">
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                        <input class="form-control" type="file" value="{{ old('foto_ktp') }}"
                                            id="foto_ktp" name="foto_ktp" style="display: none;"
                                            accept="image/jpeg,image/png,image/jpg">
                                        <p class='text-primary text-xs pt-3 mb-0'>Maksimal Size : <b>2048 KB</b></p>
                                        <p class='text-primary text-xs pt-1'>Format Image : <b>PNG,JPG,JPEG</b></p>
                                        @error('foto_ktp')
                                            <p class='text-danger text-xs pt-1'> {{ $message }} </p>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    {{-- Status --}}
                                    <div class="form-group">
                                        <label for="isactive" class="form-control-label">Status *</label>
                                        <select class="form-select" name="isactive" id="isactive" required>
                                            <option value="">Pilih Status</option>
                                            @if(isset($statusOptions))
                                                @foreach($statusOptions as $option)
                                                    <option value="{{ $option->value }}" 
                                                            {{ old('isactive', '1') == $option->value ? 'selected' : '' }}>
                                                        {{ $option->value }} - {{ $option->name }}
                                                    </option>
                                                @endforeach
                                            @endif
                                        </select>
                                        @error('isactive')
                                            <p class='text-danger text-xs pt-1'> {{ $message }} </p>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                            <hr class="horizontal dark">
                            <div class="card-footer align-items-center pt-0 pb-2">
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
@push('js')
<script>
    // Image preview functionality
    foto_ktp.onchange = evt => {
        const [file] = foto_ktp.files
        if (file) {
            // Check file size (2MB = 2048KB)
            if (file.size > 2048 * 1024) {
                alert('Ukuran file terlalu besar. Maksimal 2MB.');
                foto_ktp.value = '';
                return;
            }
            
            // Check file type
            const allowedTypes = ['image/jpeg', 'image/png', 'image/jpg'];
            if (!allowedTypes.includes(file.type)) {
                alert('Format file tidak didukung. Gunakan JPG, JPEG, atau PNG.');
                foto_ktp.value = '';
                return;
            }
            
            foto_ktppreview.src = URL.createObjectURL(file)
        }
    }
    $('#foto_ktpedit').click(function() {
        $('input[name="foto_ktp"]').click();
    });

    // Auto-fill nama pemilik rekening from nama lengkap
    document.getElementById('nama_lengkap').addEventListener('blur', function() {
        const namaPemilikRekening = document.getElementById('nama_pemilik_rekening');
        if (!namaPemilikRekening.value && this.value) {
            namaPemilikRekening.value = this.value;
        }
    });

    // NIK validation (16 digits)
    document.getElementById('nik').addEventListener('input', function() {
        this.value = this.value.replace(/\D/g, ''); // Only numbers
        if (this.value.length > 16) {
            this.value = this.value.slice(0, 16);
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

    // Form validation before submit
    document.getElementById('anggota-form').addEventListener('submit', function(e) {
        const nik = document.getElementById('nik').value;
        const namaLengkap = document.getElementById('nama_lengkap').value;
        const jenisKelamin = document.getElementById('jenis_kelamin').value;
        
        if (nik.length !== 16) {
            e.preventDefault();
            alert('NIK harus 16 digit');
            document.getElementById('nik').focus();
            return;
        }
        
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
    });
</script>
@endpush