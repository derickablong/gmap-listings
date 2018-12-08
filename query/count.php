<?php
//setup
include '../gmap_db.php';


//set sql to null
$sql = stripslashes( $_GET['SQL'] );
//check if request came from pagination
if( $sql == '' ){
    $sql = trim( file_get_contents( dirname( __FILE__ ) . '/storage/storage_data_query_xyz.txt' ) );
}

$results = $wpdb->get_results($sql);

if(count($results) == 1){
	foreach($results as $result){
		echo $result->Ml_num . '::' . $result->prop_type; 
	}
}else{
	echo '';
}