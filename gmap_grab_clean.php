<?php
$property = ['rets_property_residentialproperty', 'rets_property_condoproperty', 'rets_property_commercialproperty'];

foreach ($property as $key => $table) {
		
	$query = "SELECT Ml_num 
			  FROM {$table} 
			  WHERE Ml_num NOT IN ( SELECT Ml_num FROM {$table}_a WHERE Ml_num IS NOT NULL)";

	$results = $wpdb->get_results($query);
	foreach ($results as $result) {
		GMAP::cleanListingsPhoto($result->Ml_num);
	}

}