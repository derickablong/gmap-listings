<?php GMAP::gmap_settings_handler(); ?>
<!-- listing stylesheet -->
<div class="wrap">
	<form class="gmap-listings" method="post" action="<?php echo $_SERVER['REQUEST_URI'] ?>">
		<input type="hidden" name="map_admin_settings" value="1">
		
		
		<h4>TREB Server Settings</h4>

		<div class="map_fields">
			<fieldset>
				<legend>Settings</legend>
				<label>URL</label>
				<input type="text" name="treb_url" value="<?php echo get_option('treb_url') ?>">
			
				<label>Username</label>
				<input type="text" name="treb_username" value="<?php echo get_option('treb_username') ?>">
			
				<label>Password</label>
				<input type="text" name="treb_password" value="<?php echo get_option('treb_password') ?>">
			</fieldset>
		</div>


		<p class="submit">
			<input type="submit" name="Save" value="Save Settings" class="button button-primary">
		</p>
	</form>
</div>