<?php
include 'Service.php';
include 'Permission.php';

function get_service_by_id($id)
{
    global $wpdb;        
    $table_name = $wpdb->prefix.'mokpag_service';
    
    $results = $wpdb->get_results("SELECT * FROM  `".$table_name."` WHERE  `service_id` =  '".$id."'");
    
    foreach($results as $result)
        $service = new Service($result->service_id, $result->name, $result->pex_name, $result->description, $result->price_eu, $result->price_lt);

    return $service;    
}

