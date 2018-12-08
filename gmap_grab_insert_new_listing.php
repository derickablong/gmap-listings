<?php 
/* Search RETS server */
$search_residential = $rets->SearchQuery(
    'Property',                                
    'ResidentialProperty',                                     
    '(Status=A),(Timestamp_sql=' . $month . '+)' 
);



if ($rets->TotalRecordsFound() > 0) {
    $i = 1;
    //record progress
    GMAP::record_progress( 'residential_progress', $_SESSION['TOTAL_RES'] );

    if((int)$_SESSION['TOTAL_RES'] > 0) {

        while ($data = $rets->FetchRow($search_residential)) {
            
            if(GMAP::isRecordExist('rets_property_residentialproperty_a', $data['Ml_num']) !== true) {


            	//pgress indicator
	            GMAP::progress( 'residential', $_SESSION['TOTAL_RES'], $i );
	            
	            //record ml_num
	            $_SESSION['ML_TOTAL'] += 1;
	            $_SESSION['ML_NUMBER'] = $_SESSION['ML_NUMBER'] . '||' . $data['Ml_num'];

	            //record address
	            $address =  str_replace(array(' Rd', ' Toronto', 'Toronto'), array(' Road', '', ''), trim($data['Addr'])).','.$data['Municipality'].','.$data['County'].','.GMAP::country() . '::' . $data['Ml_num'] . '::' . $data['Addr'];
	            $_SESSION['RESIDENTIAL_ADDR'] = $_SESSION['RESIDENTIAL_ADDR'] . '||' . $address;

	            //print_r($data);
	            $insert_mls_residential = $wpdb->insert(
	                'rets_property_residentialproperty',
	                $data
	            );

	            dbDelta($insert_mls_residential);
	            $i++;


            }
            
        }
    } else {
        GMAP::progress( 'residential', 0, 0 );
    }


}
$rets->FreeResult($search_residential);
//end ResidentialProperty



/**************************************************************
 *
 * grabbing from rets server and saving data to database
 * CommercialProperty
 ************************************************************/
//CommercialProperty
/* Search RETS server */
$search_comercial = $rets->SearchQuery(
    'Property',                                
    'CommercialProperty',                                     
    '(Status=A),(Timestamp_sql=' . $month . '+)' 
);



if ($rets->TotalRecordsFound() > 0) {
    $i = 1;
    //record progress
    GMAP::record_progress( 'commercial_progress', $_SESSION['TOTAL_COM'] );
    

    if((int)$_SESSION['TOTAL_COM'] > 0) {
        while ($data = $rets->FetchRow($search_comercial)) {
            

        	if(GMAP::isRecordExist('rets_property_commercialproperty_a', $data['Ml_num']) !== true) {

	            //pgress indicator
	            GMAP::progress( 'commercial', $_SESSION['TOTAL_COM'], $i, 1 );
	                
	            //record ml_num
	            $_SESSION['ML_TOTAL'] += 1;
	            $_SESSION['ML_NUMBER'] = $_SESSION['ML_NUMBER'] . '||' . $data['Ml_num'];

	            //record address
	            $address =  str_replace(array(' Rd', ' Toronto', 'Toronto'), array(' Road', '', ''), trim($data['Addr'])).','.$data['Municipality'].','.$data['County'].','.GMAP::country() . '::' . $data['Ml_num'] . '::' . $data['Addr'];
	            $_SESSION['COMMERCIAL_ADDR'] = $_SESSION['COMMERCIAL_ADDR'] . '||' . $address;

	            //print_r($data);
	            $insert_mls_comercial = $wpdb->insert(
	                'rets_property_commercialproperty',
	                $data
	            );

	            dbDelta($insert_mls_comercial);
	            $i++;

	        }
        }

    } else {
        GMAP::progress( 'commercial', 0, 0, 1 );
    }



}
$rets->FreeResult($search_comercial);
//end CommercialProperty



/**************************************************************
 *
 * grabbing from rets server and saving data to database
 * CondoProperty
 ************************************************************/
//CondoProperty
/* Search RETS server */
$search_condo = $rets->SearchQuery(
    'Property',                                
    'CondoProperty',                                     
    '(Status=A),(Timestamp_sql=' . $month . '+)' 
);


if ($rets->TotalRecordsFound() > 0) {
    $i = 1;
    //record progress
    GMAP::record_progress( 'condo_progress', $_SESSION['TOTAL_CON'] );


    if((int)$_SESSION['TOTAL_CON'] > 0) {
        while ($data = $rets->FetchRow($search_condo)) {
           
            if(GMAP::isRecordExist('rets_property_condoproperty_a', $data['Ml_num']) !== true) {
            

	            //pgress indicator
	            GMAP::progress( 'condo', $_SESSION['TOTAL_CON'], $i, 2 );
	            
	            //record ml_num
	            $_SESSION['ML_TOTAL'] += 1;
	            $_SESSION['ML_NUMBER'] = $_SESSION['ML_NUMBER'] . '||' . $data['Ml_num'];

	            //record address
	            $address =  str_replace(array(' Rd', ' Toronto', 'Toronto'), array(' Road', '', ''), trim($data['Addr'])).','.$data['Municipality'].','.$data['County'].','.GMAP::country() . '::' . $data['Ml_num'] . '::' . $data['Addr'];
	            $_SESSION['CONDO_ADDR'] = $_SESSION['CONDO_ADDR'] . '||' . $address;

	            //print_r($data);
	            $insert_mls_condo = $wpdb->insert(
	                'rets_property_condoproperty',
	                $data
	            );
	            dbDelta($insert_mls_condo);
	            $i++;

	        }
        }


    } else {
        GMAP::progress( 'condo', 0, 0, 2 );
    }



}
$rets->FreeResult($search_condo);
$rets->Disconnect();


require_once dirname( __FILE__ ) . '/gmap_grab_photo.php';