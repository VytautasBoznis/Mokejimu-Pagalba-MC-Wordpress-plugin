<?php

/**
 * Description of Service
 *
 * @author Ideo
 */
class Service {
    
    var $service_id,$name,$pex_name,$description,$priceEu,$priceLt;
    var $permissions = array();
    
    public function __construct($service_id = -1,$name = "",$pex_name = "", $desc = "", $priceEu = 0.0, $priceLt = 0.0, $perm = array()) 
    {
        $this->service_id = $service_id;
        $this->name = $name;
        $this->pex_name = $pex_name;
        $this->description = $desc;
        $this->priceEu = $priceEu;
        $this->priceLt = $priceLt;
        $this->permissions = $perm;
    }
    
    public function set_service_id($service_id)
    {
        $this->service_id = $service_id;
    }
    
    public function get_service_id()
    {
        return $this->service_id;
    }    
    
    public function set_name($name)
    {
        $this->name = $name;
    }
    
    public function get_name()
    {
        return $this->name;
    }
    
    public function set_pex_name($pex_name)
    {
        $this->pex_name = $pex_name;
    }
    
    public function get_pex_name()
    {
        return $this->pex_name;
    }
    
    public function set_desc($desc)
    {
        $this->description = $desc;
    }
    
    public function get_desc()
    {
        return $this->description;
    }
    
    public function set_priceEu($priceEu)
    {
        $this->priceEu = $priceEu;
    }
    
    public function get_priceEu()
    {
        return $this->priceEu;
    }
    
    public function set_priceLt($priceLt)
    {
        $this->priceLt = $priceLt;
    }
    
    public function get_priceLt()
    {
        return $this->priceLt;
    }
    
    public function set_perms($perms)
    {
        $this->permissions = $perms;
    }
    
    public function get_perms()
    {
        return $this->permissions;
    }
    
    public function save_to_mysql()
    {
        
    }
    
    public function load_perms()
    {
        global $wpdb;
       
        $this->permissions = array();
        $table_name = $wpdb->prefix.'mokpag_permissions';

        $results = $wpdb->get_results("SELECT * FROM  `".$table_name."` WHERE  `service_id` =  '".$this->service_id."'");
    
        foreach($results as $result)
        {
            $perm = new Permission($result->perm_id, $result->service_id, $result->name, $result->description, $result->show_perm);
            array_push($this->permissions, $perm); 
        }
    }
    
    public function look_up_pex_permissions()
    {
        //NON Wp db login
        $settings = get_option('mokpag_options');
        $mysqli = new mysqli($settings['mc_mysql_host'], $settings['mc_mysql_user'], $settings['mc_mysql_pass'], $settings['mc_mysql_db']);
        
        if ($mysqli->connect_errno) {
            printf("Connect failed: %s\n", $mysqli->connect_error);
            exit();
        }
        
        $result = $mysqli->query("SELECT * FROM ".$settings['mc_mysql_table']." WHERE `name` =  '".$this->pex_name."'");
        
        if(!$result)
            echo "error";
        
        //WP db con
        global $wpdb;
        
        
        while($row = $result->fetch_array())
        {
            $skip = false;
            $this->load_perms();
            
            foreach($this->permissions as $permission)
            {
                if(strcasecmp($permission->get_name(), $row['permission']) == 0 )
                {
                    $skip = true;
                }
            }
            
            if(!$skip)
            {
                $table_name = $wpdb->prefix.'mokpag_permissions';
    
                $wpdb->get_var("INSERT INTO ".$table_name."(`service_id`, `name`, `description`, `show_perm`)
                                VALUES ('".$this->service_id."','".$row['permission']."','none','1')");
            }
        }
        
        $result->close();
        $mysqli->close();
    }
    
    public function get_perm_by_id($id)
    {
        $this->load_perms();
        
        foreach($this->permissions as $perm)
        {
            if($perm ->get_perm_id() == $id)
                return $perm;
        }
    }
    
}
