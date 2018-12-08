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
   

    $truncate = "TRUNCATE TABLE rets_property_residentialproperty_dummy";
    $wpdb->query($truncate);

    $truncate = "TRUNCATE TABLE rets_property_condoproperty_dummy";
    $wpdb->query($truncate);

    $truncate = "TRUNCATE TABLE rets_property_commercialproperty_dummy";
    $wpdb->query($truncate);


    /*************************************************************
     * ResidentialProperty create table
     ************************************************************/
    $resource = "Property";
    $class = "ResidentialProperty";
    // or set through a loop

    // pull field format information for this class
    $rets_metadata = $rets->GetMetadata($resource, $class);

    $table_name1 = "rets_".strtolower($resource)."_".strtolower($class).'_dummy';
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

    $table_name2 = "rets_".strtolower($resource)."_".strtolower($class).'_dummy';
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

    $table_name3 = "rets_".strtolower($resource)."_".strtolower($class).'_dummy';
    // i.e. rets_property_res

    $sql = GMAP::create_table_sql_from_metadata($table_name3, $rets_metadata, "Ml_num");
    //echo $sql.'<br><br>';
    dbDelta($sql);
    /*************************************************************
     * end CondoProperty create table
     ************************************************************/


    $month = date('Y-m-d', strtotime(date('Y-m') . " -1 days"));

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
    


    if ($rets->TotalRecordsFound() > 0) {
       
       
        while ($data = $rets->FetchRow($search_residential)) {
    
        
            if(GMAP::isRecordExist('rets_property_residentialproperty_a', $data['Ml_num']) !== true)
                $_SESSION['TOTAL_RES'] = $_SESSION['TOTAL_RES'] + 1;

            $insert_mls_residential = $wpdb->insert(
                $table_name1,
                $data
            );

            dbDelta($insert_mls_residential);
           
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
 


    if ($rets->TotalRecordsFound() > 0) {
        
        while ($data = $rets->FetchRow($search_comercial)) {
          
            if(GMAP::isRecordExist('rets_property_commercialproperty_a', $data['Ml_num']) !== true)
                $_SESSION['TOTAL_COM'] = $_SESSION['TOTAL_COM'] + 1;

            //print_r($data);
            $insert_mls_comercial = $wpdb->insert(
                $table_name2,
                $data
            );

            dbDelta($insert_mls_comercial);
           
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



    if ($rets->TotalRecordsFound() > 0) {
      
        while ($data = $rets->FetchRow($search_condo)) {
           
            if(GMAP::isRecordExist('rets_property_condoproperty_a', $data['Ml_num']) !== true)
                $_SESSION['TOTAL_CON'] = $_SESSION['TOTAL_CON'] + 1;

            //print_r($data);
            $insert_mls_condo = $wpdb->insert(
                $table_name3,
                $data
            );
            dbDelta($insert_mls_condo);

        }
    } else {
        echo '0 Records Found';
    }
    $rets->FreeResult($search_condo);


    require_once dirname( __FILE__ ) . '/gmap_grab_insert_new_listing.php';
    
}