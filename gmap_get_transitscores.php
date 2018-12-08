<?php
 //header('Access-Control-Allow-Origin: *'); 
//http://transit.walkscore.com/transit/score/?lat=47.6101359&lon=-122.3420567&city=Seattle&state=WA&wsapikey=your_key
    function getTransitScore($lat, $lon, $address,$city,$state,$api) {
        $address=urlencode($address);
        $url = "http://transit.walkscore.com/transit/score/?";
        $url .= "&lat=$lat&lon=$lon&addr=$address&wsapikey=$api";
        $url .= "&state=$state&city=$city&research=yes";
        //echo $url;
        $str = @file_get_contents($url);
        return $str;
    }

    $api = $_GET['api'];
    $lat = $_GET['lat'];
    $lon = $_GET['lon'];
    $city = $_GET['city'];
    $state = $_GET['state'];
    $address = stripslashes($_GET['address']);
    $json = getTransitScore($lat,$lon,$address,$city,$state,$api);
    echo $json;
?>