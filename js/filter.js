var no_records_found = function() {
	jQuery( '.fview' ).append( '<span class="ferror">Sorry, no listing found. Try another search. </span>' );
}

var refine_area_listings = function( e ) {

	e.preventDefault();

	clearMap();

	var filtered_data = jQuery(this).serialize();

	jQuery.ajax({
		type: 'post',
		url: request_url.ajaxurl,
		datatype: 'json',
		data: filtered_data,
		error: function(XMLHttpRequest, textStatus, errorThrown) {
			no_records_found();
		}
	}).done(function( data ) {
		
		data = jQuery.parseJSON( data );
		
	
		//console.log( data.sql );
		//grid
		jQuery( '.fview.grid' ).html( data.grid );

		//list
		jQuery( '.fview.list' ).html( data.lists );
		
		
		load_map( String(data.map.area), String(data.map.path), String(data.map.lat), String(data.map.long), String(data.map.addr) );

		viewController();

		

	});

}

jQuery(document).on( 'submit', '.fform', refine_area_listings );