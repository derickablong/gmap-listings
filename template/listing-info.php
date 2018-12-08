<?php
//* Add custom body class to the head
add_filter( 'body_class', 'add_body_class' );
function add_body_class( $classes ) {
    $classes[] = 'area-listings listing-info';
    return $classes;
}

add_filter( 'genesis_site_layout', '__genesis_return_full_width_content' );
remove_action( 'genesis_before_loop', 'genesis_do_breadcrumbs' );
remove_action( 'genesis_loop', 'genesis_do_loop' );
add_action( 'genesis_loop', 'search_listing' );


function search_listing(){ ?>
	<?php //echo GMAP::load_listings_by_area( 'query', $_SERVER["REQUEST_URI"], null ); ?>
	<?php $results = GMAP::load_listings_by_area( 'result', $_SERVER["REQUEST_URI"], null ); ?>
	<?php foreach ( $results as $result ): ?>
		

	<!-- content -->
	<div class="fleft fcontent">
		
		
		<div class="fsingle-header">
			<h1><?php GMAP::console( $result, 'Addr' ); ?></h1>
			<span>$<?php GMAP::console( $result, 'Lp_dol' ); ?></span>
		</div>



		<div id="fsingle-det">
			<div class="fleft fcol">
				<span><?php GMAP::console( $result, 'Cross_st' ); ?>, <?php GMAP::console( $result, 'County' ); ?></span>
				<span>
					<em><?php GMAP::console( $result, 'Br' ); ?> Beds</em>
					<em><?php GMAP::console( $result, 'Bath_tot' ); ?> Baths</em>
				</span>
			</div>

			<div class="fright fcol">
				<span><?php GMAP::console( $result, 'type_of_home' ); ?></span>
				<span>MLS@ ID <?php GMAP::console( $result, 'Ml_num' ); ?></span>
			</div>
		</div>




		<div id="fgallery" class="fbxslider">
			<?php GMAP::photo_gallery( GMAP::console( $result, 'Ml_num', true ), 1, 'gallery-slider' ); ?>
			<?php GMAP::photo_gallery( GMAP::console( $result, 'Ml_num', true ), 0, 'gallery-pager' ); ?>	
		</div>





		<div id="flisting">
			
			<div class="flistings fleft">
				<h3>Listing Stats</h3>
				<ul>
					<li>Price <span><?php GMAP::console( $result, 'Lp_dol' ); ?></span></li>
					<li>Property Type <span><?php GMAP::console( $result, 'type_of_home' ); ?></span></li>
					<li>Bedrooms <span><?php GMAP::console( $result, 'Br' ); ?></span></li>
					<li>Bathrooms <span><?php GMAP::console( $result, 'Bath_tot' ); ?></span></li>
					<li>Extras <span><?php GMAP::console( $result, 'Extras' ); ?></span></li>
					<li>Approx. Sq. Ft. <span><?php GMAP::console( $result, 'App_sq_ft' ); ?></span></li>
					<li>Estimated annual taxes <span><?php GMAP::console( $result, 'Taxes' ); ?></span></li>
				</ul>
			</div>


			<div class="flistings fright">
				<h3>Features</h3>
				<ul>
					<li>Exterior <span><?php GMAP::console( $result, 'Exterior' ); ?></span></li>
					<li>Living Style <span><?php GMAP::console( $result, 'Living_style' ); ?></span></li>
					<li>Garage <span><?php GMAP::console( $result, 'Garage' ); ?></span></li>
					<li>Heating <span><?php GMAP::console( $result, 'Heating' ); ?></span></li>
					<li>Square Footage <span><?php GMAP::console( $result, 'App_sq_ft' ); ?></span></li>
					<li>Water <span><?php GMAP::console( $result, 'Water_' ); ?></span></li>
					<li>Pool <span><?php GMAP::console( $result, 'Pool_' ); ?></span></li>
				</ul>
			</div>

		</div>



		<div id="fdetails">
			<h3><?php GMAP::console( $result, 'Addr' ); ?></h3>
			<p><?php GMAP::console( $result, 'Ad_text' ); ?></p>

			<p class="fnote">
				<strong>Note:</strong>
				The above information is deemed reliable, but it is not guaranteed. Search facilities other than by a consumer seeking to purchase or lease reat estate, is prohibited. Brokered By: <?php GMAP::console( $result, 'Rltr' ); ?>
			</p>
		</div>



	</div>
	<!-- end of content -->





	<!-- sidebar -->
	<div class="fright fsidebar"><?php if ( !function_exists('dynamic_sidebar') || !dynamic_sidebar('realtor-widget') ): endif; ?></div>
	<!-- end of sidebar -->


    

    <?php endforeach; ?>

	<script type="text/javascript">
		var carousel;
		var slider;	

		jQuery(document).ready(function(){
          
          var min = 10;
          if( jQuery(window).width() <= 650 ) min = 4;

          carousel = jQuery('.gallery-pager').bxSlider({
          	slideWidth: 600,
	        minSlides: min,
	        maxSlides: 15,
	        moveSlides: 1,
	        slideMargin: 10,
	        pager: false
          });

          slider = jQuery('.gallery-slider').bxSlider({
		    captions: true,
		    controls: false,
		    pager: false
		  });

		});

		function clicked(position) {
		    slider.goToSlide(position);
		}
	</script>


<?php
}
//* Run the Genesis loop
genesis();