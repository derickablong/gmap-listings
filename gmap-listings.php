<?php
ini_set('display_errors', 0);
/*
Plugin Name: Property Listings Plus
Plugin URI: http://dev.remaxurbantoronto.ca/
Description: Plugin for displaying property listings with percircle and category posts shortcode add-ons.
Author: Shane Oquinn
Version: 1.0
Author URI: http://shaneoquinn.com
*/

//This is the core sections of the property listings
//Please don't alter or remove the lements for the plugin to work properly
$map_sections = array(
//Don't changed or remove the class associated for each elements, the core script might not work normally anymore
//For more assistance pls contact the plugin author
//This element is for showing the total listings being found by the search results
'total' 			=> '<span class="counter">0</span>',
//This element will output the price range slider
//The max and min amount was base on the search results
'price_slider' 		=> '<div id="price_slider_container"><div class="price-slider"><span id="min"></span><span id="max"></span><div id="slider-range"></div></div></div>',
//This element will output property types options base on the search results
//User can choose what type of properties he want to show
'property_types'	=> '<div class="one-half first left"></div><div class="one-half right"></div>',
//This element will output the number of bedrooms dropbox
//The numbers is also base on the search results
'bedrooms'      	=> '<select id="no_bedrooms"></select>',
//This element is for showing the listings
//The minimum output is 100 by default
'listings'			=> '<div class="listings"></div><div class="pagi"></div>',
//This element will show the google map with markers base on the listings output
//The markers is base alos on the search results
'map'				=> '<div id="map" style="width: 100%;"></div>');



//gmap widgets
include dirname( __FILE__ ) . '/gmap_realtor.php';


//core handler
include dirname( __FILE__ ) . '/gmap_core.php';
GMAP::start_session();
GMAP::update_settings('search_type', 'location');
//add auto search complete
//lightweight search suggestions for user friendly output
//this will work if wp_footer() exists
add_action('wp_footer', array(GMAP, 'create_search_data'));




//admin settings
//manage property settings configuration
function map_admin_management(){
	GMAP::apply('settings/gmap_admin_settings');
}



//grab listings
function map_grab_listings(){
	GMAP::apply('settings/gmap_admin_grab_listings');
}



//treb server
function map_treb_server(){
	GMAP::apply('settings/gmap_treb_settings');
}



//work score api
function map_wsapi(){
	GMAP::apply('settings/gmap_wsapi_settings');
}



//map area
function listing_settings(){
	GMAP::apply('settings/gmap_map_area');
}



//update data
function gmap_update_data() {
	GMAP::apply('settings/gmap_admin_populate');
}




//map admin settings management
add_action('admin_menu', 'map_admin_settings');
function map_admin_settings(){
	
	add_menu_page( 'Property Settings', 'Property Settings', 'manage_options', 'property_settings', 'map_admin_management', plugins_url('gmap-listings/images/men.png'), 3);
	add_submenu_page('property_settings', 'General Settings', 'General Settings', 'manage_options', 'property_settings', 'property_settings');
	add_submenu_page('property_settings', 'Listing Settings', 'Listing Settings', 'manage_options', 'listing_settings', 'listing_settings');
	add_submenu_page('property_settings', 'TREB Server', 'TREB Server', 'manage_options', 'treb_server', 'map_treb_server');
	add_submenu_page('property_settings', 'Grabing Data Management', 'Grab Listings', 'manage_options', 'grab_listings', 'map_grab_listings');
	add_submenu_page('property_settings', 'Update Data', 'Update Data', 'manage_options', 'gmap_update_data', 'gmap_update_data');
	
}




//add theme
//register our theme
add_action('wp_enqueue_scripts', 'map_theme');
function map_theme(){
	
	wp_register_script('gmap-script-jquery', '//code.jquery.com/jquery-1.10.2.js' );
    wp_enqueue_script('gmap-script-jquery', '', array('jquery'), '', false);

    wp_register_script('gmap-script-ui', '//code.jquery.com/ui/1.10.3/jquery-ui.js' );
    wp_enqueue_script('gmap-script-ui', '', array('jquery'), '', false);

	wp_register_style('gmap-theme', plugins_url( 'gmap-listings/css/style.css' ));
    wp_enqueue_style('gmap-theme');

    wp_register_style('gmap-slider-css', plugins_url( 'gmap-listings/lib/bxslider/jquery.bxslider.css' ));
    wp_enqueue_style('gmap-slider-css');

    wp_register_script('gmap-slider-script', plugins_url( 'gmap-listings/lib/bxslider/jquery.bxslider.min.js' ));
    wp_enqueue_script('gmap-slider-script', '', array('jquery'), '', true);

    wp_register_script('gmap-theme-global', plugins_url( 'gmap-listings/js/globals.js' ));
    wp_enqueue_script('gmap-theme-global', '', array('jquery'), '', true);

    //search type is area
    if( get_option('search_type') == 'area' ) {
	    wp_register_script('gmap-theme-filter', plugins_url( 'gmap-listings/js/filter.js' ));
	    wp_localize_script('gmap-theme-filter', 'request_url', array( 'ajaxurl' => admin_url( 'admin-ajax.php' )));
	    wp_enqueue_script('gmap-theme-filter', '', array('jquery'), '', true);
	}

    
    
}




//admin theme
add_action('admin_print_styles', 'admin_theme');	
function admin_theme(){
	wp_enqueue_style('gmap-admin-theme', plugins_url( 'gmap-listings/css/style.css' ));
	wp_enqueue_script('gmap-theme-global', '', array('jquery'), '', true);
}




//add admin scripts
add_action('in_admin_footer', 'admin_scripts');
function admin_scripts(){
	echo '<script src="' .  plugins_url( 'gmap-listings/js/globals.js' ) . '" type="text/javascript"></script>';
}





//search type
add_shortcode('GMapSearchType', 'GMapSearchType');
function GMapSearchType( $attr ) {
	return GMAP::gmap_search_type( $attr );
}



//GMAP Map Image
//mapping
add_shortcode('GMapMapArea', 'GMapMapArea');
function GMapMapArea() {
	return GMAP::gmap_map_area_xml();
}




//GMap Listings
//This is the shortcodes handler for the listings
add_shortcode('GMapListings', 'GMapListings');
function GMapListings( $atts ){
	global $map_sections;
	if( GMAP::stack_value('property', $atts) ){
		$map_sections['property'] = GMAP::custom_load($atts);
	}
	//return requested elements
	return $map_sections[ $atts['load'] ];
}




//GMap Listing Slider
//Show listings in a slider view
add_shortcode( 'GMapSlider', 'GMapSlider' );
function GMapSlider( $atts ) {
	return GMAP::featured_listings_slider( $atts['search'] );
}




//GMap Listing Info
//This function is to handle the listing information
add_shortcode('GMapListingInfo', 'GMapListingInfo');
function GMapListingInfo(){
	//load listing info data
	GMAP::apply('gmap_listing_info');
}




//GMap Library
//This function or shortcode should always be called fater the other shortcodes
//better put this before the </body> tag]
add_shortcode('GMapLibrary', 'GMapLibrary');
function GMapLibrary(){
	//load librabry
	GMAP::apply('gmap_init');
}




//addition pre percircle jquery value animation
//this is a seperate shortcode to avoid conflict from the property listings
//for more dynamics value, contact the plugin support theme for guidance
add_shortcode('PCircle', 'percent_circle');
function percent_circle( $atts ){
	return GMAP::percircle( $atts );
}




//static circle element
//this is a free bundle for gmap listings plugin
//user can choose his/her predefined colors and text
add_shortcode('StaticCircle', 'static_circle');
function static_circle( $atts ){
	return GMAP::static_circle( $atts );
}




//extra shortcode
//columnar
//abiiity to create to layout
add_shortcode('Squeeze', 'squeeze');
function squeeze( $atts, $content ){
	echo GMAP::squeeze( array( 'options' => $atts, 'content' => $content ) );
}




add_shortcode('Left', 'squeeze_role');
add_shortcode('Right', 'squeeze_role');
add_shortcode('clear', 'squeeze_role');
function squeeze_role( $atts, $content, $tag ){
	return GMAP::squeeze_role( array( $atts, $content, $tag ) );
}




//load posts from a given category id
//this is a free bundle for gmap listings plugin
//users can choose what category they want to load in specific page without hardcoding
//by just using the shortcode
add_shortcode('LoadPosts', 'load_posts_under_category');
function load_posts_under_category( $atts, $content ){
	return GMAP::load_posts_under_category( array( 'options' => $atts, 'content' => $content ) );
}



//add adjax
//counter
add_action('wp_ajax_gmap_filter', 'gmap_filter');
function gmap_filter() {

	//get all request
	extract( $_POST );

	//prepare conditions
	$conditions = array(
		'location'		=> $location,
		'price_range'	=> $min_price . '::' . $max_price,
		'bedroom'		=> $min_beds,
		'baths'			=> $min_baths,
		'status'		=> $fstatus,
		'garage'		=> $min_garage,
		'parking'		=> $min_parking,
		'class'			=> $fpclass,
		'sort'			=> $sprice,
		'order'			=> $sorder
	);

	//get new filtered data
	$data = array(
		'map'	=>	GMAP::load_listings_by_area( false, $area, 'map', $conditions ),
		'grid'	=>	GMAP::load_listings_by_area( false, $area, 'grid', $conditions ),
		'lists'	=>	GMAP::load_listings_by_area( false, $area, 'list', $conditions ),
		'sql'	=>	GMAP::load_listings_by_area( 'query', $area, null, $conditions )
	);

	echo json_encode( $data );
	die();

}






//settingsgmap_settings
add_action('wp_ajax_gmap_filtered_data', 'gmap_filtered_data');
function gmap_filtered_data() {

	echo json_encode([
		'result' => GMAP::extractFilteredData( $_POST )
	]);

	die();
}





//listings
add_action( 'wp_loaded','listings_flush_rules' );
add_filter( 'rewrite_rules_array','listings_insert_rewrite_rules' );
add_filter( 'query_vars','listings_insert_query_vars' );


function listings_flush_rules() {

	$rules = get_option( 'rewrite_rules' );
	 
	if ( ! isset( $rules['(listings)/(.+)$'] ) ) {
		global $wp_rewrite;
		$wp_rewrite->flush_rules();
	}
 
}

function listings_insert_rewrite_rules( $rules ) {

	$newrules = array();
	$newrules['(listings)/(.+)$'] = 'index.php?pagename=$matches[1]&area=$matches[2]';
	 
	return $newrules + $rules;

}


function listings_insert_query_vars( $vars ) {
 
	array_push($vars, 'area');
	return $vars;

}




//place
add_action( 'wp_loaded','place_flush_rules' );
add_filter( 'rewrite_rules_array','place_insert_rewrite_rules' );
add_filter( 'query_vars','place_insert_query_vars' );


function place_flush_rules() {

	$rules = get_option( 'rewrite_rules' );
	 
	if ( ! isset( $rules['(place)/(.+)$'] ) ) {
		global $wp_rewrite;
		$wp_rewrite->flush_rules();
	}
 
}

function place_insert_rewrite_rules( $rules ) {

	$newrules = array();
	$newrules['(place)/(.+)$'] = 'index.php?pagename=$matches[1]&area=$matches[2]';
	 
	return $newrules + $rules;

}


function place_insert_query_vars( $vars ) {
 
	array_push($vars, 'area');
	return $vars;

}





add_filter( 'page_template', 'listings_template' );
function listings_template( $page_template ) {


    if ( get_query_var('pagename', 1) == 'listings' ) {

       	$this_area = explode( '/', $_SERVER["REQUEST_URI"] );
		$this_area = array_filter( $this_area );

		if( count( $this_area ) >= 3 ) {
			$page_template = dirname( __FILE__ ) . '/template/listing-info.php';
		} else {
        	$page_template = dirname( __FILE__ ) . '/template/listings.php';
		}
    
    } else if ( get_query_var('pagename', 1) == get_option('gmap_listing_page') ) {

    	$page_template = dirname( __FILE__ ) . '/template/search_by_location_template.php';
    
    }
    
    return $page_template;
}





//add widget area for listing details sidebar
function realtor_widget() {
    register_sidebar( array(
        'name' => __( 'Listing Details Sidebar', 'realtor_widget' ),
        'id' => 'realtor-widget',
        'before_widget' => '<div  class="fwidget">',
        'after_widget' => '</div>',
        'before_title' => '<h3>',
        'after_title' => '</h3>',
    ) );
}
add_action( 'widgets_init', 'realtor_widget' );






//create realtor widget
// register Foo_Widget widget
function register_realtor_widget() {
    register_widget( 'Realtor_Widget' );
}
add_action( 'widgets_init', 'register_realtor_widget' );






//add body class
add_filter('body_class', 'gmap_place');
function gmap_place( $classes ) {

	if( get_query_var('pagename', 1) == 'place' ) {
		$classes[] = 'map-page';
	}

	return $classes;

}