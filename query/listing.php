<?php
//setup
include '../gmap_db.php';

//constant
define('LISTS_PER_VIEW', get_option('map_max_view') );
define('ACTIVE_RECORD', ( ( $_GET['sr'] != '') ? $_GET['sr'] : 0) );

//set sql to null
$sql = stripslashes( $_GET['SQL'] ); $html = '';

//check if request came from pagination
if( $sql == '' ){
    $sql = trim( file_get_contents( dirname( __FILE__ ) . '/storage/storage_data_query_xyz.txt' ) );
}

//add limit
$sql .= ' LIMIT ' . ACTIVE_RECORD . ', ' . LISTS_PER_VIEW;


$counter = 0;
$listing_results = $wpdb->get_results($sql);

foreach($listing_results as $listing_result){

    $location = GMAP::this_lat_long( $listing_result->Ml_num );
    
    if( count($location) && $listing_result->Addr != ''){

        //display first image
        $upload_dir = wp_upload_dir();
        $src = get_bloginfo('siteurl') . '/wp-content/uploads/property/' . $listing_result->Ml_num . '/' .'1.jpg';
        $imgsrc =  $upload_dir['basedir'] . '/' . 'property/' . $listing_result->Ml_num . '/' .'1.jpg';
        if(!file_exists($imgsrc)){
            $src = plugins_url( 'gmap-listings/images' ).'/listing-photo.png';
        }

        $html .= '<div class="listing-item">';
        $html .= '<div class="one-third first" style="background:transparent url('.$src.') no-repeat center !important; width:85px;height: 65px;
        background-size: 150% !important;">';
        $html .= '<a style="display:block;width:100%;height: 65px;" href="' . network_site_url('/') . 'listing-info/?id=' . $listing_result->Ml_num . '&class=' . $listing_result->prop_type . '"></a>';
        //<img src="'.$src.'"></a>';
        $html .= '</div>';
        $html .= '<div class="two-thirds">';
        $html .= '<h1>$' . number_format($listing_result->Lp_dol, 2) . '</h1>';
        $addr = "{$listing_result->Addr}".", ".$listing_result->Municipality;
        $html .= '<h2><a class="propertyAdd" href="javascript:void(0)" onclick="marker_infowindow('.$counter.')">' . $listing_result->Addr . ', ' . $listing_result->Area . '</a></h2>';

        $html .= ucfirst(str_replace('pro', ' Pro', str_replace('rets_property_', '', $listing_result->prop_type)));
        $html .= '</p>';
        $html .= '</div>';
        $html .= '<div class="first"></div>';
        $html .= '</div>';

        $counter++;
    }

}//end of listings lopp

//return
echo $html;