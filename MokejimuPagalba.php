<?php

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

/*
Plugin Name: Mokejimu pagalba
Plugin URI: http://mc.skilas.lt
Description: Informacija apie galimus nusipirkti mokejimus pluginas
Version: 0.0.1
Author: Ideo
Author URI: http://skilas.lt
License: Private
Text Domain: mokejimu pagalba
*/

register_activation_hook( __FILE__, 'mokpag_install' );

//Activation
function mokpag_install() {
    
    //Default mysql configs for PEX db
    $mokpag_default = array(
        'mc_mysql_host' => 'localhost',
        'mc_mysql_db' => 'mcgame',
        'mc_mysql_user' => 'root',
        'mc_mysql_pass' => 'neatspesi',
        'mc_mysql_table' => 'permissions'
    );
    
    update_option('mokpag_options',$mokpag_default);
    
    //Creating plugin Mysql db

    global $wpdb;

    //add service db
    $table_name = $wpdb->prefix.'mokpag_service';
	
    $charset_collate = $wpdb->get_charset_collate();

    $service_sql = "CREATE TABLE $table_name (
    	service_id mediumint(9) NOT NULL AUTO_INCREMENT,
	name varchar(55) DEFAULT '' NOT NULL,
	description varchar(55) DEFAULT '' NOT NULL,
       	pex_name varchar(55) DEFAULT '' NOT NULL,
        price_lt double DEFAULT 0.0 NOT NULL,
        price_eu double DEFAULT 0.0 NOT NULL,
        UNIQUE KEY id (service_id)
    ) $charset_collate;";

    require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
    dbDelta( $service_sql );
    
    //add permission db 
    $table_name = $wpdb->prefix.'mokpag_permissions';

    $sql = "CREATE TABLE $table_name (
    	perm_id mediumint(9) NOT NULL AUTO_INCREMENT,
        service_id mediumint(9) NOT NULL,
	name varchar(55) DEFAULT '' NOT NULL,
	description varchar(55) DEFAULT '' NOT NULL,
        show_perm tinyint DEFAULT 0 NOT NULL,
        UNIQUE KEY id (perm_id)
    ) $charset_collate;";

    require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
    dbDelta( $sql );

    register_uninstall_hook(__FILE__, 'mokpag_uninstall');
}

//Diactivation
function mokpag_uninstall(){
    delete_option('mokpag_options');
}

add_shortcode( 'servicecheck', 'mokpag_check' );
// The callback function that will replace [book]
function mokpag_check() {
    wp_enqueue_script( 'mokpag_script', plugin_dir_url( __FILE__).'js/script.js' );
    wp_register_style( 'prefix-style', plugin_dir_url( __FILE__).'css/style.css' );
    wp_enqueue_style( 'prefix-style' );
    
    $form="<center><div class='mok_pag_services' id='wrap'>";
    
    global $wpdb;
    
    $table_name = $wpdb->prefix.'mokpag_service';
    
    $results = $wpdb->get_results("SELECT * FROM ".$table_name);
    
    $i=0;
    foreach($results as $result)
    {
        $service = get_service_by_id($result->service_id);
        $service->load_perms();
        
        $form .= "<div class='service' id='service_".$i++."'>"
                ."<div class='service_name'>".$service->get_name()."</div><br>"
                ."<div class='service_desc'>".$service->get_desc()."</div><br>"
                ."<div class='service_price'>Kaina: <b>".$service->get_priceLt()." Lt / ".$service->get_priceEu()." Eu</b></div><br>"
                ."<div class='service_perms'>Privilegijos:<ul>";
        
        foreach($service->get_perms() as $perm)
        {
            
            if($perm->get_show() == 1)
            {
                $form .= "<li>";
                if(strcasecmp($perm->get_desc(),'none') == 0 || strcasecmp($perm->get_desc(),' ') ==0)
                    $form .= $perm->get_name();
                else
                    $form .= $perm->get_desc();
                $form .= "</li>";
            }
            
        }
            $form .= "</ul></div></div>";
                 
    }
    
return $form;
}

include 'AdminPanel.php';

?>