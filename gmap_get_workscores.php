<?php
 //header('Access-Control-Allow-Origin: *'); 

 function getWalkScore($lat, $lon, $address, $api) {
  $address=urlencode($address);
  $url = "http://api.walkscore.com/score?format=json&addr=$address";
  $url .= "&lat=$lat&lon=$lon&wsapikey=$api";
  $str = @file_get_contents($url); 
  return $str; 
 } 

 $api = $_GET['api'];
 $lat = $_GET['lat']; 
 $lon = $_GET['lon']; 
 $address = stripslashes($_GET['address']);
 $json = getWalkScore($lat,$lon,$address,$api);
 echo $json; 
?>