<div class="wrap">
	<h2>Grab Listings Management</h2>
	<p>This management will download the latest listing data from the api.</p>
	
	<?php 
	if( isset($_GET['grab']) ):

		//output xml
		GMAP::grab_listings_xml();

		if(GMAP::containsRecords()) include dirname( __FILE__ ) . '/../gmap_grab_daily_listings.php';
		else include dirname( __FILE__ ) . '/../gmap_grab_listings.php';
	else:
		GMAP::set_default_query_session();	
		echo '<a href="#" class="button button-primary grab_start">Start Grabing Now<span></span></a>';
	endif; ?>
</div>
<script type="text/javascript">
	jQuery(document).ready(function(){
		jQuery('.grab_start').on('click', function(e){
			e.preventDefault();
			jQuery(this)
				.addClass('grab_started')
				.html('Initializing...')
				.append('<span></span>');
			window.location.href = "?page=grab_listings&grab=true";
		});
	});
</script>