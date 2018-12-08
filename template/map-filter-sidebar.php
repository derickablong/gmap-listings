<?php global $map_sections; ?>
<div id="map-filter">
	<div class="map-wrap">
		
		<?php $price = GMAP::price( GMAP::get_all_property_class() ); ?>

		<form method="post" id="filter" style="display: block">
			
			<h3>Settings <a href="#" class="hide-filter"></a></h3>

			<div class="map-field">
				<label class="title">Type:</label>
				<select name="prop_type">
					<option value="all">All</option>
					<option value="condo">Condo</option>
					<option value="commercial">Commercial</option>
					<option value="residential">Residential</option>
				</select>
			</div>

			<div class="map-field">
				<label class="title">Price:</label>
				<?php echo $map_sections['price_slider']; ?>
			</div>

			<div class="map-field">
				<label class="title">Status:</label>
				<select name="status">
					<option value="any">Any</option>
					<option value="lease">For Lease</option>
					<option value="sale">For Sale</option>
				</select>
			</div>

			<div class="map-field">
				<label class="title">Class:</label>
				<?php $class = GMAP::property_class( GMAP::get_all_property_class() ); ?>
	            <?php foreach( $class as $type ): ?>
	            	<label>
	            		<input type="checkbox" value="<?php echo $type ?>" name="fpclass">
	            		<?php echo $type ?>
	            	</label>
	            <?php endforeach; ?>
			</div>

			

			<input type="hidden" name="action" value="gmap_settings_filter">
			<input type="hidden" name="max" value="<?php echo $price['max'] ?>">
			<input type="hidden" name="min" value="<?php echo $price['min'] ?>">			
			<input type="submit" name="filter" class="btn" value="Filter">
		</form>
		

	</div>
</div>