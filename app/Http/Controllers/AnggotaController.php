<?php

namespace App\Http\Controllers;

use App\Helpers\Format_Helper;
use App\Helpers\Function_Helper;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Validation\Rule;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;

class AnggotaController extends Controller
{
    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\View\View
     */
    public function index($data)
    {
        // function helper
        $data['format'] = new Format_Helper;
        
        //list data table
        $data['table_header'] = DB::table('sys_table')
            ->where(['gmenu' => $data['gmenuid'], 'dmenu' => $data['dmenu'], 'list' => '1'])
            ->orderBy('urut')
            ->get();

        $data['table_primary'] = DB::table('sys_table')
            ->where(['gmenu' => $data['gmenuid'], 'dmenu' => $data['dmenu'], 'primary' => '1'])
            ->first();

        //check column where in table sys_dmenu
        $getdatawhere = DB::table('sys_dmenu')->select('where')->where('dmenu', $data['dmenu'])->first();
        
        if ($getdatawhere->where <> '') {
            //check authorization access rules
            $query = DB::table($data['tabel']);
            $sql = $query->toSql();
            if ($data['authorize']->rules == '1') {
                //masukkan query find in set
                $whereRules = implode(' OR ', array_map(function ($role) {
                    return "FIND_IN_SET('$role', REPLACE(rules, ' ', ''))";
                }, array_map('trim', explode(',', $data['user_login']->idroles))));
                //gabungkan query
                $sql = $sql . " " . $getdatawhere->where . "  AND ($whereRules)";
            } else {
                $sql .= ' ' . $getdatawhere->where;
            }
            // get data
            $data['table_detail'] = DB::select($sql);
        } else {
            //check authorization access rules
            if ($data['authorize']->rules == '1') {
                $roles = $data['users_rules'];
                $data['table_detail'] = DB::table($data['tabel'])
                    ->where(function ($q) use ($roles) {
                        foreach ($roles as $role) {
                            $q->orWhereRaw("FIND_IN_SET(?, REPLACE(rules, ' ', ''))", [$role]);
                        }
                    })
                    ->get();
            } else {
                // Get anggota data with user join for manual layout
                $query = DB::table('mst_anggota as a')
                    ->leftJoin('users as u', 'a.user_id', '=', 'u.id')
                    ->select(
                        'a.nik',
                        'a.nama_lengkap',
                        'a.jenis_kelamin',
                        'a.no_telp',
                        'a.alamat',
                        'a.departemen',
                        'a.jabatan',
                        'a.tanggal_bergabung',
                        'a.no_rekening',
                        'a.nama_bank',
                        'a.nama_pemilik_rekening',
                        'a.foto_ktp',
                        'a.isactive',
                        'a.user_id',
                        'u.email as user_email',
                        'u.username as user_username'
                    );

                // Apply filters if any
                $search = request('search');
                $status = request('status');
                $jenis_kelamin = request('jenis_kelamin');
                $departemen = request('departemen');

                if ($search) {
                    $query->where(function($q) use ($search) {
                        $q->where('a.nik', 'like', "%{$search}%")
                          ->orWhere('a.nama_lengkap', 'like', "%{$search}%")
                          ->orWhere('a.no_telp', 'like', "%{$search}%")
                          ->orWhere('a.departemen', 'like', "%{$search}%");
                    });
                }

                if ($status !== null && $status !== '') {
                    $query->where('a.isactive', $status);
                }

                if ($jenis_kelamin) {
                    $query->where('a.jenis_kelamin', $jenis_kelamin);
                }

                if ($departemen) {
                    $query->where('a.departemen', 'like', "%{$departemen}%");
                }

                $data['table_detail'] = $query->orderBy('a.nama_lengkap')->get();
            }
        }

        // Additional data for manual layout
        $data['statusOptions'] = DB::table('sys_enum')
            ->where('idenum', 'isactive')
            ->where('isactive', '1')
            ->select('value', 'name')
            ->get();
            
        $data['jenisKelaminOptions'] = collect([
            (object)['value' => 'L', 'name' => 'Laki-laki'],
            (object)['value' => 'P', 'name' => 'Perempuan']
        ]);

        // Filter parameters
        $data['search'] = request('search', '');
        $data['status'] = request('status', '');
        $data['jenis_kelamin'] = request('jenis_kelamin', '');
        $data['departemen'] = request('departemen', '');

        // check data table primary
        if ($data['table_primary']) {
            // return page menu
            return view($data['url'], $data);
        } else {
            //if not exist
            $data['url_menu'] = 'error';
            $data['title_group'] = 'Error';
            $data['title_menu'] = 'Error';
            $data['errorpages'] = 'Not Found!';
            //return error page
            return view("pages.errorpages", $data);
        }
    }

    /**
     * Display the specified resource for add.
     */
    public function add($data)
    {
        // function helper
        $syslog = new Function_Helper;
        
        //list data table
        $data['table_primary'] = DB::table('sys_table')
            ->where(['gmenu' => $data['gmenuid'], 'dmenu' => $data['dmenu'], 'primary' => '1'])
            ->first();
            
        $data['table_header'] = DB::table('sys_table')
            ->where(['gmenu' => $data['gmenuid'], 'dmenu' => $data['dmenu'], 'show' => '1'])
            ->orderBy('urut')
            ->get();

        $data['table_header_l'] = DB::table('sys_table')
            ->where(['gmenu' => $data['gmenuid'], 'dmenu' => $data['dmenu'], 'position' => '3', 'show' => '1'])
            ->orderBy('urut')
            ->get();
            
        $data['table_header_r'] = DB::table('sys_table')
            ->where(['gmenu' => $data['gmenuid'], 'dmenu' => $data['dmenu'], 'position' => '4', 'show' => '1'])
            ->orderBy('urut')
            ->get();

        // Get options for form
        $data['statusOptions'] = DB::table('sys_enum')
            ->where('idenum', 'isactive')
            ->where('isactive', '1')
            ->select('value', 'name')
            ->get();

        $data['jenisKelaminOptions'] = collect([
            (object)['value' => 'L', 'name' => 'Laki-laki'],
            (object)['value' => 'P', 'name' => 'Perempuan']
        ]);

        //check authorization access add
        if ($data['authorize']->add == '1') {
            // return page menu
            return view($data['url'], $data);
        } else {
            //if not authorize
            $data['url_menu'] = $data['url_menu'];
            $data['title_group'] = 'Error';
            $data['title_menu'] = 'Error';
            $data['errorpages'] = 'Not Authorized!';
            //insert log
            $syslog->log_insert('E', $data['url_menu'], 'Not Authorized!' . ' - Add -' . $data['url_menu'], '0');
            //return error page
            return view("pages.errorpages", $data);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store($data)
    {
        // function helper
        $data['format'] = new Format_Helper;
        $syslog = new Function_Helper;
        
        //list data table
        $data['table_header'] = DB::table('sys_table')
            ->where(['gmenu' => $data['gmenuid'], 'dmenu' => $data['dmenu'], 'show' => '1'])
            ->orderBy('urut')
            ->get();
            
        $data['table_primary'] = DB::table('sys_table')
            ->where(['gmenu' => $data['gmenuid'], 'dmenu' => $data['dmenu'], 'primary' => '1'])
            ->first();
            
        $sys_id = DB::table('sys_id')
            ->where('dmenu', $data['dmenu'])
            ->where('isactive', '1')
            ->orderBy('urut', 'ASC')
            ->first();
            
        $multiple = [];
        
        //get data validate
        foreach ($data['table_header']->map(function ($item) {
            return (array) $item;
        }) as $item) {
            if ($item['field'] == $data['table_primary']->field && $sys_id) {
                $validate[$item['field']] = '';
            } else {
                $validate[$item['field']] = $item['validate'];
            }
            
            // check data type multiple
            if (Str::contains($item['class'], 'select-multiple')) {
                // Convert array values to string with comma as separator
                $multiple[$item['field']] = request()->has($item['field']) && !empty(request()->input($item['field']))
                    ? implode(', ', request()->input($item['field']))
                    : null;
            }
        }
        
        //validation data
        $attributes = request()->validate(
            $validate,
            [
                'required' => ':attribute tidak boleh kosong',
                'unique' => ':attribute sudah ada',
                'min' => ':attribute minimal :min karakter',
                'max' => ':attribute maksimal :max karakter',
                'email' => 'format :attribute salah',
                'mimes' => ':attribute format harus :values',
                'between' => ':attribute diisi antara :min sampai :max'
            ]
        );
        
        // check type multiple
        if (isset($multiple)) {
            $keys = array_keys($multiple);
            foreach ($keys as $m) {
                $attributes[$m] = $multiple[$m];
            }
        }
        
        //check password
        if (isset($attributes['password'])) {
            //encrypt password
            $new_password = bcrypt($attributes['password']);
            $attributes['password'] = $new_password;
        }
        
        // check data image and file
        $data['image'] = DB::table('sys_table')
            ->where(['gmenu' => $data['gmenuid'], 'dmenu' => $data['dmenu']])
            ->whereIn('type', ['image', 'file'])
            ->get();
            
        foreach ($data['image'] as $img) {
            if (request()->file($img->field)) {
                $filenameimage = request()->file($img->field)->store($data['tabel']);
                $attributes[$img->field] = $filenameimage;
                if ($img->type == 'image') {
                    // create image manager with desired driver
                    $manager = new ImageManager(new Driver());
                    // read image from file system
                    $image = $manager->read(request()->file($img->field));
                    // resize image proportionally to 35px height
                    $image->scale(height: 35);
                    // save modified image in new format 
                    $image->toPng()->save(public_path('storage/' . $filenameimage . 'tumb.png'));
                };
            }
        }
        
        DB::beginTransaction();
        
        try {
            //check data Generate ID
            if ($sys_id) {
                //set ID from generate id
                $insert_data = DB::table($data['tabel'])->insert([
                    $data['table_primary']->field => $data['format']->IDFormat($data['dmenu'])
                ] + $attributes + ['user_create' => session('username')]);
                $nik = $data['format']->IDFormat($data['dmenu']);
            } else {
                //set ID manual
                $insert_data = DB::table($data['tabel'])->insert($attributes + ['user_create' => session('username')]);
                $nik = $attributes['nik'];
            }
            
            //check insert anggota
            if ($insert_data) {
                // Check if toggle user access is enabled
                $createUserAccess = request()->has('create_user_access') && request('create_user_access') == '1';
                
                if ($createUserAccess) {
                    // Get anggota data that was just inserted
                    $anggota = DB::table($data['tabel'])
                        ->where($data['table_primary']->field, $nik)
                        ->first();
                    
                    if ($anggota && $anggota->nama_lengkap) {
                        // Generate email dari NIK
                        $email = $anggota->nik . '@koperasi.local';
                        
                        // Check if email already exists
                        $existingUser = DB::table('users')->where('email', $email)->first();
                        if ($existingUser) {
                            throw new \Exception('Email ' . $email . ' sudah digunakan');
                        }
                        
                        // Generate username dari NIK
                        $username = $anggota->nik;
                        
                        // Check if username already exists
                        $existingUsername = DB::table('users')->where('username', $username)->first();
                        if ($existingUsername) {
                            throw new \Exception('Username ' . $username . ' sudah digunakan');
                        }
                        
                        // Default password = NIK
                        $defaultPassword = $anggota->nik;
                        
                        // Create new user
                        $userId = DB::table('users')->insertGetId([
                            'username' => $username,
                            'firstname' => $anggota->nama_lengkap,
                            'lastname' => '',
                            'email' => $email,
                            'password' => Hash::make($defaultPassword),
                            'address' => $anggota->alamat ?? '',
                            'idroles' => '3', // Default role untuk anggota
                            'isactive' => '1',
                            'user_create' => session('username'),
                            'created_at' => now(),
                            'updated_at' => now()
                        ]);
                        
                        // Update anggota dengan user_id
                        DB::table($data['tabel'])
                            ->where($data['table_primary']->field, $nik)
                            ->update(['user_id' => $userId]);
                        
                        $syslog->log_insert('C', $data['dmenu'], 'Created Anggota with User Access: ' . $nik . ' (Email: ' . $email . ', Password: ' . $defaultPassword . ')', '1');
                        
                        Session::flash('message', 'Tambah Data Anggota Berhasil! Akses login dibuat dengan Email: ' . $email . ', Password: ' . $defaultPassword);
                    } else {
                        $syslog->log_insert('C', $data['dmenu'], 'Created Anggota: ' . $nik, '1');
                        Session::flash('message', 'Tambah Data Anggota Berhasil!');
                    }
                } else {
                    $syslog->log_insert('C', $data['dmenu'], 'Created Anggota: ' . $nik, '1');
                    Session::flash('message', 'Tambah Data Anggota Berhasil!');
                }
                
                Session::flash('class', 'success');
                
                DB::commit();
                
                // return page menu
                return redirect($data['url_menu'])->with($data);
            } else {
                throw new \Exception('Gagal menyimpan data anggota');
            }
            
        } catch (\Exception $e) {
            DB::rollback();
            
            //insert sys_log
            $syslog->log_insert('E', $data['dmenu'], 'Create Error: ' . $e->getMessage(), '0');
            // Set a session message
            Session::flash('message', 'Tambah Data Anggota Gagal! ' . $e->getMessage());
            Session::flash('class', 'danger');
            // return page menu
            return redirect($data['url_menu'])->with($data);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show($data)
    {
        //list data table
        $data['table_header'] = DB::table('sys_table')
            ->where(['gmenu' => $data['gmenuid'], 'dmenu' => $data['dmenu'], 'filter' => '1', 'show' => '1'])
            ->orderBy('urut')
            ->get();
            
        $data['table_primary'] = DB::table('sys_table')
            ->where(['gmenu' => $data['gmenuid'], 'dmenu' => $data['dmenu'], 'primary' => '1'])
            ->first();

        $data['table_header_l'] = DB::table('sys_table')
            ->where(['gmenu' => $data['gmenuid'], 'dmenu' => $data['dmenu'], 'position' => '3', 'show' => '1', 'filter' => '1'])
            ->orderBy('urut')
            ->get();
            
        $data['table_header_r'] = DB::table('sys_table')
            ->where(['gmenu' => $data['gmenuid'], 'dmenu' => $data['dmenu'], 'position' => '4', 'show' => '1', 'filter' => '1'])
            ->orderBy('urut')
            ->get();

        //check decrypt
        try {
            $id = decrypt($data['idencrypt']);
        } catch (DecryptException $e) {
            $id = "";
        }
        
        //data list where primary key
        $data['list'] = DB::table($data['tabel'])
            ->where($data['table_primary']->field, $id)
            ->first();
            
        // Get anggota with user data for manual layout
        $data['anggota'] = DB::table('mst_anggota as a')
            ->leftJoin('users as u', 'a.user_id', '=', 'u.id')
            ->select(
                'a.*',
                'u.email as user_email',
                'u.username as user_username'
            )
            ->where('a.nik', $id)
            ->first();

        // Get related data (pinjaman, potongan)
        $data['pinjaman'] = DB::table('trs_piutang')
            ->where('nik', $id)
            ->orderBy('tanggal_pengajuan', 'desc')
            ->get();

        $data['potongan'] = DB::table('trs_potongan')
            ->where('nik', $id)
            ->orderBy('periode', 'desc')
            ->limit(12)
            ->get();
            
        // check data list
        if ($data['list']) {
            // return page menu
            return view($data['url'], $data);
        } else {
            //if not exist
            $data['url_menu'] = 'error';
            $data['title_group'] = 'Error';
            $data['title_menu'] = 'Error';
            $data['errorpages'] = 'Not Found!';
            //return error page
            return view("pages.errorpages", $data);
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($data)
    {
        // function helper
        $syslog = new Function_Helper;
        
        //list data table
        $data['table_header'] = DB::table('sys_table')
            ->where(['gmenu' => $data['gmenuid'], 'dmenu' => $data['dmenu'], 'filter' => '1', 'show' => '1'])
            ->orderBy('urut')
            ->get();
            
        $data['table_primary'] = DB::table('sys_table')
            ->where(['gmenu' => $data['gmenuid'], 'dmenu' => $data['dmenu'], 'primary' => '1'])
            ->first();

        $data['table_header_l'] = DB::table('sys_table')
            ->where(['gmenu' => $data['gmenuid'], 'dmenu' => $data['dmenu'], 'position' => '3', 'show' => '1', 'filter' => '1'])
            ->orderBy('urut')
            ->get();
            
        $data['table_header_r'] = DB::table('sys_table')
            ->where(['gmenu' => $data['gmenuid'], 'dmenu' => $data['dmenu'], 'position' => '4', 'show' => '1', 'filter' => '1'])
            ->orderBy('urut')
            ->get();

        //check decrypt
        try {
            $id = decrypt($data['idencrypt']);
        } catch (DecryptException $e) {
            $id = "";
        }
        
        //data list where primary key
        $data['list'] = DB::table($data['tabel'])
            ->where($data['table_primary']->field, $id)
            ->first();
            
        // Get anggota data for manual layout
        $data['anggota'] = DB::table('mst_anggota as a')
            ->leftJoin('users as u', 'a.user_id', '=', 'u.id')
            ->select('a.*', 'u.email as user_email', 'u.username as user_username')
            ->where('a.nik', $id)
            ->first();

        // Get options for form
        $data['statusOptions'] = DB::table('sys_enum')
            ->where('idenum', 'isactive')
            ->where('isactive', '1')
            ->select('value', 'name')
            ->get();
            
        $data['jenisKelaminOptions'] = collect([
            (object)['value' => 'L', 'name' => 'Laki-laki'],
            (object)['value' => 'P', 'name' => 'Perempuan']
        ]);
            
        // check data list
        if ($data['list']) {
            //check authorization access edit
            if ($data['authorize']->edit == '1') {
                // return page menu
                return view($data['url'], $data);
            } else {
                //if not authorize
                $data['url_menu'] = $data['url_menu'];
                $data['title_group'] = 'Error';
                $data['title_menu'] = 'Error';
                $data['errorpages'] = 'Not Authorized!';
                //insert log
                $syslog->log_insert('E', $data['url_menu'], 'Not Authorized!' . ' - Edit -' . $data['url_menu'], '0');
                //return error page
                return view("pages.errorpages", $data);
            }
        } else {
            //if not exist
            $data['url_menu'] = 'error';
            $data['title_group'] = 'Error';
            $data['title_menu'] = 'Error';
            $data['errorpages'] = 'Not Found!';
            //return error page
            return view("pages.errorpages", $data);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update($data)
    {
        // function helper
        $syslog = new Function_Helper;
        
        //list data table
        $data['table_primary'] = DB::table('sys_table')
            ->where(['gmenu' => $data['gmenuid'], 'dmenu' => $data['dmenu'], 'primary' => '1'])
            ->first();
            
        $data['table_header'] = DB::table('sys_table')
            ->where(['gmenu' => $data['gmenuid'], 'dmenu' => $data['dmenu']])
            ->where('field', '<>', $data['table_primary']->field)
            ->where(['filter' => '1', 'show' => '1'])
            ->orderBy('urut')
            ->get();
            
        //check decrypt
        try {
            $id = decrypt($data['idencrypt']);
        } catch (DecryptException $e) {
            $id = "";
        }
        
        $multiple = [];
        
        //get data validate
        foreach ($data['table_header']->map(function ($item) {
            return (array) $item;
        }) as $item) {
            if ($item['primary'] == '2') {
                $primarykey = explode('|', $item['validate']);
                $p = [$primarykey[0]];
                for ($i = 1; $i < count($primarykey); $i++) {
                    (substr($primarykey[$i], 0, 6) != 'unique') ? $p = array_merge($p, [$primarykey[$i]]) : '';
                }
                $validate[$item['field']] = array_merge($p, [Rule::unique($data['tabel'], $item['field'])->ignore($id, $data['table_primary']->field)]);
            } else if ($item['field'] == 'password' && request()->email && empty(request()->password)) {
                unset($validate[$item['field']]);
            } else {
                $validate[$item['field']] = $item['validate'];
            }
            
            // check data type multiple
            if (Str::contains($item['class'], 'select-multiple')) {
                // Convert array values to string with comma as separator
                $multiple[$item['field']] = request()->has($item['field']) && !empty(request()->input($item['field']))
                    ? implode(', ', request()->input($item['field']))
                    : null;
            }
        }
        
        //validasi data
        $attributes = request()->validate(
            $validate,
            [
                'required' => ':attribute tidak boleh kosong',
                'unique' => ':attribute sudah ada',
                'min' => ':attribute minimal :min karakter',
                'max' => ':attribute maksimal :max karakter',
                'email' => 'format :attribute salah',
                'mimes' => ':attribute format harus :values',
                'between' => ':attribute diisi antara :min sampai :max',
                'regex' => 'Password harus mengandung huruf besar, huruf kecil, angka, dan simbol.'
            ]
        );
        
        // check type multiple
        if (isset($multiple)) {
            $keys = array_keys($multiple);
            foreach ($keys as $m) {
                $attributes[$m] = $multiple[$m];
            }
        }
        
        //data password
        if (isset($attributes['password'])) {
            //encrypt password
            $new_password = bcrypt($attributes['password']);
            $attributes['password'] = $new_password;
        }
        
        // check data image and file
        $data['image'] = DB::table('sys_table')
            ->where(['gmenu' => $data['gmenuid'], 'dmenu' => $data['dmenu']])
            ->whereIn('type', ['image', 'file'])
            ->get();
            
        foreach ($data['image'] as $img) {
            if (request()->file($img->field)) {
                $filenameimage = request()->file($img->field)->store($data['tabel']);
                $attributes[$img->field] = $filenameimage;
                if ($img->type == 'image') {
                    // create image manager with desired driver
                    $manager = new ImageManager(new Driver());
                    // read image from file system
                    $image = $manager->read(request()->file($img->field));
                    // resize image proportionally to 35px height
                    $image->scale(height: 35);
                    // save modified image in new format 
                    $image->toPng()->save(public_path('storage/' . $filenameimage . 'tumb.png'));
                };
            }
        }
        
        DB::beginTransaction();
        
        try {
            // Get current anggota data before update
            $currentAnggota = DB::table($data['tabel'])
                ->where($data['table_primary']->field, $id)
                ->first();
            
            // Update data by id
            $updateData = DB::table($data['tabel'])
                ->where($data['table_primary']->field, $id)
                ->update($attributes + ['user_update' => session('username')]);
                
            //check update anggota
            if ($updateData || true) { // true added to handle case when no actual changes made
                // Check toggle user access
                $createUserAccess = request()->has('create_user_access') && request('create_user_access') == '1';
                
                if ($createUserAccess && !$currentAnggota->user_id) {
                    // Create user access - user belum ada, buat baru
                    $updatedAnggota = DB::table($data['tabel'])
                        ->where($data['table_primary']->field, $id)
                        ->first();
                    
                    if ($updatedAnggota && $updatedAnggota->nama_lengkap) {
                        // Generate email dari NIK
                        $email = $updatedAnggota->nik . '@koperasi.local';
                        
                        // Check if email already exists
                        $existingUser = DB::table('users')->where('email', $email)->first();
                        if ($existingUser) {
                            throw new \Exception('Email ' . $email . ' sudah digunakan');
                        }
                        
                        // Generate username dari NIK
                        $username = $updatedAnggota->nik;
                        
                        // Check if username already exists
                        $existingUsername = DB::table('users')->where('username', $username)->first();
                        if ($existingUsername) {
                            throw new \Exception('Username ' . $username . ' sudah digunakan');
                        }
                        
                        // Default password = NIK
                        $defaultPassword = $updatedAnggota->nik;
                        
                        // Create new user
                        $userId = DB::table('users')->insertGetId([
                            'username' => $username,
                            'firstname' => $updatedAnggota->nama_lengkap,
                            'lastname' => '',
                            'email' => $email,
                            'password' => Hash::make($defaultPassword),
                            'address' => $updatedAnggota->alamat ?? '',
                            'idroles' => '3', // Default role untuk anggota
                            'isactive' => '1',
                            'user_create' => session('username'),
                            'created_at' => now(),
                            'updated_at' => now()
                        ]);
                        
                        // Update anggota dengan user_id
                        DB::table($data['tabel'])
                            ->where($data['table_primary']->field, $id)
                            ->update(['user_id' => $userId]);
                        
                        $syslog->log_insert('U', $data['dmenu'], 'Updated Anggota with User Access Created: ' . $id . ' (Email: ' . $email . ', Password: ' . $defaultPassword . ')', '1');
                        
                        Session::flash('message', 'Edit Data Anggota Berhasil! Akses login dibuat dengan Email: ' . $email . ', Password: ' . $defaultPassword);
                    }
                    
                } elseif (!$createUserAccess && $currentAnggota->user_id) {
                    // Remove user access - user ada, hapus
                    $user = DB::table('users')->where('id', $currentAnggota->user_id)->first();
                    
                    if ($user) {
                        // Hapus user
                        DB::table('users')->where('id', $currentAnggota->user_id)->delete();
                        
                        // Update anggota, set user_id ke null
                        DB::table($data['tabel'])
                            ->where($data['table_primary']->field, $id)
                            ->update(['user_id' => null]);
                        
                        $syslog->log_insert('U', $data['dmenu'], 'Updated Anggota with User Access Removed: ' . $id . ' (Email: ' . $user->email . ')', '1');
                        
                        Session::flash('message', 'Edit Data Anggota Berhasil! Akses login dihapus.');
                    }
                    
                } else {
                    // No change in user access toggle
                    $syslog->log_insert('U', $data['dmenu'], 'Updated Anggota: ' . $id, '1');
                    Session::flash('message', 'Edit Data Anggota Berhasil!');
                }
                
                Session::flash('class', 'success');
                
                DB::commit();
                
                // return page menu
                return redirect($data['url_menu'])->with($data);
            } else {
                throw new \Exception('Tidak ada perubahan data');
            }
            
        } catch (\Exception $e) {
            DB::rollback();
            
            //insert sys_log
            $syslog->log_insert('E', $data['dmenu'], 'Update Error: ' . $e->getMessage(), '0');
            // Set a session message
            Session::flash('message', 'Edit Data Anggota Gagal! ' . $e->getMessage());
            Session::flash('class', 'danger');
            //return error page
            return redirect($data['url_menu'])->with($data);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($data)
    {
        // function helper
        $syslog = new Function_Helper;
        
        //list data
        $data['table_primary'] = DB::table('sys_table')
            ->where(['gmenu' => $data['gmenuid'], 'dmenu' => $data['dmenu'], 'primary' => '1'])
            ->first();
            
        $data['table_header'] = DB::table('sys_table')
            ->where(['gmenu' => $data['gmenuid'], 'dmenu' => $data['dmenu'], 'list' => '1'])
            ->orderBy('urut')
            ->get();
            
        $data['table_detail'] = DB::table($data['tabel'])->get();
        
        //check decrypt
        try {
            $id = decrypt($data['idencrypt']);
        } catch (DecryptException $e) {
            $id = "";
        }
        
        // list data update
        $list = DB::table($data['tabel'])
            ->where($data['table_primary']->field, $id)
            ->first();
            
        // Update data by id (soft delete)
        $updateData = DB::table($data['tabel'])
            ->where($data['table_primary']->field, $id)
            ->update(['isactive' => ($list->isactive == '1') ? '0' : '1']);
            
        // check update
        if ($updateData) {
            //insert sys_log
            $syslog->log_insert('D', $data['dmenu'], ($list->isactive == '1') ? 'Deleted : ' . $id : 'UnDelete : ' . $id, '1');
            // Set a session message
            Session::flash('message', ($list->isactive == '0') ? 'Aktif Data Anggota Berhasil!' : 'NonAktif Data Anggota Berhasil!');
            Session::flash('class', 'success');
            // return page menu
            return redirect($data['url_menu'])->with($data);
        } else {
            //insert sys_log
            $syslog->log_insert('E', $data['dmenu'], ($list->isactive == '1') ? 'Delete Error : ' . $id : 'UnDelete Error : ' . $id, '0');
            // Set a session message
            Session::flash('message', ($list->isactive == '0') ? 'Aktif Data Anggota Gagal!' : 'NonAktif Data Anggota Gagal!');
            Session::flash('class', 'danger');
            //return error page
            return redirect($data['url_menu'])->with($data);
        }
    }

    /**
     * Toggle user access for anggota (create/delete user login) - Standalone method
     */
    public function toggleUserAccess($data)
    {
        // function helper
        $syslog = new Function_Helper;
        
        //list data
        $data['table_primary'] = DB::table('sys_table')
            ->where(['gmenu' => $data['gmenuid'], 'dmenu' => $data['dmenu'], 'primary' => '1'])
            ->first();
        
        //check decrypt
        try {
            $id = decrypt($data['idencrypt']);
        } catch (DecryptException $e) {
            $id = "";
        }
        
        // Get anggota data
        $anggota = DB::table('mst_anggota')
            ->where('nik', $id)
            ->first();
            
        if (!$anggota) {
            Session::flash('message', 'Data Anggota tidak ditemukan!');
            Session::flash('class', 'danger');
            return redirect($data['url_menu']);
        }
        
        DB::beginTransaction();
        
        try {
            if ($anggota->user_id) {
                // User sudah ada, hapus user dan update anggota
                $user = DB::table('users')->where('id', $anggota->user_id)->first();
                
                if ($user) {
                    // Hapus user
                    DB::table('users')->where('id', $anggota->user_id)->delete();
                    
                    // Update anggota, set user_id ke null
                    DB::table('mst_anggota')
                        ->where('nik', $id)
                        ->update([
                            'user_id' => null,
                            'user_update' => session('username')
                        ]);
                    
                    $syslog->log_insert('D', $data['dmenu'], 'User Access Removed for : ' . $id . ' (Email: ' . $user->email . ')', '1');
                    
                    Session::flash('message', 'Akses login anggota berhasil dihapus!');
                    Session::flash('class', 'success');
                }
            } else {
                // User belum ada, buat user baru
                if (!$anggota->nama_lengkap) {
                    throw new \Exception('Nama lengkap anggota harus diisi terlebih dahulu');
                }
                
                // Generate email dari NIK jika tidak ada email
                $email = $anggota->nik . '@koperasi.local';
                
                // Check if email already exists
                $existingUser = DB::table('users')->where('email', $email)->first();
                if ($existingUser) {
                    throw new \Exception('Email ' . $email . ' sudah digunakan');
                }
                
                // Generate username dari NIK
                $username = $anggota->nik;
                
                // Check if username already exists
                $existingUsername = DB::table('users')->where('username', $username)->first();
                if ($existingUsername) {
                    throw new \Exception('Username ' . $username . ' sudah digunakan');
                }
                
                // Default password = NIK
                $defaultPassword = $anggota->nik;
                
                // Create new user
                $userId = DB::table('users')->insertGetId([
                    'username' => $username,
                    'firstname' => $anggota->nama_lengkap,
                    'lastname' => '',
                    'email' => $email,
                    'password' => Hash::make($defaultPassword),
                    'address' => $anggota->alamat ?? '',
                    'idroles' => '3', // Default role untuk anggota (sesuaikan dengan sistem role Anda)
                    'isactive' => '1',
                    'user_create' => session('username'),
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
                
                // Update anggota dengan user_id
                DB::table('mst_anggota')
                    ->where('nik', $id)
                    ->update([
                        'user_id' => $userId,
                        'user_update' => session('username')
                    ]);
                
                $syslog->log_insert('C', $data['dmenu'], 'User Access Created for : ' . $id . ' (Email: ' . $email . ', Password: ' . $defaultPassword . ')', '1');
                
                Session::flash('message', 'Akses login anggota berhasil dibuat! Email: ' . $email . ', Password: ' . $defaultPassword);
                Session::flash('class', 'success');
            }
            
            DB::commit();
            
        } catch (\Exception $e) {
            DB::rollback();
            
            $syslog->log_insert('E', $data['dmenu'], 'Toggle User Access Error for : ' . $id . ' - ' . $e->getMessage(), '0');
            
            Session::flash('message', 'Gagal mengubah akses login: ' . $e->getMessage());
            Session::flash('class', 'danger');
        }
        
        return redirect($data['url_menu']);
    }
}