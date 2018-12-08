<?php GMAP::gmap_settings_handler(); ?>
<div class="wrap">
	<h2>Listing Settings</h2>
	<form class="gmap-listings" method="post" action="<?php echo $_SERVER['REQUEST_URI'] ?>">
		
		<div class="map_fields">
			<fieldset>
				<legend>Assign the pages you want the listings appeard</legend>
				<label>Listing</label>
				<select name="gmap_listing_page"> 
					<option value="">
					<?php echo esc_attr( __( 'Select page' ) ); ?></option> 
					<?php 
					$pages = get_pages(); 
					foreach ( $pages as $page ) {
						print_r($page);
						$option = '<option value="' . $page->post_name . '" ' . ((get_option('gmap_listing_page') == $page->post_name)? 'selected' : '') . '>';
						$option .= $page->post_title;
						$option .= '</option>';
						echo $option;
					}
					?>
				</select>
				
				<label>Details</label>
				<select name="gmap_listing_details_page"> 
					<option value="">
					<?php echo esc_attr( __( 'Select page' ) ); ?></option> 
					<?php 
					$pages = get_pages(); 
					foreach ( $pages as $page ) {
						$option = '<option value="' . $page->post_name . '" ' . ((get_option('gmap_listing_details_page') == $page->post_name)? 'selected' : '') . '>';
						$option .= $page->post_title;
						$option .= '</option>';
						echo $option;
					}
					?>
				</select>
			</fieldset>
		</div>


		<h2>Filter Location</h2>
		<div class="map_fields">
			<fieldset>
				<legend>What part you want the filter show on the map?</legend>
				<select name="gmap_filter_location"> 
					<option value="">-</option>
					<option value="top" <?php echo ((get_option('gmap_filter_location') == 'top')? 'selected' : '') ?>>Top</option>
					<option value="sidebar" <?php echo ((get_option('gmap_filter_location') == 'sidebar')? 'selected' : '') ?>>Sidebar</option>
					<option value="bottom" <?php echo ((get_option('gmap_filter_location') == 'bottom')? 'selected' : '') ?>>Bottom</option>
				</select>
			</fieldset>
		</div>

		<p class="submit">
			<input type="submit" name="Save" value="Save Settings" class="button button-primary">
		</p>

	</form>
</div>