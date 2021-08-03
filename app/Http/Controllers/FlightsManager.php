<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;

class FlightsManager extends Controller
{
    /**
     * Gerencia o JSON do flights
     *
     * @return array
     */
    public static function getResults($params)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'http://prova.123milhas.net/api/flights'.$params);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $result = curl_exec($ch);
        if (curl_errno($ch)) {
            return false;
            // debug
            // echo 'Error:' . curl_error($ch);
        }
        curl_close($ch);
        $arr = json_decode($result, true);
        if(empty($arr)){
            return false;
        } else {
            return $arr;
        }
    }

    //
}
