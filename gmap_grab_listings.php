<?php 
require_once( dirname( __FILE__ ) . '/gmap_rts_login.php');

//initialize
date_default_timezone_set('Canada/Saskatchewan');
require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
global $wpdb;
$charset_collate = "DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci";
$rets = new PHRETS;

//$rets->setParam("cookie_file", "/var/www/html/phrets-cookies.txt");
$rets->setParam("cookie_file", plugin_dir_path( __FILE__ ) . "lib/phrets-cookies.txt");
$connect = $rets->Connect($login, $un, $pw);

if ($connect) {
   
    
    /*************************************************************
     * ResidentialProperty create table
     ************************************************************/
    $resource = "Property";
    $class = "ResidentialProperty";
    // or set through a loop

    // pull field format information for this class
    $rets_metadata = $rets->GetMetadata($resource, $class);

    $table_name1 = "rets_".strtolower($resource)."_".strtolower($class).'';
    // i.e. rets_property_res

    $sql = GMAP::create_table_sql_from_metadata($table_name1, $rets_metadata, "Ml_num");
    //echo $sql.'<br><br>';
    dbDelta($sql);
    /*************************************************************
     * end ResidentialProperty create table
     ************************************************************/

    /*************************************************************
     * CommercialProperty create table
     ************************************************************/
    $resource = "Property";
    $class = "CommercialProperty";
    // or set through a loop

    // pull field format information for this class
    $rets_metadata = $rets->GetMetadata($resource, $class);

    $table_name2 = "rets_".strtolower($resource)."_".strtolower($class).'';
    // i.e. rets_property_res


    $sql = GMAP::create_table_sql_from_metadata($table_name2, $rets_metadata, "Ml_num");
    //echo $sql.'<br><br>';
    dbDelta($sql);
    /*************************************************************
     * end CommercialProperty create table
     ************************************************************/


    /*************************************************************
     * CondoProperty create table
     ************************************************************/
    $resource = "Property";
    $class = "CondoProperty";
    // or set through a loop

    // pull field format information for this class
    $rets_metadata = $rets->GetMetadata($resource, $class);

    $table_name3 = "rets_".strtolower($resource)."_".strtolower($class).'';
    // i.e. rets_property_res

    $sql = GMAP::create_table_sql_from_metadata($table_name3, $rets_metadata, "Ml_num");
    //echo $sql.'<br><br>';
    dbDelta($sql);
    /*************************************************************
     * end CondoProperty create table
     ************************************************************/


    $month = date('Y-m-d', strtotime(date('Y-m') . " -120 days"));

    /**************************************************************
     *
     * grabbing from rets server and saving data to database
     * ResidentialProperty
     ************************************************************/

    /* Search RETS server */
    $search_residential = $rets->SearchQuery(
        'Property',                                // Resource
        'ResidentialProperty',                                        // Class
        '(Status=A),(Timestamp_sql=' . $month . '+)'    // DMQL, with SystemNames
    );
    /* If search returned results */
    //echo 'ResidentialProperty: ' . $rets->TotalRecordsFound().' Records<br>';
    if ($rets->TotalRecordsFound() > 0) {
        $i = 1;
        //record progress
        GMAP::record_progress( 'residential_progress', $rets->TotalRecordsFound() );
        while ($data = $rets->FetchRow($search_residential)) {
            
            //pgress indicator
            GMAP::progress( 'residential', $rets->TotalRecordsFound(), $i );
            
            //record ml_num
            $_SESSION['ML_TOTAL'] += 1;
            $_SESSION['ML_NUMBER'] = $_SESSION['ML_NUMBER'] . '||' . $data['Ml_num'];

            //record addressf
            $address =  str_replace(array(' Rd', ' Toronto', 'Toronto'), array(' Road', '', ''), trim($data['Addr'])).','.$data['Municipality'].','.$data['County'].','.GMAP::country() . '::' . $data['Ml_num'] . '::' . $data['Addr'];
            $_SESSION['RESIDENTIAL_ADDR'] = $_SESSION['RESIDENTIAL_ADDR'] . '||' . $address;

            //print_r($data);
            $insert_mls_residential = $wpdb->insert(
                $table_name1,
                $data
            );

            dbDelta($insert_mls_residential);
            $i++;
        }
    } else {
        echo '0 Records Found';
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
        'Property',                                // Resource
        'CommercialProperty',                                        // Class
        '(Status=A),(Timestamp_sql=' . $month . '+)'    // DMQL, with SystemNames
    );
    /* If search returned results */
    //echo 'CommercialProperty: ' . $rets->TotalRecordsFound().' Records<br>';
    if ($rets->TotalRecordsFound() > 0) {
        $i = 1;
        //record progress
        GMAP::record_progress( 'commercial_progress', $rets->TotalRecordsFound() );
        while ($data = $rets->FetchRow($search_comercial)) {
            //pgress indicator
            GMAP::progress( 'commercial', $rets->TotalRecordsFound(), $i, 1 );
                
            //record ml_num
            $_SESSION['ML_TOTAL'] += 1;
            $_SESSION['ML_NUMBER'] = $_SESSION['ML_NUMBER'] . '||' . $data['Ml_num'];

            //record address
            $address =  str_replace(array(' Rd', ' Toronto', 'Toronto'), array(' Road', '', ''), trim($data['Addr'])).','.$data['Municipality'].','.$data['County'].','.GMAP::country() . '::' . $data['Ml_num'] . '::' . $data['Addr'];
            $_SESSION['COMMERCIAL_ADDR'] = $_SESSION['COMMERCIAL_ADDR'] . '||' . $address;

            //print_r($data);
            $insert_mls_comercial = $wpdb->insert(
                $table_name2,
                $data
            );

            dbDelta($insert_mls_comercial);
            $i++;
        }
    } else {
        echo '0 Records Found';
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
        'Property',                                // Resource
        'CondoProperty',                                        // Class
        '(Status=A),(Timestamp_sql=' . $month . '+)'    // DMQL, with SystemNames
    );
    /* If search returned results */
    //echo 'CondoProperty: ' . $rets->TotalRecordsFound().' Records<br>';
    if ($rets->TotalRecordsFound() > 0) {
        $i = 1;
        //record progress
        GMAP::record_progress( 'condo_progress', $rets->TotalRecordsFound() );
        while ($data = $rets->FetchRow($search_condo)) {
            //pgress indicator
            GMAP::progress( 'condo', $rets->TotalRecordsFound(), $i, 2 );
            
            //record ml_num
            $_SESSION['ML_TOTAL'] += 1;
            $_SESSION['ML_NUMBER'] = $_SESSION['ML_NUMBER'] . '||' . $data['Ml_num'];

            //record address
            $address =  str_replace(array(' Rd', ' Toronto', 'Toronto'), array(' Road', '', ''), trim($data['Addr'])).','.$data['Municipality'].','.$data['County'].','.GMAP::country() . '::' . $data['Ml_num'] . '::' . $data['Addr'];
            $_SESSION['CONDO_ADDR'] = $_SESSION['CONDO_ADDR'] . '||' . $address;

            //print_r($data);
            $insert_mls_condo = $wpdb->insert(
                $table_name3,
                $data
            );
            dbDelta($insert_mls_condo);
            $i++;
        }
    } else {
        echo '0 Records Found';
    }
    $rets->FreeResult($search_condo);
   
    //end CondoProperty
    //clean photos
    GMAP::cleanListingsPhoto();
	require_once dirname( __FILE__ ) . '/gmap_grab_photo.php';
    $rets->Disconnect();
} else {
    $error = $rets->Error();
    print_r($error);
}