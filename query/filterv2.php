<?php 
//filter
if( file_exists(ABSPATH . 'wp-content/plugins/gmap-listings/gmap_filter.php')) include ABSPATH . 'wp-content/plugins/gmap-listings/gmap_filter.php';
else include '../gmap_filter.php';
//if search
$search_filter = new FILTERREQUEST(array(
	'query'			=> 		(isset($_GET['search']) ? $_GET['search'] : ''),
	'table'			=>		(isset($_GET['property']) ? $_GET['property'] : ''),
	'show'			=>		20,
	'active'		=>		(isset( $_GET['sr']) ? $_GET['sr'] : 0),
	'price_range'	=>		( (isset( $_GET['price_range'] )) ? $_GET['price_range'] : '' ),
	'bedroom'		=>		( ( isset( $_GET['bedrooms'] ) ) ? $_GET['bedrooms'] : '' ),
	'prop_type'		=>		( ( isset( $_GET['prop_type'] ) ) ? trim($_GET['prop_type']) : '' )
));
echo($search_filter->working_sql());
