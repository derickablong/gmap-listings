<?php
$i = 1;
$folder = ABSPATH.'wp-content/uploads/property';
$ml_num = explode('||', $_SESSION['ML_NUMBER']);
$ml_num = array_filter($ml_num);

//record progress
GMAP::record_progress( 'residential_progress', $_SESSION['ML_TOTAL'] );
if((int)$_SESSION['ML_TOTAL'] > 0) {
    foreach($ml_num as $key => $value){
        
        $sysid = $value;

        GMAP::progress( 'photos', count($ml_num), $i );
        
        $n = 1;
        if(!is_dir($folder)) mkdir($folder);
        $dir = $folder . '/' . $sysid;
        if(!is_dir($dir)) mkdir($dir);

        $photos = $rets->GetObject("Property", "Photo", $sysid);
        foreach($photos as $photo) {
            file_put_contents($dir.'/'.$n.'.jpg', $photo['Data']);
            $n++;
        }

        $i++; 
        
    }
} else {
    GMAP::progress( 'photos', 0, 0 );
}

//next will be the latlong
require_once dirname( __FILE__ ) . '/gmap_grab_latlong.php';