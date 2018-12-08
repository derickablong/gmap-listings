<?php
//setup
include '../gmap_db.php';

//get query
$sql = trim( file_get_contents( dirname( __FILE__ ) . '/storage/storage_data_query_xyz.txt' ) );
//constant data
define( 'BLOCK_LOAD', true );
define( 'TOTAL_LISTS', count( $wpdb->get_results($sql) ) );
define( 'LISTS_PER_VIEW', $_GET['LISTS_PER_VIEW'] );
define( 'ACTIVE_RECORD', $_GET['ACTIVE_RECORD'] );
define( 'LAST', $_GET['LAST'] );
define( 'FIRST', $_GET['FIRST'] );
define( 'DIRECTION', $_GET['direction'] );

$start = 0; $limit = 0; $starting_record = 0;

if( DIRECTION == 'next' ){
	$start = LAST + 1;
	$limit = LAST + 10; 
	$starting_record = LAST;
} else {
	$start = ( FIRST - 10 <= 0 ) ? 1 : FIRST - 10;

	if( TOTAL_LISTS < LISTS_PER_VIEW )
		$limit = 0;
	else 
		$limit = $start + 10 - 1; 
	
	$starting_record = $start;
}

//check if limit is in standard
if( round(TOTAL_LISTS / LISTS_PER_VIEW) < 10 ){
	
	$limit = round(TOTAL_LISTS / LISTS_PER_VIEW);

	if( $limit <= 1 ){
		$limit = 0;
	}

}



for($i = $start; $i <= $limit; $i++){

	if( $i == $start ){
		//html
		$pagi .= '<ul>';
		$pagi .= '<li><a href="#" class="prev"><</a></li>';
	}

    $active = '';

    //record to start with
    if( $i == 1 ){
    	$starting_record = 0;
    }else{
    	$starting_record +=  ( LISTS_PER_VIEW - 1 );
    }

    if( ACTIVE_RECORD == $i ){
    	$active = 'class="active"';
    } else {
    	if( $i == $start )
    		$active = 'class="active"';
    }

    //assign html
    $pagi .= '<li><a href="#" record="' . $starting_record . '" ' . $active . '>' . $i . '</a></li>';

    if( $i == $limit ){
    	$pagi .= '<li><a href="#" class="next">></a></li>';
		$pagi .= '</ul>';
    }
    
}

echo $pagi;