(function($){

	
	$.fn.gmap = function( options ){

		var gmap = $.extend({

			standard 			: false,
			broker 				: false,
			plugin_url			: null,
			listing_details 	: null,
			property 			: null,
			address 			: null,
			visible 			: false,
			isFiltered 			: false,
			lat 				: 0,
			lng 				: 0,
			top_position 		: 0,
			filter_location 	: null,
			places				: null,
			map 				: null,
			zoom 				: 15,
			panorama			: null,
			infowindow 			: null,
			markerCluster 		: null,
			current_marker		: null,
			photo_url			: null,
			search_field		: null,
			distance_path		: [],
			near_circle			: [],
			main_circle			: [],
			markers 			: [],
			nearbyMarkers 		: [],
			locator 			: [],
			data_info 			: [],
			filtered 			: [],
			isFilter			: true,
			service				: null,
			exist				: false,
			radius 				: 1,
			radius_type 		: 'km',
			distance 			: {
									mi: 3963.1676,
									km: 6378.1,
									ft: 20925524.9,
									mt: 6378100,
									"in": 251106299,
									yd: 6975174.98,
									fa: 3487587.49,
									na: 3443.89849,
									ch: 317053.408,
									rd: 1268213.63,
									fr: 31705.3408
								},
			radius_boundairy	: 0,
			doc 				: 0,
			el_doc 				: null,
			el_body 			: null,
			el_header 			: null,
			el_text_place 		: null,
			el_lat				: null,
			el_lng				: null,
			el_radius_data		: null,
			el_radius_distance	: null,
			el_google_map		: null,
			el_map_gm			: null,
			el_map_enabled		: null,
			el_map_info			: null,
			el_toggle_map		: null,
			el_toggled			: null,
			el_info_data		: null,
			el_school			: null,
			el_restaurant		: null,
			el_store			: null,
			el_slider_range		: null,
			el_text_max			: null,
			el_text_min			: null,
			el_min 				: null,
			el_max 				: null,
			el_map_filter		: null,
			el_map_wrap 		: null,
			el_filter_error		: null,
			el_map_view_wrapper : null,
			el_map_view 		: null,
			el_gallery_wrapper	: null,
			el_carousel			: null,
			el_pac 				: null,
			el_wait 			: null,
			el_sdbar 			: null,
			el_view_map 		: null,
			el_filter_field 	: null,
			el_filter_parent    : null,
			el_filter_dropdown 	: null,
			el_filter_option 	: null,
			el_filter_clear 	: null,







			createCluster: function( markers ) {


				var clusterStyles = [{

					url: gmap.plugin_url + '/images/rsz_cluster.png',
					height: 42,
					width: 42,

					textColor: '#0055a5',
					textSize: 16,

				},{

					url: gmap.plugin_url + '/images/rsz_cluster.png',
					height: 42,
					width: 42,

					textColor: '#0055a5',
					textSize: 16,

				},{
				
					url: gmap.plugin_url + '/images/rsz_cluster.png',
					height: 42,
					width: 42,

					textColor: '#0055a5',
					textSize: 16,
				
				}];


				gmap.markerCluster = new MarkerClusterer(gmap.map, markers, {
					styles: clusterStyles,
				    gridSize: 50,
				    maxZoom: 15,
				});



			},









			format: function( amount ){

			    return amount.toFixed(2).replace(/(\d)(?=(\d{3})+\.)/g, '$1,');

			},









			calcDistance: function(marker, nearby) {

			   return ((google.maps.geometry.spherical.computeDistanceBetween(marker, nearby) / 1000).toFixed(2));

			},









			clearDrawedLine: function() {

				if( gmap.distance_path.length > 0 ) gmap.distance_path[gmap.distance_path.length-1].setMap(null);

			},









			drawLine: function( marker, nearby ) {

				gmap.clearDrawedLine();

				var path = new google.maps.Polyline({
					path: [marker.getPosition(), nearby.getPosition()],
					geodesic: true,
					strokeColor: '#FF0000',
					strokeOpacity: 1.0,
					strokeWeight: 2
			    });

			    path.setMap(gmap.map);

			    gmap.distance_path.push(path);

			},









			radiusDistanceHandler: function() {

				var data = Number(gmap.el_radius_data.val());
				var type = gmap.el_radius_distance.val();
				
				gmap.radius = data;
				gmap.radius_type = type;
				gmap.radius_boundairy = (data / gmap.distance[type]) * gmap.distance['mt'];
				
				gmap.wait(true);
				setTimeout(gmap.loadMap, 2000);

			},









			radiusOptions: function() {

				gmap.el_google_map.append(
					'<div class="radius-wrapper">'
						+'<input type="text" value="'+gmap.radius+'" id="radius-data">'
						+'<select id="radius-distance">'
							+'<option value="mi" '+((gmap.radius_type=='mi')?'selected':'')+'>Mile</option>'
							+'<option value="km" '+((gmap.radius_type=='km')?'selected':'')+'>Kilometer</option>'
							+'<option value="ft" '+((gmap.radius_type=='ft')?'selected':'')+'>Feet</option>'
						+'</select>'
						+'<span>Adjust distance here.</span>'
					+'</div>'
				);

				

			},






			sdbar: function() {

				if(gmap.doc <= 620) gmap.el_sdbar.show();
				else gmap.el_sdbar.hide();

			},






			mapViewOptions: function( data ) {

				gmap.removeMapView();

				var content =  '<div class="map-view-wrapper">'
								   +'<div class="map-view sdbar" data-index="'+data[0]+'" data-locator="'+data[1]+'" data-action="sdbar">'
										+'<span></span>'
										+'Sidebar'
								   +'</div>'
								   +'<div class="map-view map active" data-index="'+data[0]+'" data-locator="'+data[1]+'" data-action="map">'
										+'<span></span>'
										+'Map View'
								   +'</div>'
								   +'<div class="map-view street" data-index="'+data[0]+'" data-locator="'+data[1]+'" data-action="street">'
										+'<span></span>'
										+'Street View'
								   +'</div>'
								   +'<div class="map-view gallery" data-index="'+data[0]+'" data-locator="'+data[1]+'"  data-action="gallery">'
										+'<span></span>'
										+'Gallery'
								   +'</div>'
						   	    +'</div>';

				gmap.el_google_map.append(content);
				gmap.element();
				gmap.sdbar();
				
			},








			customizeInfoWindow: function() {	

				

				gmap.el_map_gm.prev().css('display', 'none');
				gmap.el_map_gm.parent().css({
					'background-color' : '#fff',
					'margin-top' : '85px',
					'margin-left' : '-142px'
				});

			},







			focusMap: function() {

				$('html, body').animate({
			        scrollTop: ( gmap.el_google_map.offset().top - gmap.top_position )
			    }, 2000);

			},







			startPanorama: function() {

				gmap.panorama = new google.maps.StreetViewPanorama(
		            document.getElementById('googleMap'), {
		              addressControlOptions: {
		                position: google.maps.ControlPosition.BOTTOM_CENTER
		              },
		              linksControl: false,
		              panControl: false,
		              enableCloseButton: false,
		              zoomControl: false
		        });

		        gmap.setView(false);

			},







			resetImportantContainer: function() {

				gmap.markers = [];
				gmap.nearbyMarkers = [];
				gmap.locator = [];
				gmap.data_info = [];
				gmap.filtered = [];
				gmap.isFilter = true;

				gmap.map = null;
				gmap.main_circle = [];
				gmap.near_circle = [];
				gmap.distance_path = [];

			},









			loadMap: function() {


				if( gmap.radius_boundairy < 1 ) {
					gmap.filterError('Distance too small.');
					return;
				}


				gmap.focusMap();
				gmap.resetImportantContainer();

				gmap.console('Loaded: '+gmap.lat+', '+gmap.lng);

				var mapOpt = {
					center:new google.maps.LatLng(gmap.lat, gmap.lng),
					disableDefaultUI: true, // a way to quickly hide all controls
					zoom: gmap.zoom,
					maxZoom: 20,
					minZoom: 4,
					mapTypeId:google.maps.MapTypeId.ROADMAP
				};


				gmap.map = new google.maps.Map(document.getElementById("googleMap"),mapOpt);

				
				gmap.startPanorama();	


				//create bouindary
				gmap.main_circle = new google.maps.Circle({
					strokeColor: '#FF0000',
					strokeOpacity: 0,
					strokeWeight: 2,
					fillColor: '#FF0000',
					fillOpacity: 0,
					map: gmap.map,
					center: {lat: gmap.lat, lng: gmap.lng},
					radius: gmap.radius_boundairy
				});
				
				
				
				if( gmap.visible ) {

					//START MARKER
					var marker_type = ['school','restaurant','store'];
					
					gmap.infowindow = new google.maps.InfoWindow({
						maxWidth: 1800,
						disableAutoPan: true
					});


					gmap.service = new google.maps.places.PlacesService(gmap.map);


					gmap.service.nearbySearch({
					  location:new google.maps.LatLng(gmap.lat, gmap.lng),
					  radius: gmap.radius_boundairy,
					  type: marker_type[0]
					}, gmap.callBack);


				
				}


				gmap.el_map_enabled.fadeIn('fast', function(){
					if(gmap.isFiltered) gmap.sendFilter(true);
				});
				
				
			},









			isInCirle: function(point, circle) {

				if(gmap.isBroker()) {
					return true;
				} else {
				    var radius = circle.getRadius();
				    var center = circle.getCenter();
				    return (google.maps.geometry.spherical.computeDistanceBetween(point, center) <= radius)
				}

			},









			callBack: function(results, status) {

				if ( status === google.maps.places.PlacesServiceStatus.OK ) {
					
					var i = 0;
					var addr = gmap.address;
					var address = addr.toString().split('::');
				    var latitude = 0;
				    var longitude = 0;
				    var mark = '', i = 0;
				    var a, b,type;
				    var addrlatlong = [];
				    var data = [];
				    var loaded = false;
				    


				    $.each(address, function(index, add){
				    	
				    	a = add;
				        b = [];
				        b = a.split("|");

				        var addr = b[0];
				        var type = b[1];
				        var lsid = b[2];
				        var price = b[3];
				        var protyp = b[4];
				        var photo = b[5];
				        var area = b[8];


				        data['lat'] = b[6];
				        data['lng'] = b[7];
				        data['address'] = addr;
				        data['type'] = type;
				        data['lsid'] = lsid;
				        data['price'] = price;
				        data['proptype'] = protyp;
				        data['photo'] = photo;
				        data['area'] = area;

				        if( gmap.isInCirle(new google.maps.LatLng(parseFloat(b[6]), parseFloat(b[7])), gmap.main_circle) ) {

				        	gmap.markers.push( gmap.createMarker( data, i ) );
					        gmap.locator.push( lsid );

					        try {	
					        	gmap.filtered.push({
					        		[lsid] : { 
					        			price : price,
					        			addr : addr,
					        			img : photo,
					        			type : protyp
					        		}
					        	});
					        } catch(err) {
					        	gmap.console(err.message);
					        }


					        i++;
					       
					    }

					    if((address.length - 1) == index) loaded = true;

				    });


				    if( loaded ) {

				    	gmap.mapFilter( gmap.filtered );
				    	gmap.radiusOptions();
				    	
				    }


				}
			},









			activeInfowindow: function( info, marker ) {

				gmap.infowindow.close();
			    gmap.infowindow.setContent(info);
			    gmap.infowindow.open(gmap.map, marker);

			    //customize infowindow
				gmap.customizeInfoWindow();

			},









			markerHandler: function( data, marker, i, action ) {


				var content = '<div class="info" data-marker="'+i+'">'
					            +'<div id="focus" style="width:340px;height:340px;background-image: url('+data['photo']+')"></div>'
					            +'<h2 class="info-head"> $' + gmap.format( parseFloat(data['price']) ) + '</h2>'
					            +'<p>' + data['address'] + '<p><p class="type">' +data['proptype']+'<br />'
					            +'<div class="details">'
					            +'<h3>Neighbors <span>'+gmap.radius+gmap.radius_type+' radius</span></h3>'
					            +'<div class="dgroup schools" data-role="school:'+i+'"><span class="title">0</span><span>Schools</span></div>'
					            +'<div class="dgroup restaurant" data-role="restaurant:'+i+'"><span class="title">0</span><span>Restaurants</span></div>'
					            +'<div class="dgroup store" data-role="store:'+i+'"><span class="title">0</span><span>Stores</span></div>'
					            +'<div>'
						            +'<a class="button blue" href="/'+gmap.listing_details+'/'+data['area']+'/'+data['lsid']+'/">View Info</a>'
						            +'<a href="#" class="button default back">Back</a>'
						        +'</div>'
					            +'</div></div>';


				var info =  '<div class="info-tip">' +
				            '<h2 class="info-head"> $' + gmap.format( parseFloat(data['price']) ) + '</h2>' +
				            '<p style="margin:0;padding:0">'
				            +data['address']+'</p></div>';



				var html = '', lsid = data['lsid'];	




				if( action == 'click'  ) html = content;
				else html = info;


				gmap.data_info[i] = [content, info];


				google.maps.event.addListener(marker, action, (function(marker, i) {           

			            return function() {

			            	if( action == 'click' ) {
			            		gmap.mapViewOptions([i, lsid]);
			            		gmap.showListingInfo( html, this );
			            	} else {
			            		gmap.activeInfowindow( html, this );
			            	}

			            	//customize infowindow
							gmap.customizeInfoWindow();
			             
			            }

			    })(marker, i));



			},






			showMapInfo: function( show ) {

				gmap.el_map_info.removeClass('show hide');

				if(show) {

					gmap.el_map_info
					.addClass('hide')
					.animate({'marginLeft' : '-='+gmap.el_map_info.width()+'px'}, function(){ gmap.el_toggled.show(); });		

				} else {

					gmap.el_toggled.hide();
					gmap.el_map_info
					.addClass('show')
					.animate({'marginLeft' : '+='+gmap.el_map_info.width()+'px'}, function(){ gmap.el_toggled.hide(); });
							
				}

			},









			toggleMap: function(e) {

				if(typeof e != 'undefined') e.preventDefault();
				gmap.showMapInfo(gmap.el_map_info.hasClass('show'));

			},









			showListingInfo: function( content, marker ) {

				//clear marker first
				gmap.clearMainMarkers();
				marker.setMap(gmap.map);

				gmap.el_info_data.children('.info').remove();
				gmap.el_info_data.append( content ).fadeIn('fast', gmap.element);
				gmap.el_toggle_map.fadeIn();


				//create border
				if( gmap.near_circle.length > 0 ) gmap.near_circle[gmap.near_circle.length-1].setMap(null);

				gmap.near_circle.push(new google.maps.Circle({
					map: gmap.map,
					radius: gmap.radius_boundairy,
					fillColor: '#AA0000',
					fillOpacity: 0.2,
					strokeColor: null,
					strokeOpacity: 1,
					strokeWeight: 0  
				}));

				gmap.near_circle[gmap.near_circle.length-1].bindTo('center', marker, 'position');

				gmap.map.setCenter( marker.getPosition() );
			    gmap.map.setZoom( (gmap.doc <= 620)? 14 : 15 );

			    //show nearby
			    gmap.showNearby( marker, null );

			    //remove isolation
			    gmap.isolateForm(0);

			},









			clearNearMarkers: function( filter ) {
				

				if( gmap.nearbyMarkers.length > 0 ) {
					for (var i = 0; i < gmap.nearbyMarkers.length; i++) {
						gmap.nearbyMarkers[i].setMap(null);
					}
				}
				
				if( gmap.isFilter == true ) {
					switch( filter ) {
					
						case 'school':	gmap.el_school.text(0); break;
						case 'restaurant': gmap.el_restaurant.text(0); break;
						case 'store': gmap.el_store.text(0); break;
						case 'all':
							gmap.el_school.text(0);
							gmap.el_restaurant.text(0);
							gmap.el_store.text(0);
							break;

					}
				}
				

			},









			updateTotalNearby: function( index ) {

				switch(index) {
					case 1: 
							gmap.el_school.text( Number(gmap.el_school.text()) + 1 );
						break;
					case 2: 
							gmap.el_restaurant.text( Number(gmap.el_restaurant.text()) + 1 );
						break;
					case 3: 
							gmap.el_store.text( Number(gmap.el_store.text()) + 1 );
						break;
				}
				
			},
			filterNearby: function() {
				
				gmap.clearDrawedLine();

				var data = $(this).attr('data-role').split(':');
				gmap.showNearby( gmap.markers[parseInt(data[1])], String(data[0]) );

			},
			showNearby: function( marker, index ) {

				gmap.current_marker = marker;

				var types = ['school','restaurant','store'];

				if( index != null ) {
					gmap.isFilter = index;
					gmap.clearNearMarkers( index );
				} else {
					gmap.isFilter = true;
					gmap.clearNearMarkers( 'all' );
				}


				for (var i = 0; i < types.length; i++) {
				
					gmap.service.nearbySearch({
					  location:new google.maps.LatLng(gmap.current_marker.getPosition().lat(),gmap.current_marker.getPosition().lng()),
					  radius: gmap.radius_boundairy,
					  type:types[i]
					}, gmap.nearbyMarker);

				}

			},
			nearbyMarker: function(results, status) {
					 
				if (status === google.maps.places.PlacesServiceStatus.OK) {
					for (var i = 0; i < results.length; i++) {

						var icon='';

						for (var a = 0; a < results[i]['types'].length; a++) {

							if(results[i]['types'][a]=='school' && (gmap.isFilter == true || gmap.isFilter == 'school')){

								if( gmap.isFilter == true ) gmap.updateTotalNearby(1);
								icon = gmap.plugin_url + '/images/neighbor/school.png';
							
							}else if(results[i]['types'][a]=='restaurant' && (gmap.isFilter == true || gmap.isFilter == 'restaurant')){
								
								if( gmap.isFilter == true ) gmap.updateTotalNearby(2);
								icon = gmap.plugin_url + '/images/neighbor/restaurant.png';
							
							}else if(results[i]['types'][a]=='store' && (gmap.isFilter == true || gmap.isFilter == 'store')){
								
								if( gmap.isFilter == true ) gmap.updateTotalNearby(3);
								icon = gmap.plugin_url + '/images/neighbor/store.png';

							}

						}

						if( icon != '' ) gmap.createNearbyMarker(results[i],icon);



					}
				}
			},
			nearbyInfoWindow: function( marker, content ) {

				gmap.infowindow.close();
				gmap.infowindow.setContent(content);
				gmap.infowindow.open(gmap.map, marker);

				gmap.customizeInfoWindow();

				gmap.drawLine(gmap.current_marker, marker);

			},
			createNearbyMarker: function(place,icon) {

				var placeLoc = place.geometry.location;

				var nearbyMarker = new google.maps.Marker({
					map: gmap.map,
					icon: icon,
					position: place.geometry.location
				});

				gmap.nearbyMarkers.push(nearbyMarker);
				
				var content = '<div class="info-tip map-rating">'
							  +'<div class="info-distance">'+gmap.calcDistance(gmap.current_marker.getPosition(), nearbyMarker.getPosition())+'<span>kilometer</span></div>'
							  +'<div class="info-wrap">'
								  +'<h2 class="info-head">'+place.name+'</h2>'
								  +gmap.whatRating((typeof place.rating == 'undefined')? 0: place.rating)
								  +'</div>'
							  +'</div>';

				google.maps.event.addListener(nearbyMarker, 'mouseover', function() {
					gmap.nearbyInfoWindow(this, content);	
				});

				google.maps.event.addListener(nearbyMarker, 'click', function() {
					gmap.nearbyInfoWindow(this, content);	
				});

			},









			whatRating: function( rate ) {

				var star = '', rate = parseInt(rate);
				
				for( var i = 1; i <= 5; i++ ) {
					
					if(i <= rate) {
						type = 'stara';
						cl = 'active';
					} else {
						type = 'stard';
						cl = 'disabled';
					}

					star += '<img src="'+gmap.plugin_url+'/images/'+type+'.png" class="'+cl+'" alt="" title="" />';
				}

				return '<div class="map-rating">'+star+'</div>';
			},









			createMarker: function(data, i) {

				
				marker = new google.maps.Marker({
					map: gmap.map,
					icon: {
			            url: gmap.plugin_url + '/images/' + data['type'] + ".png"
			        },
					position: new google.maps.LatLng(data['lat'], data['lng'])
				}); 


				//click
				gmap.markerHandler( data, marker, i, 'click' );

				//hover
				gmap.markerHandler( data, marker, i, 'mouseover' );


			    return marker;
				
				
			},





			mapSettings: function(e) {

				e.preventDefault();
				
				gmap.wait(true);					

				var filtered_data = $(this).serialize();


				$.ajax({
					url: gmap.plugin_url + '/gmap-map-settings.php',
					data: filtered_data,
					type: 'post',
					datatype: 'json'
				})
				.done(function( data ){
					console.log(data);
					gmap.wait(false);
					data = jQuery.parseJSON( data );					
					gmap.mapFilter( data.result );
					

				});

			},







			setPriceSlider: function() {

				 //price range
			    var max_amount = Number(gmap.el_text_max.val());
			    var min_amount = Number(gmap.el_text_min.val());


			    gmap.el_slider_range.slider({
			        range: true,
			        min: min_amount,
			        max: max_amount,
			        values: [ min_amount, max_amount ],
			        slide: function( event, ui ) {

			            var min = ui.values[0];
			            var max = ui.values[1];

			            gmap.el_min.text( "$" + gmap.format( min ) );
			            gmap.el_max.text( "$" + gmap.format( max ) );

			            gmap.el_text_min.val(min);
			            gmap.el_text_max.val(max);

						$('.price-data').attr('data-value', min + '::' + max);			           

			        },
			        start: function(event, ui){
			            if(gmap.standard) gmap.wait(true);
			        },
			        stop: function(event, ui){

			        	if(gmap.standard) {


			        		gmap.isFiltered = true;
							gmap.wait(false);
							gmap.el_filter_parent.text( $('#min').text() + ' - ' + $('#max').text() );
							gmap.el_filter_dropdown.children('span').removeClass('selected');
							gmap.el_filter_clear
								.css('display', 'inline-block')
							gmap.sendFilter(true);


			        	}

			        }
			    });


			     gmap.el_min.text( "$" + gmap.format( min_amount ) );
			     gmap.el_max.text( "$" + gmap.format( max_amount ) );

			},









			showFilter: function( e ) {

				e.preventDefault();

				//set price sldier
				gmap.setPriceSlider();

				//show filter
				gmap.el_map_filter.fadeIn('fast', function(){
					gmap.filterErrorClear();
					gmap.el_map_wrap.animate({'marginLeft' : '+=300px'});
				});

			},










			hideFilter: function( e ) {

				e.preventDefault();
				gmap.el_map_wrap.animate({'marginLeft' : '-=300px'},function(){
					gmap.filterErrorClear();
					gmap.el_map_filter.fadeOut('fast');
				});
			},







			hideFilterDropdown: function() {

				if(gmap.el_filter_option.is(':visible'))
					gmap.el_filter_option.slideUp(); 

			},
			showFilterDropdown: function( event ) {

				event.stopPropagation();
				
				gmap.el_filter_parent   = $(this);
				gmap.el_filter_dropdown = $(this).next();

				if(gmap.el_filter_dropdown.is(':visible')) {	
					gmap.el_filter_dropdown.slideUp();
				} else {
					gmap.hideFilterDropdown();
					gmap.el_filter_dropdown.slideDown();
				}

			},
			filterErrorClear: function() {
				gmap.el_filter_error.remove();
			},
			filterError: function( msg ) {

				var error = '<div class="filter-error">'
							+'<p>'+msg+'</p>'
							+'</div>';

				gmap.el_google_map.append(error);

			},










			showActiveInfo: function() {

				var active = $(this).attr('data-marker');
				
				if( $(this).hasClass('isolated') != true ) gmap.infowindow.open(gmap.map, gmap.markers[active]);

			},









			clearMainMarkers: function() {
				$.each(gmap.markers, function(index, marker){
					marker.setMap(null);
				});
			},









			showOriginalMarkers: function() {

				$.each(gmap.markers, function(index, marker){
					marker.setMap(gmap.map);
				});

				gmap.map.setZoom( gmap.zoom );

			},
			backToOriginal: function( e ) {

				e.preventDefault();

				gmap.removeMapView();
				gmap.setView(false);
				gmap.clearDrawedLine();
				gmap.clearNearMarkers(false);

				gmap.near_circle[gmap.near_circle.length-1].setMap(null);
				gmap.map.setCenter(new google.maps.LatLng(gmap.lat, gmap.lng));
				gmap.map.setZoom( gmap.zoom );
				gmap.mapFilter(gmap.filtered);

			},









			isolateForm: function( status ) {

				if( status == 1 ) $('#search-area, .toggled').addClass('isolated');
				else $('#search-area, .toggled').removeClass('isolated');

			},







			filteredInfowindowContentShow: function( location ) {

				gmap.clearDrawedLine();

				var index = gmap.locator.indexOf(location);

				gmap.mapViewOptions([index, location]);
				gmap.showListingInfo( gmap.data_info[index][0], gmap.markers[index] );

			},
			filteredInfowindowContent: function(e) {

				e.preventDefault();
				gmap.filteredInfowindowContentShow($(this).attr('data-locator'));

			},
			filteredInfowindow: function() {

				var location = $(this).attr('data-locator');
				var index = gmap.locator.indexOf(location);
				
				if( gmap.data_info.length > 0 ) gmap.activeInfowindow(gmap.data_info[index][1], gmap.markers[index]);

			},










			mobile: function() {
				if(gmap.doc <= 620) {
					gmap.mapViewOptions([0,0]);
					gmap.el_map_view.slice(1).remove();
				}
			},









			mapFilterListings: function( info ) {

				gmap.el_info_data.html(
					'<div class="info filtered-wrapper isolated">'
					+info
					+'</div>'
				);
				
				gmap.el_info_data.fadeIn();
				gmap.el_toggle_map.fadeIn();

				gmap.map.setCenter(new google.maps.LatLng(gmap.lat, gmap.lng));
				
				if(gmap.isBroker()) gmap.map.setZoom(gmap.zoom);
				
				gmap.mobile();
			},
			mapFilter: function( locator ) {
				
				gmap.removeMapView();
				gmap.setView(false);
				gmap.clearDrawedLine();

				var result = '', has_content = false;

				if( locator instanceof Array && locator.length > 0 ) {

					gmap.infowindow.close();
					gmap.clearNearMarkers(false);
					
					if( gmap.near_circle.length > 0 ) gmap.near_circle[gmap.near_circle.length-1].setMap(null);

					if( locator.length == 1 ) {
				
						if( typeof gmap.locator[locator[0]] != 'undefined' ) {
							gmap.clearMainMarkers();
						} else {
							gmap.clearMainMarkers();
							gmap.showOriginalMarkers();
						}
				 
					} else {
						gmap.clearMainMarkers();
					}
					
					$.each( locator, function( prop, info ){
						$.each(info, function(ml_num, data){

							var index = Number(gmap.locator.indexOf(String(ml_num)));

							if( index >= 0 ) {

								if( gmap.isInCirle(gmap.markers[ index ].getPosition(), gmap.main_circle) ) {

									gmap.markers[ index ].setMap(gmap.map);

									result +=  '<div class="filtered-box" data-locator="'+ml_num+'">'
													+'<div class="img" style="background-image:url('+data.img+')"></div>'
													+'<div class="filtered-info">'
														+'<span class="title">$'+gmap.format(Number(data.price))+'</span>'
														+'<span class="address">'+data.addr+'</span>'
														+'<span class="type">'+data.type+'</span>'
													+'</div>'
												+'</div>';

									has_content = true;
								}
								

							}

						});
					});

					if( result != '' ) gmap.mapFilterListings(result);

				}


				if( has_content ) {
					
					gmap.isolateForm(1);
					gmap.filterErrorClear();

				} else {

					gmap.filterError('Nothing found.');
					gmap.mapFilterListings([]);
					gmap.showOriginalMarkers();
				
				}


			},
			clearFilter: function() {
				
				gmap.el_filter_clear
					.css('display', 'none');
				gmap.el_filter_field.each(function(){

					$(this).children('span').removeClass('selected');
					$(this).children('.filter-dropdown').text($(this).children('.filter-dropdown').attr('data-label'))

					var filter_options = $(this).children('.filter-options');
					filter_options.children('span').removeClass('selected');
					filter_options.children('.default').addClass('selected');

				});
				gmap.mapFilter( gmap.filtered );			

			},
			setFilter: function(selected) {

				if(gmap.el_filter_parent.hasClass('selected') === false)
					gmap.el_filter_parent.addClass('selected');

				gmap.el_filter_clear
					.css('display', 'inline-block')
					.click(gmap.clearFilter);
				gmap.el_filter_parent.text($(selected).text());

			},
			getFilter: function(child) {

				if(typeof child !== "boolean")
					$(child).addClass('selected');

				var data = {};
				$('.filter-options').each(function(){
					data[$(this).attr('data-role')] = $(this).find('.selected').attr('data-value');
				});

				return data;
			},
			sameFilter: function() {
				
				var total = 0;
				gmap.el_filter_field.each(function(){
					var filter_options = $(this).children('.filter-options');
					if(filter_options.children('.selected').attr('data-value') == 'any')
						total += 1;
				});
				return (total == 5);

			},
			sendFilter: function(el) {

				gmap.wait(true);

				$.ajax({
					url: gmap.plugin_url + '/gmap-map-filter.php',
					data: gmap.getFilter(el),
					type: 'post',
					datatype: 'json'
				})
				.done(function( data ){

					gmap.wait(false);
					data = jQuery.parseJSON( data );
					console.log(data);
					gmap.mapFilter( data.result );

				});

			},
			filterHandler: function() {


				gmap.isFiltered = true;
				gmap.filterErrorClear();
				gmap.hideFilterDropdown();
				gmap.wait(false);
				gmap.setFilter(this);
				gmap.el_filter_dropdown.children('span').removeClass('selected');
				$(this).addClass('selected');

				if(gmap.sameFilter()) {
					gmap.mapFilter( gmap.filtered );
				} else {
					gmap.sendFilter(this);							
				}
				
				
			},







			formatPlaceSearch: function( place ) {

				var add = place.replace(/,\s/g , "/").toLowerCase();
				place = add.replace(/\s+/g, '_');

				return place;

			},






			setView: function(show) {

				try {
					gmap.panorama.setVisible(show);
				} catch(err) {
					//do nothing
				}

			},
			removeMapView: function() {
				gmap.el_map_view_wrapper.remove();
			},
			viewHandler: function(e) {

				e.preventDefault();
				
				gmap.el_map_view.removeClass('active');
				$(this).addClass('active');
				
				var active = $(this).attr('data-action');
				var marker = gmap.markers[parseInt($(this).attr('data-index'))];
				var locator = $(this).attr('data-locator'); 

				if( active == 'sdbar' ) gmap.sidebar();
				else if( active == 'map' ) gmap.mapView(marker, locator);
				else if( active == 'street' ) gmap.streetView(marker, locator);
				else if( active == 'gallery' ) gmap.galleryView(locator);

			},






			sidebar: function() {
				gmap.toggleMap();
			},







			mapView: function( marker, locator ) {

				marker.setVisible(true);
				gmap.setView(false);
				gmap.filteredInfowindowContentShow( locator );

			},
			streetView: function( marker, locator ) {

				
				gmap.panorama.setPosition(marker.getPosition());
		        marker.setVisible(false);
		       
		        gmap.clearDrawedLine();
		        gmap.clearNearMarkers();
		        gmap.setView(true);

			},






			removeGallery: function(e) {

				e.preventDefault();
				gmap.el_gallery_wrapper.fadeOut('fast').remove();

			},
			activeGallery: function() {

				var el = $(this), path = el.attr('data-path');
				
				$('.gallery-photo').fadeOut('fast', function(){

					$('.gallery-carousel-photo').removeClass('active');
					el.addClass('active');

					$('.gallery-photo')
						.css('background-image', 'url('+path+')')
						.fadeIn('fast');

				});

			},
			showGallery: function( gallery ) {

				var thumbnails = '', counter = 1;

				if( gallery instanceof Array ) {


					$.each(gallery, function(index, path){
						thumbnails += '<span class="gallery-carousel-photo '+((counter==1)?'active':'')+'" style="background-image:url('+path+')" data-path="'+path+'"></span>';
						counter++;
					});

					gmap.el_google_map.append(
						'<div class="gallery-wrapper">'
							+'<a href="#" class="close-gallery">x</a>'
							+'<div class="gallery-photo" style="background-image:url('+gallery[0]+')"></div>'
							+'<div class="gallery-carousel">'+thumbnails+'</div>'
						+'</div>'
					);

					gmap.element();

					gmap.el_carousel.bxSlider({
						slideWidth: $(document).width(),
						minSlides: (gmap.doc <= 620)? 5 : 10,
						maxSlides: 20,
						slideMargin: 3,
						pager: false
					});

				}

				gmap.wait(false);


			},
			galleryView: function( locator ) {
				
				gmap.wait(true);	

				$.ajax({
					url: gmap.plugin_url + '/gmap-property-gallery.php',
					data: 'action=gmap_get_gallery&ml_num=' + locator + '&random=' + Math.random(),
					type: 'post',
					datatype: 'json'
				})
				.done(function( data ){
					
					console.log('Request: ' + locator);
					
					data = $.parseJSON( data );
					gmap.showGallery( data.result );

				});	

			},






			wait: function( show ) {

				gmap.el_wait = $('.waiting');
				if(show)
					gmap.el_google_map.append('<div class="waiting"></div>');
				else
					gmap.el_wait.remove();
			},







			redirectUserView: function( data ) {
				
				data = jQuery.parseJSON(JSON.stringify(data));
				window.location = '/place/' + data.place + '/' + data.lat + '/' + data.lng;
				
			},







			initializeMap: function( e ) {

				e.preventDefault();

				if( gmap.visible ) {

					gmap.clearDrawedLine();

				} else {

					if( gmap.el_lat.val() != '' ) {

						gmap.redirectUserView({
							place : gmap.formatPlaceSearch( gmap.el_text_place.val().trim() ),
							lat: gmap.el_lat.val(),
							lng: gmap.el_lng.val()
						});

					} else {

						var firstResult = '';

						if( gmap.el_text_place.val() != '' )
							firstResult = gmap.el_text_place.val()
						else
							firstResult = gmap.el_pac.children(".pac-item:first").text();

				        var geocoder = new google.maps.Geocoder();
				        geocoder.geocode({"address":firstResult }, function(results, status) {
				            if (status == google.maps.GeocoderStatus.OK) {
				                var lat = results[0].geometry.location.lat(),
				                    lng = results[0].geometry.location.lng(),
				                    placeName = results[0].address_components[0].long_name,
				                    latlng = new google.maps.LatLng(lat, lng);


				                    var place = firstResult;
									place = place.replace(/,\s/g , "/").toLowerCase();
									place = place.replace(/\s+/g, '_');
									window.location = '/place/' + place + '/' + lat + '/' + lng;

				                	

				            }
				        });

					}

				}

			},








			triggerSearchPlaceHandler: function() {

				if( gmap.visible ) {

					google.maps.event.trigger(places, 'place_changed');
				    return false;

				} else {

					if( gmap.visible !== 'true' ) {
				 		setTimeout(function(){
							gmap.redirectUserView({
								place : gmap.formatPlaceSearch( gmap.el_text_place.val().trim() ),
								lat: gmap.el_lat.val(),
								lng: gmap.el_lng.val()
							});
						}, 2000);
				 	}
				 	

				}

			},







			docSize 	: function() { gmap.doc = gmap.el_doc.width(); },
			isBroker	: function() { return gmap.broker; },
			setPosition : function() {
				if(gmap.filter_location == 'top') {
					if(gmap.el_body.hasClass('logged-in'))
						gmap.top_position = 98;
					else
						gmap.top_position = 60;
				} else {
					if(gmap.el_body.hasClass('logged-in'))
						gmap.top_position = 40;
					else
						gmap.top_position = 0;
				}
			},








			prepare: function(){

				gmap.element();
				gmap.docSize();
				gmap.setPosition();

				gmap.visible = (gmap.visible === 'true'); 
				gmap.broker  = (gmap.broker === 'true'); 
				gmap.zoom    = (gmap.isBroker())? 10 : 15;

				//set price sldier
				if(gmap.standard) gmap.setPriceSlider();
				
			},







			gmapDOM: function() {

				try{

					google.maps.event.addDomListener(window, 'load', function(){

						var options = {
							componentRestrictions: {country: "ca"}
						};

						var places = new google.maps.places.Autocomplete(gmap.search_field, options);

						
						google.maps.event.addListener(places, 'place_changed', function(){

							var place = places.getPlace();
						
							if (!place.geometry) {
								filterError( 'Place not exist.' );
								return;
							}

							var address = place.formatted_address;
							var latitude = place.geometry.location.lat();
							var longitude = place.geometry.location.lng();
							
							
							gmap.lat = parseFloat(latitude);
							gmap.lng = parseFloat(longitude);

							document.getElementById('lat').value=latitude;
							document.getElementById('lng').value=longitude;
							
							
							
							var components = place.adr_address;
							var componentForm = {
								street_number: 'short_name',
								route: 'long_name',
								locality: 'long_name',
								administrative_area_level_1: 'short_name',
								administrative_area_level_2: 'short_name',
								country: 'long_name',
								postal_code: 'short_name',
								location: 'short_name'
							  };
							
							for (var component in componentForm) {
							  document.getElementById(component).value = '';
							 }
							
							for (var i = 0; i < place.address_components.length; i++) {
							  var addressType = place.address_components[i].types[0];
							  if (componentForm[addressType]) {
								var val = place.address_components[i][componentForm[addressType]];
								document.getElementById(addressType).value = val;
							  }
							}

							gmap.loadMap();

						});

					});


					setTimeout(function() {
						if( gmap.visible ) {
							google.maps.event.addDomListener(window, 'load', gmap.loadMap());
						}
					}, 1000);


				} catch(err) {}
		

			},






			console: function( msg ) {
				console.log(msg);
			},






			element: function() {

				gmap.el_doc					= $(window);
				gmap.el_body				= $('body');
				gmap.el_header				= $('header');
				gmap.el_text_place 			= $('#txtPlaces');
				gmap.el_lat					= $('#lat');
				gmap.el_lng					= $('#lng');
				gmap.el_radius_data			= $('#radius-data');
				gmap.el_radius_distance		= $('#radius-distance');
				gmap.el_google_map			= $('#googleMap');
				gmap.el_map_gm				= $('.gm-style-iw');
				gmap.el_map_enabled			= $('.map-page form.map_enabled');
				gmap.el_map_info			= $('#map-info');
				gmap.el_toggle_map			= $('.toggle-map');
				gmap.el_toggled				= $('.toggled');
				gmap.el_school				= $('.schools span.title');
				gmap.el_restaurant			= $('.restaurant span.title');
				gmap.el_store				= $('.store span.title');
				gmap.el_info_data			= $('.info-data');
				gmap.el_slider_range		= $( "#slider-range" );
				gmap.el_text_max			= $('input[name=max]');
				gmap.el_text_min			= $('input[name=min]');
				gmap.el_min 				= $( "#min" );
				gmap.el_max 				= $( "#max" );
				gmap.el_map_filter			= $('#map-filter');
				gmap.el_map_wrap 			= $('.map-wrap');
				gmap.el_sdbar				= $('.map-view.sdbar');
				gmap.el_view_map			= $('.map-view.map');
				gmap.el_filter_error		= $('.filter-error');
				gmap.el_map_view_wrapper 	= $('.map-view-wrapper');
				gmap.el_map_view 			= $('.map-view');
				gmap.el_gallery_wrapper		= $('.gallery-wrapper');
				gmap.el_carousel			= $('.gallery-carousel');
				gmap.el_pac 				= $('.pac-container');
				gmap.el_wait				= $('.waiting');
				gmap.el_filter_field        = $('.filter-field');
				gmap.el_filter_option       = $('.filter-options');
				gmap.el_filter_clear        = $('.filter-clear-filters');
	

			},







			init: function() {
			
				gmap.prepare();
				gmap.radius_boundairy = (gmap.radius / gmap.distance[gmap.radius_type]) * gmap.distance['mt'];
				gmap.gmapDOM();
				

			}				

		}, options);






		return this.each(function(){

			gmap.init();

			gmap.el_text_place.on('change', gmap.triggerSearchPlaceHandler);

			$( window ).on( 'resize', gmap.docSize );

			$( window ).on('click touchstart', gmap.hideFilterDropdown);

			$( document ).on( 'submit', '#search-area', gmap.initializeMap );

			$('body').on('load click touchstart change mouseover', gmap.element);

			$('.toggle-map, .toggled').on('click touchstart', gmap.toggleMap);

			$('.filter').on('click touchstart', gmap.showFilter);

			$('.hide-filter').on('click touchstart', gmap.hideFilter);

			$('body').on('click touchstart', '.dgroup', gmap.filterNearby);

			$('body').on('mouseover', '.filtered-box', gmap.filteredInfowindow);

			$('body').on('click touchstart', '.filtered-box', gmap.filteredInfowindowContent);

			$('body').on('click touchstart', '.back', gmap.backToOriginal);

			$('body').on('change', '#radius-distance', gmap.radiusDistanceHandler);

			$('body').on('keyup', '#radius-data', gmap.radiusDistanceHandler);

			$('body').on('click touchstart', '.map-view', gmap.viewHandler);

			$('body').on('click touchstart', '.gallery-carousel-photo', gmap.activeGallery);

			$('body').on('click touchstart', '.close-gallery', gmap.removeGallery);

			$('body').on('click touchstart', '.filter-dropdown', gmap.showFilterDropdown);

			$('body').on('click touchstart', '.filter-options span', gmap.filterHandler);

			$('#filter').on('submit', gmap.mapSettings);


		});

	}

}(jQuery));