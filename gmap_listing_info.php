<!-- google map api -->
<script src="https://maps.googleapis.com/maps/api/js?key=<?php echo GMAP::get_map_api() ?>&libraries=places,geometry&language=en&callback=initialize" type="text/javascript"></script>
<script type="text/javascript" src="<?php echo plugin_dir_url( __FILE__ ) ?>/js/gmaps.js"></script>
<script type="text/javascript" src="<?php echo plugin_dir_url( __FILE__ ) ?>/js/GeoJSON.js"></script>
<script type="text/javascript" src="<?php echo plugin_dir_url( __FILE__ ) ?>/js/jquery.bxslider/jquery.bxslider.min.js"></script>
<link rel="stylesheet" type="text/css" href="<?php echo plugin_dir_url( __FILE__ ) ?>/js/jquery.bxslider/jquery.bxslider.css">
<!-- listing stylesheet -->
<link rel="stylesheet" type="text/css" href="<?php echo plugin_dir_url( __FILE__ ) ?>/css/style.css">

<?php
//setup
include dirname( __FILE__ ) . '/gmap_db.php';

?>
<div class="listing-intro">

<?php
//get single listing
$id = $_GET['id'];
//echo $id;
if($id!=''){

    $class = $_GET['class'];
    if($class == 'rets_property_residentialproperty'){
        $dClass = 'Residential Property';
    }elseif($class == 'rets_property_commercialproperty'){
        $dClass = 'Commercial Property';
    }elseif($class == 'rets_property_condoproperty'){
        $dClass = 'Condo Property';
    }
    $sql = "SELECT * FROM $class
        WHERE Ml_num = '$id'
        GROUP BY CONCAT(Addr,' ',Area)
            ";
    //echo $sql;        
    $results = $wpdb->get_results($sql) or die(mysql_error());

     $result = $results[0];

    // get listing with zip
    $sql_n = "SELECT * FROM (SELECT DISTINCT Ml_num, Addr,Zip, Ad_text,Area,Timestamp_sql,Lp_dol,'rets_property_residentialproperty' as prop_type
                FROM rets_property_residentialproperty WHERE Municipality = '$result->Municipality'
                UNION
                SELECT DISTINCT Ml_num, Addr,Zip, Ad_text,Area,Timestamp_sql,Lp_dol,'rets_property_commercialproperty' as prop_type
                FROM rets_property_commercialproperty WHERE Municipality = '$result->Municipality'
                UNION
                SELECT DISTINCT Ml_num, Addr,Zip, Ad_text,Area,Timestamp_sql,Lp_dol,'rets_property_condoproperty' as prop_type
                FROM rets_property_condoproperty WHERE Municipality = '$result->Municipality') tbl
                GROUP BY CONCAT(Addr,' ',Area)
                ORDER BY Timestamp_sql DESC
                ";// LIMIT 60
    
    //echo $sql_n;
    $results_n = $wpdb->get_results($sql_n) or die(mysql_error());
    $res_address = array();
    foreach ($results_n as $result_n) {
        $src = get_bloginfo('siteurl') . '/wp-content/uploads/property/' . $result_n->Ml_num . '/' .'1.jpg';
        if(filesize($imgsrc)==192 || filesize($imgsrc)==''){
            $src = plugins_url( 'gmap-listings/images' ).'/listing-photo.png';
        }
        $addr = $result_n->Addr.' '.$result_n->Area.'|'.$result_n->prop_type.'|'.$result_n->Ml_num.'|'.number_format($result_n->Lp_dol, 2).'|'.ucfirst(str_replace('pro', ' Pro', str_replace('rets_property_', '', $result_n->prop_type))).'|'.$src;
        array_push($res_address,$addr);
    }
    $js_array = json_encode($res_address);
    //echo $js_array.' array';
    ?>
    <h1>
        <?php echo $result->Addr.', '.$result->Area; ?>
        <span><?php echo $dClass;?></span>
    </h1>
    <?php
        // integer starts at 0 before counting
        $photo_count = 0;
        $dir = ABSPATH.'wp-content/uploads/property/'.$result->Ml_num.'/';
        if ($handle = opendir($dir)) {
            while (($file = readdir($handle)) !== false){
                if (!in_array($file, array('.', '..')) && !is_dir($dir.$file))
                    $photo_count++;
            }
        }
        // prints out how many were in the directory
        //echo "There were $photo_count files";

        //get the polygon coordinates
        $coor_sql = "select * from rets_property_municipality_shape_coord WHERE Municipality = '$result->Municipality'";

        $c_res = $wpdb->get_results($coor_sql);
        $c_result = $c_res[0];

        $latlongstr = $c_result->latlong_data;

    ?>

    <div class="listing-right neighbourhood-map">
    <script type="text/javascript">

        var latitude = '';
        var longitude = '';

        jQuery(document).ready(function(){
            jQuery('.bxslider').bxSlider({
                mode: 'fade'
            });

            jQuery('#slider_map.tabs a').click(function(){
                var id = jQuery(this).attr('href');
                jQuery('#slider_map.tabs a').removeClass('active');
                jQuery(this).addClass('active');
                jQuery('.tab-content').hide();
                jQuery(id).show();
                return false;
            });

        
            var address = '<?php echo $result->Addr.' '.$result->Area.', '.$result->County.', '.$result->Zip; ?>';
            var city ='<?php echo $result->Municipality;?>';
            var state ='<?php echo $result->County;?>';

            GMaps.geocode({
                address: address,
                callback: function (results, status) {
                    if (status == 'OK') {
                        var latlng = results[0].geometry.location;
                        latitude = latlng.lat();
                        longitude = latlng.lng();


                        //console.log("lat:"+latitude+" long:"+longitude+"");
                        jQuery.ajax({
                            url: "<?php echo plugin_dir_url( __FILE__ ) ?>"+'/gmap_get_workscores.php',
                            type: 'GET',
                            data: {addr:address,lat:latitude,lon:longitude, api: "<?php echo get_option('work_score_api') ?>"},
                            success: function(data) {
                                //called when successful
                                //console.log('Json: '+data);
                                var score = JSON.parse(data || '{}');
                                //alert(score.walkscore);
                                jQuery('#walkscore span').html(score.walkscore);

                            }
                        });

                        jQuery.ajax({
                            url: "<?php echo plugin_dir_url( __FILE__ ) ?>"+'/gmap_get_transitscores.php',
                            type: 'GET',
                            data: {address:address,lat:latitude,lon:longitude,city:city,state:state,api: "<?php echo get_option('work_score_api') ?>"},
                            success: function(data) {
                                //called when successful
                                //console.log('Json: '+data);
                                var score = JSON.parse(data || '{}');
                               // alert(score.transit_score);
                                jQuery('#transitscore span').html(score.transit_score);
                                jQuery('.walk-score h3 span').html(score.summary);
                            }
                        });

                    }
                }
            });

            jQuery('.tabs a:nth-child(2)').click(function(){
                //alert(latitude+", "+longitude);
                panorama = GMaps.createPanorama({
                    el: '#streetview',
                    lat: latitude,
                    lng: longitude
                });
            });

            codeAddress(address,0,function(lat,long){
                map = new google.maps.Map(document.getElementById('streetview'), {
                    zoom: 10,
                    center: new google.maps.LatLng(lat, long),
                    mapTypeId: google.maps.MapTypeId.ROADMAP,
                    scrollwheel: false
                });

                

                var latlongstr = '<?php echo $latlongstr;?>';
                var latlongarray = latlongstr.split("|");

                //console.log(latlongstr);

                var destination = new google.maps.MVCArray();

                for (i = 0; i < latlongarray.length; i++) {
                    var latlongnum = latlongarray[i].split(", ");
                    destination.push(new google.maps.LatLng(Number(latlongnum[0]), Number(latlongnum[1])));
                }


                var polygonOptions = {path: destination, strokeColor: "#578e8e", fillColor:"#91b7b2" };
                var polygon = new google.maps.Polygon(polygonOptions);
                polygon.setMap(map);

                more_listing();
            });



            jQuery('#map-tabs .tabs a').click(function(){
                var type = jQuery(this).attr('href');
                if(type=='#neighborhood'){
                    more_listing();
                    jQuery('#map-tabs .tabs a').removeClass('active');
                    jQuery(this).addClass('active');
                }else {
                    jQuery('#map-tabs .tabs a').removeClass('active');
                    jQuery(this).addClass('active');
                    var filename = jQuery(this).attr('accesskey');
                    var resulta = type.replace("#", "");
                    var icon = "<?php echo get_stylesheet_directory_uri(); ?>" + "/images/" + filename + ".png";
                    initialize(resulta, icon);
                }
                return false;
            });
        });
        
        var pyrmont;
        var map;
        var request;
        var service;
        var place;
        var marker;
        var markers = new Array();
        var mapa

        var infowindow = new google.maps.InfoWindow();

        //infoWindow = new google.maps.InfoWindow();
        function initialize($type,icon,lat,long) {
            //alert(lat+", "+long);
            pyrmont = new google.maps.LatLng(lat, long);

            mapa = new google.maps.Map(document.getElementById('streetview'), {
                zoom: 12,
                center: new google.maps.LatLng(lat, long),
                mapTypeId: google.maps.MapTypeId.ROADMAP,
                scrollwheel: false
            });

            // Specify location, radius and place types for your Places API search.
            request = {
                location: pyrmont,
                radius: '1000',
                types: [$type] //grocery_or_supermarket
            };

            // Create the PlaceService and send the request.
            // Handle the callback with an anonymous function.
            infoWindow = new google.maps.InfoWindow();
            service = new google.maps.places.PlacesService(mapa);

            service.nearbySearch(request, function(results, status) {
                if (status == google.maps.places.PlacesServiceStatus.OK) {
                    for (var i = 0; i < results.length; i++) {
                        place = results[i];
                        addMarker(place,icon);
                    }
                }
            });
        }

        function addMarker(place,icon) {
            var marker = new google.maps.Marker({
                map: mapa,
                position: place.geometry.location,
                icon: {
                    url: icon
                }
            });
            markers.push(marker);

            google.maps.event.addListener(marker, 'click', function() {
                service.getDetails(place, function(result, status) {
                    if (status !== google.maps.places.PlacesServiceStatus.OK) {
                        console.error(status);
                        return;
                    }
                    //infoWindow.setContent(result.name);
                    var temp='';
                    var rating = (!result.rating)?'none':result.rating;
                    var phone = (!result.formatted_phone_number)?'none':result.formatted_phone_number;
                    var website = (!result.website) ? 'none' : result.website;

                    var sContent =
                        '<h2 class="info-head">' + result.name + '</h2>' +
                        '<p>' +
                        'Address:'+ ' <strong>' +result.formatted_address+'</strong><br />' +
                        'Rating:'+ ' ' +rating+'<br />' +
                        'Phone:'+ ' ' +phone+'<br />' +
                        'Website:'+ ' ' +website+'<br />' +
                        '<img style=width:71px; src="'+ result.icon +'">'+'<br />' +
                        '</p>';

                    infoWindow.setContent(sContent);
                    infoWindow.open(mapa, marker);
                });
            });
        }

        function more_listing(){
            var address = new Array();
            address = <?php echo $js_array;?>;


            for(var i = 0; i < markers.length; i++) {
                    markers[i].setMap(null);
            }
            var latitude = 0;
            var longitude = 0;
            var marker = '', i = 0;
            var a, b,type;
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
                addMarkerN(addr,type,i,lsid,price,protyp,photo,i * 200);
            }
            //console.log(i);
        }
        function addMarkerN(addr,type,i,lsid,price,protyp,photo,timeOut){
            codeAddress(addr,timeOut,function (lati, longi){
                marker = new google.maps.Marker({
                    position: new google.maps.LatLng(lati, longi),
                    map: map,
                    icon: {
                        url: "<?php echo get_stylesheet_directory_uri(); ?>" + "/images/" + type + ".png"
                    }
                    /*,
                    animation: google.maps.Animation.DROP*/
                });

                google.maps.event.addListener(marker, 'click', (function(marker, i) {
                    var url = '<?php echo network_site_url('/');?>';
                    var sContent =
                        '<h2 class="info-head"> $' + price + '</h2>' +
                        '<p>' +
                        '<strong>' +addr+'</strong><br />' +
                        '' +protyp+'<br />' +
                        '<a class="button blue" href="'+url+'/listing-info/?id='+lsid+'&class='+type+'">View Listing</a>'+ '<br /><br />' +
                        '<div class="one-third first" style="background:transparent url('+photo+') no-repeat center !important; width:250px; height: 175px; background-size: 150% !important;">'+'<br />' +
                        '</p>';
                    return function() {
                        infowindow.setContent(sContent);
                        infowindow.open(map, marker);
                    }
                })(marker, i));
            });
        }

        function codeAddress(addr,timeOut,callback) {
            window.setTimeout(function() {
                GMaps.geocode({
                    address: addr,
                    callback: function (results, status) {
                        if (status == 'OK') {
                            var latlng = results[0].geometry.location;
                            var lat = latlng.lat();
                            var long = latlng.lng();
                            callback.call(null, lat, long);
                        }
                    }
                });
            },timeOut)
        }

    </script>

        <div id="slider_map" class="tabs clearfix">
            <a href="#gallery" class="active">Gallery</a>
            <a href="#streetview">Street View</a>
        </div>
        <div id="streetview" class="map tab-content">

        </div>
        <div id="gallery" class="tab-content show">
            <ul class="bxslider">
                <?php
                if($photo_count>0) {
                    for ($i = 1; $i <= $photo_count; $i++) {
                        $src = get_bloginfo('siteurl') . '/wp-content/uploads/property/' . $result->Ml_num . '/' . $i . '.jpg';
                        ?>
                        <li><img src="<?php echo $src;?>"></li>
                    <?php
                    }
                }else{
                    ?>
                    <li> <img src="<?php echo plugins_url( 'gmap-listings/images' ) ?>/listing-photo.png"></li>
                    <?php
                }
                ?>

            </ul>
        </div>
        <h2>Description</h2>
        <p><?php echo $result->Ad_text; ?></p>
    </div>
    <div class="listing-left">
        <h2><?php echo '$'.number_format($result->Lp_dol);?></h2>
        <div class="rooms">
            <span class="bedroom"><?php
                if(!empty($result->Br)){
                    echo $result->Br.' Bedrooms';
                }else{
                    echo 'n/a';
                }
                 ?></span>
            <span class="bathroom"><?php
                if(!empty($result->Bath_tot)) {
                    echo $result->Bath_tot . ' Bathrooms';
                }else{
                    echo 'n/a';
                }?> </span>
        </div>
        <span class="onmarket"><?php
            date_default_timezone_set('Canada/Central');

            $date1 = new DateTime($result->Timestamp_sql);
            $date2 = new DateTime(date('Y-m-d', time()));
            $diff = $date2->diff($date1);

            echo ($result->Days_open!='') ? $result->Days_open.' Days on Market': $diff->days.' Days on Market';?></span>
        <div class="walk-score">
            <h3>Walk Score
                <span>Near Finch/Victoria Park </span>
            </h3>
            <div class="scores">
                <div id="walkscore" class="walk">
                    Walk Score
                    <span>n/a</span>
                </div>
                <div id="transitscore" class="transit">
                    Transit Score
                    <span>n/a</span>
                </div>
            </div>

        </div>

       

    </div>

    <div class="first"></div>
    </div>




    <div class="listing-information room-extras">
        <h2>Listing Information</h2>
        <div class="item-row clearfix">
            <div class="one-half first"><strong>Property Type:</strong></div>
            <div class="one-half"><?php echo ((!empty($result->Br))?$result->Type_own1_out:'n/a'); ?></div>
        </div>
        <div class="item-row clearfix">
            <div class="one-half first"><strong>Property Style:</strong></div>
            <div class="one-half"><?php echo ((!empty($result->Style))?$result->Style:'n/a'); ?></div>
        </div>
        <div class="item-row clearfix">
            <div class="one-half first"><strong>Condo/HOA Fee:</strong></div>
            <div class="one-half"><?php echo '$'.number_format($result->Maint,2);?></div>
        </div>
        <div class="item-row clearfix">
            <div class="one-half first"><strong>City:</strong></div>
            <div class="one-half"><?php echo ((!empty($result->Municipality))?$result->Municipality:'n/a');?></div>
        </div>
        <div class="item-row clearfix">
            <div class="one-half first"><strong>Status:</strong></div>
            <div class="one-half"><?php echo ($result->Status=='A') ? 'Active': 'Inactive'?></div>
        </div>
        <div class="item-row clearfix">
            <div class="one-half first"><strong>MLS® Number:</strong></div>
            <div class="one-half"><?php echo $result->Ml_num?></div>
        </div>
        <div class="item-row clearfix">
            <div class="one-half first"><strong>Bedrooms:</strong></div>
            <div class="one-half"><?php echo $result->Br;?></div>
        </div>
        <div class="item-row clearfix">
            <div class="one-half first"><strong>Full Bathrooms:</strong></div>
            <div class="one-half"><?php echo $result->Bath_tot;?></div>
        </div>
        <div class="item-row clearfix">
            <div class="one-half first"><strong>Neighbourhood:</strong></div>
            <div class="one-half"><?php echo $result->Community;?></div>
        </div>
        <div class="item-row clearfix">
            <div class="one-half first"><strong>Area:</strong></div>
            <div class="one-half"><?php echo $result->Area;?></div>
        </div>
        <div class="item-row clearfix">
            <div class="one-half first"><strong>Province:</strong></div>
            <div class="one-half"><?php echo $result->County;?></div>
        </div>
        <div class="item-row clearfix">
            <div class="one-half first"><strong>Listing Date:</strong></div>
            <div class="one-half"><?php echo $result->Timestamp_sql;?></div>
        </div>
        <div class="item-row clearfix">
            <div class="one-half first"><strong>Listing Price:</strong></div>
            <div class="one-half"><?php echo '$'.number_format($result->Lp_dol,2);?></div>
        </div>
        <div class="item-row clearfix">
            <div class="one-half first"><strong>Taxes:</strong></div>
            <div class="one-half"><?php echo ($result->Taxes>0) ? '$'.$result->Taxes : '$0';?></div>
        </div>
        <div class="item-row clearfix">
            <div class="one-half first"><strong>Condo/HOA Fee Includes:</strong></div>
            <div class="one-half">
                <?php
                if($result->Comel_inc=='Y') echo 'Common Elements, ';
                if($result->Cond_txinc=='Y') echo 'Condo Taxes, ';
                if($result->Heat_inc=='Y') echo 'Heating, ';
                if($result->Hydro_inc=='Y') echo 'Hydro, ';
                if($result->Insur_bldg=='Y') echo 'Building Insurance, ';
                if($result->Prkg_inc=='Y') echo 'Parking, ';
                if($result->Water_inc=='Y') echo 'Water, ';
                if($result->Cable=='Y') echo 'Cable TV, ';
                if($result->Cac_inc=='Y') echo 'CAC, ';
                ?>
            </div>
        </div>
        <div class="item-row clearfix">
            <div class="one-half first"><strong>Apartment Number:</strong></div>
            <div class="one-half"><?php echo ((!empty($result->Apt_num))?$result->Apt_num:'n/a');//condo?></div>
        </div>

        <div class="first"></div>
    </div>
    <div class="listing-general room-extras clearfix">
        <h2>General Information</h2>
        <div class="one-half first clearfix">
            <div class="item-row clearfix">
                <div class="one-half first">
                    <strong>Size:</strong>
                </div>
                <div class="one-half"><?php echo ((!empty($result->Sqft))?$result->Sqft:'n/a');?></div>
            </div>
            <div class="item-row clearfix">
                <div class="one-half first">
                    <strong>Tax Year:</strong>
                </div>
                <div class="one-half"><?php echo ((!empty($result->Yr))?$result->Yr:'n/a');?></div>
            </div>
        </div>
        <div class="one-half clearfix">
            <div class="item-row clearfix">
                <div class="one-half first">
                    <strong>Exposure:</strong>
                </div>
                <div class="one-half"><?php echo ((!empty($result->Condo_exp))?$result->Condo_exp:'n/a');//condo?></div>
            </div>
        </div>
    </div>

    <div class="listing-additional room-extras clearfix">
        <h2>Additional Information</h2>
        <div class="one-half first clearfix">
            <div class="item-row clearfix">
                <div class="one-half first"><strong>Heating Type:</strong></div>
                <div class="one-half"><?php echo ((!empty($result->Heating))?$result->Heating:'n/a');?></div>
            </div>
            <div class="item-row clearfix">
                <?php
                $spc_str = '';
                for($i=1;$i<=6;$i++) {
                    $room_query = "select Spec_des".$i."_out as room from $class where Ml_num='$id' and Spec_des".$i. "_out!=''";
                    $rm_results = $wpdb->get_results($room_query);
                    if(count($rm_results)>0) {
                        if ($i != 1) {
                            $spc_str .= ', ';
                        }
                        $res = $rm_results[0];
                        $spc_str .= $res->room;
                    }
                }?>
                <div class="one-half first"><strong>Special Designations:</strong></div>
                <div class="one-half"><?php echo ((!empty($spc_str))?$spc_str:'n/a');?></div>
            </div>
            <div class="item-row clearfix">
                <div class="one-half first"><strong>Locker:</strong></div>
                <div class="one-half"><?php echo ((!empty($result->Locker))?$result->Locker:'n/a');?></div>
            </div>
            <div class="item-row clearfix">
                <div class="one-half first"><strong>Extras:</strong></div>
                <div class="one-half"><?php echo ((!empty($result->Extras))?$result->Extras:'n/a');?></div>
            </div>
            <div class="item-row clearfix">
                <div class="one-half first"><strong>Fuel:</strong></div>
                <div class="one-half"><?php echo ((!empty($result->Fuel))?$result->Fuel:'n/a');?></div>
            </div>
            <div class="item-row clearfix">
                <div class="one-half first"><strong>Garage Type:</strong></div>
                <div class="one-half"><?php echo ((!empty($result->Gar_type))?$result->Gar_type:'n/a');?></div>
            </div>
            <?php if($class == 'rets_property_condoproperty'){?>
                <div class="item-row clearfix">
                    <div class="one-half first"><strong>Seller Info Statement:</strong></div>
                    <div class="one-half">N</div>
                </div>
            <?php }?>
            <div class="item-row clearfix">
                <?php
                $amen_str = '';
                for($i=1;$i<=6;$i++) {
                    $room_query = "select Bldg_amen".$i."_out as room from $class where Ml_num='$id' and Bldg_amen".$i. "_out!=''";
                    $rm_results = $wpdb->get_results($room_query);
                    if(count($rm_results)>0) {
                        if ($i != 1) {
                            $amen_str .= ', ';
                        }
                        $res = $rm_results[0];
                        $amen_str .= $res->room;
                    }
                }?>
                <div class="one-half first"><strong>Billing Amenities:</strong></div>
                <div class="one-half"><?php echo ((!empty($amen_str))?$amen_str:'n/a');?></div>
            </div>
            <div class="item-row clearfix">
                <div class="one-half first"><strong>Parking Spaces:</strong></div>
                <div class="one-half"><?php echo ($result->Park_spcs>0) ? $result->Park_spcs.' ' :'0 '?></div>
            </div>
            <div class="item-row clearfix">
                <?php
                $rm_num = 0;
                for($i=1;$i<=12;$i++) {
                    $room_query = "select Rm".$i."_out as room from $class where Ml_num='$id' and Rm".$i. "_out!=''";
                    $rm_results = $wpdb->get_results($room_query);
                    if(count($rm_results)>0){
                        $rm_num++;
                    }
                }
                ?>
                <div class="one-half first"><strong>Total Rooms:</strong></div>
                <div class="one-half"><?php echo ((!empty($rm_num))?$rm_num:'n/a');?></div>
            </div>
        </div>
        <div class="one-half clearfix">
            <div class="item-row clearfix">
                <?php

                $feat_str = '';
                for($i=1;$i<=6;$i++) {
                    $room_query = "select Prop_feat".$i."_out as room from $class where Ml_num='$id' and Prop_feat".$i. "_out!=''";
                    $rm_results = $wpdb->get_results($room_query);
                    if(count($rm_results)>0) {
                        if ($i != 1) {
                            $feat_str .= ', ';
                        }
                        $res = $rm_results[0];
                        $feat_str .= $res->room;
                    }
                }
                ?>
                <div class="one-half first"><strong>Property Feature:</strong></div>
                <div class="one-half"><?php echo ((!empty($feat_str))?$feat_str:'n/a');?></div>
            </div>
            <div class="item-row clearfix">
                <?php
                $ext_str = '';
                for($i=1;$i<=2;$i++) {
                    $room_query = "select Constr".$i."_out as room from $class where Ml_num='$id' and Constr".$i. "_out!=''";
                    $rm_results = $wpdb->get_results($room_query);
                    if(count($rm_results)>0) {
                        if ($i != 1) {
                            $ext_str .= ', ';
                        }
                        $res = $rm_results[0];
                        $ext_str .= $res->room;
                    }
                }
                ?>
                <div class="one-half first"><strong>Exterior:</strong></div>
                <div class="one-half"><?php echo ((!empty($ext_str))?$ext_str:'n/a');?></div>
            </div>
            <div class="item-row clearfix">
                <div class="one-half first"><strong>Condo Corporation Code:</strong></div>
                <div class="one-half"><?php echo ((!empty($result->Corp_num))?$result->Corp_num:'n/a');?></div>
            </div>
            <div class="item-row clearfix">
                <div class="one-half first"><strong>Fireplace/Stove:</strong></div>
                <div class="one-half"><?php echo ((!empty($result->Fpl_num))?$result->Fpl_num:'n/a');?></div>
            </div>
            <div class="item-row clearfix">
                <div class="one-half first"><strong>Parking Spaces:</strong></div>
                <div class="one-half"><?php echo $result->Park_spcs;?></div>
            </div>
            <div class="item-row clearfix">
                <div class="one-half first"><strong>Stories:</strong></div>
                <div class="one-half"><?php echo ((!empty($result->Stories))?$result->Stories:'n/a');?></div>
            </div>
            <div class="item-row clearfix">
                <div class="one-half first"><strong>Parking Type:</strong></div>
                <div class="one-half"><?php echo ((!empty($result->Park_desig))?$result->Park_desig:'n/a');?></div>
            </div>
            <div class="item-row clearfix">
                <div class="one-half first"><strong>Parking Facilities:</strong></div>
                <div class="one-half"><?php echo ((!empty($result->Park_fac))?$result->Park_fac:'n/a');?></div>
            </div>
            <div class="item-row clearfix">
                <div class="one-half first"><strong>Patio Type:</strong></div>
                <div class="one-half"><?php echo ((!empty($result->Patio_ter))?$result->Patio_ter:'n/a');?></div>
            </div>
        </div>
    </div>

    <div class="listing-general room-extras clearfix">
        <h2>Other Information</h2>
        <div class="one-half first clearfix">
            <div class="item-row clearfix">
                <div class="one-half first"><strong>Air Coditioning:</strong></div>
                <div class="one-half"><?php echo ((!empty($result->A_c))?$result->A_c:'n/a');?></div>
            </div>
            <div class="item-row clearfix">
                <div class="one-half first"><strong>Pet Permitted:</strong></div>
                <div class="one-half"><?php echo ((!empty($result->Pets))?$result->Pets:'n/a');?></div>
            </div>
        </div>
        <div class="one-half clearfix">
            <div class="item-row clearfix">
                <div class="one-half first"><strong>Basement Description:</strong></div>
                <div class="one-half"><?php echo ((!empty($result->Bsmt1_out))?$result->Bsmt1_out:'n/a'); echo ($result->Bsmt2_out!='')? ', '.$result->Bsmt2_out : '';?></div>
            </div>
            <div class="item-row clearfix">
                <div class="one-half first"><strong>Property Management Company:</strong></div>
                <div class="one-half"><?php echo ((!empty($result->Rltr))?$result->Rltr:'n/a');?></div>
            </div>
        </div>
    </div>



    <div class="listing-general room-extras clearfix">
        <div><?php echo trim( get_option('map_general_info') ) ?></div>
    </div>



</div>


    <?php
}
