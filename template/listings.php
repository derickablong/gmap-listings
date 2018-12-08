<?php
//* Add custom body class to the head
add_filter( 'body_class', 'add_body_class' );
function add_body_class( $classes ) {
    $classes[] = 'area-listings';
    return $classes;
}

add_filter( 'genesis_site_layout', '__genesis_return_full_width_content' );
remove_action( 'genesis_before_loop', 'genesis_do_breadcrumbs' );
remove_action( 'genesis_loop', 'genesis_do_loop' );
add_action( 'genesis_loop', 'search_listing' );


function search_listing(){ ?>

   
	<h1 class="ftitle">Featured Listings</h1>

    <div class="fsidebar">

        <form method="post" class="fform">

            <input type="hidden" name="action" value="gmap_filter">
            <input type="hidden" name="area" value="<?php echo $_SERVER["REQUEST_URI"] ?>">
    		<div class="fholder">    	
            	<span class="field_title"><em id="total_record">0</em> Records Found</span>
            	<input type="text" value="" placeholder="Location" id="location" name="location">
            </div>


            <div class="fholder">    	
            	<span class="field_title">Min Price</span>
            	<input type="text" value="" placeholder="Minimum Price" id="min_price" name="min_price">
            </div>


            <div class="fholder">    	
            	<span class="field_title">Max Price</span>
            	<input type="text" value="" placeholder="Maximum Price" id="max_price" name="max_price">
            </div>


            <div class="fholder">    	
            	<span class="field_title">Min Beds</span>
            	<select id="min_beds" class="small" name="min_beds">
            		<?php for( $i = 1; $i <= 6; $i++ ): ?>
            		<option value="<?php echo $i ?>"><?php echo $i; ?></option>
            		<?php endfor; ?>
            	</select>
            </div>


            <div class="fholder">    	
            	<span class="field_title">Min Baths</span>
            	<select id="min_baths" class="small" name="min_baths">
            		<?php for( $i = 1; $i <= 6; $i++ ): ?>
            		<option value="<?php echo $i ?>"><?php echo $i; ?></option>
            		<?php endfor; ?>
            	</select>
            </div>


            <div class="fholder">    	
            	<span class="field_title">Status</span>
            	<label>
            		<input type="checkbox" value="sale" name="fstatus" id="fstatus">
            		For Sale
            	</label>

            	<label>
            		<input type="checkbox" value="lease" name="fstatus" id="fstatus">
            		For Lease
            	</label>
            </div>


            <div class="fholder">    	
            	<span class="field_title">Min Garage Spaces</span>
            	<input type="text" value="" placeholder="0" id="min_garage" name="min_garage">
            </div>


            <div class="fholder">    	
            	<span class="field_title">Min Parking Spaces</span>
            	<input type="text" value="" placeholder="0" id="min_parking" name="min_parking">
            </div>


            <div class="fholder">    	
            	<span class="field_title">Property Class</span>
                
                <?php $class = GMAP::property_class( GMAP::load_listings_by_area( 'result', $_SERVER["REQUEST_URI"], null ) ); ?>
                <?php foreach( $class as $type ): ?>
                	<label>
                		<input type="checkbox" value="<?php echo $type ?>" name="fpclass">
                		<?php echo $type ?>
                	</label>
                <?php endforeach; ?>
            
            </div>



            <div class="fholder">    	
            	<span class="field_title">Sort By</span>
            	<select id="sprice" name="sprice">
            		<option value="Lp_dol">Price</option>
            	</select>

            	<select id="sorder" name="sorder">
            		<option value="desc">Descending</option>
                    <option value="asc">Ascending</option>
            	</select>
            </div>



            <div class="fholder">
            	<input type="submit" class="fbutton gmap_filter" value="Search">
            </div>

        </form>

    </div>

    <!-- google map api -->
    <script src="https://maps.googleapis.com/maps/api/js?key=<?php echo GMAP::get_map_api() ?>&libraries=places,geometry&language=en" type="text/javascript"></script>
    <!-- marker clusterer -->
    <script type="text/javascript" src="<?php echo plugins_url() ?>/gmap-listings/js/markerclusterer.js"></script>
    <script type="text/javascript" src="<?php echo plugins_url() ?>/gmap-listings/js/gmaps.js"></script>
    <script type="text/javascript" src="<?php echo plugins_url() ?>/gmap-listings/js/GeoJSON.js"></script>
    <script type="text/javascript" src="<?php echo plugins_url() ?>/gmap-listings/js/map.js"></script>



    <div class="fcontent <?php if( GMAP::load_listings_by_area( false, $_SERVER["REQUEST_URI"], 'grid' ) > 0 ){ echo 'load'; } ?>">
        
	    <div class="fview_options">
	    	<span data-value="grid">Grid</span>
	    	<span data-value="map" class="active">Map</span>
	    	<span data-value="list">List</span>
	    	<span data-value="larger" class="right">Larger</span>
	    </div>


        <div class="fview map active">
            <div id="map" style="width: 100%"></div>
            <?php echo GMAP::load_listings_by_area( false, $_SERVER["REQUEST_URI"], 'map' ); ?>  
        </div>
	    <div class="fview grid"><?php echo GMAP::load_listings_by_area( false, $_SERVER["REQUEST_URI"], 'grid' ); ?></div>
	    <div class="fview list"><?php echo GMAP::load_listings_by_area( false, $_SERVER["REQUEST_URI"], 'list' ); ?></div>

    </div>


    <script src="<?php echo plugins_url( 'gmap-listings/js' ) ?>/jPaginate.js"></script>
    <script>
    jQuery(document).ready(function(){
        jQuery(".list").jPaginate({ items: 10, paginaton_class: 'list_pagination' });                       
    });
    </script>
    
<?php
}
//* Run the Genesis loop
genesis();