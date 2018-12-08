<?php
if( !class_exists('GMAP')){ 
	class GMAP{
	    public static function create_table_sql_from_metadata($table_name, $rets_metadata, $key_field, $field_prefix = "") {
	      
	        $sql_query = "CREATE TABLE ".$table_name." (\n";
	        foreach ($rets_metadata as $field) {
	            $cleaned_comment = addslashes($field['LongName']);

	            $sql_make = "\t`" . $field_prefix . $field['SystemName']."` ";
	            if ($field['Interpretation'] == "LookupMulti") {
	                $sql_make .= "TEXT";
	            } elseif ($field['Interpretation'] == "Lookup") {
	                $sql_make .= "VARCHAR(50)";
	            } elseif ($field['DataType'] == "Int" || $field['DataType'] == "Small" || $field['DataType']== "Tiny") {
	                $sql_make .= "INT(".$field['MaximumLength'].")";
	            } elseif ($field['DataType'] == "Long") {
	                $sql_make .= "BIGINT(".$field['MaximumLength'].")";
	            } elseif ($field['DataType'] == "DateTime") {
	                $sql_make .= "DATETIME default '0000-00-00 00:00:00' NOT NULL";
	            } elseif ($field['DataType'] == "Character" && $field['MaximumLength'] <= 255) {
	                $sql_make .= "VARCHAR(".$field['MaximumLength'].")";
	            } elseif ($field['DataType'] == "Character" && $field['MaximumLength'] > 255) {
	                $sql_make .= "TEXT";
	            } elseif ($field['DataType'] == "Decimal") {
	                $pre_point = ($field['MaximumLength'] - $field['Precision']);
	                $post_point = !empty($field['Precision']) ? $field['Precision'] : 0;
	                $sql_make .= "DECIMAL({$field['MaximumLength']},{$post_point})";
	            } elseif ($field['DataType'] == "Boolean") {
	                $sql_make .= "CHAR(1)";
	            } elseif ($field['DataType'] == "Date") {
	                $sql_make .= "DATE default '0000-00-00' NOT NULL";
	            } elseif ($field['DataType'] == "Time") {
	                $sql_make .= "TIME default '00:00:00' NOT NULL";
	            } else {
	                $sql_make .= "VARCHAR(255)";
	            }
	            $sql_make .=  " COMMENT '".$cleaned_comment."',\n";
	            $sql_query .= $sql_make;
	        }
	        $sql_query .=  "PRIMARY KEY(`".$field_prefix.$key_field."`) )";
	        return $sql_query;
	    }

	    public static function hosts( $hosts = true ){
	    	if( $hosts ){
	    		$hosts = $_SERVER['DOCUMENT_ROOT'] . '/webdev/remax/';
	    	}else{
	    		$hosts = $_SERVER['DOCUMENT_ROOT'];
	    	}
	    	return $hosts;
	    }

	    public static function progress( $prop, $total, $rows, $index = 0, $space = 10 ){
	    	//color
	    	$color = array('#337ab7', '#449d44', '#ec971f');
	    	// Calculate the percentation
	    	$percent = ($total > 0)? intval($rows/$total * 100)."%" : "100%";
	    	$rows = ($rows > 0)? $rows.' row(s) processed.' : 'No new listing added.';
	    	// Javascript for updating the progress bar and information
		    echo '<script language="javascript">
		    document.getElementById("'.$prop.'_progress").innerHTML="<div style=\"width:'.$percent.';background-color:'.$color[$index].'\">&nbsp;</div>";
		    document.getElementById("'.$prop.'_information").innerHTML="'.$rows.'";
		    </script>';
			// This is for the buffer achieve the minimum size in order to flush data
		    echo str_repeat(' ', $space);
			// Send output to browser immediately
		    flush();
			// Sleep one second so we can see the delay
		    //sleep(1);
	    }

	    public static function completeAction($section) {
	    	?>
	    	<script type="text/javascript">
	    		jQuery(document).ready(function(){
	    			jQuery('.gmap_complete_action.<?php echo $section ?>').fadeIn('fast');
	    		});
	    	</script>
	    	<?php
	    }

	    public static function start_session(){
	    	if (session_id() == '') {
			    session_start();
			}
	    }

		public static function apply( $file, $atts = array()){
			include dirname( __FILE__ ) . "/$file" . ".php";
		}

		public static function custom_load( $atts = array(), $content = '' ){
			global $wpdb; $order = 'ASC'; $limit = 3; $title = ''; $keywords = '';

			$order = self::stack_value('order', $atts);
			$limit = self::stack_value('limit', $atts);
			$title = self::stack_value('title', $atts);
			$random = self::stack_value('random', $atts);
			$keywords = trim(self::stack_value('property', $atts));
			$search = trim(self::stack_value('search', $atts));

			$content .= '<h4 class="widget-title recent-title">' . $title . '</h4>';
			$content .= '<p style="text-align:center;"><a href="/place/'.strtolower(str_replace(array('/', ' ', '.'),array('_','-',''),$search)).'">See All Listings</a></p>';

			$sql = self::filter_query( $keywords, $search );

			//create random number
			if( isset( $random ) && $random != 'false' )
			$sql = str_replace( 'Timestamp_sql', 'RAND()', $sql );
			
			$sql .= " LIMIT $limit";

		
			
			$results = $wpdb->get_results( $sql ); $level = array('first', 'second', 'third'); $c = 0; $index = 0;
			foreach ($results as $result) {
				if( $index >= 3 ) $index = 0;

				$img = self::get_first_image( $result->Ml_num );


				//get area
				$area_data = explode( ' ', $result->Municipality_district );
				$area = self::gmap_is_in_area_code( $area_data[1] );


				$content .= '<div class="recent-listings one-third '. $level[$index] .'">
							<div class="recent-photo" style="background-image:url('.$img.')"></div>
							<div class="listing-info">
							<h2>' . $result->Addr . '</h2>
							<h3>'. $result->Municipality .', '. $result->County .'</h3>
							'. ( (!empty($result->Br))? $result->Br . ' Beds' : 'n/a' ) .', '. ( (!empty($result->Bath_tot))? $result->Bath_tot . ' Baths' : 'n/a' ) .'<br>
							'. ((!empty($result->Br))?$result->Type_own1_out:'n/a') .'
							<h4>$'. number_format($result->Lp_dol) .'</h4>

							<div class="listing-link">
							<a href="/listings/'. $area . '/' . $result->Ml_num .'" class="button blue">VIEW LISTING</a>
							</a>
							</div></div></div>';
				$c++; $index++;
			
			}
			wp_reset_query();

			$content .= '<div class="first"></div>';
			return $content;
		}

		public static function save_lat_long( $data ){
			global $wpdb;
			if( $data['latitude'] != '' )
			$wpdb->insert('rets_property_lat_long', $data);
		}

		public static function get_lat_long($address, $lat = '', $long = ''){
		    
		    //api keys
		    $api_keys = self::get_all_map_api_keys();

		    //address
		    $address = str_replace( array( " ", "&" ), array( "+", "+and+" ) , $address);
		    $address = trim( $address );

		    //geocode
		    foreach ($api_keys as $index => $key) {
		    	$json = self::url_get_contents("https://maps.googleapis.com/maps/api/geocode/json?address=$address&key=$key");
			    $json = json_decode($json);
			    //print_r($json);
			    if( $json->{'status'} == 'OK' ){
			    	$lat = $json->{'results'}[0]->{'geometry'}->{'location'}->{'lat'};
					$long = $json->{'results'}[0]->{'geometry'}->{'location'}->{'lng'};
			    	break;
			    } else {
			    	continue;
			    }
		    }

		    return array('lat' => $lat, 'long' => $long);
		}

		function this_lat_long($Ml_num = '', $loc = array()){
		    global $wpdb; $loc = array();
		    $s = "SELECT latitude, longitude FROM rets_property_lat_long WHERE Ml_num='$Ml_num'";
		    $rs = $wpdb->get_results($s);

		    if( count( $rs ) ) {
			    foreach ($rs as $key) {
			        $loc['latitude'] = $key->latitude;
			        $loc['longitude'] = $key->longitude;
			    }
			}
		    
		    return $loc;

		}

		public static function get_first_image( $ml_num ) {

			//display first image
	        $upload_dir = wp_upload_dir();
	        $src = get_bloginfo('siteurl') . '/wp-content/uploads/property/' . $ml_num . '/' .'1.jpg';
	        $imgsrc =  $upload_dir['basedir'] . '/' . 'property/' . $ml_num . '/' .'1.jpg';
	  

	        if(!file_exists($imgsrc)){
	            $src = plugins_url( 'gmap-listings/images' ).'/listing-photo.png';
	        } else {
	        	if(!is_array(getimagesize($imgsrc))) {
	        		$src = plugins_url( 'gmap-listings/images' ).'/listing-photo.png';
	        	}
	        }


	        return $src;

		}

		public static function filter_query( $keywords = '', $search = '' ){
			ob_start();
			$_GET['property'] = $keywords;
			$_GET['search'] = $search;
			self::apply( 'query/filterv2', '' );
			return ob_get_clean();
		}

		public static function send_header( $header ){
			echo '<script>window.location = "?' . $header . '"</script>';
		}

		public function get_header(){
			return $_SERVER["QUERY_STRING"];
		}

		public static function is_latlong_started( $status = false ){
			if( isset($_GET['status']) )
				$status = true;	
			return $status;
		}

		public static function is_generated_latlong_complete( $results ){
			global $wpdb;
			$total = $wpdb->get_var("SELECT COUNT(*) as total FROM rets_property_lat_long");
			return ($results == $total)? true : false;
		}

		public static function contains( $words, $contains ){
			if( strpos($words, $contains) )
				return true;
		}

		public static function stack_value( $key, $sttack ){
			if( array_key_exists($key, $sttack) )
				return $sttack[ $key ];
			else
				return '';	
		}

		public static function set_default_query_session(){
			self::clear_query_session();

			$_SESSION['TOTAL_RES'] = 0;
			$_SESSION['TOTAL_COM'] = 0;
			$_SESSION['TOTAL_CON'] = 0;

			$_SESSION['ML_TOTAL'] = 0;
			$_SESSION['ML_NUMBER'] = '';
			$_SESSION['RESIDENTIAL_ADDR'] = '';
			$_SESSION['COMMERCIAL_ADDR'] = '';
			$_SESSION['CONDO_ADDR'] = '';
			$_SESSION['current_progress'] = 0;
			$_SESSION['mn'] = 0;
			$_SESSION['mx'] = 100;
		}

		public static function clear_query_session(){
			self::start_session();
			unset($_SESSION['TOTAL_RES']);
			unset($_SESSION['TOTAL_COM']);
			unset($_SESSION['TOTAL_CON']);

			unset($_SESSION['ML_TOTAL']);
			unset($_SESSION['ML_NUMBER']);
			unset($_SESSION['RESIDENTIAL_ADDR']);
			unset($_SESSION['COMMERCIAL_ADDR']);
			unset($_SESSION['CONDO_ADDR']);
			unset($_SESSION['current_progress']);
			unset($_SESSION['mn']);
			unset($_SESSION['mx']);
			unset($_SESSION['residential_progress']);
			unset($_SESSION['commercial_progress']);
			unset($_SESSION['condo_progress']);
			unset($_SESSION['photos_progress']);
			unset($_SESSION['latlong_percent']);
			unset($_SESSION['latlong_progress']);
			unset($_SESSION['current_progress']);
		}

		public static function create_search_data(){
			echo '<div id="search_data" style="display:none"></div>';
		}

		public static function percircle( $atts = array(), $content = '' ){
			$id = self::stack_value('id', $atts);
			$data_percent = self::stack_value('percent', $atts);
			$class = self::stack_value('class', $atts);
			$start = self::stack_value('start', $atts);
			$title = self::stack_value('title', $atts);
			
			//include library
			if( $start ){
				$content .= '<link rel="stylesheet" href="'. plugin_dir_url( __FILE__ ) .'js/percircle/percircle.css"/>';
				$content .= '<script src="'. plugin_dir_url( __FILE__ ) .'js/percircle/jquery-2.1.4.min.js"></script>';
				$content .= '<script src="'. plugin_dir_url( __FILE__ ) .'js/percircle/percircle.js"></script>';
			}

			$text = '$("#'.$id.'").percircle({ text: "'. $data_percent . "%<i>$title</i>" .'" });';
			$content .= '<div id="'. $id .'" data-percent="'. $data_percent .'" class="'. $class .'"></div>';
			$content .= '<script>$(document).ready(function(){';
			$content .= $text;
			$content .= '});</script>';	

			return $content;
		}

		public static function record_progress( $session, $progress ){
			$_SESSION[$session] = $progress;
		}

		public static function residential_status( $xml = '' ){
			if( $_SESSION['residential_progress'] ){
				$xml = '<div id="residential_progress">
							<div style="width:100%;background-color:#337ab7">&nbsp;</div>
						</div>
						<span id="residential_information">' . $_SESSION['residential_progress'] . ' rows(s) processed.</span>';
			}else{
				$xml = '<div id="residential_progress"></div>
						<span id="residential_information"></span>';
			}
			return $xml;
		}

		public static function progress_status( $property, $percent, $label, $xml = '' ){
			if( isset($_SESSION[ $property.'_progress' ]) ){
				$xml = '<div id="'. $property .'_progress">
							<div style="width:'. $percent .'%;background-color:#'. $label .'">&nbsp;</div>
						</div>
						<span id="'. $property .'_information">' . $_SESSION[ $property.'_progress' ] . ' row(s) processed.</span>';
			}else{
				$xml = '<div id="'. $property .'_progress"></div>
						<span id="'. $property .'_information"></span>';
			}
			return $xml;
		}

		public static function static_circle( $atts = array() ){
			$bgcolor = self::stack_value('bgcolor', $atts);
			$color = self::stack_value('color', $atts);
			$width = self::stack_value('width', $atts);
			$text = self::stack_value('text', $atts);
			return '<div class="static_circle" style="background-color:'. $bgcolor .'; color:'. $color .';width:'. $width .'px; height:'. $width .'px">'. $text .'</div>';
		}

		public static function load_posts_under_category( $atts = array() ){
			ob_start();
			self::apply('extra/load_posts', $atts);
			return ob_get_clean();
		}

		public static function grab_listings_xml(){
			echo '<fieldset class="gmap_progress_holder">
					<legend>STEP 1</legend>
					<div id="gmap_progress_col">
						<span>Residential Property</span>
						'. self::progress_status('residential', 100, '337ab7') .'
					</div>
					<div id="gmap_progress_col">
						<span>Commercial Property</span>
						'. self::progress_status('commercial', 100, '449d44') .'
					</div>
					<div id="gmap_progress_col">
						<span>Condo Property</span>
						'. self::progress_status('condo', 100, 'ec971f') .'
					</div>
					<p>STEP 1: Downloading the updated property data listings for residential, commercial and condo.</p>
				 </fieldset>
				 <fieldset class="gmap_progress_holder">
					<legend>STEP 2</legend>
					<span>Property Photos</span>
					'. self::progress_status('photos', 100, '337ab7') .'
					<p>STEP 2: Downloding the corresponding photos for each property listings.<br>This step will take a little time to download all the photos.</p>
				 </fieldset>
				 <fieldset class="gmap_progress_holder">
					<legend>STEP 3</legend>
					
					<div id="gmap_progress_col">
						<span>Residential Property</span>
						'. self::progress_status('latlong_residential', 100, '337ab7') .'
					</div>
					<div id="gmap_progress_col">
						<span>Commercial Property</span>
						'. self::progress_status('latlong_commercial', 100, '449d44') .'
					</div>
					<div id="gmap_progress_col">
						<span>Condo Property</span>
						'. self::progress_status('latlong_condo', 100, 'ec971f') .'
					</div>
					<p>STEP 3: Final step, generates latitude and longitude for each property listings.<br>Better take a coffee because this final step will take time.</p>
				 </fieldset>';
		}

		public static function gmap_map_area_xml() {

			$html  = '<div class="map_image"><img src="' . get_option( 'map_image_url' ) . '" title="Map Area" alt="" usemap="#map-area" /></div>';
			$html .= '<div class="map_area">' . get_option( 'map_area' ) . '</div>';
			$html .= '<script type="text/javascript" src="' . trim(plugin_dir_url( __FILE__ )) . 'lib/maphilight/jquery.maphilight.js"></script>';
			$html .= '<script type="text/javascript">jQuery(document).ready(function(){ jQuery("img[usemap]").maphilight({ fillColor: "35597b", fillOpacity: 0.4, strokeColor:"35597b" }); });</script>';

			return $html;

		}

		public static function update_settings( $key, $data ){
			update_option( $key, $data );		
		}	

		public static function gmap_settings_handler(){

			if( !empty($_POST) ){
				
				extract( $_POST );
				$settings = get_defined_vars();

				foreach ($settings as $name => $options) {
					
					if( is_array($options) ){
						$options = implode("::", $options);
					}

					self::update_settings( $name, stripcslashes($options) );
					//save areas if defined
					self::save_listings_address( $options );

				}

				//show success message
				echo '<div class="updated" style="margin-left: 0"><p><strong>Options saved.</strong></p></div>';
				
				
			}

		}

		public static function load_apis(){
			$options = explode('::', get_option('map_api_key'));
			foreach ($options as $key => $value) {
				if( $value ) echo '<input type="text" name="map_api_key[]" class="api_keys" value="' . $value . '">';
			}
		}

		public static function get_map_api( $index = 0 ){
			$options = explode('::', get_option('map_api_key'));
			$api = array_reverse($options);
			return $api[ $index ];
		}

		public static function get_all_map_api_keys(){
			return explode('::', get_option('map_api_key'));
		}

		public static function country(){
			if( get_option('map_country') ){
				return get_option('map_country');
			}
		}

		public static function squeeze( $args = array() ){
			$content = '<div id="squeez_container" style="max-width:' . ((($args['options']['width']) > 0)? $args['options']['width'] : 0 ) . 'px">';
			$content .= do_shortcode( $args['content'] );
			$content .= '</div>';

			return $content;
		}

		public static function squeeze_role( $args ){
			if( $args[2] == 'clear' ){
				$content = '<div class="clear"></div>';
			} else {
				$content = '<div class="' . strtolower($args[2]) . '">';
				$content .= '<div class="wrap">' . do_shortcode( $args[1] ) . '</div>';
				$content .= '</div>';
			}

			return $content;
		}



		public static function show_error( $request ) {

			if( $request ) {

				return '<div class="error">No record found from this area "' . ucwords( str_replace( '-', ' ', $request ) ) . '".</div>';

			}

		}




		public static function residential( $args = '' ) {

			return "SELECT DISTINCT Addr, Ml_num,Zip, Community, Ad_text,Municipality,County,Area,Timestamp_sql,Lp_dol,Br,Bath_tot, Rltr, Cross_st, Extras, Taxes, Heating, Sqft as App_sq_ft, Constr1_out as Exterior, Style as Living_style, Gar_spaces as Garage, Water as Water_, Pool as Pool_, Type_own1_out as type_of_home, 'rets_property_residentialproperty' as prop_type 
				FROM rets_property_residentialproperty {$args}";

		}




		public static function condo( $args = '' ) {

			return "SELECT DISTINCT Addr, Ml_num,Zip, Community, Ad_text,Municipality,County,Area,Timestamp_sql,Lp_dol,Br,Bath_tot, Rltr, Cross_st, Extras, Taxes, Heating, Sqft as App_sq_ft, Constr1_out as Exterior, Style as Living_style, Gar as Garage, Null as Water_, Null as Pool_, Type_own1_out as type_of_home, 'rets_property_condoproperty' as prop_type
				FROM rets_property_condoproperty {$args}";

		}




		public static function commercial( $args = '' ) {

			return "SELECT DISTINCT Addr, Ml_num,Zip, Community, Ad_text,Municipality,County,Area,Timestamp_sql,Lp_dol,Null as Br,Bath_tot, Rltr, Cross_st, Extras, Taxes, Heating, Null as App_sq_ft, Null as Exterior, Null as Living_style, Null as Garage, Null as Water_, Null as Pool_, Prop_type as type_of_home,
				'rets_property_commercialproperty' as prop_type  
				FROM rets_property_commercialproperty {$args}";

		} 



		public static function get_filters( $args, $where = '' ) {

			switch( (string)$args[0] ){
				
				//address
				case 'location':
					if( $args[1] != '' )
					$where = " AND Addr LIKE '%{$args[1]}%'";
					break;

				//price range
				case 'price_range':
					
					$price_range = explode('::', $args[1]);
					
					if( $price_range[0] != '' ) 
						$where = " AND Lp_dol >= {$price_range[0]}";
					
					if( $price_range[1] != '' ) 
						$where = " AND Lp_dol <= {$price_range[1]}";

					break;

				//bedroom
				case 'baths':
					if( $args[1] > 0 )
					$where = " AND Bath_tot >= {$args[1]}";
					break;


				//status
				case 'status':
					if( $args[1] != '' )
					$where = " AND S_r LIKE '%{$args[1]}%'";
					break;


				//parking
				case 'parking':
					if( $args[1] > 0 )
					$where = " AND Park_spcs >= {$args[1]}";
					break;	

			}

			return $where;

		}



		public static function area_code( $area_index ) {

			$area_code = array(
				'east-toronto'		=>	array( 'E01', 'E02', 'EO3' ),
				'west-toronto'		=>	array( 'W01', 'W02', 'W03' ),
				'downtown-toronto'	=>	array( 'C01', 'C08' ),
				'midtown-toronto'	=>	array( 'C02', 'C03', 'C04', 'C09', 'C10', 'C11', 'C12' ),
				'scarborough'		=>	array( 'E04', 'E05', 'E06', 'E07', 'E08', 'E09', 'E10', 'E11'),
				'etobicoke'			=>	array( 'W06', 'W07', 'W08', 'W09', 'W10' ),
				'north-york'		=>	array( 'W04', 'W05', 'C06', 'C07', 'C13', 'C14', 'C15' )
			);

			return implode( '|', $area_code[ $area_index ] );

		}



		public static function get_all_property_class() {

			global $wpdb;

			$query = "SELECT * FROM(
						  ".self::residential()."
						  UNION
						  ".self::condo()."
						  UNION
						  ".self::commercial()."
					  ) as property";
					

			
			return $wpdb->get_results( $query );

		}



		public static function load_listings_by_area( $req, $area, $view, $filter = '', $where = '', $garage = '', $bedroom = '', $rc_type = '', $com_type = '', $sort_and_order = 'ORDER BY Timestamp_sql DESC', $ml_num = '', $regx = '' ) {

			global $wpdb;
			$filtered = false;

			$this_area = explode( '/', $area );
			$this_area = array_filter( $this_area );
			$area = $this_area[2];

			//ml number
			if( count( $this_area ) >= 3 ) {
				$ml_num = "Ml_num = '" . $this_area[3] . "'";
			} else {
				$regx = "Municipality_district REGEXP '". self::area_code( $area ) ."'";
			}

			//if filtered
			if( is_array( $filter ) ) {

				$filtered = true;

				foreach( $filter as $index => $data ) {
					$where .= self::get_filters( array( $index, $data ) );
				}
				
				
				$sort_and_order = "ORDER BY " . $filter['sort'] . ' ' . $filter['order'];


				//for special cases
				if( $filter['garage'] != '' ) {
					$garage = "AND Gar_spaces >= " . $filter['garage'];
				}

				if( $filter['bedroom'] != '' ) {
					$bedroom = " AND Br >= " . $filter['bedroom'];
				}

				if( $filter['fpclass'] != '' ) {
					$rc_type = " AND Type_own1_out = '". $filter['fpclass'] ."'";
					$com_type = " AND Prop_type = '". $filter['fpclass'] ."'";
				}


			}
			//end if filtered

			$query = "SELECT * FROM(
					  	".self::residential("WHERE {$regx} {$ml_num} {$where} {$garage} {$bedroom} {$rc_type}")."
					  	UNION
					 	".self::condo("WHERE {$regx} {$ml_num} {$where} {$bedroom} {$rc_type}")."
					 	UNION
					 	".self::commercial("WHERE {$regx} {$ml_num} {$where} {$com_type}")."
					  ) as property {$sort_and_order}";

			
			$results = $wpdb->get_results( $query );

			
			if( ! $req ) {

				if( count( $results ) ) {

					return self::get_listings_by_area( $area, $results, $view, $filtered );
				
				} else {
				
					return self::show_error( $area );
				
				}

			} else {

				if( ! is_bool( $req ) ) {

					if( $req == 'result' )
						return $results;
					else
						return $query;
				
				} else {
					
					return count( $results );
				
				}

			}

		}



		public static function get_listings_by_area( $area, $listing_results, $view, $filter, $html = '', $address = array() ) {

			

			foreach($listing_results as $listing_result){

			    $location = self::this_lat_long( $listing_result->Ml_num );
			    

			    if( count($location) && ! empty( $listing_result->Addr ) ) {

				    //grid
				    if( $view == 'grid' ) {
					   

					       
					        $html .= '<div class="fgrid">';				       	

					       	$html .= '<a href="/listings/'. $area . '/'  . $listing_result->Ml_num . '">';
					       	$html .= '<div class="ftop">';
					       	$html .= '<span>' . $listing_result->Area . '</span>';
					       	$html .= '<span>' . $listing_result->Addr . '</span>';
					       	$html .= '</div>';

					       	
					       	$html .= '<div class="fbottom">';
					        $html .= '<span class="large">$' . number_format($listing_result->Lp_dol, 2) . ' <i>' . $listing_result->Br . ' bd</i>' . ' <i>' . $listing_result->Bath_tot . ' ba</i>' . '</span>';
					        $html .= '<span class="small">Courtesy of ' . $listing_result->Rltr . '</span>';
					        $html .= '</div>';
					        $html .= '</a>';


					       	$html .= '<img src="' . self::get_first_image($listing_result->Ml_num) . '" title="" alt="" />';
					       	$html .= '</div>';
					   
					   

					        $counter++;
					    
					}//end for grid





					//list
				    if( $view == 'list' ) {


					        
					        $html .= '<div class="fitem">';
					       	
					       	
					       	$html .= '<div class="fimage">';
					       	$html .= '<img src="' . self::get_first_image($listing_result->Ml_num) . '" title="" alt="" />';
					       	$html .= '<span>Courtesy of ' . $listing_result->Rltr . '</span>';
					       	$html .= '</div>';
					       	

					       
					        $html .= '<div class="fdetails">';
					        
					        $html .= '<h3><a class="propertyAdd" href="javascript:void(0)" onclick="marker_infowindow('.$counter.')">' . $listing_result->Addr . ', ' . $listing_result->Area . '</a></h3>';

					        $html .= '<span>$' . number_format($listing_result->Lp_dol, 2) . '</span>';

					        $html .= '<span class="small">' . $listing_result->Community . '</span>';

					        $html .= '<span class="space"></span>';

					        $html .= '<span class="large">Beds: ' . $listing_result->Br . '</span>';
					       	
					       	$html .= '<span class="large">Baths: ' . $listing_result->Bath_tot . '</span>';

					       	$html .= '<span class="space"></span>';


					       	$html .= '<p>' . $listing_result->Ad_text . '</p>';

					       	$html .= '<a href="/listings/'. $area . '/' . $listing_result->Ml_num . '" class="fbutton">View Listing Details</a>';

					        
					        $html .= '</div>';
					        
					        $html .= '</div>';

					        $counter++;
					    

					}//end for list






					//map
					if( $view == 'map' ) {


							
					        
					        $addr = $listing_result->Addr.' '.$listing_result->Area.'|'.trim($listing_result->prop_type).'|'.$listing_result->Ml_num.'|'.$listing_result->Lp_dol.'|'.ucfirst(str_replace('pro', ' Pro', str_replace('rets_property_', '', $listing_result->prop_type))).'|'.self::get_first_image().'|'.$location['latitude'].'|'.$location['longitude'];
					        //push data
					        array_push( $address, addslashes( $addr ) );



					}//end of map view

			
				}//end of if lat/long and address is empty		





			}//end of listings lopp


			if( count( $address ) > 0 ) {

				$addr = implode( '::', $address );

				if( ! $filter ) {

					$html .=    '<script type="text/javascript">';
					$html .=    "load_map( '". $area ."', '" . plugin_dir_url( __FILE__ ) . "', ". get_option('map_lat') .", ". get_option('map_long') .", '". $addr ."' );</script>";
				
				} else {


					$html = array(
						'area'		=> $area,
						'path'		=> plugin_dir_url( __FILE__ ),
						'lat'		=> get_option('map_lat'),
						'long'		=> get_option('map_long'),
						'addr'		=> $addr
					);

				}

			} else {

				$html .= '<div class="load_more"></div>';
				$html .= '<a href="#top" class="ftotop">Back to Top</a>';
				
			}


			return $html;


		}



		public static function map_settings( $table, $price, $class, $status, $info = array() ) {

			global $wpdb;


			switch ( $table ) {
			
				case 'residential':
						$query = self::residential( "WHERE {$price} {$class[0]} {$status}" );
					break;


				case 'condo':
						$query = self::condo( "WHERE {$price} {$class[0]} {$status}" );
					break;


				case 'commercial':
						$query = self::commercial( "WHERE {$price} {$class[1]} {$status}" );
					break;

				default:
						$query = "SELECT * FROM(
									  ".self::residential( "WHERE {$price} {$class[0]} {$status}" )."
									  UNION
									  ".self::condo( "WHERE {$price} {$class[0]} {$status}" )."
									  UNION
									  ".self::commercial( "WHERE {$price} {$class[1]} {$status}" )."
								  ) as property";
					break;

			}	
			

			$results = $wpdb->get_results($query);

			foreach( $results as $result ) {
				array_push( $info, [
					$result->Ml_num => [
						'price' => $result->Lp_dol, 
						'addr' => $result->Addr, 
						'img' => self::get_first_image($result->Ml_num),
						'type' => ucfirst(str_replace('pro', ' Pro', str_replace('rets_property_', '', $result->prop_type)))
					]
				]);
			}


			return $info;
			

		}



		public static function map_filter( $table, $filter_data, $info = array() ) {

			global $wpdb;

			$filter = (object) $filter_data;
			$table  = ($filter->bed != "Br >= 0")? 'res_con' : $table;
			switch ( $table ) {
			
				case 'res_com':
						$query = "SELECT * FROM(
									  ".self::residential( "WHERE {$filter->price} {$filter->bed} {$filter->bath} {$filter->status} {$filter->residential}" )."
									  UNION
									  ".self::commercial( "WHERE {$filter->price} {$filter->bath} {$filter->status} {$filter->commercial}" )."
								  ) as property";
					break;

				case 'res_con':
						$query = "SELECT * FROM(
									  ".self::residential( "WHERE {$filter->price} {$filter->bed} {$filter->bath} {$filter->status} {$filter->residential}" )."
									  UNION
									  ".self::condo( "WHERE {$filter->price} {$filter->bed} {$filter->bath} {$filter->status} {$filter->condo}" )."
								  ) as property";
					break;


				case 'condo':
						$query = self::condo( "WHERE {$filter->price} {$filter->bed} {$filter->bath} {$filter->status} {$filter->condo}" );
					break;


				default:
						$query = "SELECT * FROM(
									  ".self::residential( "WHERE {$filter->price} {$filter->bed} {$filter->bath} {$filter->status} {$filter->residential}" )."
									  UNION
									  ".self::condo( "WHERE {$filter->price} {$filter->bed} {$filter->bath} {$filter->status} {$filter->condo}" )."
									  UNION
									  ".self::commercial( "WHERE {$filter->price} {$filter->bath} {$filter->status} {$filter->commercial}" )."
								  ) as property";
					

			}	
			
			
			$results = $wpdb->get_results($query);

			foreach( $results as $result ) {
				array_push( $info, [
					$result->Ml_num => [
						'price' => $result->Lp_dol, 
						'addr' => $result->Addr, 
						'img' => self::get_first_image($result->Ml_num),
						'type' => ucfirst(str_replace('pro', ' Pro', str_replace('rets_property_', '', $result->prop_type)))
					]
				]);
			}


			return $info;


		}




		public static function extractFilteredData( $data, $info = array() ) {

			extract($data);

			array_push( $info, [
				$lsid => [
					'price' => $price, 
					'addr' => $addr, 
					'img' => $img,
					'type' => $type
				]
			]);

			return $info;

		}



		public static function console( $result, $col, $return = false, $column = null ) {
		
			$column = $result->$col;

			
			if( $col == 'Lp_dol' )
				$column = number_format( $column, 2);
		

			if( empty( $column ) )
				$column = 'None';
			
			if( ! $return ) echo $column;
			else return $column;
		
		}




		public static function property_class( $results, $prop = array() ) {

			foreach ( $results as $result ) {
				array_push( $prop, $result->type_of_home );
			}

			sort( $prop );

			return array_unique( $prop );

		}





		public static function gmap_get_gallery_path( $mls, $count = 1, $images = array() ) {

			$dir = ABSPATH.'wp-content/uploads/property/' . $mls .'/';

            if ($handle = opendir($dir)) {

            	
                while (($file = readdir($handle)) != false){
                    if (!in_array($file, array('.', '..')) && !is_dir($dir.$file)) {

                    	array_push($images, get_bloginfo('siteurl') . "/wp-content/uploads/property/{$mls}/{$count}.jpg");          

                    	$count++;

                    }
                }


            } else {

            	array_push($images, 'Nothing found.');

            }

            return $images;

		}





		public static function price( $results, $price = array() ) {
			
			foreach( $results as $result ){
				array_push( $price, $result->Lp_dol );
			}

			return array(
				'min' => min($price), 
				'max' => max($price)
			);			
		}





		public static function photo_gallery( $mls, $out, $class, $slider = '', $count = 1 ) {

			
			$dir = ABSPATH.'wp-content/uploads/property/' . $mls .'/';


			if( $out ) $slider = '<ul class="'. $class .' fslider">';
			else $slider = '<div class="'. $class .' fslider-pager">';



            if ($handle = opendir($dir)) {


                while (($file = readdir($handle)) !== false){
                    if (!in_array($file, array('.', '..')) && !is_dir($dir.$file)) {

                    	
                    	if( $out ) $slider .= '<li>';
                    	else $slider .= '<a data-slide-index="' . ( $count - 1 ) . '" href="#" onclick="clicked('.( $count - 1 ).');return false;">';

                    	$slider .= '<div class="photo" style="background-image:url('. get_bloginfo('siteurl') . '/wp-content/uploads/property/' . $mls . '/' . $count .'.jpg)"></div>';
                    	

                    	if( $out ) $slider .= '</li>';
                    	else $slider .= '</a>';

                    	$count++;

                    }
                }


            } else {

            	//create temporary slider
            	for( $i = 1; $i <= 10; $i++ ) {

            		if( $out ) $slider .= '<li>';
            		else $slider .= '<a data-slide-index="' . ( $i - 1 ) . '" href="">';

            		$slider .= '<div style="background-image:url('. plugins_url( 'gmap-listings/images/listing-photo.png' ) . ')"></div>';


            		if( $out ) $slider .= '</li>';
                    else $slider .= '</a>';

            	}

            }


            if( $out ) $slider .= '</ul>';
            else $slider .= '</div>';



            echo $slider;


		}




		public static function gmap_is_in_area_code( $district, $area = 'details' ) {

			$area_code = array(
				'east-toronto'		=>	array( 'E01', 'E02', 'EO3' ),
				'west-toronto'		=>	array( 'W01', 'W02', 'W03' ),
				'downtown-toronto'	=>	array( 'C01', 'C08' ),
				'midtown-toronto'	=>	array( 'C02', 'C03', 'C04', 'C09', 'C10', 'C11', 'C12' ),
				'scarborough'		=>	array( 'E04', 'E05', 'E06', 'E07', 'E08', 'E09', 'E10', 'E11'),
				'etobicoke'			=>	array( 'W06', 'W07', 'W08', 'W09', 'W10' ),
				'north-york'		=>	array( 'W04', 'W05', 'C06', 'C07', 'C13', 'C14', 'C15' )
			);



			foreach ( $area_code as $code => $areas ) {
						
				if( in_array( $district, $areas ) ) {
					$area = $code;
				}
			}

			return $area;

		}




		public static function featured_listings_slider( $where, $slider = '', $area = '' ) {

			global $wpdb;

			$query = "SELECT * FROM(
					  	".self::residential("WHERE CONCAT_WS(',', Community,Municipality,Ml_num,Zip,Rltr) LIKE '%{$where}%'")."
					  	UNION
					  	".self::condo("WHERE CONCAT_WS(',', Community,Municipality,Ml_num,Zip,Rltr) LIKE '%{$where}%'")."
					  	UNION
					  	".self::commercial("WHERE CONCAT_WS(',', Community,Municipality,Ml_num,Zip,Rltr) LIKE '%{$where}%'")."
					  ) as property
					  
					  GROUP BY CONCAT(Addr,' ',Municipality,' ',County)
					  ORDER BY Timestamp_sql DESC";



			$results = $wpdb->get_results( $query );




			if( count( $results ) ) {

				
				
				$slider .= '<div class="ffslider-cover">';
				$slider .= '<ul class="ffslider">';



				foreach( $results as $listing_result ) {
				


					$area_data = explode( ' ', $listing_result->Municipality_district );
					$area = self::gmap_is_in_area_code( $area_data[1] );



					if( ! empty( $area ) ) {

						$slider .= '<li>';


						$slider .= '<div class="fgrid">';				       	

				       	$slider .= '<a href="/listings/'. $area . '/'  . $listing_result->Ml_num . '">';
				       	$slider .= '<div class="ftop">';
				       	$slider .= '<span>' . $listing_result->Area . '</span>';
				       	$slider .= '<span>' . $listing_result->Addr . '</span>';
				       	$slider .= '</div>';

				       	
				       	$slider .= '<div class="fbottom">';
				        $slider .= '<span class="large">$' . number_format($listing_result->Lp_dol, 2) . ' <i>' . $listing_result->Br . ' bd</i>' . ' <i>' . $listing_result->Bath_tot . ' ba</i>' . '</span>';
				        $slider .= '<span class="small">Courtesy of ' . $listing_result->Rltr . '</span>';
				        $slider .= '</div>';
				        $slider .= '</a>';


				       	$slider .= '<img src="' . self::get_first_image($listing_result->Ml_num) . '" title="" alt="" />';
				       	$slider .= '</div>';


				       	$slider .= '</li>';



				    }


				}


				$slider .= '</ul>';
				$slider .= '</div>';




				
				$slider .= '<script type="text/javascript">';
				$slider .= 'jQuery(document).ready(function(){
				  var min = 5;
				  if( jQuery(window).width() <= 650 ) min = 2;

				  jQuery(".ffslider").bxSlider({
				  	minSlides: min,
				  	maxSlides: 6,
				  	slideWidth: 250,
				  	slideMargin: 10,
				  	pager: false
				  });
				});';
				$slider .= '</script>';


			}



			return $slider;


		}



		public static function url_get_contents ($Url) {
			if (!function_exists('curl_init')){ 
			die('CURL is not installed!');
			}
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $Url);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			$output = curl_exec($ch);
			curl_close($ch);
			return $output;
		}





		public static function gmap_search_type( $options ) {


			$map = $options['map'];
			$page = ( $options['page'] == 'true' )? true : false;

			$search_type = array(
				'area'		=>	do_shortcode( get_option( 'map_area' ) ),
				'location'	=>	self::url_get_contents( plugins_url( 'gmap-listings/template/search_by_location.php' ) )
			);

			return $search_type[ get_option( 'search_type' ) ];

		}




		public static function create_needed_folder( $folder ) {

			$upload_dir = wp_upload_dir();
			$user_dirname = $upload_dir['basedir'].'/'.$folder;

			if( !is_dir($user_dirname) ) {
				wp_mkdir_p($user_dirname);
			}

			return $user_dirname;

		}



		public static function get_contents( $file, $any = false, $storage = false) {

			$upload_dir = wp_upload_dir();
			$user_dirname = $upload_dir['basedir'].'/'.$file;

			if( $any ) $user_dirname = glob($user_dirname)[0];
			$filename = explode('/', $user_dirname);
			
			if($storage) $user_dirname = get_bloginfo('siteurl') . '/wp-content/uploads/property/storage/' . $filename[count($filename) - 1];
			else $user_dirname = get_bloginfo('siteurl') . '/wp-content/uploads/property/storage/broker/' . $filename[count($filename) - 1];

			return self::url_get_contents($user_dirname);
			
		}




		public static function save_listings_address( $option ) {

			if( $option == 'location' ) {

				$storage = self::create_needed_folder('property/storage');
				$broker = self::create_needed_folder('property/storage/broker');

				self::save_listings_brokerage($broker);

				self::save_contents("{$storage}/addresses.txt", self::gmap_populate_address());
				
			}

		}



		public static function save_contents( $file, $content ) {


			if( file_exists( $file ) ) {

				file_put_contents( $file, implode( '::', $content ) );

			} else {
				
				$file = fopen( $file, 'w' );
				fputs( $file, implode( '::', $content ) );
				fclose( $file );

			}

		}




		public static function save_listings_brokerage( $dir ) {

			global $wpdb;

			$query = "SELECT * FROM(
						  ".self::residential()."
						  UNION
						  ".self::condo()."
						  UNION
						  ".self::commercial()."
					  ) as property
					  GROUP BY Rltr";

			$brokerage = $wpdb->get_results($query);

			foreach ($brokerage as $name) {
				

				$query = "SELECT * FROM(
					  	".self::residential("WHERE Rltr LIKE '%$name->Rltr%'")."
					  	UNION
					  	".self::condo("WHERE Rltr LIKE '%$name->Rltr%'")."
					  	UNION
					  	".self::commercial("WHERE Rltr LIKE '%$name->Rltr%'")."					  
					  ) as property
					  GROUP BY CONCAT(Addr,' ',Municipality,' ',County)
					  ORDER BY Timestamp_sql DESC";

				
				$results = $wpdb->get_results( $query );
				$address = array();
				$counter = 1;
				
				foreach ( $results as $listing_result ) {

					
					$location = self::this_lat_long( $listing_result->Ml_num );
				    if( count($location) && ! empty( $listing_result->Addr ) ) {


						$area_data = explode( ' ', $listing_result->Municipality_district );
						$area = self::gmap_is_in_area_code( $area_data[1] );

					
						
				        $addr = $listing_result->Addr.' '.$listing_result->Area.'|'.trim($listing_result->prop_type).'|'.$listing_result->Ml_num.'|'.$listing_result->Lp_dol.'|'.ucfirst(str_replace('pro', ' Pro', str_replace('rets_property_', '', $listing_result->prop_type))).'|'.self::get_first_image($listing_result->Ml_num).'|'.$location['latitude'].'|'.$location['longitude'].'|'.$area;
				        //push data
				        array_push( $address, addslashes( $addr ) );



				        if(count($results) == $counter) {

				        	$filename = strtolower(str_replace(array('/', ' ', '.'),array('_','-',''),$name->Rltr)).'.txt';
				        	$file = "{$dir}/{$filename}";
							
							self::save_contents($file, $address);

				        }


				        $counter++;
				    }   

				}//end of foreach



			}
					


		}




		public static function gmap_get_broker( $name ) {
		
			$filename = "property/storage/broker/{$name}*.txt";
			return self::get_contents($filename, true);
			
		}




		public static function gmap_get_all_address() {
			return self::get_contents( 'property/storage/addresses.txt', true, true );
		}





		public static function gmap_populate_address( $address = array() ) {


			global $wpdb;

			$query = "SELECT * FROM(
					  	".self::residential()."
					  	UNION
					  	".self::condo()."
					  	UNION
					  	".self::commercial()."					  
					  ) as property
					  GROUP BY CONCAT(Addr,' ',Municipality,' ',County)
					  ORDER BY Timestamp_sql DESC";

			
			$results = $wpdb->get_results( $query );

			foreach ( $results as $listing_result ) {


				$location = self::this_lat_long( $listing_result->Ml_num );
			    if( count($location) && ! empty( $listing_result->Addr ) ) {


					$area_data = explode( ' ', $listing_result->Municipality_district );
					$area = self::gmap_is_in_area_code( $area_data[1] );

				
					
			        $addr = $listing_result->Addr.' '.$listing_result->Area.'|'.trim($listing_result->prop_type).'|'.$listing_result->Ml_num.'|'.$listing_result->Lp_dol.'|'.ucfirst(str_replace('pro', ' Pro', str_replace('rets_property_', '', $listing_result->prop_type))).'|'.self::get_first_image($listing_result->Ml_num).'|'.$location['latitude'].'|'.$location['longitude'].'|'.$area;
			        //push data
			        array_push( $address, addslashes( $addr ) );


			    }   

			}


			return $address;		  


		}





		//CREATE rets_properties
		//This function will merge the property tables into one
		public static function merge_tables( $raw = array(), $columns = array(), $sql = '', $count = 0 ) {

			global $wpdb;


			$tables = array( 'rets_property_residentialproperty', 'rets_property_commercialproperty', 'rets_property_condoproperty' );

			foreach ( $tables as $table => $name ) {
				

				$query = "SHOW COLUMNS FROM " . $name;
				$result = $wpdb->get_results( $query );

				foreach ( $result as $field ) {
					
					$columns[$name][] = $field->Field;
					array_push( $raw, $field->Field );
					
				}


			}

			$unique = array_unique( $raw );


			$sql = "CREATE TABLE IF NOT EXISTS rets_properties ( id INT(10) UNSIGNED AUTO_INCREMENT PRIMARY KEY, ";
			$sql .= implode( ' VARCHAR(50),', $unique );
			$sql .= " VARCHAR(50), lat VARCHAR(30), lng VARCHAR(30), tbl VARCHAR(50) )";

			

			
			require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
			dbDelta( $sql );

			self::insert_ret_properties( $columns );
			

		}





		//INSERT data from all properties to the new table: rets_properties
		public static function insert_ret_properties( $columns, $table_name = '', $last_id = 0, $cur_id = 0 ) {

			global $wpdb;
			


			$tables = array( 'rets_property_residentialproperty', 'rets_property_commercialproperty', 'rets_property_condoproperty', '' );
			foreach ( $tables as $table => $name ) {
					
				//update
				if( $last_id > 0 ) {

					if( $cur_id == 0 ) {
						$sql = "UPDATE rets_properties SET tbl = '" . $table_name . "'";
					} else {
						$sql = "UPDATE rets_properties SET tbl = '" . $table_name . "' WHERE id >= " . $last_id ;
					}

					$wpdb->query( $sql );
					$cur_id = $last_id;

				}

				$table_name = $name;
					
				//insert
				if( ! empty( $name ) ) {

					$state = implode( ',', $columns[$name] );
					$sql = "INSERT INTO rets_properties (" . $state . ") 
							SELECT " . $state . " FROM " . $name;
					
					$wpdb->query( $sql );
					$last_id = $wpdb->insert_id;

				}



			}


			self::insert_ret_properties_latlong();



		}






		//UPDATE update lat and long from each listings
		public static function insert_ret_properties_latlong() {

			global $wpdb;

			$sql = "UPDATE rets_properties a
					LEFT JOIN rets_property_lat_long b ON
						a.Ml_num = b.Ml_num 
					SET a.lat = b.latitude, a.lng = b.longitude";
			$wpdb->query( $sql );

		}





		public static function gmap_place_request() {

			$server = str_replace( array( 'place/' , '_' ), array( '', ' ' ), $_SERVER["REQUEST_URI"] );
			$page = array_filter( explode( '/', $server ) );
			$position = array_slice( $page, count( $request ) - 2 ); 
			$request = array_diff( $page, $position );

			$url = array_map( 'ucwords', $request );	
			return array( $position, $url );
		}








		public static function cleanListingsPhoto($ml_num = '') {

			if(!empty($ml_num))	$path = ABSPATH."wp-content/uploads/property/{$ml_num}";
			else $path = ABSPATH.'wp-content/uploads/property';
			exec("find  {$path} -not -name 'storage' | xargs rm -rf");

		}





		public static function applyPriceRange($min, $max, $html = '') {

			$min = ($min < 1000)? 1000 : $min;
			while($min <= $max) {

				$range  = number_format($min) . ' - ' . number_format(($min + 3000));
				$html .= '<span data-value="'. $range .'">'. $range .'</span>';
				$min += 3000;
			}

			return $html;

		}



		public static function containsRecords() {
			global $wpdb;
			$rowcount = $wpdb->get_results("SELECT * FROM rets_property_commercialproperty LIMIT 1");
			return count($rowcount);
		}



		public static function isRecordExist($table, $ml_num) {
			global $wpdb;
			$query = "SELECT * 
					  FROM {$table}
					  WHERE Ml_num = '$ml_num'";
			$result = $wpdb->get_results($query);		  
			return (count($result) > 0)? true : false;
		}




	}

}