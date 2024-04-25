<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class LoadApi extends Model {

    function callApiWithHeader($url, $headerData, $bodyData) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headerData);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $bodyData);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        $myvar = curl_exec($ch);

        return $myvar;
    }

    function call_api_direct($path, $strPost) {
        $headers = array("Content-Type:multipart/form-data");

        $url = url('/api') . '/' . $path;
        //intialize cURL and send POST data
        $ch = @curl_init();
        @curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        @curl_setopt($ch, CURLOPT_POSTFIELDS, $strPost);
        @curl_setopt($ch, CURLOPT_URL, $url);
        @curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        $myvar = @curl_exec($ch);
        curl_close($ch);
        return $myvar;
    }

    function apiCallJSON($path, $strPost) {
//        $url = site_url('/api') . '/' . $path;
        $url = $path;
        $ch = curl_init($url);
        //$payload = json_encode(array('league_id' => $strPost['league_id'],'match_id'=>$strPost['match_id'],'team_id'=>$strPost['team_id'],'user_id'=>$strPost['user_id']));
        $payload = $strPost;
        //print_r($payload);exit;
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        $result = curl_exec($ch);
        curl_close($ch);
        return $result;

//        $url = site_url('/api') . '/' . $path;
//        //$url = "http://localhost/api/fantasy_magnet/api/".$path;
//        //$urla = "http://localhost/my_project/mangoos/api/index.php/".$path;
//        //intialize cURL and send POST data
//        $ch = @curl_init();
//        @curl_setopt($ch, CURLOPT_POST, true);
//        @curl_setopt($ch, CURLOPT_POSTFIELDS, $strPost);
//        @curl_setopt($ch, CURLOPT_URL, $url);
//        @curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
//        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
//        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
//        $myvar = @curl_exec($ch);
//        curl_close($ch);
//        return $myvar;
    }

}
