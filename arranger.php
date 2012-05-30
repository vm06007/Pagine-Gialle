<?php

//VITALLY MARINCHENKO
//vitally.marinchenko@gmail.com

ini_set('display_errors',1);
include('igniter.php'); 
error_reporting(E_ALL);

$ignite = new Igniter();
$ignite->connect();

session_start();

if($ignite->logincheck(@$_SESSION['loggedin'], 'logon', 'password', 'useremail') == false) { 
  if(isset($_REQUEST['action']) && $_REQUEST['action'] == "login") { echo(1);
     if($ignite->login("logon", $_REQUEST['username'], $_REQUEST['password']) == true) { 
       //do something on successful login
    } else $ignite->redirectPage('login.php');
  } else $ignite->redirectPage('login.php');
}

if(isset($_REQUEST['action']) && $_REQUEST['action'] == "logout") {
 $ignite->logout();
  $ignite->redirectPage('login.php');
}


if(isset($_POST['idvalue']) && isset($_POST['smallinput1']) && isset($_POST['smallinput2']) && isset($_POST['mediuminput1'])) { 
$sql = mysql_query("UPDATE grids SET grid_width = ".$_POST['smallinput1']." WHERE grid_id = ".$_POST['idvalue']);
$sql = mysql_query("UPDATE grids SET grid_height = ".$_POST['smallinput2']." WHERE grid_id = ".$_POST['idvalue']);
$sql = mysql_query("UPDATE grids SET grid_name = '".$_POST['mediuminput1']."' WHERE grid_id = ".$_POST['idvalue']);
$sql = @mysql_query("UPDATE grids SET link = '".$_POST['mediuminput3']."' WHERE grid_id = ".$_POST['idvalue']);
//echo("UPDATE grids SET grid_name = '".$_POST['mediuminput1']."' WHERE grid_id = ".$_POST['idvalue']);
}

function yTubeId($url){
	if (preg_match('%(?:youtube\.com/(?:[^/]+/.+/|(?:v|e(?:mbed)?)/|.*[?&]v=)|youtu\.be/)([^"&?/ ]{11})%i', $url, $match)) {
    $video_id = $match[1];
    return $video_id;
  }
}

if(isset($_POST['mediuminput2'])) {
$url = $_POST['mediuminput2'];
$yt_id = yTubeId($url);
$cc = 'http://www.youtube.com/watch?v='.$yt_id;
$sql = mysql_query("UPDATE grids SET grid_content = '".$cc."' WHERE grid_id = ".$_POST['idvalue']);
}        

?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">

<html xmlns="http://www.w3.org/1999/xhtml">
 <head>


<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.4.2/jquery.min.js"></script> 
<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jqueryui/1.8.2/jquery-ui.min.js" ></script> 
<script>
    $(document).ready(function(){
        $('input').bind('change', function(){
            //var oldvalue = ???
            var newvalue = $(this).val();
            alert(newvalue);
        });
    });
</script>
	<script>

function saveForm(id) {


var w = document.getElementById('smallinput1'+id).value;
var h = document.getElementById('smallinput2'+id).value;
var nome = document.getElementById('mediuminput1'+id).value;
alert(w)
alert(h)
          $.ajax({
            type: "POST",
            url: "ajax.php",
            data: 'id='+id+'&w='+w+'&h='+h+'&nome='+nome,
            
            success: function(data) {}
          });

}
  
  
  

	$(function() 
	{

  $('.status').click(function() { 
   
       if($(this).parent().parent().attr('class') == 'ui-state-default disabled') {
        
         $(this).parent().parent().attr('class','ui-state-default enabled')
         $(this).attr('title','Enable Item') 
          
          $.ajax({
            type: "POST",
            url: "ajax.php",
            data: 'status=1&toggle=1&grid=' + $(this).attr('id'),
            
            success: function(data) {}
          });

       }
       
       else {
          $(this).parent().parent().attr('class','ui-state-default disabled')
          $(this).attr('title','Disable Item') 
          
          $.ajax({
            type: "POST",
            url: "ajax.php",
            data: 'status=0&toggle=1&grid=' + $(this).attr('id'),
            success: function(data) {}
          });
       
      
      }
  
  });


$("input[type='text']").change( function() {
  alert($(this).val()) 
});
	
$('.savior').click(function()
	  {
     alert(1);
    });
    
  
  	$('.cancel').click(function()
	  {
      $("#sortable").sortable('cancel');
    });
    
    
	  $('.save').click(function()
	  {
	  
	   
	   //alert($("#sortable").sortable("serialize"));

    $.ajax({
      type: "POST",
      url: "ajax.php",
      data: $("#sortable").sortable("serialize"),
      
      success: function(data)
    {
      //alert(data); 
      $('.flash').fadeIn('slow');
      //$('.flash').html("Updated")
    }
    
    });

  $("#sortable").sortable("refresh");
  	$( "#sortable" ).sortable({
		revert: true,
		forcePlaceholderSize: true,
		cancel: 'button',
		handle: '.dragable',
		cursor: 'move',
		opacity: 0.77,
		zIndex: 5
	 
		
		});

  setTimeout(function(){
  $(".flash").fadeOut("slow", function () {  
  $(".flash").hide();
      }); }, 2000);

	  });
	
		$( "#sortable" ).sortable({
		revert: true,
		forcePlaceholderSize: true,
		cancel: 'button',
		handle: '.dragable',
		cursor: 'move',
		opacity: 0.77,
		zIndex: 5
	 
		
		});
		
		$( "ul, li, h2, font, ").disableSelection();
	});
	
	
	
	</script>

		
		<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
		
		<title>Facems Admin</title>
		
		<link rel="stylesheet" href="resources/css/reset.css" type="text/css" media="screen" />
		<link rel="stylesheet" href="resources/css/style.css" type="text/css" media="screen" />
		<link rel="stylesheet" href="resources/css/invalid.css" type="text/css" media="screen" />	
		
		<!--[if lte IE 7]>
			<link rel="stylesheet" href="resources/css/ie.css" type="text/css" media="screen" />
		<![endif]-->
  

		<script type="text/javascript" src="resources/scripts/simpla.jquery.configuration.js"></script>
		<script type="text/javascript" src="resources/scripts/facebox.js"></script>
		<script type="text/javascript" src="resources/scripts/jquery.wysiwyg.js"></script>
		<script type="text/javascript" src="resources/scripts/jquery.datePicker.js"></script>
		<script type="text/javascript" src="resources/scripts/jquery.date.js"></script>

		<!--[if IE]>
    <script type="text/javascript" src="resources/scripts/jquery.bgiframe.js"></script>
    <![endif]-->
		
		<!--[if IE 6]>
			<script type="text/javascript" src="resources/scripts/DD_belatedPNG_0.0.7a.js"></script>
			<script type="text/javascript">
				DD_belatedPNG.fix('.png_bg, img, li');
			</script>
		<![endif]-->
		
		
			<style>

  #s h2 { cursor: move;}
	#s ul { list-style-type: none; margin: 0; padding: 0; margin-bottom: 10px; } 
	#s li { width: 250px;  border:solid 0px #333; height:150px; float:left; font-size:26px; text-align:center;  
box-shadow:0px 0px 10px #AAC599;
-moz-box-shadow:0px 0px 10px #AAC599;
-webkit-box-shadow:0px 0px 10px #AAC599;

background-color:#f2f2f2;
display:block;

	
	
	}
	
	
	#container img {padding:1px;}
	#container
	{
	width:520px; margin-left:165px; margin-top:100px; 
	}
	#header
	{
	height:90px;
	background-color:#f2f2f2;
	}
	.cancel
	{
	background-color:#999;
	padding:6px;
	font-weight:bold;
	
	color:#fff;
	cursor:pointer;
	font-size:18px;
	border-radius: 6px;-moz-border-radius:6px;-webkit-border-radius:6px;
	margin-bottom:20px;
		
	}

	.save
	{
	background-color:#999;
	padding:6px;
	font-weight:bold;
	
	color:#fff;
	cursor:pointer;
	font-size:18px;
	border-radius: 6px;-moz-border-radius:6px;-webkit-border-radius:6px;
	margin-bottom:20px;
		
	}
	.flash
	{

	padding:8px;
	display:none;

	}



#s ul li {

padding:0px !important;
background-image:none;
background-color: #fff;
}

.info {
background-image: url(resources/images/icons/information.png);
cursor: pointer;
margin-top:10px; 
float: right;
margin-left:1px;
width: 16px;
height: 16px;
overflow:hidden;
}

.delete {
background-image: url(resources/images/icons/cross_circle.png);
cursor: pointer;
margin-top:10px;
margin-right:10px; 
float: right;
margin-left:1px;
width: 16px;
height: 16px;
overflow:hidden;
}


.enabled h2 { color:#333; cursor: move; }
.disabled h2 { color:#999; cursor: move; }


.enabled .dragable { background-color:#fff;}
.disabled .dragable { background-color:#eee;}

.enabled .status {
background-image: url(resources/images/icons/tick_circle.png);
margin-top:10px;
margin-left:1px; 
cursor: pointer;
float: right;
width: 16px;
height: 16px;
overflow:hidden;
}


.disabled .status {
background-image: url(resources/images/icons/tick_circle_gray.png);
margin-top:10px;
margin-left:1px; 
cursor: pointer;
float: right;
width: 16px;
height: 16px;
overflow:hidden;
}

	</style>
</head>
		
	</head>
  
	<body><div id="body-wrapper"> <!-- Wrapper for the radial gradient background -->
		
		<div id="sidebar"><div id="sidebar-wrapper"> <!-- Sidebar with logo and menu -->
			
			<h1 id="sidebar-title"><a href="index.php">Facems Admin</a></h1>
		  
		  
		  
			<!-- Logo (221px wide) -->
			<a href="index.php"><img id="logo" src="resources/images/logo.png" alt="Simpla Admin logo" /></a>
		  
			<!-- Sidebar Profile links -->
			<div id="profile-links">
				Hello, <a href="#" title="Edit your profile"><?php echo $_SESSION['name']; ?></a> you have <a href="#messages" rel="modal" title="1 Messages">1 Messages</a><br />
				<br />
				<a href="http://www.facebook.com/pages/Hendymendy/292942664068311?sk=app_114940221954013" target="_blank" title="View the Site">View the Site</a> | <a href="?action=logout" title="Sign Out">Sign Out</a>
			</div>        
			
			<ul id="main-nav">  <!-- Accordion Menu -->
				
				<li>
					<a href="index.php" class="nav-top-item no-submenu "> <!-- Add the class "no-submenu" to menu items with no sub menu -->
						Dashboard
					</a>       
				</li>
				
				<li> 
					<a href="#" class="nav-top-item current" > <!-- Add the class "current" to current menu item -->
					Layout Manager
					</a>
					<ul>
					  <li><a href="arranger.php" class="current">Edit Layout</a></li> <!-- Add class "current" to sub menu items also -->
						<li><a href="#messages" rel="modal" title="1 Messages">Create Layout</a></li>
						<!-- <li><a href="#">Modules</a></li> -->
					</ul>
				</li>
				
			<li>
					<a  href="#" class="nav-top-item">
						Media Library
					</a>
					<ul>
						<li><a href="#explorer" rel="modal" title="Explorer">Explorer</a></li>
					<!--	<li><a href="#">Manage Galleries</a></li>
						<li><a href="#">Manage Albums</a></li>
						<li><a href="#">Gallery Settings</a></li> -->
					</ul>
				</li>
						
				<li>
					<a href="#" class="nav-top-item-disabled">
						Page Publisher
					</a>
					<ul>
						<li><a href="#">Create a new Page</a></li>
						<li><a href="#">Edit Pages</a></li>
					</ul>
				</li>
				
				<li>
					<a href="#" class="nav-top-item-disabled">
						Client Management
					</a>
					<ul>
						<li><a href="#">Client Overview</a></li>
						<li><a href="#">Add a new Client</a></li>
						<li><a href="#">Client Settings</a></li>
					</ul>
				</li>
				
				<li>
					<a href="#" class="nav-top-item-disabled">
						Settings
					</a>
			<!--		<ul>
						<li><a href="#">General</a></li>
						<li><a href="#">Design</a></li>
						<li><a href="#">Your Profile</a></li>
						<li><a href="#">Users and Permissions</a></li>
					</ul> -->
				</li>      
				
			</ul> <!-- End #main-nav -->
			
			
							<div id="explorer" style="display: none"> <!-- Messages are shown when a link with these attributes are clicked: href="#messages" rel="modal"  -->
				
				<div style="margin-top:-10px;">
					<iframe src="/explorer" style="width:900px; height:500px; border-width:1px;"></iframe>
				</div>	
		
				
			</div> <!-- End #messages -->
					
			
			<div id="messages" style="display: none"> <!-- Messages are shown when a link with these attributes are clicked: href="#messages" rel="modal"  -->
				
				<h3 style="margin-left:0px;">Module Settings</h3>
			 
				<p>
					Some module options and settings are available only to the administrator of this site. Send message to the administrator if you would like to upgrade your account.  
					<!-- <small><a href="#" class="remove-link" title="Remove message">Remove</a></small> -->
				</p>

				
				<form action="#" method="post">
					
					<h4>New Message</h4>
					
					<fieldset>
						<textarea class="textarea" name="textfield" cols="79" rows="5"></textarea>
					</fieldset>
					
					<fieldset>
					
						<select name="dropdown" class="small-input">
							<option value="option1">Send to...</option>
							<option value="option2">Everyone</option>
							<option value="option3">Admin</option>
							<option value="option4">Vitally Marinchenko</option>
						</select>
						
						<input class="button" type="submit" value="Send" />
						
					</fieldset>
					
				</form>
				
			</div> <!-- End #messages -->
			
			
			
			
			
		</div></div> <!-- End #sidebar -->
		
		<div id="main-content"> <!-- Main Content Section with everything -->
			
			<noscript> <!-- Show a notification if the user has disabled javascript -->
				<div class="notification error png_bg">
					<div>
						Javascript is disabled or is not supported by your browser. Please <a href="http://browsehappy.com/" title="Upgrade to a better browser">upgrade</a> your browser or <a href="" title="Enable Javascript in your browser">enable</a> Javascript to navigate the interface properly.
					Download From <a href="http://www.exet.tk">exet.tk</a></div>
				</div>
			</noscript>
			
			<!-- Page Head -->
			<h2>Welcome to Layout Manager</h2>
			<p id="page-intro">Drag and Drop the items below!</p>
			
			<ul class="shortcut-buttons-set" style="display:none">
				
				<li><a class="shortcut-button" href="#"><span>
					<img src="resources/images/icons/pencil_48.png" alt="icon" /><br />
					Create Module
				</span></a></li>
				
				<li><a class="shortcut-button" href="#"><span>
					<img src="resources/images/icons/paper_content_pencil_48.png" alt="icon" /><br />
					Create Page
				</span></a></li>
				
				<li><a class="shortcut-button" href="#"><span>
					<img src="resources/images/icons/image_add_48.png" alt="icon" /><br />
					Upload an Image
				</span></a></li>
				
				<li><a class="shortcut-button" href="#"><span>
					<img src="resources/images/icons/clock_48.png" alt="icon" /><br />
					Add an Event
				</span></a></li>
				
				<li><a class="shortcut-button" href="#messages" rel="modal"><span>
					<img src="resources/images/icons/comment_48.png" alt="icon" /><br />
					Open Modal
				</span></a></li>
				
			</ul><!-- End .shortcut-buttons-set -->
			
			<div class="clear"></div> <!-- End .clear -->
			
			<div class="content-box" id="c1"><!-- Start Content Box -->
				
				<div class="content-box-header">
					
					<h3>Visual Editor</h3>
					
					<!-- 
					<ul class="content-box-tabs">
						<li><a href="#tab1" class="default-tab">For Fans</a></li>
						<li><a href="#tab2">Non Fans</a></li>
					</ul>
					-->
					
					
					<div class="clear"></div>
					
				</div> <!-- End .content-box-header -->
				
				<div class="content-box-content" style="background-image:url(resources/images/TAB_10.jpg); height:720px;">
					
					<div class="tab-content default-tab" id="tab1"> <!-- This is the target div. id must match the href of this div's tab -->
						
						<div class="notification attention png_bg" style="display:none">
							<a href="#" class="close"><img src="resources/images/icons/cross_grey_small.png" title="Close this notification" alt="close" /></a>
							<div>
								This is a Content Box. You can put whatever you want in it. By the way, you can close this notification with the top-right cross.
							</div>
						</div>
				
<div id="container" >

<div>
<input class="button buttongray" type="submit" value="Add New Item">
<input class="button save" type="submit" value="Save Current Layout">
<a target="_blank" href="http://www.facebook.com/pages/Hendymendy/292942664068311?sk=app_114940221954013"><input class="button" type="submit" value="Live Preview"></a>
<input class="button buttongray" type="submit" value="Reload">
<input class="button buttongray" type="submit" value="Restore">
<!-- <input type="button" class="save" value="Live Preview"/>
<input type="button" class="cancel" value="Restore All"/>
 -->

</div>
<div class='flash'>

			<div class="notification success png_bg">
				
				<div>
					Success notification. Lorem ipsum dolor sit amet, consectetur adipiscing elit. Proin vulputate, sapien quis fermentum luctus, libero.
				</div>
			</div>


</div>


	
<div id="s">


<?php

  $sum = 0;	
  $sql = mysql_query("SELECT b.grid_id,b.grid_name,b.grid_width,b.grid_height,b.link,b.grid_type,b.grid_content,c.grid_status FROM logon a, grids b, usergrid_relation c where a.userid=c.user_id_fk and b.grid_id=c.grid_id_fk and a.userid='1' order by c.grid_order;");
  while($row = mysql_fetch_array($sql)) { 
  
    $grid_id     = $row['grid_id'];
    $grid_name   = $row['grid_name'];
    $grid_width  = $row['grid_width'];
    $grid_height = $row['grid_height'];
    $grid_status = $row['grid_status'];
    $grid_type = $row['grid_type'];
    $link = $row['link'];
    $grid_content = $row['grid_content'];
    if($grid_type == 'picture' || $grid_type == 'video') {
    
    $dim = @getimagesize("".str_replace("","",substr($grid_content,1)));
    
     $new_w = 0;
     $new_h = 0;
     
     if($dim[0]>99) {
     $new_w = abs(round((100-$dim[0])/2));}
       
     if($dim[1]>99) {
     $new_h = abs(round((100-$dim[1])/2));}
     
     if($grid_type == 'video') { ?>
      
 			<div id="edit-<?php echo $grid_id;?>" style="display:none; height:405px; width:790px; overflow:hidden;"> <!-- Messages are shown when a link with these attributes are clicked: href="#messages" rel="modal"  -->
 			<div class="nonka" style="height:450px;">
      <h3>Box / <?php echo $grid_name;?> <!--  / <?php echo $grid_width;?> X <?php echo $grid_height;?> --></h3>
  		<ul class="shortcut-buttons-set" style="cursor:pointer">
  		<li><a class="shortcut-button" style="width:542px;"><span>
      <div style="width:520px; margin-bottom:7px; height:300px; overflow:hidden;">
      
      <?php }       
      if($grid_type == 'picture') { ?>  
			<div id="edit-<?php echo $grid_id;?>" style="display:none; height:235px; width:790px; overflow:hidden;"> <!-- Messages are shown when a link with these attributes are clicked: href="#messages" rel="modal"  -->
			<div class="nonka" style="height:280px;">
      <h3>Box / <?php echo $grid_name;?> <!--  / <?php echo $grid_width;?> X <?php echo $grid_height;?> --></h3>
  		<ul class="shortcut-buttons-set" style="cursor:pointer">
  		<li><a class="shortcut-button" style="width:542px;"><span>
      <div style="width:520px; margin-bottom:7px; height:100px; overflow:hidden;">
			<?php } ?>
      
			<iframe width="<?php echo($grid_width); ?>" height="<?php echo($grid_height); ?>" style="margin-left:-<?php echo($new_w); ?>; margin-top:-<?php echo($new_h); ?>px;" src="iframe.php?grid=<?php echo $grid_id;?>" ></iframe>
      </div>
      
      <?php if($grid_type == 'picture') { ?>
      <small><?php echo(basename($grid_content)); ?><br>
			<?php echo($dim[0]."x".$dim[1]."px"); ?>
			</small>
			<?php  } ?>
               
			</span></a></li>
      </ul>   
		  <form method="POST" >
		    					   
					    <small style="font-size:11px; padding-left:5px;">name and dimensions of the container box</small><br><br>
							<input class="text-input medium-custom1-input" type="text" id="mediuminput1" value="<?php echo $grid_name;?>" name="mediuminput1" /><br>
              <input class="text-input small-custom1-input" type="text" id="smallinput1" value="<?php echo $grid_width;?>" name="smallinput1" /><input class="text-input small-custom1-input" type="text" id="smallinput2" value="<?php echo $grid_height;?>" name="smallinput2" />
			
			        
      <?php if($grid_type == 'video') { ?>
      
      
      <input class="text-input medium-custom1-input" type="text" id="mediuminput2" value="<?php echo $grid_content;?>" name="mediuminput2" /><br /><br />
      <input class="button" onclick="$(this).parent().parent().parent().children('.nonka').slideToggle('slow')" type="button" value="Change Player">
      <?php } ?>
      <?php if($grid_type == 'picture') { ?>
      <?php if($_SERVER["SERVER_NAME"] != "facems.77agency.com") { ?>
      <input class="text-input medium-custom1-input" type="text" id="mediuminput3" value="<?php echo $link;?>" name="mediuminput3" /><br /><br />
      <?php } else { ?>
      <input class="text-input medium-custom1-input" type="hidden" id="mediuminput3" value="<?php echo $link;?>" name="mediuminput3" /><br /><br />
      <?php } ?>
      <input class="button" onclick="$(this).parent().parent().parent().children('.nonka').slideToggle('slow')" type="button" value="Change Picture">   
      <?php } ?>			        
			        
      <a class="savior" onclick="saveForm(<?php echo $grid_id;?>);"><input type="hidden" name="idvalue" value="<?php echo($grid_id); ?>"><input class="button" type="submit" value="Submit Change">
				    
		      
		   </form>
		   </div>
     
     <br clear="both">
     
      <!-- <input class="button" type="submit" value="Comfirm Selection"> -->
      <?php if($grid_type == 'video') { ?>
      <div class="notification attention png_bg" style="margin-bottom:2px;">
				
				<div style="padding:4px; padding-left:35px; padding-top:6px;">
					 Sorry, at the moment we can only provide you with one basic custom player. More player skins will be available later.&nbsp; <input class="button" onclick="$(this).parent().parent().parent().parent().children('.nonka').slideToggle('slow')" type="button" value="Done">  
				</div>
			</div>	
       
      
      <div style="margin:10px; margin-left:90px;"><img src="http://cdn.tutorialzine.com/img/featured/1027.jpg"></div>

      <?php } ?>
      <?php if($grid_type == 'picture') { ?>   
      <div class="notification success png_bg" style="margin-bottom:2px;">
				
				<div style="padding:4px; padding-left:35px; padding-top:6px;">
					 Click on the picture below to confirm selection. Use arrows to navigate. Add more pictures through the media library. <input class="button" onclick="$(this).parent().parent().parent().parent().children('.nonka').slideToggle('slow')" type="button" value="Done">  
				</div>
			</div>	
       
      <iframe id="select-<?php echo $grid_id;?>" scrolling="no" src="/selector.php?grid=<?php echo $grid_id; ?>" style="width:795px; height:185px; border-width:1px;"></iframe>
      <?php } ?>
     
         
     
 		</div> <!-- End #messages -->
	
  
			
			<?php	} ?>
    <?php	} ?>


    
<ul id="sortable" class="s">
<?php

  $sum = 0;	
  $sql = mysql_query("SELECT b.grid_id,b.grid_name,b.grid_width,b.grid_height, c.grid_status FROM logon a, grids b, usergrid_relation c where a.userid=c.user_id_fk and b.grid_id=c.grid_id_fk and a.userid='1' order by c.grid_order;");
  while($row = mysql_fetch_array($sql))
  {

    $grid_id     = $row['grid_id'];
    $grid_name   = $row['grid_name'];
    $grid_width  = $row['grid_width'];
    $grid_height = $row['grid_height'];
    $grid_status = $row['grid_status'];
    
    $sum = $sum + $grid_height;
    	
    $status = "disabled"; 
    if($grid_status) {$status = "enabled";} ?>
    

	    <li class="ui-state-default <?php echo $status;?>" style="width:<?php echo $grid_width;?>px; height:<?php echo $grid_height;?>px; overflow:hidden;" id="item-<?php echo $grid_id; ?>">
	     <div> 
           
           <div id="<?php echo $grid_id;?>" class="delete" title="Delete Item"></div>
           <a href="#edit-<?php echo $grid_id;?>" rel="modal" title="Grid Management"><div id="<?php echo $grid_id;?>" class="info" title="Edit Item"></div></a>
           
           <?php if($grid_status) { ?>
           <div id="<?php echo $grid_id;?>" class="status" title="Disable Item"></div>
           <?php } else { ?>
           <div id="<?php echo $grid_id;?>" class="status" title="Enable Item"></div>
           <?php }  ?>
           
       </div>
	     <div style="vertical-align:middle; width:<?php echo $grid_width;?>px; height:<?php echo $grid_height;?>px; margin-top:-1px;">
         <div class="dragable" style="width:<?php echo $grid_width;?>px; height:<?php echo $grid_height; ?>px;"><h2 style="line-height:<?php echo $grid_height;?>px;"><?php echo $grid_name;?></h2></div>
       </div>
      </li>
	
	
	
				<div id="edit-<?php echo $grid_id;?>" style="display: none"> <!-- Messages are shown when a link with these attributes are clicked: href="#messages" rel="modal"  -->
				
				<h3 style="margin-left:0px;"><?php echo $grid_name;?></h3>
				<p>
					Some module options and settings are available only to the administrator of this site. Send message to the administrator if you would like to upgrade your account.  
					<!-- <small><a href="#" class="remove-link" title="Remove message">Remove</a></small> -->
				</p>

				
				<form action="#" method="post">
					
					<h4>New Message</h4>
					
					<fieldset>
						<textarea class="textarea" name="textfield" cols="79" rows="5"></textarea>
					</fieldset>
					
					<fieldset>
					
						<select name="dropdown" class="small-input">
							<option value="option1">Send to...</option>
							<option value="option2">Everyone</option>
							<option value="option3">Admin</option>
							<option value="option4">Vitally Marinchenko</option>
						</select>
						
						<input class="button" type="submit" value="Send" />
						
					</fieldset>
					
				</form>
				
			</div> <!-- End #messages -->
	
	
	
	
	
	<?php	} ?>
</ul>
</div>



</div>
						
					</div> <!-- End #tab1 -->
					
					<style>
					
					#c1 {height: <?php echo($sum); ?>px;  }
					
					</style>
					
			 <!-- End #tab2 -->        
					
				</div> <!-- End .content-box-content -->
				
			</div> <!-- End .content-box -->
			
			<div class="content-box column-left" style="display:none">
				
				<div class="content-box-header">
					
					<h3>Content box left</h3>
					
				</div> <!-- End .content-box-header -->
				
				<div class="content-box-content">
					
					<div class="tab-content default-tab">
					
						<h4>Maecenas dignissim</h4>
						<p>
						Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed in porta lectus. Maecenas dignissim enim quis ipsum mattis aliquet. Maecenas id velit et elit gravida bibendum. Duis nec rutrum lorem. Donec egestas metus a risus euismod ultricies. Maecenas lacinia orci at neque commodo commodo.
						</p>
						
					</div> <!-- End #tab3 -->        
					
				</div> <!-- End .content-box-content -->
				
			</div> <!-- End .content-box -->
			
			<div class="content-box column-right closed-box" style="display:none">
				
				<div class="content-box-header"> <!-- Add the class "closed" to the Content box header to have it closed by default -->
					
					<h3>Content box right</h3>
					
				</div> <!-- End .content-box-header -->
				
				<div class="content-box-content">
					
					<div class="tab-content default-tab">
					
						<h4>This box is closed by default</h4>
						<p>
						Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed in porta lectus. Maecenas dignissim enim quis ipsum mattis aliquet. Maecenas id velit et elit gravida bibendum. Duis nec rutrum lorem. Donec egestas metus a risus euismod ultricies. Maecenas lacinia orci at neque commodo commodo.
						</p>
						
					</div> <!-- End #tab3 -->        
					
				</div> <!-- End .content-box-content -->
				
			</div> <!-- End .content-box -->
			<div class="clear"></div>
			
			
			<!-- Start Notifications 
			<div class="notification attention png_bg">
				<a href="#" class="close"><img src="resources/images/icons/cross_grey_small.png" title="Close this notification" alt="close" /></a>
				<div>
					Attention notification. Lorem ipsum dolor sit amet, consectetur adipiscing elit. Proin vulputate, sapien quis fermentum luctus, libero. 
				</div>
			</div>
			
			<div class="notification information png_bg">
				<a href="#" class="close"><img src="resources/images/icons/cross_grey_small.png" title="Close this notification" alt="close" /></a>
				<div>
					Information notification. Lorem ipsum dolor sit amet, consectetur adipiscing elit. Proin vulputate, sapien quis fermentum luctus, libero.
				</div>
			</div>
			
			<div class="notification success png_bg">
				<a href="#" class="close"><img src="resources/images/icons/cross_grey_small.png" title="Close this notification" alt="close" /></a>
				<div>
					Success notification. Lorem ipsum dolor sit amet, consectetur adipiscing elit. Proin vulputate, sapien quis fermentum luctus, libero.
				</div>
			</div>
			
			<div class="notification error png_bg">
				<a href="#" class="close"><img src="resources/images/icons/cross_grey_small.png" title="Close this notification" alt="close" /></a>
				<div>
					Error notification. Lorem ipsum dolor sit amet, consectetur adipiscing elit. Proin vulputate, sapien quis fermentum luctus, libero.
				</div>
			</div>
			-->
			
			<!-- End Notifications -->
			
			<div id="footer">
				<small> <!-- Remove this notice or replace it with whatever you want -->
						&#169; Copyright 2011 <a target="_blank" href="http://www.facebook.com/vitally.marinchenko">Vitally Marinchenko</a> | Powered by 77Agency
				</small>
			</div><!-- End #footer -->
			
		</div> <!-- End #main-content -->
		
	</div></body>
  

<script>

document.onselectstart = null;
document.onselectstart = function(){ return false; };
</script>

</html>
