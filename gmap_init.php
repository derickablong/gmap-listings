<!-- google map api -->
<script src="https://maps.googleapis.com/maps/api/js?key=<?php echo GMAP::get_map_api() ?>&libraries=places,geometry&language=en&callback=initialize" type="text/javascript"></script>
<!-- marker clusterer -->
<script type="text/javascript" src="<?php echo plugin_dir_url( __FILE__ ) ?>/js/markerclusterer.js"></script>
<script type="text/javascript" src="<?php echo plugin_dir_url( __FILE__ ) ?>/js/gmaps.js"></script>
<script type="text/javascript" src="<?php echo plugin_dir_url( __FILE__ ) ?>/js/GeoJSON.js"></script>
<!-- price slider library -->
<link rel="stylesheet" href="<?php echo plugin_dir_url( __FILE__ ) ?>/css/jquery-ui.css">
<script src="<?php echo plugin_dir_url( __FILE__ ) ?>/js/jquery-1.10.2.js"></script>
<script src="<?php echo plugin_dir_url( __FILE__ ) ?>/js/jquery-ui.js"></script>
<!-- listing app -->
<script type="text/javascript">
//global path of our folder
var PATH = '<?php echo plugin_dir_url( __FILE__ ) ?>';
//our markers holder
var markers = [];
//group our markers
var markerCluster;
//container for our property keys from each search results
var prop_keys;
//handler for our setimeout event
var time;
//search key
var search_key = "<?php echo $_GET['search'] ?>";
//property
var search_property = "<?php echo $_GET['property'] ?>";
//site url
var site_url = "<?php echo get_bloginfo('siteurl') ?>";

//run our app
var App = function( q ){
    //console.log(q);

    clearTimeout(time);
    
    time = setTimeout(function(){
        
        //map
        map_handler({ 
            SQL: q 
        });


    }, 3000);
    
    //price range
    price_range_slider({ 
        SQL: q 
    });

    //type of homes
    type_of_homes({
        SQL: q
    });

    //number of bedrooms
    bedroom_handler({
        SQL: q
    });

    //listings
    listings({ 
        SQL: q 
    });

}


var add_body_class = function(){
    jQuery('body').addClass('property-listing');
}

//add class to our body
add_body_class();


//stored methods
var methods = function( m, xml ){

    switch( m ){
        case 'load_map'                 :    load_map( xml );                   break;
        case 'start_price_range'        :    start_price_range( xml );          break;
        case 'type_of_homes_options'    :    type_of_homes_options( xml );      break;     

    }

}


var redirect = function( c ){
    var d = c.split('::');
    window.location = site_url + "/listing-info/?id=" + d[0] + "&class=" + d[1];
}


//this will be our ajax handler
var run = function( opt, num ){

    jQuery.ajax({
        url: PATH + '/query/' + opt[0],
        type: 'GET',
        data: opt[1]
    })
    .always(function( xml ) {
       
        
        if( num ){
            if(xml.trim() != ''){
                redirect( xml.trim() );
            }else{
                jQuery('.property-listing .inner-top').css({ 'display':'block' });
                jQuery('.property-listing .site-inner').css({ 'display':'block' });
                init(num, false);
            }

        }else{

            if( opt[2] != ''){
                //output the html results
                jQuery( opt[2] ).html( xml );

            } else {
                //call the methods associated for each event
                methods( opt[3], jQuery.parseJSON(xml) );

            }

            //balance our map sizes
            equalizer();
        }

    });   

}


var process = function(){
   //clea map
   clearMap();

   //run map
    var rq = {
        search          : search_key,
        property        : search_property,
        sr              : jQuery('.pagi li a').find('.active').attr('record'),
        price_range     : jQuery( "#slider-range" ).slider( "values", 0 ) + '::' + jQuery( "#slider-range" ).slider( "values", 1 ),
        bedrooms        : jQuery('#no_bedrooms').val(),
        prop_type       : prop_keys
    };

    init( rq, false );
}


var isMlnum = function( request, q ){
    var options = ['count.php', {SQL: q}, '', ''];
    run(options, request);
}





//equalize the size of our map and sidebar, optional
var equalizer = function(){

    var sidebar = jQuery('.res-sidebar').outerHeight();

    jQuery('#map').css( { 'height' : sidebar + 'px' } );
}


//run map
var map_handler = function( options ){

    var options = ['map.php', options, '', 'load_map'];
    run(options, false);

}


//set price range slider
var price_range_slider = function( options ){

    if( jQuery('#min').text() == '' ){
        
        var options = ['price_range.php', options, '', 'start_price_range'];
        run(options, false);

    }

}


//type of homes
var type_of_homes = function( options ){

    if( jQuery('.type-of-home .left').html() == '' ){

        var options = ['property_type.php', options, '', 'type_of_homes_options'];
        run(options, false);

    }

}


//number of bedrooms handler
var bedroom_handler = function( options ){

    if( jQuery('#no_bedrooms').html() == '' ){
        
        var options = ['number_of_bedrooms.php', options, '#no_bedrooms', ''];
        run(options, false); 

    }
    
}


//run listings
var listings = function( options ){
    var options = ['listing.php', options, '.listings', ''];
    run(options, false); 
}


//filter type of homes
var filter_type_of_homes = function(event){
    if( jQuery(this).hasClass('uncheck') ){
        jQuery(this)
        .removeClass('uncheck')
        .addClass('check');
    }else{
        jQuery(this)
        .removeClass('check')
        .addClass('uncheck');
    }

    var type = '';
    jQuery('.type-of-home span.check').each(function(index, el) {
        type += jQuery(this).html() + "|";
    });;

    prop_keys = type;
   
    process();
}


//bedrooms filter
var filter_bedrooms = function(){
    //process
    process();
}


//start price range slider
var start_price_range = function( data ){
    //price range
    var max_amount = data[1];
    var min_amount = data[0];


    jQuery( "#slider-range" ).slider({
        range: true,
        min: 0,
        max: max_amount,
        values: [ min_amount, max_amount ],
        slide: function( event, ui ) {

            var min = ui.values[0];
            var max = ui.values[1];

            jQuery( "#min" ).text( "$" + format( min ) );
            jQuery( "#max" ).text( "$" + format( max ) );

        },
        start: function(event, ui){
            //wait(1);
        },
        stop: function(event, ui){

            //process
            process();

        }
    });


    jQuery( "#min" ).text( "$" + format( jQuery( "#slider-range" ).slider( "values", 0 ) ) );
    jQuery('#max').text("$" + format( jQuery( "#slider-range" ).slider( "values", 1 ) ) );
    //end of price range
}


//type of homes details
var type_of_homes_options = function( data ){
    //left
    jQuery('.type-of-home .left').html( data[0] );
    //right
    jQuery('.type-of-home .right').html( data[1] );
}


//error handler
var error = function( total ){
    var el = '';
    if( total ){
        el = '<div class="map_error"><span>"0" Record found. Try follow this search format:<br><i>Neighbourhood, City, Listing ID or Postal Code</i></span></div>';
    }else{
        jQuery('body').find('.map_error').remove();
    }
    jQuery('.map').append(el);
}


//populate map data
var load_map = function( data ){
    var address = new Array();
    address = data;

    //check if result is > 0
    if(address.length <= 0)
        error( 1 );
    else
        error( 0 );

    var z = 4;

    if( address.length <= 999 )
        z = 12;
    /*if( address.length <= 5 )
        z = 15;*/

    var map = new google.maps.Map(document.getElementById('map'), {
        zoom: z,
        center: new google.maps.LatLng(<?php echo get_option('map_lat') ?>, <?php echo get_option('map_long') ?>),
        mapTypeId: google.maps.MapTypeId.ROADMAP
    });

    var infowindow = new google.maps.InfoWindow();

  	

    var latitude = 0;
    var longitude = 0;
    var marker = '', i = 0;
    var a, b,type;
    var addrlatlong = new Array();


    //update taotal
    updateTotal(address.length);


    for (i; i < address.length; i++) {
        
        a = address[i];
        b = new Array();
        b = a.split("|");
        var addr = b[0];
        var type = b[1];
        var lsid = b[2];
        var price = b[3];
        var protyp = b[4];
        var photo = b[5];

       

        marker = new google.maps.Marker({
            position: new google.maps.LatLng(b[6], b[7]),
            map: map, 
            icon: {
                url: PATH + '/images/' + type + ".png"
            }
        });

        markers.push(marker);

        google.maps.event.addListener(marker, 'click', (function(marker, i) {
            //var url = '<?php echo network_site_url('/');?>';
            var sContent =
                '<h2 class="info-head"> $' + format( parseFloat(price) ) + '</h2>' +
                '<p>' +
                '<strong>' +addr+'</strong><br />' +
                '' +protyp+'<br />' +
                '<a class="button blue" href="/listing-info/?id='+lsid+'&class='+type+'">View Listing</a>'+ '<br /><br />' +
                '<div id="focus" style="width:300px;height:250px;background-size:150%;background: url('+photo+') no-repeat center;"></div>'+'<br />' +
                '</p>';
                
            return function() {

                infowindow.close();
                infowindow.setContent(sContent);
                infowindow.open(map, this);

                map.setCenter( this.getPosition() );
                map.setZoom(15);
             
            }
        })(marker, i));
        
        
    }//end of loop
   

    var clusterStyles = [{

	    url: PATH + '/images/cluster.png',
	    height: 84,
	    width: 84,
	   
	    textColor: '#0055a5',
	    textSize: 16,
	   
	  },{
	    url: PATH + '/images/cluster.png',
	    height: 84,
	    width: 84,
	   
	    textColor: '#0055a5',
	    textSize: 16,
	  },{
	    url: PATH + '/images/cluster.png',
	    height: 84,
	    width: 84,
	   
	    textColor: '#0055a5',
	    textSize: 16,
	  }];


	markerCluster = new MarkerClusterer(map, markers, {
		styles: clusterStyles,
	    gridSize: 40,
	    maxZoom: 15,
	    minimumClusterSize: 1000
	});


    done( address.length );

}


//clear the map markers
var clearMap = function(){
    for(i = 0; i < markers.length; i++){
		markers[i].setMap(null);
	}

	markers = [];
	markerCluster.clearMarkers();
}


//add on click event on the marker
var marker_infowindow = function( id ){
    google.maps.event.trigger(markers[id], 'click');
    return false;
}


//update the total lists found from the search result
var updateTotal = function( total ){
	jQuery('.counter').text( total );
}


//format amount
var format = function( amount ){
    return amount.toFixed(2).replace(/(\d)(?=(\d{3})+\.)/g, '$1,');
}


//complete request handler
var done = function( total ){

    //pagination
    var frst = jQuery('.pagi li:eq(1) a').text();
    
    var options = ['pagination.php', {
            TOTAL_LISTS     : total,
            LISTS_PER_VIEW  : '<?php echo LISTS_PER_VIEW ?>',  
            ACTIVE_RECORD   : jQuery('.pagi li a').find('.active').attr('record'),
            LAST            : jQuery('.pagi li:nth-last-child(2) a').text(),
            FIRST           : frst,
            direction       : 'prev',  
        }, 

        '.pagi', ''];
    
    run(options, false);
    
    //complete
    wait(0);
}


//waiting
var wait = function( o ){
    var map_loader = jQuery('.map');
    var el = "<span id='wait'></span>";

    //remove first
    jQuery('#wait').remove();

    if( o ){
        jQuery( map_loader ).append(el);
    } else {
        jQuery('#wait').fadeOut('fast', function(){
            jQuery(this).remove();
        });
    }
}


//initialize app
var init = function( request, num ){
    if(!num){
        //in process
        wait(1);
    }

    jQuery.ajax({
        url: PATH + 'query/filterv2.php',
        type: 'GET',
        data: request
    })
    .always(function(sql) {
        
        //load all elements
        if(num)
            isMlnum( request, sql );
        else
            App( sql );
        
        //console.log(sql);
    });
}

//end of functions
</script>


<script type="text/javascript">
//our initializer
//run map
var rq = {
    search          : search_key,
    property        : search_property,
    sr              : '',
    price_range     : '',
    bedrooms        : ''
};

init( rq, true);

//our listener
jQuery(document).ready(function() {

    //var
    var el;
    var file;
    var options = [];

    //pagination
    jQuery('body').on('click', '.pagi li a', function(event) {
        
        //prevent page from loading
        event.preventDefault();
        
        //check if not prev and next arrows was clicked
        if(jQuery(this).hasClass('prev') || jQuery(this).hasClass('next')){

            var frst = jQuery('.pagi li:eq(1) a').text();

            var options = ['pagination.php', {
                    LISTS_PER_VIEW  : '<?php echo LISTS_PER_VIEW ?>',  
                    ACTIVE_RECORD   : jQuery('.pagi li a').find('.active').attr('record'),
                    LAST            : jQuery('.pagi li:nth-last-child(2) a').text(),
                    FIRST           : frst,
                    direction       : jQuery(this).attr('class')  
                }, 

                '.pagi', ''];
            
            run(options, false);

        }else{

            //reset active
            jQuery('.pagi li a').removeClass('active');
            jQuery(this).addClass('active');

            //run map
            var options = {
                search          : search_key,
                sr              : jQuery(this).attr('record'),
            };

            //listings
            listings({ 
                SQL     : '',
                 sr     : jQuery(this).attr('record'),
            });

        }

    });//end of pagination

    //type of homes
    jQuery('body').on('click', '.type-of-home span', filter_type_of_homes);
    //bedrooms
    jQuery('#no_bedrooms').on('change', filter_bedrooms);

});
</script>