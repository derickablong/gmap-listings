console.log(document.location.href);
var PATH = '<?php echo get_stylesheet_directory_uri() ?>';
var markers = [];
var markerCluster;


function init( data ){
	
    var map = new google.maps.Map(document.getElementById('map'), {
        zoom: 4,
        center: new google.maps.LatLng(43.653226, -79.383184),
        mapTypeId: google.maps.MapTypeId.ROADMAP
    });

    var infowindow = new google.maps.InfoWindow();
    var address = new Array();
    address = data[0];

  	

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
                url: data[1] + type + ".png"
            }
        });

        markers.push(marker);

        google.maps.event.addListener(marker, 'click', (function(marker, i) {
            var url = data[2];
            var sContent =
                '<h2 class="info-head"> $' + format( parseFloat(price) ) + '</h2>' +
                '<p>' +
                '<strong>' +addr+'</strong><br />' +
                '' +protyp+'<br />' +
                '<a class="button blue" href="'+url+'/listing-info/?id='+lsid+'&class='+type+'">View Listing</a>'+ '<br /><br />' +
                '<div style="width:300px;height:250px;background-size:150%;background: url('+photo+') no-repeat center;"></div>'+'<br />' +
                '</p>';
            return function() {
                infowindow.setContent(sContent);
                infowindow.setPosition(new google.maps.LatLng(b[6], b[7]));
                infowindow.open(map, marker);

            }
        })(marker, i));
        
        
    }//end of loop
   

    var clusterStyles = [
		{   
	    url: PATH + '/listing/images/cluster.png',
	    height: 84,
	    width: 84,
	   
	    textColor: '#0055a5',
	    textSize: 16,
	   
	  }, {
	    url: PATH + '/listing/images/cluster.png',
	     height: 84,
	    width: 84,
	   
	    textColor: '#0055a5',
	    textSize: 16,
	  }, {
	    url: PATH + '/listing/images/cluster.png',
	      height: 84,
	    width: 84,
	   
	    textColor: '#0055a5',
	    textSize: 16,
	  }
	];


	markerCluster = new MarkerClusterer(map, markers, {
		styles: clusterStyles,
	    gridSize: 40,
	    maxZoom: 15,
	    minimumClusterSize: 1000
	});

}


function clearMap(){
	for(i = 0; i < markers.length; i++){
		markers[i].setMap(null);
	}
	markers = [];
	markerCluster.clearMarkers();
}

function updateTotal( total ){
	jQuery('.counter').text( total );
}

function marker_infowindow(id){
    google.maps.event.trigger(markers[id], 'click');
    return false;
}


function query( request ){
	
	var json = null; 

    jQuery.ajax({
        url: request[0],
        type: 'GET',
        data: request[2]
    })
    .always(function(xml) {

    	if( request[1] != '' ){
        	
        	jQuery(request[1]).html(xml);

    	} else {

    		clearMap();
    		//run map
    		var d = jQuery.parseJSON(xml);

            var property = [d, request[2]['directory_uri'], request[2]['network_site']];
            init( property );
            
    	}

        //complete
        jQuery('#lazy').fadeOut('fast');
    });

}

function format ( amount ){
    return amount.toFixed(2).replace(/(\d)(?=(\d{3})+\.)/g, '$1,');
}
