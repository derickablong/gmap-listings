<?php GMAP::gmap_settings_handler(); ?>
<!-- listing stylesheet -->
<div class="wrap">
	<form class="gmap-listings" method="post" action="<?php echo $_SERVER['REQUEST_URI'] ?>">
		<input type="hidden" name="map_admin_settings" value="1">

		<h4>Work/Transit Score API</h4>

		<div class="map_fields">
			<fieldset>
				<legend>Settings</legend>
				<label>API</label>
				<input type="text" name="work_score_api" value="<?php echo get_option('work_score_api') ?>">
			</fieldset>
		</div>

		<p class="submit">
			<input type="submit" name="Save" value="Save Settings" class="button button-primary">
		</p>
	</form>
</div>