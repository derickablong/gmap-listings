<?php
define( 'GMAP_PATH', dirname(__FILE__) );
include GMAP_PATH . '/../gmap_db.php';

$class = 'map_disabled';
$map_lat = get_option('map_lat');
$map_lng = get_option('map_long');
$map = 'false';
$address = '';
$broker = 'false';

if( $map == 'true' ) $class = 'map_enabled';
?>
  
<div id="map-info" <?php if( is_page() || is_single() ): echo 'class="map-info show"'; endif;?>>
	<form method="post" id="search-area" runat="server" class="<?php echo $class ?>"> 

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



<div id="googleMap" style="width:100%;height:600px;" class="<?php echo $class ?>"></div> 

<script type="text/javascript" src="http://maps.googleapis.com/maps/api/js?key=<?php echo GMAP::get_map_api() ?>&libraries=places,geometry&language=en"></script>
<!-- marker clusterer -->
<script type="text/javascript" src="<?php echo "http://" . $_SERVER['SERVER_NAME'] ?>/wp-content/plugins/gmap-listings/js/markerclusterer.js"></script>
<script type="text/javascript" src="<?php echo "http://" . $_SERVER['SERVER_NAME'] ?>/wp-content/plugins/gmap-listings/js/gmaps.js"></script>
<script type="text/javascript" src="<?php echo "http://" . $_SERVER['SERVER_NAME'] ?>/wp-content/plugins/gmap-listings/js/GeoJSON.js"></script>
<script type="text/javascript" src="<?php echo "http://" . $_SERVER['SERVER_NAME'] ?>/wp-content/plugins/gmap-listings/js/jquery.gmap.js"></script>

<script type="text/javascript">
	$('#googleMap').gmap({
		plugin_url		: 'http://<?php echo $_SERVER['SERVER_NAME'] ?>/wp-content/plugins/gmap-listings',
		property 		: 'http://<?php echo $_SERVER['SERVER_NAME'] ?>/wp-content/uploads/property',
		visible			: "<?php echo $map ?>",
		lat 			: <?php echo $map_lat ?>,
		lng 			: <?php echo $map_lng ?>,
		broker 			: "<?php echo $broker ?>",
		search_field	: document.getElementById('txtPlaces'),
		address			: "<?php echo $address ?>"
	});
</script>