//admin settings
//add adtional fields
var pop_api_remove_button = function(){
	jQuery('#remove_api_button').remove();
	jQuery('.api_keys').removeAttr('data-role');
	jQuery(this).prop('data-role', 'option_to_remove').after('<a href="#" id="remove_api_button">-</a>');
}



var add_new_gmap_api_field = function(e){
	e.preventDefault();
	jQuery(this).parent().prepend('<input type="text" name="map_api_key[]" class="api_keys" placeholder="Enter another api key here...">');
	focus_first_map_api_field();
}



var focus_first_map_api_field = function(){
	jQuery('.api_keys').first().focus();
}



var remove_api_button = function(){
	if(jQuery('#remove_api_button').is(':hover') == false)
	jQuery('#remove_api_button').remove();
}



var remove_map_api_field = function(e){
	e.preventDefault();
	jQuery(this).prev().remove();
}



//admin settings
//country
var assign_country_value = function(){
	jQuery('option:selected', this).val( jQuery(this).val() );
}



var set_active_map_country = function(){
	jQuery('.map_fields #country option').each(function(index, el) {
		if( jQuery(this).val() == jQuery('.map_fields #country').attr('data-role') ){
			jQuery(this).prop('selected', 'selected');
		}	
	});
}


//percircle
//reanimate circle data
var animate_percircle = function(){
	jQuery('.percircle').each(function(index, el) {
		
		var custom = jQuery(this).find('i').text();
		var percent = jQuery(this).attr('data-percent');

		jQuery(this).html('').percircle({
			text: percent + '%<i>' + custom + '</i>'
		});
	});
}


//balance squeeze
var balance_squeeze = function(){
	jQuery('#squeez_container .image-holder.bt').height( jQuery('#squeez_container .right').height() );
}


var switch_view = function() {

	//set active view
	var active = jQuery(this).attr('data-value');


	if( active == 'larger' ) {
		if( jQuery( 'body' ).hasClass( 'flarger' ) ) {
			jQuery( 'body' ).removeClass( 'flarger' );
			jQuery(this).removeClass('active');
		} else {
			jQuery( 'body' ).addClass( 'flarger' );
			jQuery(this).addClass('active');

			jQuery('<style type="text/css">.flarger .content-sidebar-wrap:after{ height: '+ jQuery('.content-sidebar-wrap .content').height() +'px; }</style>' ).insertAfter('footer');
		}
	} else {

		jQuery('.fview_options span').removeClass('active');
		jQuery(this).addClass('active');


		//show selected view
		jQuery('.fview').removeClass('active');
		jQuery('.'+active).addClass('active');

	}

	if( active == 'list' ) {
		jQuery('.pagination').fadeIn('fast');
	} else {
		jQuery('.pagination').fadeOut('fast');
	}


}



var viewController = function() {


	jQuery(".fitem").slice(0, 10).show();

	
	//lists
	if( jQuery('.fitem').length > 0 ) {
		jQuery('.fcontent').removeClass('load').addClass('scrolled');
	
	}

	if( jQuery('.fgrid').length > 0 ) {
		jQuery('.fcontent').removeClass('load').removeClass('scrolled');
	}
	
	

	jQuery(window).scroll(function () {

		if(jQuery(window).scrollTop() == jQuery(document).height() - jQuery(window).height()) {

			if( jQuery('.fitem').length > 0 ) {
		        jQuery(".fitem:hidden").slice(0, 4).slideDown();
		        if (jQuery(".fitem:hidden").length == 0) {
		            jQuery('.fcontent').removeClass('scrolled');
		        } else {
		        	jQuery(".ftotop").fadeIn('slow');
		        }
		        jQuery('html,body').animate({
		            scrollTop: jQuery(this).offset().top
		        }, 1500);
		    }
	    }
    });
    //end of list






    jQuery('a[href=#top]').click(function () {
	    jQuery('body,html').animate({
	        scrollTop: 0
	    }, 600);
	    return false;
	});



	jQuery(window).scroll(function () {
	    if (jQuery(this).scrollTop() == 0) {
	        jQuery('.ftotop').fadeOut();
	    }
	});

}





var search_type = function() {

	var el = jQuery('select[name=search_type]');

	if( el.val() == 'area' ) {
		jQuery('#area').fadeIn('fast');
	} else {
		jQuery('#area').fadeOut('fast');
	}

}














jQuery(document).ready(function(){


	jQuery('.pagination').fadeOut('fast');
	jQuery('.fview_options span').click(switch_view);




	jQuery('p').filter(function () { return jQuery.trim(this.innerHTML) == "" }).remove();
	
	//event listener
	//add more api fields
	jQuery('#add_more_api').on('click', add_new_gmap_api_field);
	jQuery('body').on('click', '#remove_api_button', remove_map_api_field);


	//map country handler
	set_active_map_country();
	jQuery('.map_fields #country').on('change', assign_country_value);


	//percircle
	jQuery(window).scroll(animate_percircle);


	//remove br
	jQuery('#squeez_container').find('br').remove();
	jQuery('#squeez_container p:empty').remove();
	balance_squeeze();
	jQuery(window).on('resize', balance_squeeze);


	//radomize member
	jQuery("div.textwidget, div#person-wrap").randomize("div.person");

	//remove_marker_out_of_bound();


	//search type
	search_type();
	jQuery('select[name=search_type]').on('change', search_type);


});

(function($) {

$.fn.randomize = function(childElem) {
  return this.each(function() {
      var $this = $(this);
      var elems = $this.children(childElem);

      elems.sort(function() { return (Math.round(Math.random())-0.5); });  

      $this.detach(childElem);  

      for(var i=0; i < elems.length; i++)
        $this.append(elems[i]);      

  });    
}
})(jQuery);