<?php GMAP::gmap_settings_handler(); ?>
<div class="wrap">
	<h2>Update Records</h2>
	<p>Clicking the button bellow will refresh the local data<br>on the server for the listings local storage.</p>
	<form class="gmap-listings" method="post" action="<?php echo $_SERVER['REQUEST_URI'] ?>">
		<input type="hidden" name="gmap_search_type" value="location">
		<p class="submit">
			<input type="submit" name="refresh" value="Refresh Data" class="button button-primary">
		</p>
	</form>
</div>