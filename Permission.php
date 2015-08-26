<?php

/**
 * Description of Permission
 *
 * @author Ideo
 */
class Permission {
   
    var $perm_id,$service_id,$name,$desc,$show;
    
    public function __construct($perm_id = -1,$service_id = -1,$name = "",$desc = "", $show = false) {
        
        $this->perm_id = $perm_id;
        $this->service_id = $service_id;
        $this->name = $name;
        $this->desc = $desc;
        $this->show = $show;
    }
    
    public function set_perm_id($perm_id)
    {
        $this->perm_id = $perm_id;
    }
    
    public function get_perm_id()
    {
        return $this->perm_id;
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
    
    public function set_desc($desc)
    {
        $this->desc = $desc;
    }
    
    public function get_desc()
    {
        return $this->desc;
    }
    
    public function set_show($show)
    {
        $this->show = $show;
    }
    
    public function get_show()
    {
        return $this->show;
    }
    
    public function save_to_mysql()
    {
        
    }
}
