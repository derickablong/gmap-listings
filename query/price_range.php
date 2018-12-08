<?php
//setup
include '../gmap_db.php';

//set sql to null
$sql = stripslashes( $_GET['SQL'] );



$results = $wpdb->get_results($sql);

//price range
$price_range = array();

foreach ($results as $result) {

     array_push($price_range, $result->Lp_dol);

}//end of loop


//set price range
$MAX_AMOUNT = max($price_range);
$MIN_AMOUNT = min($price_range);

echo json_encode( array( $MIN_AMOUNT, $MAX_AMOUNT ) );