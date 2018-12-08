<?php
//RESIDENTIAL
$count = 1;
$residential_address = explode('||', $_SESSION['RESIDENTIAL_ADDR']);
$residential_address = array_filter($residential_address);


if(count( $residential_address )) {
    foreach ($residential_address as $key => $value) {
       
            
        GMAP::progress( 'latlong_residential', count( $residential_address ), $count );

        $address =  explode('::', $value);
        $coordinates = GMAP::get_lat_long( $address[0] );
        
        if( isset( $coordinates['lat'] ) ){
            GMAP::save_lat_long(array(
                'ML_num'    =>  $address[1],
                'latitude'  =>  $coordinates['lat'],
                'longitude' =>  $coordinates['long'],
                'Addr'      =>  $address[2]
            ));
        }
       
        $count++;
    }
} else {
    GMAP::progress( 'latlong_residential', 0, 0 );
}




//COMMERCIAL
$count = 1;
$commercial_address = explode('||', $_SESSION['COMMERCIAL_ADDR']);
$commercial_address = array_filter($commercial_address);

if(count($commercial_address)) {
    foreach ($commercial_address as $key => $value) {

        GMAP::progress( 'latlong_commercial', count( $commercial_address ), $count, 1 );

        $address =  explode('::', $value);
        $coordinates = GMAP::get_lat_long( $address[0] );
        
        if( isset( $coordinates['lat'] ) ){
            GMAP::save_lat_long(array(
                'ML_num'    =>  $address[1],
                'latitude'  =>  $coordinates['lat'],
                'longitude' =>  $coordinates['long'],
                'Addr'      =>  $address[2]
            ));
        }
        
        $count++;
    }
} else {
    GMAP::progress( 'latlong_commercial', 0, 0, 1 );
}




//CONDO
$count = 1;
$condo_address = explode('||', $_SESSION['CONDO_ADDR']);
$condo_address = array_filter($condo_address);

if(count($condo_address)) {
    foreach ($condo_address as $key => $value) {
        

        GMAP::progress( 'latlong_condo', count( $condo_address ), $count, 2 );

        $address =  explode('::', $value);
        $coordinates = GMAP::get_lat_long( $address[0] );
        
        if( isset( $coordinates['lat'] ) ){
            GMAP::save_lat_long(array(
                'ML_num'    =>  $address[1],
                'latitude'  =>  $coordinates['lat'],
                'longitude' =>  $coordinates['long'],
                'Addr'      =>  $address[2]
            ));
        }
        
        $count++;
    }
} else {
    GMAP::progress( 'latlong_condo', 0, 0, 2 );
}

//clean grabbing
require_once dirname( __FILE__ ) . '/gmap_grab_clean.php';

//save all listings
GMAP::save_listings_address('location');