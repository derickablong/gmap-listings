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

//price range
$bedroom = array();
$html = '';

foreach ($results as $result) {

     array_push( $bedroom, $result->Br );

}//end of loop

$nob = array_unique($bedroom);
sort($nob);

$i = 0;
foreach( $nob as $n ){
	
	if( $i == 0 ){
		$html .= "<option value='0'> All </option>";
	}

	if( $n > 0 && $n != '' ){
		
		$html .= "<option value='$n' >$n</option>";
	}

	$i++;
}


echo $html;