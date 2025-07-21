<?php

namespace App\Http\Controllers;


use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Torann\GeoIP\Facades\GeoIP;

class RptseederController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\View\View
     */
    public function index($data)
    {
        //list data table
        $data['table_filter'] = DB::table('sys_table')->where(['gmenu' => $data['gmenuid'], 'dmenu' => $data['dmenu'], 'filter' => '1'])->get();
        //check data filter
        if ($data['table_filter']) {
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
     * Store a newly created resource in storage.
     */
    public function store($data)
    {
        //list data
        $data['table_filter'] = DB::table('sys_table')->where(['gmenu' => $data['gmenuid'], 'dmenu' => $data['dmenu'], 'filter' => '1'])->get();
        //get data validate
        foreach ($data['table_filter']->map(function ($item) {
            return (array) $item;
        }) as $item) {
            if ($item['type'] == 'date2') {
                if ($item['validate'] == null) {
                    $validate['fr' . $item['field']] = '';
                    $validate['to' . $item['field']] = '';
                } else {
                    $validate['fr' . $item['field']] = $item['validate'];
                    $validate['to' . $item['field']] = $item['validate'];
                }
            } else {
                if ($item['validate'] == null) {
                    $validate[$item['field']] = '';
                } else {
                    $validate[$item['field']] = $item['validate'];
                }
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
                'mimes' => ':attribute file harus format png,jpg,jpeg',
                'between' => ':attribute diisi antara :min sampai :max'
            ]
        );
        $where = [];
        foreach ($attributes as  $column => $value) {
            if ($value == null) {
                if (substr($column, 0, 2) == 'fr') {
                    $where[$column] = "2000-01-01";
                } else if (substr($column, 0, 2) == 'to') {
                    $where[$column] = "2100-01-01";
                } else {
                    $where[$column] = "%";
                }
            } else {
                $where[$column] = "{$value}";
            }
        }
        $data['table_query'] = $where['query'];
        // Extract table name from the query
        if ($data['table_query']) {
            preg_match('/from\s+(\w+)/', $data['table_query'], $matches);
            $data['table_name'] = $matches[1] ?? null;
        } else {
            $data['table_name'] = null;
        }
        //check authorization
        if (@$where['rules']) {
            //check athorization access rules
            if ($data['authorize']->rules == '1') {
                $where['rules'] = session('user')->idroles;
            } else {
                $where['rules'] = '%';
            }
        }
        //list data        
        try {
            $data['table_result'] = DB::select($data['table_query']);
        } catch (\Throwable $th) {
            $data['table_result'] = DB::select("select 'NOT FOUND' from dual");
        }
        $data['table_class'] = DB::table('sys_table')->where(['gmenu' => $data['gmenuid'], 'dmenu' => $data['dmenu'], 'class' => 'filter'])->first();
        $data['filter'] = $where;

        // dd($data);
        // check data result
        if ($data['table_result']) {
            // return page menu
            return view($data['url'], $data);
        } else {
            // return page menu
            return view($data['url'], $data);
        }
    }
    /**
     * Display the specified resource.
     */
    public function result($data)
    {
        if ($data['table_result']) {
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
}
