<?php
//residential
$residential_property_keys = "residentials, residential, townhouses, townhouse, townhomes, townhome, homes, home, houses, house, real";
$residential_property = explode(',', preg_replace('/\s+/', '', $residential_property_keys));

//condo
$condo_property_keys = "apartments, appartment, condominiums, condominium, condos, condo, real, lofts, loft";
$condo_property = explode(',', preg_replace('/\s+/', '', $condo_property_keys));

//commercial
$commercial_property_keys = "commercials, commercial, lots, lot";
$commercial_property = explode(',', preg_replace('/\s+/', '', $commercial_property_keys));

//not allowed keys
$not_allowed_keys = array_merge($residential_property, $condo_property, $commercial_property);

//content handler
$content = '';

$txt = fopen( dirname( __FILE__ ) . '/storage/search_cache_data.txt', 'r' );
if( $txt ){
	while( !feof( $txt ) ){
		//set new search key words
		$keyword = fgets( $txt );
		$content .= '<span class="search_item">' . trim( $keyword ) . '</span>';
	}
	fclose( $txt );
}
echo $content;