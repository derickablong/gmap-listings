<?php
//constant
define('LISTS_PER_VIEW', 20);
//get views request
define('ACTIVE_RECORD', ( ( $_GET['sr'] != '') ? $_GET['sr'] : 0) );
//price range
define('PRICE_RANGE' , ( (isset( $_GET['price_range'] )) ? $_GET['price_range'] : '' ) );
//bedroom
define('NO_BEDROOMS', ( ( isset( $_GET['bedrooms'] ) ) ? $_GET['bedrooms'] : '' ) );
//property type
define('PROP_TYPE', ( ( isset( $_GET['prop_type'] ) ) ? trim($_GET['prop_type']) : '' ) );


//define function
if( !function_exists('getPropType') ){
    function getPropType( $t ){
        switch( $t ){
            case 'rets_property_commercialproperty':    return 'Prop_type as type_of_home';        break;
            case 'rets_property_residentialproperty':   return 'Type_own1_out as type_of_home';    break;
            case 'rets_property_condoproperty':         return 'Style as type_of_home';            break;
        }
    }
}
//set sql to null
$sql = ''; $html = ''; $WHERE_PRICE = ''; $WHERE_BEDROOMS = ''; $WHERE_PROPTYPE = PROP_TYPE;
//get request
$search = $_GET['search'];
//db table holder
$table_name = '';
$AND_MUNICIPALITY = '';
//if has request
if($search!=''){

    //added by derick
    //search key
    $keys = explode(",", $search);
    $search = trim($keys[0]);
    $municipality_ = (count($keys) > 1)? trim($keys[ count($keys) - 1 ]) : '';
    $keyword = explode(" ", trim($keys[0]));

    if( trim($municipality_ )!= '')
    $AND_MUNICIPALITY = " AND municipality = '$municipality_'";

    //residential
    $residential_property_keys = "residentials, residential, townhouses, townhouse, townhomes, townhome, homes, home, houses, house, real";
    $residential_property = explode(',', preg_replace('/\s+/', '', $residential_property_keys));

    //condo
    $condo_property_keys = "apartments, appartment, condominiums, condominium, condos, condo, real, lofts, loft";
    $condo_property = explode(',', preg_replace('/\s+/', '', $condo_property_keys));

    //commercial
    $commercial_property_keys = "commercials, commercial, lots, lot";
    $commercial_property = explode(',', preg_replace('/\s+/', '', $commercial_property_keys));
    
    
    //words that not allowed for search description
    $not_allowed_keys = array_merge($residential_property, $condo_property, $commercial_property, array('bedrooms', 'bedroom', 'properties', 'property', 'for sale', 'for sell', 'state', 'and', '&', ','));

    //set new search key words
    $new_search = trim(str_replace($not_allowed_keys, '', $search));

    //set key_find to false
    $key_find = false; 

    //if has price range
    if( PRICE_RANGE != '' ){
        $price_range = explode( '::', PRICE_RANGE );
        $WHERE_PRICE = ' AND Lp_dol >= ' . $price_range[0] . ' AND Lp_dol <= ' . $price_range[1] . ' ';
    }

    //if has bedroom
    if( NO_BEDROOMS != '' && NO_BEDROOMS > 0 ){
        $WHERE_BEDROOMS = " AND Br =  " . NO_BEDROOMS;
    }

    
    //filter keyword
    if(array_key_exists(1, $keys)){

        $key = $keys[1];    
        //pass through
        $pass_through = false;

        //remove leading spaces
        $key = strtolower(preg_replace('/\s+/', '', $key));


        //if clue contains residential
        if(in_array($key, $residential_property)){

            //set table name
            $table_name = 'rets_property_residentialproperty ';

            //to check if search request has matching table
            $pass_through = true;

        }

        //if clue contains condo
        if(in_array($key, $condo_property)){

            //set table name
            $table_name .= 'rets_property_condoproperty ';

             //to check if search request has matching table
            $pass_through = true;


        }

        //if clue contains commercial
        if(in_array($key, $commercial_property)){

            //set table name
            $table_name .= 'rets_property_commercialproperty';

             //to check if search request has matching table
            $pass_through = true;


        }

        //if found matching table
        if($pass_through){

            //check if key_find is true
            if($key_find)
                $sql .= " UNION ";

            //set key_ find to true
            $key_find = true;
        
        }

        //comporess
        if($key_find && $pass_through){

            $BR = 'Br, Bath_tot';

            if(!$new_search){ 
                $where = "CONCAT(Ml_num,' ',Zip,' ',Ad_text,' ',Addr,' ',Municipality,' ',County) like '%%'"; 
            } else {

                $search_arr = explode(' ', $new_search); $c = 1; $is_numeric = false;
                $search_arr = array_filter($search_arr);
                foreach($search_arr as $key){
                    if( !empty($key) ){
                        if( is_numeric($key) ){
                            $is_numeric = true;
                            $where .= " Br = $key AND ("; 
                        }else{
                            $where .= " CONCAT(Ml_num,' ',Zip,' ',Ad_text,' ',Addr,' ',Municipality,' ',County) like '%$key%' OR "; 
                        }
                    }     
                }

                $where = ( $is_numeric )?  substr($where, 0, -3) . ') ' :  ( (count($search_arr) > 1)? ' (' . substr($where, 0, -3) . ') ' : substr($where, 0, -3) );
                
            }

            $multiple = explode(' ', $table_name);

            if(count($multiple) > 1){

                $sql = '';

                foreach($multiple as $key){

                    if($key != '' && $key != ' ' && !empty($key)){

                        if( trim($key) == 'rets_property_commercialproperty' )
                            $BR = 'null as Br, Bath_tot';

                         $PROP = getPropType( $key );

                         if( PROP_TYPE ){
                           
                            $props = explode( '|', PROP_TYPE);

                            $pt = array_filter($props);     

                            if( count($pt) > 1 ){
                                $c = 1; $OR = 'OR';
                                foreach( $pt as $p ){
                                    if($c == 1){
                                        $WHERE_PROP .= " AND (";
                                    }

                                    if($c == count($pt))
                                        $OR = '';
                                    
                                    $WHERE_PROP .= str_replace(' as type_of_home', '', $PROP) . " = '$p' $OR ";
                                    
                                    if($c == count($pt)){
                                        $WHERE_PROP .= ")";
                                    }

                                    $c++;
                                }
                            }else{
                                $WHERE_PROP .= " AND " . str_replace(' as type_of_home', '', $PROP) . " LIKE '%" . str_replace('|', '', PROP_TYPE) . "%'";
                            }
                        }
   



                        $sql .= "SELECT  Ml_num, Addr,Zip, Ad_text,Municipality,County,Area,Timestamp_sql,Lp_dol,$BR, $PROP, '$key' as prop_type
                        FROM $key
                        WHERE $where $WHERE_PRICE $WHERE_BEDROOMS $WHERE_PROP $AND_MUNICIPALITY UNION ";
                    }
                }

                $sql = substr($sql, 0, -6);

            }else{

                if( trim($table_name) == 'rets_property_commercialproperty' )
                    $BR = 'null as Br';
                
                $PROP = getPropType( trim($table_name) );
               
                if( PROP_TYPE ){
                    
                     $props = explode( '|', PROP_TYPE);

                     $pt = array_filter($props);

                    if( count($pt) > 1 ){

                        $c = 1; $OR = 'OR';
                        
                        foreach( $pt as $p ){

                            if( $p != ''){

                                if($c == 1){
                                    $WHERE_PROP .= " AND (";
                                }

                                if($c == count($pt))
                                    $OR = '';
                                
                                $WHERE_PROP .= str_replace(' as type_of_home', '', $PROP) . " = '$p' $OR ";
                                
                                if($c == count($pt)){
                                    $WHERE_PROP .= ")";
                                }

                            }

                            $c++;
                        }
                    }else{
                        $WHERE_PROP .= " AND " . str_replace(' as type_of_home', '', $PROP) . " LIKE '%" . str_replace('|', '', PROP_TYPE) . "%'";
                    }
                }

                $sql .= "SELECT DISTINCT Ml_num, Addr,Zip, Ad_text,Municipality,County,Area,Timestamp_sql,Lp_dol,$BR, $PROP, '$table_name' as prop_type
                FROM $table_name
                WHERE $where $WHERE_PRICE $WHERE_BEDROOMS $WHERE_PROP $AND_MUNICIPALITY";
            }

        }

    
    }

    $count = 0;
   
    //check if key find is false
    if(!$key_find){
        
        //words that not breakable
        $fix_words = array('downtown toronto');

        if(in_array(strtolower($new_search), $fix_words)){

            $where = "CONCAT(Ml_num,' ',Zip,' ',Ad_text,' ',Addr,' ',Municipality,' ',County) like '%$new_search%' ";

        }else{

            $search_arr = explode(' ', $new_search);
            foreach($search_arr as $key){
                if($key != '' && $key != ' ' && !empty($key))
                $where .= "CONCAT(Ml_num,' ',Zip,' ',Ad_text,' ',Addr,' ',Municipality,' ',County) like '%$key%' OR ";   
            }

            $where = substr($where, 0, -3);

        }


        $fields = array('Type_own1_out', 'Prop_type', 'Style');

        if( PROP_TYPE ){

            $props = explode( '|', PROP_TYPE);

            $pt = array_filter($props);
          
            if( count($pt) > 1 ){

                $i = 1;
                foreach( $fields as $tb ){

                    $c = 1; $OR = 'OR'; 

                    foreach( $pt as $p ){
                        
                        if($c == 1){
                            $WHERE_PROP .= " AND (";
                        }


                        if( $c == count($pt) )
                            $OR = '';

                        
                        $WHERE_PROP .= $tb . " = '$p' $OR ";
                     


                        if($c == count($pt)){
                            $WHERE_PROP .= ")";
                        }


                        $c++;
                    }

                    if( $i == 1 ){
                        $WHERE_PROP_RESIDENTIAL = $WHERE_PROP;
                    }else if( $i == 2 ){
                        $WHERE_PROP_COMMERCIAL = $WHERE_PROP;
                    }else if( $i == 3 ){
                        $WHERE_PROP_CONDO = $WHERE_PROP;
                    }

                    $WHERE_PROP = '';
                    $i++;

                }

               
            }else{
               
                 $WHERE_PROP_RESIDENTIAL =  " AND Type_own1_out LIKE '%" .str_replace('|', '', PROP_TYPE). "%'";
                 $WHERE_PROP_CONDO =  " AND Style LIKE '%" .str_replace('|', '', PROP_TYPE). "%'";
                 $WHERE_PROP_COMMERCIAL =  " AND Prop_type LIKE '%" .str_replace('|', '', PROP_TYPE). "%'";
                    
            }
        }


        
        
    
        $sql = "
            SELECT * FROM (SELECT DISTINCT Ml_num, Addr,Zip, Ad_text,Municipality,County,Area,Timestamp_sql,Lp_dol,Br, Type_own1_out as type_of_home, 'rets_property_residentialproperty' as prop_type
            FROM rets_property_residentialproperty
            WHERE $where $WHERE_PRICE $WHERE_BEDROOMS $WHERE_PROP $WHERE_PROP_RESIDENTIAL $AND_MUNICIPALITY
            UNION
            SELECT DISTINCT Ml_num, Addr,Zip, Ad_text,Municipality,County,Area,Timestamp_sql,Lp_dol,null as Br, Prop_type as type_of_home, 'rets_property_commercialproperty' as prop_type
            FROM rets_property_commercialproperty
            WHERE $where $WHERE_PRICE $WHERE_PROP $WHERE_PROP_COMMERCIAL $AND_MUNICIPALITY
            UNION
            SELECT DISTINCT Ml_num, Addr,Zip, Ad_text,Municipality,County,Area,Timestamp_sql,Lp_dol,Br,  Style as type_of_home, 'rets_property_condoproperty' as prop_type
            FROM rets_property_condoproperty
            WHERE $where $WHERE_PRICE $WHERE_BEDROOMS $WHERE_PROP $WHERE_PROP_CONDO $AND_MUNICIPALITY
            ) tbl
            GROUP BY CONCAT(Addr,' ',Municipality,' ',County)
            ORDER BY Timestamp_sql DESC
            "; //LIMIT 60

            
    }



    

}else{

    if( $WHERE_PRICE != '' )
        $WHERE_PRICE = "WHERE $WHERE_PRICE";

    $sql = "SELECT * FROM (SELECT DISTINCT Ml_num, Addr,Zip, Ad_text,Municipality,County,Area,Timestamp_sql,Lp_dol,Br, Type_own1_out as type_of_home, 'rets_property_residentialproperty' as prop_type
        FROM rets_property_residentialproperty
        $WHERE_PRICE $WHERE_BEDROOMS AND Type_own1_out LIKE '%".str_replace('|', '', PROP_TYPE)."%' $AND_MUNICIPALITY
        UNION
        SELECT DISTINCT Ml_num, Addr,Zip, Ad_text,Municipality,County,Area,Timestamp_sql,Lp_dol,null as Br, Prop_type as type_of_home, 'rets_property_commercialproperty' as prop_type
        FROM rets_property_commercialproperty
        $WHERE_PRICE AND Type_own1_out LIKE '%".str_replace('|', '', PROP_TYPE)."%' $AND_MUNICIPALITY
        UNION
        SELECT DISTINCT Ml_num, Addr,Zip, Ad_text,Municipality,County,Area,Timestamp_sql,Lp_dol,Br, Style as type_of_home, 'rets_property_condoproperty' as prop_type
        FROM rets_property_condoproperty) tbl
        $WHERE_PRICE $WHERE_BEDROOMS AND Type_own1_out LIKE '%".str_replace('|', '', PROP_TYPE)."%' $AND_MUNICIPALITY
        GROUP BY CONCAT(Addr,' ',Municipality,' ',County)
        ORDER BY Timestamp_sql DESC
        ";// LIMIT 60


}// end search is not empty
//let's write the currect query
file_put_contents( dirname( __FILE__ ) . '/storage/storage_data_query_xyz.txt', $sql );
//output result request
echo $sql;