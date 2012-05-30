<?PHP

ini_set('display_errors',1);
error_reporting(E_ALL);


  // filetypes to display
  $imagetypes = array("image/jpeg", "image/gif", "image/png");
?>
	<!-- include the Tools -->
	<!-- include the Tools -->
	<script src="http://sorgalla.com/projects/jcarousel/lib/jquery-1.4.2.min.js"></script>
  <script type="text/javascript" src="http://sorgalla.com/projects/jcarousel/lib/jquery.jcarousel.min.js"></script>


<style>

.jcarousel-skin-tango .jcarousel-container-horizontal {
    width: 650px;
    padding-top:0px;
    padding-left:60px;
    padding-right:50px;
}


.jcarousel-skin-tango .jcarousel-clip {
    overflow: hidden;
}

.jcarousel-skin-tango .jcarousel-clip-horizontal {
    width:  650px;
    height: 185px;
}



.jcarousel-skin-tango .jcarousel-item {
    width:  120px;
    height: 185px;
}

.jcarousel-skin-tango .jcarousel-item-horizontal {
	margin-left: 0;
  margin-right: 10px;
}

.jcarousel-skin-tango .jcarousel-direction-rtl .jcarousel-item-horizontal {
	margin-left: 10px;
  margin-right: 0;
}


.jcarousel-skin-tango .jcarousel-item-placeholder {
    background: #fff;
    color: #000;
}

/**
 *  Horizontal Buttons
 */
.jcarousel-skin-tango .jcarousel-next-horizontal {
    position: absolute;
    top: 43px;
    right: 5px;
    width: 37px;
    height: 56px;
    cursor: pointer;
    background: transparent url(resources/images/ar-right.png);
}



.jcarousel-skin-tango .jcarousel-next-horizontal:hover,
.jcarousel-skin-tango .jcarousel-next-horizontal:focus {
    background-position: 2px 0;
}



.jcarousel-skin-tango .jcarousel-prev-horizontal {
    position: absolute;
    top: 43px;
    left: 5px;
    width: 37px;
    height: 56px;
    cursor: pointer;
    background: transparent url(resources/images/ar-left.png);
}



.jcarousel-skin-tango .jcarousel-prev-horizontal:hover, 
.jcarousel-skin-tango .jcarousel-prev-horizontal:focus {
    background-position: -2px 0;
}

  .photo {
  
    cursor: pointer;
    float: left;
    margin: 0.5em;
      font-family: Helvetica, Arial, sans-serif;
    color: #222;
    font-size: 11px;
    
  }
 .photo a {color:#579125;}

.green {background:#D5FFCE  !important;}


.shortcut-button {
                border: 1px solid #ccc;
                background: #f7f7f7 url('../images/shortcut-button-bg.gif') top left no-repeat;
                display: block;
                width: 120px;
                margin: 0 0 20px 0;
                
                    margin: 0.5em;
      font-family: Helvetica, Arial, sans-serif;
    color: #222;
    font-size: 10px ;
    text-decoration:none;
                }

.shortcut-button span {
                border: 1px solid #fff;
                display:block;
                padding: 15px 10px 15px 10px;
                text-align: center;
                color: #555;
                
                line-height: 1.3em;
                }




.shortcut-button span img {
                margin-bottom: 10px;
                }

.shortcut-button:hover {
                background: #fff;
				
                }
				
.shortcut-button span:hover {
				color: #57a000; 
                }

ul.shortcut-buttons-set li {
                float: left;
                margin: 0 15px 0 0;
				padding: 0 !important;
				background: 0;
                }
h1, h2, h3, h4, h5, h6 {
                font-family: Helvetica, Arial, sans-serif;
                color: #222;
                font-weight: bold;
                
                }

h3            { font-size: 17px; padding: 0 0 10px 0; }

</style>
	
<?PHP
  // Original PHP code by Chirp Internet: www.chirp.com.au
  // Please acknowledge use of this code by including this header.

  function getImages($dir)
  {
    global $imagetypes;

    // array to hold return value
    $retval = array();

    // add trailing slash if missing
    if(substr($dir, -1) != "/") $dir .= "/";

    // full server path to directory
    $fulldir = "$dir";

    $d = @dir($fulldir) or die("getImages: Failed opening directory $dir for reading");
    while(false !== ($entry = $d->read())) {
      // skip hidden files
      if($entry[0] == ".") continue;

      // check for image files
      $f = escapeshellarg("$fulldir$entry");
      $mimetype = trim('$f');
      foreach($imagetypes as $valid_type) {
        //if(preg_match("@^{$valid_type}@", $mimetype)) {
          $retval[] = array(
           'file' => "/$dir$entry",
           'size' => getimagesize("$fulldir$entry")
          );
          break;
        //}
      }
    }
    $d->close();

    return $retval;
  }
?>


		<link rel="stylesheet" href="resources/css/invalid.css" type="text/css" media="screen" />	

 
 <ul id="mycarousel" class="jcarousel-skin-tango shortcut-buttons-set">
<?php

$a = @$_GET['grid'];

if(!$a) {$_GET['grid'] = 1;}

$a = $_GET['grid'];
  
  // fetch image details
  $images = getImages("explorer/files/pictures");
  
  // display on page
  foreach($images as $img) { 
  
   ?> 
   
   <li><a class="shortcut-button"><span>

				<?php
				
           $new_w = 0;
           $new_h = 0;
          
            if($img['size'][0]>99) {
            $new_w = abs(round((100-$img['size'][0])/2));}
    
            if($img['size'][1]>99) {
            $new_h = abs(round((100-$img['size'][1])/2));}
    
            echo "<div style='width:100px; margin-bottom:7px; height:100px; overflow:hidden;'><img style='margin-left:-".$new_w."px; margin-top:-".$new_h."px;' src=\"{$img['file']}\" ></div>";
            //echo "<a target='_blank' href=\"{$img['file']}\">",basename($img['file']),"</a>";
            echo "".basename($img['file']."");
            echo "<br>\n";
            echo "({$img['size'][0]}x{$img['size'][1]} pixels)";    
         ?>
         
			</span></a></li>
   
   <?php
   
  }
  
?> </ul>

<style>

.shortcut-button {
border: 1px solid #CCC;
background: #999 url('resources/images/shortcut-button-bg.gif') top left no-repeat;
background-repeat-x: no-repeat;
background-repeat-y: no-repeat;
background-attachment: initial;
background-position-x: 0%;
background-position-y: 0%;
background-origin: initial;
background-clip: initial;
background-color: #F7F7F7;
display: block;
width: 120px;
margin: 0 0 20px 0;
cursor:pointer;
}
</style>


<script type="text/javascript">

  var selector = "";
  var grid = <?php echo($_GET['grid']); ?>;


        $("ul#mycarousel li").click(function () {
        //$(this).attr("style","background-color:#eeeeee");
        if(selector) {selector.removeClass("green");}
        //alert($(this).html())
         selector = $("a",this);
         
        $("a",this).addClass("green");
        
          $.ajax({
            type: "POST",
            url: "ajax.php",
            data: 'grid='+grid+'&image=' + $("img",this).attr('src'),
            
            success: function(data) {}
          });
        
        //alert();
        //$("ul#mycarousel li").attr("style","background-color:#eeeeee");

    });
     
$(document).ready(function() {
    $('#mycarousel').jcarousel();
});

</script>
<?php //echo($_GET['grid']); ?>






