<?php
//setup
include '../gmap_db.php';

//set sql to null
$sql = stripslashes( $_POST['SQL'] );
//check if request came from pagination
if( $sql == '' ){
    $sql = trim( file_get_contents( dirname( __FILE__ ) . '/storage/storage_data_query_xyz.txt' ) );
}


$results = $wpdb->get_results($sql);

//price range
$prop_type = array();

foreach ($results as $result) {

    if(count( GMAP::this_lat_long( $result->Ml_num ) ))
    array_push( $prop_type, $result->type_of_home  );

}//end of loop


$nob = array_unique($prop_type);
sort($nob);

$i = 1; $left = ''; $right = '';

//get exact devision
$total = count( $nob );
$rem = $total % 2;
$dev = round( ( $total - $rem ) / 2 );

foreach( $nob as $n ){
	
    
    if( $i <= ( $dev + $rem ) ){
    	$left .= "<span class='uncheck'>$n</span>";
    }else{
    	$right .= "<span class='uncheck'>$n</span>";
    }
	
	$i++;
}


echo json_encode( array( $left, $right ) );