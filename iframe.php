<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="content-type" content="text/html;charset=utf-8" />
<title>Facems Item</title>

<style>
body {margin:0px;}
</style>
</head>

<body class="limited">
<?php

$grid_width = 0;
$grid_height = 0;

include("db.php");
$sql = mysql_query("SELECT grid_height, grid_width, link, grid_type, grid_content, grid_type FROM grids where grid_id = ".$_GET['grid']);
while($row = mysql_fetch_array($sql)) {

  $grid_type = $row['grid_type'];
  $grid_contnet = $row['grid_content'];
  $grid_width = $row['grid_width'];
  $grid_height = $row['grid_height'];
  $link = $row['link'];

  switch ($grid_type) {
    case "picture":
        if($link) {echo('<a href="'.$link.'" target="_blank">');}
        echo "<img src='".$grid_contnet."'>";
        if($link) {echo('</a>');}
        break;
    case "component":
        include_once($grid_contnet);
        break;
    case "video":
        ?>
        
      <script src="http://ajax.googleapis.com/ajax/libs/jquery/1.4.2/jquery.min.js"></script>  
      <script src="http://demo.tutorialzine.com/2010/07/youtube-api-custom-player-jquery-css/jquery.swfobject.1-1-1.min.js"></script>
      <script src="http://demo.tutorialzine.com/2010/07/youtube-api-custom-player-jquery-css/youTubeEmbed/youTubeEmbed-jquery-1.0.js"></script>
         
      <link rel="stylesheet" type="text/css" href="http://demo.tutorialzine.com/2010/07/youtube-api-custom-player-jquery-css/youTubeEmbed/youTubeEmbed-jquery-1.0.css" />
      <div id="player"></div>
			<script>
       $('#player').youTubeEmbed({
            video           : '<?php echo($grid_contnet); ?>',
            width           : 520,      // Height is calculated automatically
            progressBar : true     // Hide the progress bar
        });
        </script>
        <?php
        break;
       default:
       echo $grid_contnet;        
}
}

mysql_close();
?>

<style>

.limited {

width: <?php echo $grid_width; ?>px;
height: <?php echo $grid_height; ?>px;
overflow:hidden;

}

</style>

</body>
</html>
