<?php
//setup
include 'gmap_db.php';


//get all request
extract( $_POST );

echo json_encode([
	'result' =>	GMAP::gmap_get_gallery_path($ml_num),
	'mlnum' => $ml_num
]);