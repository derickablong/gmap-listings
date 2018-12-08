<?php
//setup
include '../gmap_db.php';

$data = array(
	'Ml_num'	=>	$_POST['Ml_num'],
	'latitude'	=>	$_POST['latitude'],
	'longitude'	=>	$_POST['longitude']
);
$wpdb->insert('rets_property_lat_long_test', $data);