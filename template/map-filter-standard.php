<?php global $map_sections; ?>
<div id="map-filter-standard">
	<div class="map-wrap wrap">
		<?php $price = GMAP::price( GMAP::get_all_property_class() ); ?>
		<form method="post" id="filter" style="display: block">

			<!-- field -->
			<div class="filter-field">
				<h3>Property Filter</h3>
			</div>
			<!-- end of field -->

			<!-- field -->
			<div class="filter-field">
				<span class="filter-dropdown" data-label="Price">Price</span>
				<div class="filter-options price-slider-dropdown" data-role="price">
					<span data-value="any" class="default selected">Any Price</span>
					<inpu name="price-data" type="hidden" class="price-data selected" data-role="" value="0">
					<?php echo $map_sections['price_slider']; ?>
				</div>
			</div>
			<!-- end of field -->

			
			<!-- field -->
			<div class="filter-field">
				<span class="filter-dropdown" data-label="How many bed?">How many bed?</span>
				<div class="filter-options" data-role="bed">
					<span data-value="any" class="default selected">Any number</span>
					<?php for($i = 1; $i <= 6; $i++): ?>
						<span data-value="<?php echo $i ?>"><?php echo $i ?> bed<?php echo ($i > 1)? 's' : '' ?></span>
					<?php endfor; ?>
				</div>
			</div>
			<!-- end of field -->


			<!-- field -->
			<div class="filter-field">
				<span class="filter-dropdown" data-label="How many bath?">How many bath?</span>
				<div class="filter-options" data-role="bath">
					<span data-value="any" class="default selected">Any number</span>
					<?php for($i = 1; $i <= 6; $i++): ?>
						<span data-value="<?php echo $i ?>"><?php echo $i ?> bath<?php echo ($i > 1)? 's' : '' ?></span>
					<?php endfor; ?>
				</div>
			</div>
			<!-- end of field -->


			<!-- field -->
			<div class="filter-field">
				<span class="filter-dropdown" data-label="Condo or Freehold">Condo or Freehold</span>
				<div class="filter-options" data-role="con_free">
					<span data-value="any" class="default selected">Any type</span>
					<span data-value="condo">Condo</span>
					<span data-value="freehold">Freehold</span>
				</div>
			</div>
			<!-- end of field -->


			<!-- field -->
			<div class="filter-field">
				<span class="filter-dropdown" data-label="Sale or Lease">Sale or Lease</span>
				<div class="filter-options" data-role="sale_lease">
					<span data-value="any" class="default selected">Any type</span>
					<span data-value="sale">Sale</span>
					<span data-value="lease">Lease</span>
				</div>
			</div>
			<!-- end of field -->


			<!-- field -->
			<div class="filter-field filter-clear-filters">
				<span class="filter-clear" data-label="Clear Filters">Clear Filters</span>
			</div>
			<!-- end of field -->

			<input type="hidden" name="max" value="<?php echo $price['max'] ?>">
			<input type="hidden" name="min" value="<?php echo $price['min'] ?>">	
		</form>
		

	</div>
</div>