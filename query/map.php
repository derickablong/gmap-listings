<?php
//setup
include '../gmap_db.php';
//set sql to null
$sql = stripslashes( $_GET['SQL'] ); $html = '';
$results = $wpdb->get_results($sql);
//map
$res_address = array();
$counter = 0;

foreach ($results as $result) {

    $location = GMAP::this_lat_long( $result->Ml_num );
    
    if( count($location) && $result->Addr != '' ){

         //display first image
        $upload_dir = wp_upload_dir();
        $src = get_bloginfo('siteurl') . '/wp-content/uploads/property/' . $result->Ml_num . '/' .'1.jpg';
        $imgsrc =  $upload_dir['basedir'] . '/' . 'property/' . $result->Ml_num . '/' .'1.jpg';
        if(!file_exists($imgsrc)){
            $src = plugins_url( 'gmap-listings/images' ).'/listing-photo.png';
        }
        
        $addr = $result->Addr.' '.$result->Area.'|'.trim($result->prop_type).'|'.$result->Ml_num.'|'.$result->Lp_dol.'|'.ucfirst(str_replace('pro', ' Pro', str_replace('rets_property_', '', $result->prop_type))).'|'.$src.'|'.$location['latitude'].'|'.$location['longitude'];
        //push data
        array_push($res_address,$addr);

    }

    $counter++;


}//end of loop

echo json_encode($res_address);