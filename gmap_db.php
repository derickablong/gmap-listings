<?php
//set version
define('GMAP_HOSTS', false); //if TRUE for local else live
define( 'BLOCK_LOAD', true );
//core class
require_once( 'gmap_core.php' );
//config
require_once( GMAP::hosts( GMAP_HOSTS ) . '/wp-config.php' );
require_once( GMAP::hosts( GMAP_HOSTS ) . '/wp-includes/wp-db.php' );
$wpdb = new wpdb( DB_USER, DB_PASSWORD, DB_NAME, DB_HOST);