<?php
//setup
include 'gmap_db.php';


$price = '';
$stat = '';
$class = [];


//get all request
extract( $_POST );


$price = "Lp_dol >= $min AND Lp_dol <= $max";

if( isset($fpclass) ) {

	$class = ["AND Type_own1_out LIKE '$fpclass%'",
			  "AND Prop_type LIKE '$fpclass%'"];

}


if( $status != 'any' ) {
	$stat = "AND S_r LIKE '%$status%'";
}

echo json_encode([
	'result' =>	GMAP::map_settings(
					$prop_type,
					$price,
					$class,
					$stat
				)
]);