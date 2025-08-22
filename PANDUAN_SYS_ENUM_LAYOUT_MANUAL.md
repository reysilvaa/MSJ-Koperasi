# Panduan Memanfaatkan sys_enum dalam Layout Manual

## Pengenalan sys_enum

`sys_enum` adalah tabel yang menyimpan data enumerasi (pilihan tetap) yang dapat digunakan di seluruh aplikasi. Tabel ini memiliki struktur:

- `idenum`: Identifier/kategori enum (contoh: 'isactive', 'status', 'layout')
- `value`: Nilai enum (contoh: '1', '0', 'manual')
- `name`: Label yang ditampilkan (contoh: 'Active', 'Not Active', 'Manual')
- `isactive`: Status aktif/tidak (1/0)

## Cara Menggunakan sys_enum dalam Layout Manual

### 1. Membuat Data Enum Baru

Pertama, tambahkan data enum ke dalam seeder atau langsung ke database:

```php
// Di dalam seeder
DB::table('sys_enum')->insert([
    'idenum' => 'status_koperasi',
    'value' => '1',
    'name' => 'Aktif'
]);

DB::table('sys_enum')->insert([
    'idenum' => 'status_koperasi',
    'value' => '0',
    'name' => 'Tidak Aktif'
]);

DB::table('sys_enum')->insert([
    'idenum' => 'jenis_simpanan',
    'value' => 'pokok',
    'name' => 'Simpanan Pokok'
]);

DB::table('sys_enum')->insert([
    'idenum' => 'jenis_simpanan',
    'value' => 'wajib',
    'name' => 'Simpanan Wajib'
]);
```

### 2. Menggunakan sys_enum dalam Controller (Layout Manual)

Untuk layout manual, Anda perlu mengambil data enum di controller dan mengirimkannya ke view:

```php
<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class KoperasiController extends Controller
{
    public function index()
    {
        // Mengambil data enum untuk dropdown/select
        $statusOptions = DB::table('sys_enum')
            ->where('idenum', 'status_koperasi')
            ->where('isactive', '1')
            ->select('value', 'name')
            ->get();

        $jenisSimpananOptions = DB::table('sys_enum')
            ->where('idenum', 'jenis_simpanan')
            ->where('isactive', '1')
            ->select('value', 'name')
            ->get();

        // Data untuk form atau tampilan
        $data = [
            'statusOptions' => $statusOptions,
            'jenisSimpananOptions' => $jenisSimpananOptions,
            'title' => 'Data Koperasi'
        ];

        return view('pages.koperasi.index', $data);
    }

    public function create()
    {
        // Mengambil enum untuk form create
        $statusOptions = DB::table('sys_enum')
            ->where('idenum', 'status_koperasi')
            ->where('isactive', '1')
            ->pluck('name', 'value');

        return view('pages.koperasi.create', compact('statusOptions'));
    }

    public function store(Request $request)
    {
        // Validasi dengan enum values
        $validStatuses = DB::table('sys_enum')
            ->where('idenum', 'status_koperasi')
            ->where('isactive', '1')
            ->pluck('value')
            ->toArray();

        $request->validate([
            'nama' => 'required|string|max:255',
            'status' => 'required|in:' . implode(',', $validStatuses),
            'jenis_simpanan' => 'required'
        ]);

        // Simpan data
        // ...
    }
}
```

### 3. Menggunakan sys_enum dalam View Blade

#### a. Dropdown/Select dengan sys_enum

```blade
{{-- resources/views/pages/koperasi/create.blade.php --}}
@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h6>Form Koperasi</h6>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('koperasi.store') }}">
                        @csrf
                        
                        <div class="mb-3">
                            <label class="form-label">Nama Koperasi</label>
                            <input type="text" class="form-control" name="nama" required>
                        </div>

                        {{-- Dropdown menggunakan sys_enum --}}
                        <div class="mb-3">
                            <label class="form-label">Status</label>
                            <select class="form-select" name="status" required>
                                <option value="">Pilih Status</option>
                                @foreach($statusOptions as $value => $name)
                                    <option value="{{ $value }}">{{ $name }}</option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Multiple select dengan sys_enum --}}
                        <div class="mb-3">
                            <label class="form-label">Jenis Simpanan</label>
                            <select class="form-select" name="jenis_simpanan[]" multiple>
                                @foreach($jenisSimpananOptions as $option)
                                    <option value="{{ $option->value }}">{{ $option->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <button type="submit" class="btn btn-primary">Simpan</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
```

#### b. Radio Button dengan sys_enum

```blade
<div class="mb-3">
    <label class="form-label">Status Koperasi</label>
    <div>
        @foreach($statusOptions as $option)
            <div class="form-check">
                <input class="form-check-input" type="radio" 
                       name="status" value="{{ $option->value }}" 
                       id="status_{{ $option->value }}">
                <label class="form-check-label" for="status_{{ $option->value }}">
                    {{ $option->name }}
                </label>
            </div>
        @endforeach
    </div>
</div>
```

#### c. Checkbox dengan sys_enum

```blade
<div class="mb-3">
    <label class="form-label">Jenis Layanan</label>
    <div>
        @foreach($jenisLayananOptions as $option)
            <div class="form-check">
                <input class="form-check-input" type="checkbox" 
                       name="jenis_layanan[]" value="{{ $option->value }}" 
                       id="layanan_{{ $option->value }}">
                <label class="form-check-label" for="layanan_{{ $option->value }}">
                    {{ $option->name }}
                </label>
            </div>
        @endforeach
    </div>
</div>
```

### 4. Menampilkan Data dengan Label dari sys_enum

```blade
{{-- Dalam tabel list data --}}
<table class="table">
    <thead>
        <tr>
            <th>Nama</th>
            <th>Status</th>
            <th>Jenis Simpanan</th>
        </tr>
    </thead>
    <tbody>
        @foreach($dataKoperasi as $item)
            <tr>
                <td>{{ $item->nama }}</td>
                <td>
                    {{-- Menampilkan label dari enum --}}
                    @php
                        $statusLabel = DB::table('sys_enum')
                            ->where('idenum', 'status_koperasi')
                            ->where('value', $item->status)
                            ->value('name');
                    @endphp
                    <span class="badge bg-{{ $item->status == '1' ? 'success' : 'danger' }}">
                        {{ $statusLabel }}
                    </span>
                </td>
                <td>{{ $item->jenis_simpanan }}</td>
            </tr>
        @endforeach
    </tbody>
</table>
```

### 5. Helper Function untuk sys_enum

Buat helper function untuk memudahkan penggunaan:

```php
// Di app/Helpers/EnumHelper.php
<?php

class EnumHelper
{
    public static function getEnumOptions($idenum, $activeOnly = true)
    {
        $query = DB::table('sys_enum')->where('idenum', $idenum);
        
        if ($activeOnly) {
            $query->where('isactive', '1');
        }
        
        return $query->select('value', 'name')->get();
    }

    public static function getEnumLabel($idenum, $value)
    {
        return DB::table('sys_enum')
            ->where('idenum', $idenum)
            ->where('value', $value)
            ->value('name');
    }

    public static function getEnumArray($idenum, $activeOnly = true)
    {
        $query = DB::table('sys_enum')->where('idenum', $idenum);
        
        if ($activeOnly) {
            $query->where('isactive', '1');
        }
        
        return $query->pluck('name', 'value')->toArray();
    }
}
```

Penggunaan helper:

```php
// Di Controller
$statusOptions = EnumHelper::getEnumOptions('status_koperasi');
$statusArray = EnumHelper::getEnumArray('status_koperasi');

// Di View
{{ EnumHelper::getEnumLabel('status_koperasi', $item->status) }}
```

### 6. JavaScript untuk Dynamic Enum

```javascript
// Mengambil enum via AJAX
function loadEnumOptions(idenum, targetSelect) {
    $.ajax({
        url: '/api/enum/' + idenum,
        method: 'GET',
        success: function(data) {
            var options = '<option value="">Pilih...</option>';
            data.forEach(function(item) {
                options += '<option value="' + item.value + '">' + item.name + '</option>';
            });
            $(targetSelect).html(options);
        }
    });
}

// Penggunaan
loadEnumOptions('status_koperasi', '#status_select');
```

### 7. API Endpoint untuk Enum

```php
// Di routes/api.php
Route::get('/enum/{idenum}', function($idenum) {
    return DB::table('sys_enum')
        ->where('idenum', $idenum)
        ->where('isactive', '1')
        ->select('value', 'name')
        ->get();
});
```

## Keuntungan Menggunakan sys_enum

1. **Konsistensi**: Data pilihan yang sama di seluruh aplikasi
2. **Mudah Maintenance**: Perubahan data enum hanya di satu tempat
3. **Fleksibilitas**: Dapat menambah/mengurangi pilihan tanpa mengubah kode
4. **Validasi**: Mudah untuk validasi input
5. **Internationalization**: Dapat disesuaikan untuk multi-bahasa

## Tips Penggunaan

1. Gunakan nama `idenum` yang deskriptif
2. Konsisten dalam penamaan `value` (gunakan lowercase, underscore)
3. Buat seeder untuk data enum yang sering digunakan
4. Gunakan helper function untuk menghindari duplikasi kode
5. Cache data enum yang sering diakses untuk performa yang lebih baik

Dengan mengikuti panduan ini, Anda dapat memanfaatkan `sys_enum` secara optimal dalam layout manual untuk membuat aplikasi yang lebih terstruktur dan mudah dimaintain.