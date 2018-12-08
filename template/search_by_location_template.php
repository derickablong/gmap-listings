<?php
//* Add custom body class to the head
add_filter( 'body_class', 'add_body_class' );
function add_body_class( $classes ) {
    $classes[] = 'gmap-listings-result';
    return $classes;
}

add_filter( 'genesis_site_layout', '__genesis_return_full_width_content' );
remove_action( 'genesis_before_loop', 'genesis_do_breadcrumbs' );
remove_action( 'genesis_loop', 'genesis_do_loop' );
add_action( 'genesis_loop', 'search_by_location' );

function search_by_location() {

	//core class
	$core = plugin_dir_path(__FILE__);
	require_once($core . '../gmap_core.php');

	$class = 'map_disabled';
	$map_lat = get_option('map_lat');
	$map_lng = get_option('map_long');
	$map = 'false';
	$address = '';
	$broker = 'false';

		
	$class = 'map_enabled';
	$map = 'true';
	

	$data = GMAP::gmap_place_request();
	$search = implode( ', ', $data[1] );
	$position = $data[0];
	
	if(count($position) <= 1) {

		$address = GMAP::gmap_get_broker(str_replace(' ','_',$position[0]));
		$broker = 'true';

	} else {

		$address = GMAP::gmap_get_all_address();
		$map_lat = $position[0];
		$map_lng = $position[1];

	}

	?>



	<?php if(get_option('gmap_filter_location') == 'top') require_once($core . 'map-filter-standard.php'); ?>
	


	<div id="map-section">
		<a href="#" class="toggled"></a>	  
		<div id="map-info" <?php if( is_page() || is_single() ): echo 'class="map-info show"'; endif;?>>
			<form method="post" id="search-area" runat="server" class="<?php echo $class ?> <?php if(get_option('gmap_filter_location') == 'sidebar'): echo 'filter-sidebar'; endif; ?>"> 

				<?php if(get_option('gmap_filter_location') == 'sidebar'): ?>
					<a href="#" class="burger-map filter"></a>
				<?php endif; ?>
				<a href="#" class="toggle-map"></a>
				
				<input type="text" id="txtPlaces" style="width:340px" placeholder="Enter a location" value="<?php echo $search ?>" />

				<input type="hidden" id="lat" style="width: 340px" value="<?php echo $position[0] ?>" />
				<input type="hidden" id="lng" style="width: 340px" value="<?php echo $position[1] ?>" />
				<input type="hidden" id="address" style="width: 340px" />
				<input type="hidden" id="street_number" style="width: 340px" />
				<input type="hidden" id="route" style="width: 340px" />
				<input type="hidden" id="locality" style="width: 340px" />
				<input type="hidden" id="administrative_area_level_1" style="width: 340px" />
				<input type="hidden" id="administrative_area_level_2" style="width: 340px" />
				<input type="hidden" id="country" style="width: 340px" />
				<input type="hidden" id="postal_code" style="width: 340px" />
				<input type="hidden" id="location" style="width: 340px" />

			</form>

			<div class="info-data"></div>
		</div>

		<?php if(get_option('gmap_filter_location') == 'sidebar') require_once($core . 'map-filter-sidebar.php'); ?>
		<div id="googleMap" style="width:100%;height:600px;" class="<?php echo $class ?>"></div> 
	</div>


	<?php if(get_option('gmap_filter_location') == 'bottom') require_once($core . 'map-filter-standard.php'); ?>


	<script type="text/javascript" src="http://maps.googleapis.com/maps/api/js?key=<?php echo GMAP::get_map_api() ?>&libraries=places,geometry&language=en"></script>
	<!-- marker clusterer -->
	<script type="text/javascript" src="<?php echo "http://" . $_SERVER['SERVER_NAME'] ?>/wp-content/plugins/gmap-listings/js/markerclusterer.js"></script>
	<script type="text/javascript" src="<?php echo "http://" . $_SERVER['SERVER_NAME'] ?>/wp-content/plugins/gmap-listings/js/gmaps.js"></script>
	<script type="text/javascript" src="<?php echo "http://" . $_SERVER['SERVER_NAME'] ?>/wp-content/plugins/gmap-listings/js/GeoJSON.js"></script>
	<script type="text/javascript" src="<?php echo "http://" . $_SERVER['SERVER_NAME'] ?>/wp-content/plugins/gmap-listings/js/jquery.gmap.js"></script>

	<script type="text/javascript">
		$('#googleMap').gmap({
			standard 		: <?php echo ((get_option('gmap_filter_location') == 'top') || (get_option('gmap_filter_location') == 'bottom'))? 'true' : 'false' ?>,
			plugin_url		: 'http://<?php echo $_SERVER['SERVER_NAME'] ?>/wp-content/plugins/gmap-listings',
			property 		: 'http://<?php echo $_SERVER['SERVER_NAME'] ?>/wp-content/uploads/property',
			visible			: "<?php echo $map ?>",
			lat 			: <?php echo $map_lat ?>,
			lng 			: <?php echo $map_lng ?>,
			broker 			: "<?php echo $broker ?>",
			search_field	: document.getElementById('txtPlaces'),
			listing_details : "<?php echo get_option('gmap_listing_details_page') ?>",
			filter_location : "<?php echo get_option('gmap_filter_location') ?>",
			address			: "<?php echo $address ?>"
		});
	</script>

<?php
}


//* Run the Genesis loop
genesis();