<?php 
require_once( dirname( __FILE__ ) . '/gmap_rts_login_a.php');

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
   

	$truncate = "TRUNCATE TABLE rets_property_residentialproperty_a";
    $wpdb->query($truncate);

	$truncate = "TRUNCATE TABLE rets_property_condoproperty_a";
    $wpdb->query($truncate);

    $truncate = "TRUNCATE TABLE rets_property_commercialproperty_a";
    $wpdb->query($truncate);


    /*************************************************************
     * ResidentialProperty create table
     ************************************************************/
    $resource = "Property";
    $class = "ResidentialProperty";
    // or set through a loop

    // pull field format information for this class
    $rets_metadata = $rets->GetMetadata($resource, $class);

    $table_name1 = "rets_".strtolower($resource)."_".strtolower($class).'_a';
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

    $table_name2 = "rets_".strtolower($resource)."_".strtolower($class).'_a';
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

    $table_name3 = "rets_".strtolower($resource)."_".strtolower($class).'_a';
    // i.e. rets_property_res

    $sql = GMAP::create_table_sql_from_metadata($table_name3, $rets_metadata, "Ml_num");
    //echo $sql.'<br><br>';
    dbDelta($sql);
    /*************************************************************
     * end CondoProperty create table
     ************************************************************/

    /**************************************************************
     *
     * grabbing from rets server and saving data to database
     * ResidentialProperty
     ************************************************************/

    /* Search RETS server */
    $search_residential = $rets->SearchQuery(
        'Property',                                // Resource
        'ResidentialProperty',                                        // Class
        '(Status=A)'    // DMQL, with SystemNames
    );
    /* If search returned results */
    //echo 'ResidentialProperty: ' . $rets->TotalRecordsFound().' Records<br>';
    if ($rets->TotalRecordsFound() > 0) {
        $i = 1;
     
        while ($data = $rets->FetchRow($search_residential)) {

            //print_r($data);
            $insert_mls_residential = $wpdb->insert(
                $table_name1,
                $data
            );

            dbDelta($insert_mls_residential);
            $i++;
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
        'Property',                                // Resource
        'CommercialProperty',                                        // Class
        '(Status=A)'    // DMQL, with SystemNames
    );
    /* If search returned results */
    //echo 'CommercialProperty: ' . $rets->TotalRecordsFound().' Records<br>';
    if ($rets->TotalRecordsFound() > 0) {
        $i = 1;
       
        while ($data = $rets->FetchRow($search_comercial)) {
            
            //print_r($data);
            $insert_mls_comercial = $wpdb->insert(
                $table_name2,
                $data
            );

            dbDelta($insert_mls_comercial);
            $i++;
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
        'Property',                                // Resource
        'CondoProperty',                                        // Class
        '(Status=A)'    // DMQL, with SystemNames
    );
    /* If search returned results */
    //echo 'CondoProperty: ' . $rets->TotalRecordsFound().' Records<br>';
    if ($rets->TotalRecordsFound() > 0) {
        $i = 1;
      
        while ($data = $rets->FetchRow($search_condo)) {
           
            //print_r($data);
            $insert_mls_condo = $wpdb->insert(
                $table_name3,
                $data
            );
            dbDelta($insert_mls_condo);
            $i++;
        }
    }
    $rets->FreeResult($search_condo);
    $rets->Disconnect();

    require_once dirname( __FILE__ ) . '/gmap_grab_daily_dummy_listings.php';
} 