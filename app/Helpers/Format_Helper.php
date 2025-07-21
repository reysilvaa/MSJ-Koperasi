<?php

namespace App\Helpers;

use Illuminate\Support\Facades\DB;

class Format_Helper
{
    function CurrencyFormat($nominal, $decimal = 0, $prefix = 'Rp.')
    {
        return $prefix . ' ' . number_format($nominal, $decimal, ',', '.');
    }

    function IDFormat($dmenu)
    {
        $generate_id = '';
        $counter = 1;
        $zero = '';
        $string = '';
        $sys_id = DB::table('sys_id')->where(['dmenu' => $dmenu, 'isactive' => '1'])->orderBy('urut', 'ASC')->get();
        $sys_str = $sys_id;
        foreach ($sys_str as $str) {
            if ($str->source == 'int') {
                $string = $string . substr(request()->{$str->internal}, 0, $str->length);
            } elseif ($str->source == 'ext') {
                $string = $string . substr($str->external, 0, $str->length);
            } elseif ($str->source == 'th2') {
                $string = $string . date_format(now(), "y");
            } elseif ($str->source == 'th4') {
                $string = $string . date_format(now(), "Y");
            } elseif ($str->source == 'bln') {
                $string = $string . date_format(now(), "m");
            } elseif ($str->source == 'tgl') {
                $string = $string . date_format(now(), "d");
            } elseif ($str->source == 'cnt') {
                $string = $string . '-';
            }
        }
        $sys_counter = DB::table('sys_counter')->where('character', $string)->first();
        if ($sys_counter) {
            $counter = $sys_counter->counter + 1;
        }
        foreach ($sys_id as $id) {
            if ($id->source == 'int') {
                $generate_id = $generate_id . substr(request()->{$id->internal}, 0, $id->length);
            } elseif ($id->source == 'ext') {
                $generate_id = $generate_id . substr($id->external, 0, $id->length);
            } elseif ($id->source == 'th2') {
                $generate_id = $generate_id . date_format(now(), "y");
            } elseif ($id->source == 'th4') {
                $generate_id = $generate_id . date_format(now(), "Y");
            } elseif ($id->source == 'bln') {
                $generate_id = $generate_id . date_format(now(), "m");
            } elseif ($id->source == 'tgl') {
                $generate_id = $generate_id . date_format(now(), "d");
            } elseif ($id->source == 'cnt') {
                for ($i = 0; $i < $id->length - strlen((string)$counter); $i++) {
                    $zero = $zero . '0';
                }
                $generate_id = $generate_id . $zero . $counter;
            }
        }
        if ($sys_counter) {
            $data = [
                'counter' => $counter,
                'lastid' => $generate_id
            ];
            $upd_sys_counter = DB::table('sys_counter')->where('character', $string)->update($data);
            if ($upd_sys_counter) {
                // dd($sys_id, $generate_id, $counter, $zero, $string, 'update', $id->length);
                return $generate_id;
            }
        } else {
            $data = [
                'character' => $string,
                'counter' => $counter,
                'lastid' => $generate_id
            ];
            $ins_sys_counter = DB::table('sys_counter')->insert($data);
            if ($ins_sys_counter) {
                // dd($sys_id, $generate_id, $counter, $zero, $string, 'insert', $id->length);
                return $generate_id;
            }
        }
    }
}
