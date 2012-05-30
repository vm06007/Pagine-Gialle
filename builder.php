<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<title>Layout</title>

	<style>
	body  
	{
    margin:0px; padding:0px;
	}
	ul { list-style-type: none; margin: 0; padding: 0;} 
	li {  border:solid 0px #333; height:150px; float:left; font-size:26px; text-align:center;  

	}

	#container
	{
	width:520px; 
	}
	#header
	{

	}
	#main
	{
	overflow:auto;
	}


	</style>
</head>

<body>
<div id="container">

<ul>
<?php

	
include("db.php");
$sql=mysql_query("SELECT b.grid_id,b.grid_name,b.grid_width,b.grid_height, b.grid_content, b.grid_type FROM logon a, grids b, usergrid_relation c where a.userid=c.user_id_fk and c.grid_status = 1 and b.grid_id=c.grid_id_fk and a.userid='1' order by c.grid_order;");
while($row=mysql_fetch_array($sql))
{
$grid_id=$row['grid_id'];
$grid_name=$row['grid_name'];
$grid_width=$row['grid_width'];
$grid_height=$row['grid_height'];
 
	?>
	<li class="ui-state-default" style="width: <?php echo $grid_width;?>px; height: <?php echo $grid_height;?>px; overflow:hidden;" id="item-<?php echo $grid_id;?>">
  <iframe src="iframe.php?grid=<?php echo($grid_id) ?>" frameborder="0" scrolling="no" width="<?php echo $grid_width;?>px" height="<?php echo $grid_height;?>px">wefwef</iframe>
  </li>
	   
	<?php	} mysql_close(); ?>
</ul>
</div>

		 <script src="http://connect.facebook.net/en_US/all.js"></script>
		 <script>
     	 
		  FB.Canvas.setSize();
		 
		 </script>


</body>
</html>
