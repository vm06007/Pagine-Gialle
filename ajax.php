<?php
include('db.php');
$user_session_id = '1';

if($_POST['item']) { 
  foreach($_POST['item'] as $grid_order=>$grid_id) { if($grid_id>0) {
    mysql_query("UPDATE usergrid_relation SET grid_order = '$grid_order' WHERE grid_id_fk ='$grid_id' and user_id_fk='$user_session_id'");
    }
  }
} 

if($_POST['modules']) { 
  foreach($_POST['modules'] as $grid_order=>$grid_id) { if($grid_id>0) {
    mysql_query("UPDATE usergrid_relation SET grid_order = '$grid_order' WHERE grid_id_fk ='$grid_id' and user_id_fk='$user_session_id'");
    }
  }
}

if($_POST['grid'] && $_POST['toggle']) {
  mysql_query("UPDATE usergrid_relation SET grid_status = ".$_POST['status']." WHERE grid_id_fk = ".$_POST['grid']);
}

if($_POST['grid'] && $_POST['image']) {
  mysql_query("UPDATE grids SET grid_content = '".$_POST['image']."' WHERE grid_id = ".$_POST['grid']);
}


if($_POST['nome'] && $_POST['w'] && $_POST['h'] && $_POST['id']) {

  mysql_query("UPDATE grids SET grid_width = ".$_POST['w']." WHERE grid_id = ".$_POST['id']);
  mysql_query("UPDATE grids SET grid_height = ".$_POST['h']." WHERE grid_id = ".$_POST['id']);
  mysql_query("UPDATE grids SET grid_name = '".$_POST['nome']."' WHERE grid_id = ".$_POST['id']);
    
  
}

?>