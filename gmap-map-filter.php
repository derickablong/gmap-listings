<?php
//setup
include 'gmap_db.php';

$table                = "";
$price_data           = "Lp_do >= 0";
$bed_data             = "AND Br >= 0";
$bath_data            = "AND Bath_tot >= 0";
$status_data          = "AND S_r != ''";
$freehold_residential = "";
$freehold_commercial  = "";
$freehold_condo       = "";



//get all request
extract( $_POST );



//price section
if( $price != 'any' ) {
	$price      = explode('::', $price);
	$price_data = "(Lp_dol >= " . $price[0] . " AND Lp_dol <= " . $price[1] . ") ";
}
//bed section
if( $bed != 'any' ) $bed_data = "AND Br = $bed";
//bath section
if( $bath != 'any' ) $bath_data = "AND Bath_tot = $bath";
//sale or lease section
if( $sale_lease != 'any' ) $status_data = "AND S_r LIKE '%$sale_lease%'";


//condo or freehold
if( $con_free != 'any' ) {
	
	if($con_free == 'freehold') {

		$table    = "res_com";
		$freehold = array(
			'Semi detached',
			'Detached',
			'Townhouse/row',
			'Multiplex',
			'Triplex',
			'Duplex',
			'Four plex'
		);

		$counter = 0; $and = ""; $close = "";
		$total   = count($freehold);
		foreach ($freehold as $key => $value) {

			if($counter == 0) $and = "AND (";
			else if($counter < ($total-1)) $and = "OR";
			else if(($counter+1) == $total) $close = ")";

			$freehold_residential .= "$and Type_own1_out LIKE '%$value%' $close";
			$freehold_commercial  .= "$and Prop_type LIKE '%$value%' $close";

			$counter++;
		}
	}


	if($con_free == 'condo') {

		$table = 'condo';
		$condo = array(
			'Apartment',
			'Condo townhouse',
			'Common element condo',
			'Co open apt'
		);


		$counter = 0; $and = ""; $close = "";
		$total   = count($condo);
		foreach ($condo as $key => $value) {

			if($counter == 0) $and = "AND (";
			else if($counter < ($total-1)) $and = "OR";
			else if(($counter+1) == $total) $close = ")";

			$freehold_condo .= "$and Type_own1_out LIKE '%$value%' $close";

			$counter++;
		}
	}

}



echo json_encode([
	'result' =>	GMAP::map_filter($table,array(
		'price'			=> $price_data,
		'bed' 			=> $bed_data,
		'bath' 			=> $bath_data,
		'status' 		=> $status_data,
		'residential' 	=> $freehold_residential,
		'commercial' 	=> $freehold_commercial,
		'condo' 		=> $freehold_condo
	))
]);