<?php
include "Functions.php";

add_action('admin_menu', 'mokpag_setup_menu');
 
function mokpag_setup_menu(){
        add_menu_page( 'Mokejimu Pagalba plugin', 'Mokejimu pagalba', 'manage_options', 'mokejimu_pagalba', 'mokpag_menu_init' );
        add_submenu_page('mokejimu_pagalba', 'Prideti Paslauga', 'Prideti Paslauga', 'manage_options', 'add_service', 'mokpag_add_serv_form');
        add_submenu_page('mokejimu_pagalba', 'Redaguoti Privilegijas', 'Redaguoti Privilegijas', 'manage_options', 'edit_perms', 'mokpag_edit_perm_form');
        add_submenu_page('mokejimu_pagalba', 'Redaguoti Paslauga', 'Redaguoti Paslauga', 'manage_options', 'edit_service', 'mokpag_edit_service_form');
}

//Generuoja Prideti Paslauga forma ir vygdo uzklausas
function mokpag_add_serv_form() {
    
        echo '<h1>Prideti Paslauga</h1>';
    
        echo '<form action="'.get_admin_url().'admin.php?page=add_service" method="POST">';
        
        echo 'Pavadinimas (Bus rodomas kaip pagrindinis):<br>';
        echo '<input type="text" name="name" value=""><br><br>';
        
        echo 'PEx Pavadinimas (Bus naudojamas ieskoti privilegiju):<br>';
        echo '<input type="text" name="pex_name" value=""><br><br>';
        
        echo 'Aprasimas:<br>';
        echo '<input type="text" name="desc" value=""><br><br>';
        
        echo 'Kaina Eurais:<br>';
        echo '<input type="text" name="price_eu" value="0.0"><br><br>';   
        
        echo 'Kaina Litais:<br>';
        echo '<input type="text" name="price_lt" value="0.0"><br><br>';
        
        echo '<input type="hidden" name="save" value="1">';
        echo '<input type="submit" value="Prideti">';
        
        echo '</form>';
        
        echo '<br><br>';
        if(isset($_POST['save']) && $_POST['save'] == 1)
            save_service($_POST['name'],$_POST['pex_name'],$_POST['desc'],$_POST['price_eu'],$_POST['price_lt']);
}

//Generuoja Redaguoti Privilegijas forma ir vygdo uzklausas
function mokpag_edit_perm_form() {
    echo "<h1>Paslaugos privilegiju redagavimas</h1><br>";
    
    if(!isset($_POST['service_id']))//veikia nepasirinkus privilegijos
    {
        echo "Norint redaguoti privilegiju pateikima pasirinkite paslauga:<br><br>";
        gen_service_table();
    }
    else if(isset($_POST['service_id']) && isset($_POST['look_up_pex']))//atnaujina privilegijas pagal pex
    {
        $service = get_service_by_id($_POST['service_id']);
        
        echo "Pasirinkta paslauga: <b>".$service->get_name()."</b><br><br>";
        echo "<b>PEx privilegijos atnaujintos</b> <br><br>";
        
        $service->look_up_pex_permissions();
        
        echo "<form action='".get_admin_url()."admin.php?page=edit_perms' method='POST'>"
                ."<input type='hidden' name='service_id' value='".$service->get_service_id()."'>"
                ."<input type='submit' value='ATGAL'></form>";
        
    }
    else if(isset($_POST['service_id']) && isset($_POST['edit_perm']) && !isset($_POST['save_edit']))//redaguoja privilegija
    {
        echo "Redaguojamos privilegijos ID: <b>".$_POST['perm_id']."</b><br><br>";
        $service = get_service_by_id($_POST['service_id']);
        $perm = $service->get_perm_by_id($_POST['perm_id']);

        echo "<form action='".get_admin_url()."admin.php?page=edit_perms' id='edit_perm' method='POST'>"
            ."<input type='hidden' name='service_id' value='".$service->get_service_id()."'>"
            ."<input type='hidden' name='perm_id' value='".$perm->get_perm_id()."'>"
            ."<input type='hidden' name='edit_perm' value='1'>"
            ."<input type='hidden' name='save_edit' value ='1'>"
            ."Privilegijos ID:<br>"
            ."<input type='number' name='perm_id' value='".$perm->get_perm_id()."'><br><br>"
            ."Paslaugos ID:<br>"
            ."<input type='number' name='service_id' value='".$perm->get_service_id()."'><br><br>"
            ."Pavadinimas:<br>"
            ."<input type='text' name='name' value='".$perm->get_name()."'><br><br>"
            ."Aprasymas (none - rodomas tik pavadinimas vietoj aprasymo):<br>"
            ."<input type='text' name='desc' value='".$perm->get_desc()."'><br><br>"
            ."Ar Rodyti?:<br>"
            ."<select name='show' form='edit_perm'>
                <option value='1'>Taip</option>
                <option value='0'>NE</option></select><br><br>"
            ."<input type='submit' value='REDAGUOTI'></form><br>";
    }
    else if(isset($_POST['service_id']) && isset($_POST['edit_perm']) && isset($_POST['save_edit']))//issaugo pakeitimus i db
    {
        global $wpdb;
    
        $table_name = $wpdb->prefix.'mokpag_permissions';
    
        $wpdb->get_var("UPDATE `".$table_name."` SET `perm_id`= '".$_POST['perm_id']."',"
                        ."`service_id`= '".$_POST['service_id']."',`name`='".$_POST['name']."',`description`='".$_POST['desc']."',"
                        ."`show_perm`='".$_POST['show']."' WHERE `perm_id`='".$_POST['perm_id']."'");
        
        echo "<b>Pakeitimai issaugoti</b>";
        
        echo "<form action='".get_admin_url()."admin.php?page=edit_perms' method='POST'>"
            ."<input type='hidden' name='service_id' value='".$_POST['service_id']."'>"
            ."<input type='hidden' name='perm_id' value='".$_POST['perm_id']."'>"
            ."<input type='hidden' name='edit_perm' value='1'>"
            ."<input type='submit' value='GRYSTI I TOS PACIOS PRIVILEGIJOS REDAGAVIMA'></form><br>";
         echo "<form action='".get_admin_url()."admin.php?page=edit_perms' method='POST'>"
            ."<input type='hidden' name='service_id' value='".$_POST['service_id']."'>"
            ."<input type='submit' value='GRYSTI I VISU PRIVILEGIJU REDAGAVIMA'></form><br>";
    }
    else if(isset($_POST['service_id']) && isset($_POST['perm_id']) && isset($_POST['delete_perm']))//panaikina privilegija is db
    {
        global $wpdb;
    
        $table_name = $wpdb->prefix.'mokpag_permissions';
    
        $wpdb->get_var("DELETE FROM `".$table_name."` WHERE `perm_id` = '".$_POST['perm_id']."'");
        
        echo "<b>Privilegija isrinta!</b>";
        echo "<form action='".get_admin_url()."admin.php?page=edit_perms' method='POST'>"
            ."<input type='hidden' name='service_id' value='".$_POST['service_id']."'>"
            ."<input type='submit' value='GRYSTI I VISU PRIVILEGIJU REDAGAVIMA'></form><br>";
    }
    else//parodo visas privilegijas
    {
        $service = get_service_by_id($_POST['service_id']);
        
        echo "Pasirinkta paslauga: <b>".$service->get_name()."</b><br><br>";
        echo "<b>Patikrinti pasikeitimus PEx privilegijose: <br>";
        echo "<form action='".get_admin_url()."admin.php?page=edit_perms' method='POST'>"
                ."<input type='hidden' name='service_id' value='".$service->get_service_id()."'>"
                ."<input type='hidden' name='look_up_pex' value='1'>"
                ."<input type='submit' value='PATIKRINTI'></form><br>";
        
        $service->load_perms();
        $perms = $service->get_perms();
        
        echo "<table>";
        echo "<tr>
                  <th>Privilegijos ID</th>
                  <th>Paslaugos ID</th>
                  <th>Pavadinimas</th>
                  <th>Aprasymas</th>
                  <th>Rodyti</th>
                  <th>REDAGUOTI PRIVILEGIJA</th>
                  <th>TRINGTI PRIVILEGIJA</th>
              </tr>";
        
        foreach($perms as $perm)
        {
            echo "<tr>
                    <th>".$perm->get_perm_id()."</th>
                    <th>".$perm->get_service_id()."</th>
                    <th>".$perm->get_name()."</th>
                    <th>".$perm->get_desc()."</th>
                    <th>";
            if($perm->get_show() == true)
                echo "TAIP</th>";
            else
                echo "NE</th>";
            echo "<th><form action='".get_admin_url()."admin.php?page=edit_perms' method='POST'>"
                 ."<input type='hidden' name='service_id' value='".$service->get_service_id()."'>"
                 ."<input type='hidden' name='edit_perm' value='1'>"
                 ."<input type='hidden' name='perm_id' value='".$perm->get_perm_id()."'>"
                 ."<input type='submit' value='REDAGUOTI'></form></th>";
            echo "<th><form action='".get_admin_url()."admin.php?page=edit_perms' method='POST'>"
                 ."<input type='hidden' name='service_id' value='".$service->get_service_id()."'>"
                 ."<input type='hidden' name='delete_perm' value='1'>"
                 ."<input type='hidden' name='perm_id' value='".$perm->get_perm_id()."'>"
                 ."<input type='submit' value='TRINTI'></form></th>
                 </tr>";
        }
        echo "</table>";
    }
}

function mokpag_edit_service_form()
{
    echo "<h1>Paslaugos redagavimas</h1><br>";

    if(isset($_POST['save_edit']))
    {
        edit_service($_POST['service_id'], $_POST['name'], $_POST['pex_name'], $_POST['desc'], $_POST['price_eu'], $_POST['price_lt']);
        
        echo '<b>Paslauga sekmingai redaguota!</b>';
        
        echo "<form action='".get_admin_url()."admin.php?page=edit_service' method='POST'>"
                ."<input type='hidden' name='service_id' value='".$_POST['service_id']."'>"
                ."<input type='submit' value='GRYZTI I PASLAUGOS REDAGAVIMA'></form><br>";
        
        echo "<form action='".get_admin_url()."admin.php?page=edit_service' method='POST'>"
                ."<input type='submit' value='GRYZTI I VISU PASLAUGU REDAGAVIMA'></form>";
    }
    else if (isset ($_POST['service_id']) && !isset($_POST['delete_service']))
    {   
        $service = get_service_by_id($_POST['service_id']);
    
        echo "Pasirinkta paslauga: <b>".$service->get_name()."<br><br>";
        echo '<form action="'.get_admin_url().'admin.php?page=edit_service" method="POST">';
        
        echo '<input type="hidden" name="service_id" value="'.$service->get_service_id().'">';
        
        echo 'Pavadinimas (Bus rodomas kaip pagrindinis):<br>';
        echo '<input type="text" name="name" value="'.$service->get_name().'"><br><br>';
        
        echo 'PEx Pavadinimas (Bus naudojamas ieskoti privilegiju):<br>';
        echo '<input type="text" name="pex_name" value="'.$service->get_pex_name().'"><br><br>';
        
        echo 'Aprasimas:<br>';
        echo '<input type="text" name="desc" value="'.$service->get_desc().'"><br><br>';
        
        echo 'Kaina Eurais:<br>';
        echo '<input type="text" name="price_eu" value="'.$service->get_priceEu().'"><br><br>';   
        
        echo 'Kaina Litais:<br>';
        echo '<input type="text" name="price_lt" value="'.$service->get_priceLt().'"><br><br>';
        
        echo '<input type="hidden" name="save_edit" value="1">';
        echo '<input type="submit" value="REDAGUOTI">';
        
        echo '</form>';
    }
    else if (isset ($_POST['service_id']) && isset($_POST['delete_service']))
    {
        delete_service($_POST['service_id']);
        
        echo "<b>Paslauga istrinta!</b>";
        echo "<form action='".get_admin_url()."admin.php?page=edit_service' method='POST'>"
                ."<input type='submit' value='GRYZTI I VISU PASLAUGU REDAGAVIMA'></form>";
    }
    else
    {
        echo "Norint redaguoti paslauga ja pasirinkite:<br><br>";
        gen_service_table();
    }
      
}

//Ideda Service i Mysql
function save_service($name,$pex_name,$desc,$price_eu,$price_lt)
{
    global $wpdb;
    
    $table_name = $wpdb->prefix.'mokpag_service';
    
    $wpdb->get_var("INSERT INTO ".$table_name."(name, description,pex_name, price_lt,price_eu)
                              VALUES ('".$name."','".$desc."','".$pex_name."',".$price_lt.",".$price_eu.")");
    
    echo "Sekmingai prideta paslauga <b>".$name."</b>";
}

function edit_service($id,$name,$pex_name,$desc,$price_eu,$price_lt)
{
    global $wpdb;
    
    $table_name = $wpdb->prefix.'mokpag_service';
    
    $wpdb->get_var("UPDATE `".$table_name."` SET `name`='".$name."',`description`='".$desc."',`pex_name`='".$pex_name."',"
                        ."`price_lt`='".$price_lt."', `price_eu`='".$price_eu."' WHERE `service_id`='".$id."'");
}

function delete_service($id)
{
    global $wpdb;
    
    $table_name = $wpdb->prefix.'mokpag_permissions';
    
    $wpdb->get_var("DELETE FROM `".$table_name."` WHERE `service_id` = '".$_POST['service_id']."'");
    
    $table_name = $wpdb->prefix.'mokpag_service';
    
    $wpdb->get_var("DELETE FROM `".$table_name."` WHERE `service_id` = '".$_POST['service_id']."'");
}

//Pagr admin langas
function mokpag_menu_init(){
    
        echo "<h1>Mokejimu Pagalba</h1>";
        echo "<br>";
        echo "Visos sukurtos paslaugos:<br><br>";
        
        gen_service_table();
        
        echo "<br><form action='".get_admin_url()."admin.php?page=add_service' method='POST'>"
                ."<input type='submit' value='PRIDETI PASLAUGA'></form>";
        
}

//Sugeneruoja service lentele is mysql
function gen_service_table(){
    echo "<table>";
    echo "<tr>
            <th>Id</th>
            <th>Pavadinimas</th>
            <th>Aprasymas</th>
            <th>PEx Pavadinimas</th>
            <th>Kaina Lt</th>
            <th>Kaina Eu</th>
            <th>REDAGUOTI PRIVILEGIJAS</th>
            <th>REDAGUOTI PASLAUGA</th>
            <th>TRINTI PASLAUGA</th>
         </tr>";
    
    global $wpdb;
    
    $table_name = $wpdb->prefix.'mokpag_service';
    
    $results = $wpdb->get_results("SELECT * FROM ".$table_name);
    
    foreach($results as $result)
    {
        echo "<tr>";
        echo "<th>".$result->service_id."</th>";
        echo "<th>".$result->name."</th>";
        echo "<th>".$result->description."</th>";
        echo "<th>".$result->pex_name."</th>";
        echo "<th>".$result->price_lt."</th>";
        echo "<th>".$result->price_eu."</th>";
        echo "<th><form action='".get_admin_url()."admin.php?page=edit_perms' method='POST'>"
                ."<input type='hidden' name='service_id' value='".$result->service_id."'>"
                ."<input type='submit' value='REDAGUOTI PRIVILEGIJAS'></form></th>";
        echo "<th><form action='".get_admin_url()."admin.php?page=edit_service' method='POST'>"
                ."<input type='hidden' name='service_id' value='".$result->service_id."'>"
                ."<input type='submit' value='REDAGUOTI PASLAUGA'></form></th>";
        echo "<th><form action='".get_admin_url()."admin.php?page=edit_service' method='POST'>"
                ."<input type='hidden' name='service_id' value='".$result->service_id."'>"
                ."<input type='hidden' name='delete_service' value ='1'>"
                ."<input type='submit' value='TRINTI PASLAUGA'></form></th>";
        echo "</tr>";
    }
    
    echo "</table>";
}
?>

